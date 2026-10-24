<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABP_Account_Security' ) ) {
		/**
		 * My Account security layer.
		 *
		 * 1. Email verification gate - accounts created through the My Account
		 *    registration form stay "pending" until the visitor confirms the
		 *    email address. Unverified accounts cannot sign in, stay out of the
		 *    default Users list, and do not trigger an admin new-user email
		 *    until verification succeeds.
		 * 2. Anti-bot protection for the login and the registration form -
		 *    honeypot field, signed timestamp, dedicated nonce, user agent
		 *    screening and transient based rate limiting.
		 */
		class ABP_Account_Security {
			const VERSION            = '1.1.0';
			const NONCE_LOGIN        = 'abp_login_secure';
			const NONCE_REGISTER     = 'abp_register_secure';
			const NONCE_RESEND       = 'abp_resend_verification';
			const FIELD_HP           = 'abp_hp_email';
			const FIELD_TS           = 'abp_form_ts';
			const FIELD_SIG          = 'abp_form_sig';
			const FIELD_NONCE        = 'abp_secure_nonce';
			const META_PENDING       = '_edd_pending_verification';
			/** Set on My Account registrations; cleared after admin is notified. */
			const META_NOTIFY_ADMIN  = '_abp_notify_admin_on_verify';
			const OPTION_MIGRATED    = 'abp_account_security_migrated';
			const OPTION_VERSION     = 'abp_account_security_version';
			const TRANSIENT_ACCOUNT  = 'abp_my_account_url';
			const COOKIE_NOTICE      = 'abp_verify_notice';
			const TRANSIENT_NOTICE   = 'abp_verify_notice_';
			/** Lifetime (seconds) of the "verification email sent" notice. */
			const NOTICE_LIFETIME    = 3600;

			/** Minimum seconds between rendering and submitting a form. */
			const MIN_SECONDS        = 2;
			/** Maximum age (seconds) of a rendered form before it expires. */
			const MAX_FORM_AGE       = 21600;

			const LOGIN_MAX_IP       = 10;
			const LOGIN_MAX_ACCOUNT  = 5;
			const LOGIN_WINDOW       = 900;
			const REGISTER_MAX       = 5;
			const REGISTER_WINDOW    = 3600;
			const RESEND_MAX_IP      = 5;
			const RESEND_MAX_MAIL    = 3;
			const RESEND_WINDOW      = 3600;

			/**
			 * Result of the last verification mail attempt.
			 *
			 * @var bool
			 */
			private $verification_mail_sent = false;

			public function __construct() {
				add_action( 'init', array( $this, 'swap_login_handler' ), 5 );
				add_action( 'init', array( $this, 'maybe_migrate' ), 20 );

				add_action( 'edd_login_fields_before', array( $this, 'render_login_fields' ) );
				add_action( 'edd_register_form_fields_before_submit', array( $this, 'render_register_fields' ) );
				add_action( 'edd_register_fields_after', array( $this, 'render_checkout_fields' ) );

				add_action( 'edd_process_register_form', array( $this, 'guard_registration' ), 1 );
				add_filter( 'edd_insert_user_args', array( $this, 'guard_checkout_registration' ), 10, 2 );
				add_filter( 'wp_new_user_notification_email_admin', array( $this, 'suppress_admin_new_user_email' ) );
				add_filter( 'wp_send_new_user_notification_to_admin', array( $this, 'maybe_block_admin_notification' ), 10, 2 );

				add_action( 'user_register', array( $this, 'flag_new_registration' ), 11 );
				add_action( 'edd_insert_user', array( $this, 'finalize_registration' ), 10, 1 );
				add_action( 'edd_post_set_user_to_active', array( $this, 'on_user_verified' ), 10, 2 );
				add_action( 'template_redirect', array( __CLASS__, 'maybe_clear_notice_after_verification' ), 20 );

				add_filter( 'edd_user_verification_base_url', array( $this, 'verification_base_url' ) );
				add_filter( 'wp_authenticate_user', array( $this, 'block_unverified_login' ), 10, 2 );
				add_action( 'wp_login_failed', array( $this, 'record_failed_login' ), 10, 2 );
				add_action( 'wp_login', array( $this, 'clear_login_rate_limits' ), 10, 2 );

				add_filter( 'users_list_table_query_args', array( $this, 'filter_users_list_query' ) );
				add_filter( 'views_users', array( $this, 'filter_user_views' ) );

				add_action( 'wp_ajax_abp_resend_verification', array( $this, 'ajax_resend_verification' ) );
				add_action( 'wp_ajax_nopriv_abp_resend_verification', array( $this, 'ajax_resend_verification' ) );
			}

			/* -----------------------------------------------------------------
			 * Form fields
			 * -------------------------------------------------------------- */
			public function render_login_fields(): void {
				$this->render_security_fields( 'login', self::NONCE_LOGIN );
			}

			public function render_register_fields(): void {
				$this->render_security_fields( 'register', self::NONCE_REGISTER );
			}

			/**
			 * Security fields for the EDD checkout registration form.
			 *
			 * The checkout form is rendered through a different template than
			 * [edd_register] and does not fire
			 * `edd_register_form_fields_before_submit`, so the fields have to be
			 * printed on `edd_register_fields_after` as well.
			 */
			public function render_checkout_fields(): void {
				if ( is_user_logged_in() ) {
					return;
				}
				$this->render_security_fields( 'register', self::NONCE_REGISTER );
			}

			private function render_security_fields( string $context, string $nonce_action ): void {
				$ts = time();
				?>
				<div class="abp-hp-wrap" aria-hidden="true">
					<label for="abp_hp_email">Email</label>
					<input type="text" id="abp_hp_email" name="<?php echo esc_attr( self::FIELD_HP ); ?>" value="" tabindex="-1" autocomplete="off" />
				</div>
				<input type="hidden" name="<?php echo esc_attr( self::FIELD_TS ); ?>" value="<?php echo esc_attr( (string) $ts ); ?>" />
				<input type="hidden" name="<?php echo esc_attr( self::FIELD_SIG ); ?>" value="<?php echo esc_attr( $this->sign( $ts, $context ) ); ?>" />
				<input type="hidden" name="<?php echo esc_attr( self::FIELD_NONCE ); ?>" value="<?php echo esc_attr( wp_create_nonce( $nonce_action ) ); ?>" />
				<?php
			}

			/* -----------------------------------------------------------------
			 * Shared validation
			 * -------------------------------------------------------------- */
			private function validate_security( string $context, string $nonce_action, array $data ): string {
				$honeypot = isset( $data[ self::FIELD_HP ] ) ? trim( (string) wp_unslash( $data[ self::FIELD_HP ] ) ) : '';
				if ( '' !== $honeypot ) {
					return __( 'Security validation failed. Please refresh the page and try again.', 'abp-plugin' );
				}

				$ts  = isset( $data[ self::FIELD_TS ] ) ? absint( $data[ self::FIELD_TS ] ) : 0;
				$sig = isset( $data[ self::FIELD_SIG ] ) ? (string) wp_unslash( $data[ self::FIELD_SIG ] ) : '';
				if ( $ts <= 0 || '' === $sig || ! hash_equals( $this->sign( $ts, $context ), $sig ) ) {
					return __( 'Your session has expired. Please refresh the page and try again.', 'abp-plugin' );
				}

				$elapsed = time() - $ts;
				if ( $elapsed < self::MIN_SECONDS ) {
					return __( 'Please take a moment to complete the form before submitting.', 'abp-plugin' );
				}
				if ( $elapsed > self::MAX_FORM_AGE ) {
					return __( 'Your session has expired. Please refresh the page and try again.', 'abp-plugin' );
				}

				$nonce = isset( $data[ self::FIELD_NONCE ] ) ? (string) wp_unslash( $data[ self::FIELD_NONCE ] ) : '';
				if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
					return __( 'Security token expired. Please refresh the page and try again.', 'abp-plugin' );
				}

				if ( $this->is_bot_request() ) {
					return __( 'Your request was blocked by our security system.', 'abp-plugin' );
				}

				return '';
			}

			private function sign( int $ts, string $context ): string {
				return hash_hmac( 'sha256', $ts . '|' . $context, wp_salt( 'auth' ) );
			}

			private function is_bot_request(): bool {
				$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( trim( (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
				if ( '' === $ua ) {
					return true;
				}

				$blocked = (array) apply_filters(
					'abp_blocked_user_agents',
					array(
						'bot',
						'spider',
						'crawler',
						'slurp',
						'curl/',
						'wget',
						'python-requests',
						'python-urllib',
						'httpclient',
						'scrapy',
						'phantomjs',
						'headlesschrome',
						'lwp-',
						'okhttp',
						'libwww-perl',
						'nikto',
						'sqlmap',
						'masscan',
						'nmap',
						'go-http-client',
					)
				);

				foreach ( $blocked as $needle ) {
					$needle = strtolower( (string) $needle );
					if ( '' !== $needle && false !== strpos( $ua, $needle ) ) {
						return true;
					}
				}

				return false;
			}

			/* -----------------------------------------------------------------
			 * Rate limiting
			 * -------------------------------------------------------------- */
			private function rate_key( string $scope, string $value ): string {
				return 'abp_rl_' . $scope . '_' . substr( md5( strtolower( trim( $value ) ) ), 0, 20 );
			}

			private function rate_count( string $scope, string $value ): int {
				return (int) get_transient( $this->rate_key( $scope, $value ) );
			}

			private function rate_increment( string $scope, string $value, int $window ): void {
				$key   = $this->rate_key( $scope, $value );
				$count = (int) get_transient( $key ) + 1;
				set_transient( $key, $count, $window );
			}

			private function rate_reached( string $scope, string $value, int $max ): bool {
				return $this->rate_count( $scope, $value ) >= $max;
			}

			private function client_ip(): string {
				$ip = '';
				if ( function_exists( 'edd_get_ip' ) ) {
					$ip = (string) edd_get_ip();
				}
				if ( '' === $ip && isset( $_SERVER['REMOTE_ADDR'] ) ) {
					$ip = (string) wp_unslash( $_SERVER['REMOTE_ADDR'] );
				}
				$ip = trim( $ip );

				return '' !== $ip ? $ip : 'unknown';
			}

			/* -----------------------------------------------------------------
			 * Login
			 * -------------------------------------------------------------- */
			public function swap_login_handler(): void {
				if ( ! function_exists( 'edd_process_login_form' ) ) {
					return;
				}
				remove_action( 'edd_user_login', 'edd_process_login_form' );
				add_action( 'edd_user_login', array( $this, 'process_login' ), 10, 1 );
			}

			public function process_login( $data ): void {
				$data = is_array( $data ) ? $data : array();

				$error = $this->validate_security( 'login', self::NONCE_LOGIN, $data );
				if ( '' !== $error ) {
					edd_set_error( 'abp_security_error', $error );
					return;
				}

				$ip = $this->client_ip();
				if ( $this->rate_reached( 'login_ip', $ip, self::LOGIN_MAX_IP ) ) {
					edd_set_error( 'abp_login_locked', __( 'Too many sign-in attempts from this device. Please try again in 15 minutes.', 'abp-plugin' ) );
					return;
				}

				$login = isset( $data['edd_user_login'] ) ? trim( (string) wp_unslash( $data['edd_user_login'] ) ) : '';
				if ( '' !== $login && $this->rate_reached( 'login_user', $login, self::LOGIN_MAX_ACCOUNT ) ) {
					edd_set_error( 'abp_login_locked', __( 'Too many sign-in attempts for this account. Please try again in 15 minutes or reset your password.', 'abp-plugin' ) );
					return;
				}

				$user = $this->find_user( $login );
				if ( $user && $this->is_pending( $user->ID ) ) {
					edd_set_error( 'abp_email_unverified', $this->unverified_message() );
					return;
				}

				edd_process_login_form( $data );
			}

			public function record_failed_login( $username, $error = null ): void {
				if ( $error instanceof WP_Error && 'abp_email_unverified' === $error->get_error_code() ) {
					return;
				}
				if ( $this->is_registration_request() ) {
					return;
				}

				$this->rate_increment( 'login_ip', $this->client_ip(), self::LOGIN_WINDOW );

				$username = trim( (string) $username );
				if ( '' !== $username ) {
					$this->rate_increment( 'login_user', $username, self::LOGIN_WINDOW );
				}
			}

			/**
			 * A successful sign-in clears the counters so a real customer is never
			 * punished for earlier typos.
			 */
			public function clear_login_rate_limits( $user_login, $user = null ): void {
				$login = trim( (string) $user_login );
				if ( '' !== $login ) {
					delete_transient( $this->rate_key( 'login_user', $login ) );
				}
				delete_transient( $this->rate_key( 'login_ip', $this->client_ip() ) );
			}

			/**
			 * Backstop for wp-login.php and any other authentication entry point.
			 */
			public function block_unverified_login( $user, $password = '' ) {
				if ( ! ( $user instanceof WP_User ) ) {
					return $user;
				}
				if ( ! $this->is_pending( $user->ID ) ) {
					return $user;
				}

				return new WP_Error( 'abp_email_unverified', $this->unverified_message() );
			}

			private function unverified_message(): string {
				return __( 'Your email address is not verified yet. Please check your inbox for the verification link, or request a new one below.', 'abp-plugin' );
			}

			/* -----------------------------------------------------------------
			 * Registration
			 * -------------------------------------------------------------- */
			public function guard_registration(): void {
				$data  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$error = $this->validate_security( 'register', self::NONCE_REGISTER, $data );
				if ( '' !== $error ) {
					edd_set_error( 'abp_security_error', $error );
					return;
				}

				$ip = $this->client_ip();
				if ( $this->rate_reached( 'register_ip', $ip, self::REGISTER_MAX ) ) {
					edd_set_error( 'abp_rate_limited', __( 'Too many accounts have been created from this device. Please try again later.', 'abp-plugin' ) );
					return;
				}

				$email = isset( $data['edd_user_email'] ) ? sanitize_email( (string) $data['edd_user_email'] ) : '';
				if ( $this->is_disposable_email( $email ) ) {
					edd_set_error( 'abp_disposable_email', $this->disposable_email_message() );
					return;
				}

				$password = isset( $data['edd_user_pass'] ) ? (string) $data['edd_user_pass'] : '';
				if ( strlen( $password ) < 8 ) {
					edd_set_error( 'abp_password_weak', __( 'Your password must be at least 8 characters long.', 'abp-plugin' ) );
					return;
				}
				if ( ! preg_match( '/[A-Za-z]/', $password ) || ! preg_match( '/[0-9]/', $password ) ) {
					edd_set_error( 'abp_password_weak', __( 'Your password must contain at least one letter and one number.', 'abp-plugin' ) );
					return;
				}
			}

			/**
			 * Anti-bot gate for the checkout registration form.
			 *
			 * EDD registers checkout customers through
			 * `edd_register_and_login_new_user()` directly, which bypasses the
			 * `edd_process_register_form` action entirely. The user arguments are
			 * therefore filtered right before `wp_insert_user()` and an invalid
			 * login is forced when the request looks automated, so no account is
			 * ever created.
			 *
			 * @param array $user_args Prepared arguments for wp_insert_user().
			 * @param array $user_data Raw data provided by the checkout form.
			 * @return array
			 */
			public function guard_checkout_registration( $user_args, $user_data = array() ) {
				if ( ! is_array( $user_args ) || ! $this->is_checkout_registration() ) {
					return $user_args;
				}

				$data  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$error = $this->validate_security( 'register', self::NONCE_REGISTER, $data );

				if ( '' === $error ) {
					$email = isset( $data['edd_email'] ) ? sanitize_email( (string) $data['edd_email'] ) : '';
					if ( '' === $email && isset( $user_args['user_email'] ) ) {
						$email = sanitize_email( (string) $user_args['user_email'] );
					}
					if ( $this->is_disposable_email( $email ) ) {
						$error = $this->disposable_email_message();
					}
				}

				if ( '' === $error && $this->rate_reached( 'register_ip', $this->client_ip(), self::REGISTER_MAX ) ) {
					$error = __( 'Too many accounts have been created from this device. Please try again later.', 'abp-plugin' );
				}

				if ( '' !== $error ) {
					edd_set_error( 'abp_security_error', $error );
					// An empty login makes wp_insert_user() fail, which stops
					// edd_register_and_login_new_user() before any user meta or
					// customer record is written.
					$user_args['user_login'] = '';
					$user_args['user_email'] = '';
				}

				return $user_args;
			}

			/**
			 * Whether the current request is an EDD checkout registration.
			 */
			private function is_checkout_registration(): bool {
				$var = isset( $_POST['edd-purchase-var'] ) ? sanitize_key( wp_unslash( $_POST['edd-purchase-var'] ) ) : '';

				return 'needs-to-register' === $var;
			}

			private function disposable_email_message(): string {
				return __( 'Please use a permanent email address. Disposable or temporary email providers are not allowed.', 'abp-plugin' );
			}

			/**
			 * Reject known disposable / throw-away email domains.
			 *
			 * Domains can be extended with the `abp_blocked_email_domains` filter.
			 */
			private function is_disposable_email( string $email ): bool {
				$email = strtolower( trim( $email ) );
				if ( '' === $email || false === strpos( $email, '@' ) ) {
					return false;
				}

				$domain = substr( $email, strpos( $email, '@' ) + 1 );
				$domain = preg_replace( '/^www\./', '', $domain );
				if ( '' === $domain ) {
					return false;
				}

				$blocked = (array) apply_filters(
					'abp_blocked_email_domains',
					array(
						'10minutemail.com',
						'20minutemail.com',
						'blogbliss.site',
						'blogs.pics',
						'carshop.work',
						'cloudns.asia',
						'dispostable.com',
						'digimail.info',
						'emailondeck.com',
						'fakeinbox.com',
						'getnada.com',
						'guerrillamail.com',
						'guerrillamail.net',
						'guerrillamail.org',
						'inboxkiller.com',
						'maildrop.cc',
						'mailinator.com',
						'mailnesia.com',
						'maptech.online',
						'masonic.live',
						'mintemail.com',
						'moakt.com',
						'mytemp.email',
						'pokemonica.online',
						'pos.coupons',
						'proton.rest',
						'sancto.xyz',
						'searchmail.club',
						'sharklasers.com',
						'spam4.me',
						'tangos.click',
						'temp-mail.org',
						'tempmail.com',
						'tempmail.net',
						'tempmailo.com',
						'throwawaymail.com',
						'trashmail.com',
						'yopmail.com',
					)
				);

				foreach ( $blocked as $item ) {
					$item = strtolower( ltrim( trim( (string) $item ), '@' ) );
					if ( '' === $item ) {
						continue;
					}
					if ( $item === $domain || substr( $domain, -( strlen( $item ) + 1 ) ) === '.' . $item ) {
						return true;
					}
				}

				return false;
			}

			/**
			 * Hold back WordPress's immediate admin new-user mail for My Account
			 * registrations. The real notice is sent from on_user_verified().
			 */
			public function suppress_admin_new_user_email( $email ) {
				if ( ! $this->should_defer_admin_notification() ) {
					return $email;
				}
				if ( is_array( $email ) ) {
					$email['to'] = '';
				}

				return $email;
			}

			/**
			 * @param bool             $send Whether to email the site admin.
			 * @param WP_User|mixed    $user New user object when available.
			 * @return bool
			 */
			public function maybe_block_admin_notification( $send, $user = null ) {
				if ( $this->should_defer_admin_notification() ) {
					return false;
				}
				if ( $user instanceof WP_User && $this->is_pending( (int) $user->ID ) ) {
					return false;
				}

				return (bool) $send;
			}

			private function should_defer_admin_notification(): bool {
				return $this->is_registration_request();
			}

			public function flag_new_registration( $user_id ): void {
				if ( ! $this->is_registration_request() ) {
					return;
				}
				$user_id = (int) $user_id;
				if ( $user_id <= 0 ) {
					return;
				}

				$this->set_pending( $user_id );
				update_user_meta( $user_id, self::META_NOTIFY_ADMIN, '1' );
				$this->rate_increment( 'register_ip', $this->client_ip(), self::REGISTER_WINDOW );
				$this->send_verification_email( $user_id );
			}

			/**
			 * After the visitor confirms their email: notify the site admin and
			 * clear the deferred-notification flag so the account can appear in
			 * the default Users list.
			 *
			 * @param int  $user_id User that was activated.
			 * @param bool $success Whether pending meta was removed.
			 */
			public function on_user_verified( $user_id, $success = true ): void {
				$user_id = (int) $user_id;
				if ( $user_id <= 0 || ! $success ) {
					return;
				}

				$should_notify = (string) get_user_meta( $user_id, self::META_NOTIFY_ADMIN, true );
				if ( '' === $should_notify ) {
					return;
				}

				delete_user_meta( $user_id, self::META_NOTIFY_ADMIN );
				$this->send_admin_new_user_email( $user_id );
			}

			public function finalize_registration( $user_id ): void {
				$user_id = (int) $user_id;
				if ( $user_id <= 0 ) {
					return;
				}

				if ( ! $this->is_pending( $user_id ) ) {
					// Checkout registrations are normal accounts (no pending
					// verification gate), but they still count towards the
					// per-device registration limit.
					if ( $this->is_checkout_registration() ) {
						$this->rate_increment( 'register_ip', $this->client_ip(), self::REGISTER_WINDOW );
					}

					return;
				}

				if ( is_user_logged_in() && get_current_user_id() === $user_id ) {
					wp_logout();
				}

				edd_unset_error( 'edd_invalid_login' );
				edd_set_success(
					'abp_verification_pending',
					__( 'Your account has been created. We have sent a verification link to your email address - please verify it before signing in.', 'abp-plugin' )
				);

				if ( ! $this->verification_mail_sent ) {
					edd_set_error(
						'abp_verification_email_failed',
						__( 'Your account was created, but we could not send the verification email from the server. Please use the box below to request a link again, or contact support if the problem continues.', 'abp-plugin' )
					);

					return;
				}

				$user = get_userdata( $user_id );
				if ( $user instanceof WP_User ) {
					$this->set_verify_notice( $user->user_email );
				}
			}

			/* -----------------------------------------------------------------
			 * Persistent "verification email sent" notice
			 * -------------------------------------------------------------- */
			private function set_verify_notice( string $email ): void {
				$email = sanitize_email( $email );
				if ( '' === $email || ! is_email( $email ) ) {
					return;
				}

				$hash    = md5( strtolower( $email ) );
				$expires = time() + self::NOTICE_LIFETIME;

				set_transient( self::TRANSIENT_NOTICE . $hash, $expires, self::NOTICE_LIFETIME + DAY_IN_SECONDS );

				if ( ! headers_sent() ) {
					setcookie(
						self::COOKIE_NOTICE,
						$hash,
						array(
							'expires'  => $expires,
							'path'     => ( defined( 'COOKIEPATH' ) && COOKIEPATH ) ? COOKIEPATH : '/',
							'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
							'secure'   => is_ssl(),
							'httponly' => true,
							'samesite' => 'Lax',
						)
					);
				}

				$_COOKIE[ self::COOKIE_NOTICE ] = $hash;
			}

			/**
			 * Notice state for the current browser.
			 *
			 * @return array{email_hash:string,expires:int}|array
			 */
			public static function get_verify_notice(): array {
				$hash = isset( $_COOKIE[ self::COOKIE_NOTICE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NOTICE ] ) ) : '';
				if ( '' === $hash || ! preg_match( '/^[a-f0-9]{32}$/', $hash ) ) {
					return array();
				}

				$expires = get_transient( self::TRANSIENT_NOTICE . $hash );
				if ( false === $expires ) {
					return array();
				}

				return array(
					'email_hash' => $hash,
					'expires'    => (int) $expires,
				);
			}

			public static function clear_verify_notice(): void {
				$hash = isset( $_COOKIE[ self::COOKIE_NOTICE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NOTICE ] ) ) : '';

				if ( '' !== $hash ) {
					delete_transient( self::TRANSIENT_NOTICE . $hash );
				}

				if ( ! headers_sent() ) {
					setcookie(
						self::COOKIE_NOTICE,
						'',
						array(
							'expires'  => time() - HOUR_IN_SECONDS,
							'path'     => ( defined( 'COOKIEPATH' ) && COOKIEPATH ) ? COOKIEPATH : '/',
							'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
							'secure'   => is_ssl(),
							'httponly' => true,
							'samesite' => 'Lax',
						)
					);
				}

				unset( $_COOKIE[ self::COOKIE_NOTICE ] );
			}

			public static function maybe_clear_notice_after_verification(): void {
				if ( ! isset( $_GET['edd-verify-success'] ) ) {
					return;
				}

				self::clear_verify_notice();
			}

			private function is_registration_request(): bool {
				$action = isset( $_POST['edd_action'] ) ? sanitize_key( wp_unslash( $_POST['edd_action'] ) ) : '';

				return 'user_register' === $action;
			}

			/* -----------------------------------------------------------------
			 * Users list - hide unverified accounts from the default view
			 * -------------------------------------------------------------- */
			private function is_pending_users_view(): bool {
				return isset( $_GET['abp_pending'] ) && '1' === (string) wp_unslash( $_GET['abp_pending'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			/**
			 * Default Users list excludes pending accounts. The dedicated
			 * "Pending verification" view shows only those accounts.
			 *
			 * @param array $args WP_User_Query args for the list table.
			 * @return array
			 */
			public function filter_users_list_query( $args ) {
				if ( ! is_array( $args ) ) {
					return $args;
				}

				$clause = $this->is_pending_users_view()
					? array(
						'key'   => self::META_PENDING,
						'value' => '1',
					)
					: array(
						'key'     => self::META_PENDING,
						'compare' => 'NOT EXISTS',
					);

				if ( empty( $args['meta_query'] ) || ! is_array( $args['meta_query'] ) ) {
					$args['meta_query'] = array( $clause );
				} else {
					$args['meta_query']['relation'] = isset( $args['meta_query']['relation'] )
						? $args['meta_query']['relation']
						: 'AND';
					$args['meta_query'][]           = $clause;
				}

				return $args;
			}

			/**
			 * @param array $views Existing role / status view links.
			 * @return array
			 */
			public function filter_user_views( $views ) {
				if ( ! is_array( $views ) ) {
					$views = array();
				}

				$pending_count = $this->count_pending_users();
				$pending_url   = add_query_arg(
					array(
						'abp_pending' => '1',
					),
					admin_url( 'users.php' )
				);

				if ( $this->is_pending_users_view() ) {
					foreach ( $views as $key => $html ) {
						$views[ $key ] = str_replace( ' class="current"', '', (string) $html );
						$views[ $key ] = str_replace( " class='current'", '', $views[ $key ] );
					}
				}

				$views['abp_pending'] = sprintf(
					'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
					esc_url( $pending_url ),
					$this->is_pending_users_view() ? ' class="current" aria-current="page"' : '',
					esc_html__( 'Pending verification', 'abp-plugin' ),
					number_format_i18n( $pending_count )
				);

				return $views;
			}

			private function count_pending_users(): int {
				$query = new WP_User_Query(
					array(
						'fields'     => 'ID',
						'number'     => 1,
						'count_total'=> true,
						'meta_key'   => self::META_PENDING,
						'meta_value' => '1',
					)
				);

				return (int) $query->get_total();
			}

			/* -----------------------------------------------------------------
			 * Verification helpers
			 * -------------------------------------------------------------- */
			private function is_pending( int $user_id ): bool {
				if ( $user_id <= 0 ) {
					return false;
				}
				if ( function_exists( 'edd_user_pending_verification' ) ) {
					return (bool) edd_user_pending_verification( $user_id );
				}

				return '' !== (string) get_user_meta( $user_id, self::META_PENDING, true );
			}

			private function set_pending( int $user_id ): void {
				if ( function_exists( 'edd_set_user_to_pending' ) ) {
					edd_set_user_to_pending( $user_id );

					return;
				}
				update_user_meta( $user_id, self::META_PENDING, '1' );
			}

			private function find_user( string $login ): ?WP_User {
				$login = trim( $login );
				if ( '' === $login ) {
					return null;
				}

				if ( is_email( $login ) ) {
					$user = get_user_by( 'email', $login );
				} else {
					$user = get_user_by( 'login', $login );
				}

				return ( $user instanceof WP_User ) ? $user : null;
			}

			public function verification_base_url( $url ): string {
				$account = $this->my_account_url();

				return $account ? $account : $url;
			}

			private function my_account_url(): string {
				$cached = get_transient( self::TRANSIENT_ACCOUNT );
				if ( is_string( $cached ) && '' !== $cached ) {
					return $cached;
				}

				$pages = get_pages(
					array(
						'post_type'      => 'page',
						'number'         => 100,
						'fields'         => 'ids',
						'no_found_rows'  => true,
						'cache_results'  => false,
					)
				);

				foreach ( (array) $pages as $page_id ) {
					$content = (string) get_post_field( 'post_content', $page_id );
					if ( false !== strpos( $content, '[abp_my_account]' ) ) {
						$url = get_permalink( $page_id );
						if ( $url ) {
							set_transient( self::TRANSIENT_ACCOUNT, $url, DAY_IN_SECONDS );

							return $url;
						}
					}
				}

				return '';
			}

			private function verification_url( int $user_id ): string {
				if ( function_exists( 'edd_get_user_verification_url' ) ) {
					$url = edd_get_user_verification_url( $user_id );
					if ( $url ) {
						return $url;
					}
				}

				return '';
			}

			/* -----------------------------------------------------------------
			 * Verification email
			 * -------------------------------------------------------------- */
			private function send_verification_email( int $user_id ): bool {
				$user = get_userdata( $user_id );
				if ( ! ( $user instanceof WP_User ) ) {
					return false;
				}

				$url = $this->verification_url( $user_id );
				if ( '' === $url ) {
					return false;
				}

				$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
				$site_name = $site_name ? $site_name : 'ABP-TEAM';
				$brand     = 'ABP-TEAM';
				$from      = class_exists( 'ABP_Function' ) ? ABP_Function::get_from_email() : get_option( 'admin_email' );
				$support   = class_exists( 'ABP_Function' ) ? ABP_Function::get_valid_email( get_option( 'abp_smart_target_email', 'support@abp-team.com' ) ) : 'support@abp-team.com';

				$subject = sprintf( __( 'Verify your email address to activate your account - %s', 'abp-plugin' ), $site_name );
				$body    = $this->build_verification_email( $user, $url, $site_name, $brand, $support );

				$headers = array(
					'Content-Type: text/html; charset=UTF-8',
					sprintf( 'From: %s <%s>', $site_name, $from ),
				);

				$this->verification_mail_sent = (bool) wp_mail( $user->user_email, $subject, $body, $headers );

				return $this->verification_mail_sent;
			}

			/**
			 * Notify the site admin once a My Account registration is verified.
			 */
			private function send_admin_new_user_email( int $user_id ): bool {
				$user = get_userdata( $user_id );
				if ( ! ( $user instanceof WP_User ) ) {
					return false;
				}

				$admin_email = class_exists( 'ABP_Function' )
					? ABP_Function::get_valid_email( get_option( 'admin_email' ) )
					: get_option( 'admin_email' );
				$admin_email = is_email( (string) $admin_email ) ? (string) $admin_email : '';
				if ( '' === $admin_email ) {
					return false;
				}

				$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
				$site_name = $site_name ? $site_name : 'ABP-TEAM';
				$from      = class_exists( 'ABP_Function' ) ? ABP_Function::get_from_email() : $admin_email;
				$edit_url  = admin_url( sprintf( 'user-edit.php?user_id=%d', $user_id ) );

				$subject = sprintf(
					/* translators: %s: site name */
					__( '[%s] New user registration', 'abp-plugin' ),
					$site_name
				);

				$body = sprintf(
					"New user registration on %1\$s\n\nUsername: %2\$s\nEmail: %3\$s\n\nEdit user: %4\$s\n",
					$site_name,
					$user->user_login,
					$user->user_email,
					$edit_url
				);

				/**
				 * Filters the admin new-user notification after email verification.
				 *
				 * @param array   $email {
				 *     @type string $to      Admin recipient.
				 *     @type string $subject Subject line.
				 *     @type string $message Plain-text body.
				 *     @type array  $headers Headers for wp_mail().
				 * }
				 * @param WP_User $user Verified user.
				 */
				$email = apply_filters(
					'abp_admin_verified_user_email',
					array(
						'to'      => $admin_email,
						'subject' => $subject,
						'message' => $body,
						'headers' => array(
							sprintf( 'From: %s <%s>', $site_name, $from ),
						),
					),
					$user
				);

				if ( empty( $email['to'] ) || empty( $email['subject'] ) ) {
					return false;
				}

				return (bool) wp_mail(
					$email['to'],
					$email['subject'],
					isset( $email['message'] ) ? $email['message'] : $body,
					isset( $email['headers'] ) ? $email['headers'] : array()
				);
			}

			private function build_verification_email( WP_User $user, string $url, string $site_name, string $brand, string $support ): string {
				$mail_font = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif";
				$hello     = esc_html( $user->display_name ? $user->display_name : $user->user_login );
				$safe_url  = esc_url( $url );
				$safe_site = esc_html( $site_name );
				$brand     = esc_html( $brand );
				$support   = esc_html( $support );

				return '
				<html>
				<body style="margin:0;padding:0;background:#eef2f7;font-family:' . $mail_font . ';color:#1e293b;line-height:1.65;-webkit-text-size-adjust:100%;">
					<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:28px 12px;">
						<tr>
							<td align="center">
								<table role="presentation" width="640" cellpadding="0" cellspacing="0" style="width:100%;max-width:640px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
									<tr>
										<td style="background:#0b1220;background:linear-gradient(135deg,#0b1220 0%,#123a6b 55%,#2563eb 100%);padding:30px 32px 28px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
												<tr>
													<td style="font-size:13px;font-weight:800;color:#ffffff;letter-spacing:3px;">' . $brand . '</td>
													<td align="right"><span style="display:inline-block;padding:6px 14px;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.3);color:#dbeafe;font-size:10.5px;font-weight:700;letter-spacing:1.6px;">EMAIL VERIFICATION</span></td>
												</tr>
											</table>
											<h1 style="margin:22px 0 8px;font-size:23px;line-height:1.3;color:#ffffff;font-weight:700;">Verify your email address</h1>
											<p style="margin:0;font-size:14px;color:#c7d2fe;">One quick step and your ' . $safe_site . ' account is ready to use.</p>
										</td>
									</tr>
									<tr>
										<td style="padding:28px 32px 8px;">
											<p style="margin:0 0 16px;font-size:15px;color:#334155;">Hi <b>' . $hello . '</b>,</p>
											<p style="margin:0 0 16px;font-size:14.5px;color:#475569;">Thank you for creating your account. To keep your purchases, licenses and downloads protected, please confirm that this email address belongs to you.</p>
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:22px 0;">
												<tr>
													<td align="center">
														<a href="' . $safe_url . '" style="display:inline-block;padding:14px 30px;border-radius:10px;background:#2563eb;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;letter-spacing:.3px;">Verify my email address</a>
													</td>
												</tr>
											</table>
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
												<tr>
													<td style="padding:16px 20px;font-size:13px;color:#475569;">
														<b style="color:#0f172a;">Button not working?</b><br>
														Copy this link into your browser:<br>
														<a href="' . $safe_url . '" style="color:#2563eb;word-break:break-all;text-decoration:none;">' . $safe_url . '</a>
													</td>
												</tr>
											</table>
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;background:#eff6ff;border-left:4px solid #2563eb;border-radius:8px;">
												<tr>
													<td style="padding:16px 20px;font-size:13.5px;color:#1e3a8a;">
														This link expires in 24 hours. If you did not create this account, simply ignore this email - no action will be taken.
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="padding:18px 32px 28px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #eef2f7;">
												<tr>
													<td style="padding-top:16px;font-size:13px;color:#64748b;">
														Need help? Reply to this email or write to <a href="mailto:' . $support . '" style="color:#2563eb;text-decoration:none;">' . $support . '</a>.
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</body>
				</html>';
			}

			/* -----------------------------------------------------------------
			 * Resend verification
			 * -------------------------------------------------------------- */
			public function ajax_resend_verification(): void {
				if ( ! check_ajax_referer( self::NONCE_RESEND, 'nonce', false ) ) {
					wp_send_json_error( [ 'message' => __( 'Security token expired. Please refresh the page and try again.', 'abp-plugin' ) ], 403 );
				}

				$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
				if ( '' === $email || ! is_email( $email ) ) {
					wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'abp-plugin' ) ] );
				}

				$ip = $this->client_ip();
				if ( $this->rate_reached( 'resend_ip', $ip, self::RESEND_MAX_IP ) ) {
					wp_send_json_error( [ 'message' => __( 'Too many requests from this device. Please try again later.', 'abp-plugin' ) ] );
				}
				if ( $this->rate_reached( 'resend_mail', $email, self::RESEND_MAX_MAIL ) ) {
					wp_send_json_error( [ 'message' => __( 'A verification link was already sent recently. Please check your inbox.', 'abp-plugin' ) ] );
				}

				$this->rate_increment( 'resend_ip', $ip, self::RESEND_WINDOW );

				$user = get_user_by( 'email', $email );
				if ( ! ( $user instanceof WP_User ) ) {
					wp_send_json_success( [ 'message' => __( 'If that email address is registered with us, a verification link is on its way.', 'abp-plugin' ) ] );
				}

				if ( ! $this->is_pending( $user->ID ) ) {
					wp_send_json_success( [ 'message' => __( 'This email address is already verified. You can sign in with your password.', 'abp-plugin' ) ] );
				}

				$this->rate_increment( 'resend_mail', $email, self::RESEND_WINDOW );

				if ( ! $this->send_verification_email( $user->ID ) ) {
					wp_send_json_error( [ 'message' => __( 'We could not send the verification email. Please try again in a few minutes.', 'abp-plugin' ) ] );
				}

				$this->set_verify_notice( $email );

				wp_send_json_success( [ 'message' => __( 'Verification link sent. Please check your inbox and your spam folder.', 'abp-plugin' ) ] );
			}

			/* -----------------------------------------------------------------
			 * Migration
			 * -------------------------------------------------------------- */
			public function maybe_migrate(): void {
				if ( get_option( self::OPTION_MIGRATED ) ) {
					return;
				}
				self::clear_pending_flags();
				update_option( self::OPTION_MIGRATED, 1 );
				update_option( self::OPTION_VERSION, self::VERSION );
			}

			public static function activate(): void {
				self::clear_pending_flags();
				update_option( self::OPTION_MIGRATED, 1 );
				update_option( self::OPTION_VERSION, self::VERSION );
			}

			private static function clear_pending_flags(): void {
				global $wpdb;
				$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => self::META_PENDING ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
		}
		new ABP_Account_Security();
	}
