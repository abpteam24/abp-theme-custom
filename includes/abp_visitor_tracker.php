<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	if ( ! class_exists( 'ABP_Visitor_Tracker' ) ) {
		class ABP_Visitor_Tracker {
			const VERSION       = '1.0.0';
			const DB_VERSION    = '1.0.0';
			const OPTION_DB     = 'abp_visitor_db_version';
			const OPTION_CONFIG = 'abp_visitor_config';
			const COOKIE        = 'abp_vid';
			const NONCE         = 'abp_visitor_track';
			const MENU          = 'abp-visitors';
			const SUB_BLOCKED   = 'abp-visitors-blocked';
			const SUB_SETTINGS  = 'abp-visitors-settings';
			const CRON_HOOK     = 'abp_visitor_daily_cleanup';
			const ONLINE_WINDOW = 300;
			const PER_PAGE      = 25;

			private static $instance   = null;
			private static $installed  = false;

			private $log_id        = 0;
			private $visitor_key  = '';
			private $country_cache = array();
			private $blocked_cache = array();

			public static function instance(): self {
				if ( null === self::$instance ) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			public function __construct() {
				add_action( 'init', array( $this, 'maybe_install' ), 1 );
				add_action( 'init', array( $this, 'enforce_block' ), 2 );
				add_action( 'init', array( $this, 'schedule_cleanup' ), 5 );
				add_action( 'template_redirect', array( $this, 'log_visit' ), 20 );
				add_action( 'wp_footer', array( $this, 'print_tracker' ), 5 );
				add_action( 'wp_ajax_abp_visitor_track', array( $this, 'ajax_track' ) );
				add_action( 'wp_ajax_nopriv_abp_visitor_track', array( $this, 'ajax_track' ) );
				add_action( 'edd_complete_purchase', array( $this, 'capture_purchase_email' ), 10, 3 );
				add_action( 'user_register', array( $this, 'capture_registered_email' ), 20, 1 );
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
				add_action( 'admin_post_abp_visitor_block', array( $this, 'post_block' ) );
				add_action( 'admin_post_abp_visitor_unblock', array( $this, 'post_unblock' ) );
				add_action( 'admin_post_abp_visitor_log_delete', array( $this, 'post_log_delete' ) );
				add_action( 'admin_post_abp_visitor_config', array( $this, 'post_config' ) );
				add_action( 'admin_post_abp_visitor_export', array( $this, 'post_export' ) );
				add_action( 'admin_post_abp_visitor_clear', array( $this, 'post_clear' ) );
				add_action( self::CRON_HOOK, array( $this, 'cleanup' ) );
			}

			/* -----------------------------------------------------------------
			 * Tables
			 * -------------------------------------------------------------- */
			public static function table( string $name ): string {
				global $wpdb;

				$map = array(
					'logs'    => 'visitor_logs',
					'blocked' => 'blocked_ips',
				);

				$key = isset( $map[ $name ] ) ? $map[ $name ] : $name;

				return $wpdb->prefix . $key;
			}

			public function maybe_install(): void {
				if ( self::$installed ) {
					return;
				}
				if ( get_option( self::OPTION_DB ) === self::DB_VERSION ) {
					self::$installed = true;
					return;
				}
				self::install();
			}

			public static function install(): void {
				global $wpdb;

				require_once ABSPATH . 'wp-admin/includes/upgrade.php';

				$charset_collate = $wpdb->get_charset_collate();
				$logs            = self::table( 'logs' );
				$blocked         = self::table( 'blocked' );

				$sql_logs = "CREATE TABLE {$logs} (
	id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	visit_id varchar(40) NOT NULL DEFAULT '',
	visitor_key varchar(40) NOT NULL DEFAULT '',
	ip varchar(45) NOT NULL DEFAULT '',
	user_id bigint(20) unsigned NOT NULL DEFAULT 0,
	email varchar(190) NOT NULL DEFAULT '',
	country varchar(100) NOT NULL DEFAULT '',
	page_url varchar(255) NOT NULL DEFAULT '',
	page_title varchar(255) NOT NULL DEFAULT '',
	referrer varchar(255) NOT NULL DEFAULT '',
	device varchar(20) NOT NULL DEFAULT '',
	browser varchar(50) NOT NULL DEFAULT '',
	os varchar(50) NOT NULL DEFAULT '',
	duration int(11) NOT NULL DEFAULT 0,
	visit_number int(11) NOT NULL DEFAULT 1,
	is_bot tinyint(1) NOT NULL DEFAULT 0,
	visited_at datetime DEFAULT NULL,
	last_seen datetime DEFAULT NULL,
	PRIMARY KEY  (id),
	KEY abp_ip (ip),
	KEY abp_visitor_key (visitor_key),
	KEY abp_visit_id (visit_id),
	KEY abp_email (email),
	KEY abp_visited_at (visited_at),
	KEY abp_last_seen (last_seen)
) {$charset_collate};";

				dbDelta( $sql_logs );

				$sql_blocked = "CREATE TABLE {$blocked} (
	id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	ip varchar(45) NOT NULL DEFAULT '',
	reason varchar(255) NOT NULL DEFAULT '',
	hits bigint(20) unsigned NOT NULL DEFAULT 0,
	created_at datetime DEFAULT NULL,
	last_blocked_at datetime DEFAULT NULL,
	expires_at datetime DEFAULT NULL,
	created_by bigint(20) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY  (id),
	UNIQUE KEY abp_blocked_ip (ip)
) {$charset_collate};";

				dbDelta( $sql_blocked );

				update_option( self::OPTION_DB, self::DB_VERSION );
				self::$installed = true;
			}

			public static function activate(): void {
				self::install();
				self::instance()->schedule_cleanup();
			}

			public function schedule_cleanup(): void {
				if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
					wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
				}
			}

			public function cleanup(): void {
				global $wpdb;

				$days = (int) $this->config( 'retention_days' );
				if ( $days > 0 ) {
					$cutoff = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS ) );
					$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::table( 'logs' ) . ' WHERE visited_at < %s', $cutoff ) );
				}

				$now = $this->now();
				$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::table( 'blocked' ) . ' WHERE expires_at IS NOT NULL AND expires_at < %s', $now ) );
			}

			/* -----------------------------------------------------------------
			 * Configuration
			 * -------------------------------------------------------------- */
			public function config( string $key ): string {
				$defaults = array(
					'tracking_enabled' => '1',
					'track_duration'   => '1',
					'geo_lookup'       => '1',
					'log_bots'         => '0',
					'log_admins'       => '0',
					'session_gap'      => '30',
					'retention_days'   => '90',
					'heartbeat'        => '60',
					'blocked_message'  => 'Your access has been blocked because this network address has been flagged as abusive. If you believe this is a mistake, contact our support team and we will review it right away.',
				);

				$saved = get_option( self::OPTION_CONFIG, array() );
				$saved = is_array( $saved ) ? $saved : array();
				$saved = array_merge( $defaults, $saved );

				return isset( $saved[ $key ] ) ? (string) $saved[ $key ] : '';
			}

			private function now(): string {
				return date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
			}

			/* -----------------------------------------------------------------
			 * Visitor identity
			 * -------------------------------------------------------------- */
			public function client_ip(): string {
				$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

				if ( '' !== $ip ) {
					$validated = filter_var( $ip, FILTER_VALIDATE_IP );
					if ( $validated ) {
						$ip = $validated;
					}
				}

				$ip = (string) apply_filters( 'abp_visitor_client_ip', $ip );

				return $ip;
			}

			private function is_local_ip( string $ip ): bool {
				$is_local = false;

				if ( '' === $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					$is_local = true;
				} elseif ( '::1' === $ip || '127.0.0.1' === $ip || '0.0.0.0' === $ip ) {
					$is_local = true;
				} elseif ( false === filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE ) ) {
					$is_local = true;
				}

				return (bool) apply_filters( 'abp_visitor_treat_as_local', $is_local, $ip );
			}

			private function visitor_key(): string {
				if ( '' !== $this->visitor_key ) {
					return $this->visitor_key;
				}

				$key = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';

				if ( ! preg_match( '/^[a-f0-9]{32}$/', $key ) ) {
					$key = md5( wp_generate_password( 24, false ) . wp_rand( 1000, 9999 ) . microtime() );
					$this->set_visitor_cookie( $key );
				}

				$this->visitor_key = $key;

				return $key;
			}

			private function set_visitor_cookie( string $key ): void {
				if ( headers_sent() ) {
					return;
				}

				setcookie(
					self::COOKIE,
					$key,
					array(
						'expires'  => time() + YEAR_IN_SECONDS,
						'path'     => '/',
						'domain'   => COOKIE_DOMAIN,
						'secure'   => is_ssl(),
						'httponly' => true,
						'samesite' => 'Lax',
					)
				);
			}

			private function user_agent(): string {
				return isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
			}

			private function device_info(): array {
				$ua = $this->user_agent();

				$device = 'Desktop';
				if ( preg_match( '/mobile|iphone|ipod|android.*mobile|windows phone|blackberry|opera mini/i', $ua ) ) {
					$device = 'Mobile';
				} elseif ( preg_match( '/ipad|tablet|kindle|playbook|silk|android(?!.*mobile)/i', $ua ) ) {
					$device = 'Tablet';
				}

				$browser = 'Other';
				if ( preg_match( '/edg(?:e|a|ios)?\/([\d.]+)/i', $ua ) ) {
					$browser = 'Edge';
				} elseif ( preg_match( '/opr\/([\d.]+)/i', $ua ) ) {
					$browser = 'Opera';
				} elseif ( preg_match( '/chrome\/([\d.]+)/i', $ua ) ) {
					$browser = 'Chrome';
				} elseif ( preg_match( '/firefox\/([\d.]+)/i', $ua ) ) {
					$browser = 'Firefox';
				} elseif ( preg_match( '/safari\/([\d.]+)/i', $ua ) ) {
					$browser = 'Safari';
				} elseif ( preg_match( '/msie|trident/i', $ua ) ) {
					$browser = 'Internet Explorer';
				}

				$os = 'Other';
				if ( preg_match( '/windows nt/i', $ua ) ) {
					$os = 'Windows';
				} elseif ( preg_match( '/mac os x|macintosh/i', $ua ) ) {
					$os = 'macOS';
				} elseif ( preg_match( '/android/i', $ua ) ) {
					$os = 'Android';
				} elseif ( preg_match( '/iphone|ipad|ipod/i', $ua ) ) {
					$os = 'iOS';
				} elseif ( preg_match( '/linux/i', $ua ) ) {
					$os = 'Linux';
				}

				return array(
					'device'  => $device,
					'browser' => $browser,
					'os'      => $os,
				);
			}

			private function is_bot(): bool {
				$ua = strtolower( $this->user_agent() );
				if ( '' === $ua ) {
					return false;
				}

				$needles = (array) apply_filters(
					'abp_visitor_bot_patterns',
					array(
						'bot',
						'spider',
						'crawl',
						'slurp',
						'wget',
						'curl/',
						'python-requests',
						'python-urllib',
						'headless',
						'phantomjs',
						'scrapy',
						'lighthouse',
						'pingdom',
						'ahrefs',
						'semrush',
						'mj12',
						'dotbot',
						'petalbot',
						'facebookexternalhit',
						'bingpreview',
						'gptbot',
						'claudebot',
						'ccbot',
					)
				);

				foreach ( $needles as $needle ) {
					$needle = strtolower( (string) $needle );
					if ( '' !== $needle && false !== strpos( $ua, $needle ) ) {
						return true;
					}
				}

				return false;
			}

			/* -----------------------------------------------------------------
			 * Country
			 * -------------------------------------------------------------- */
			private function resolve_country( string $ip, int $user_id, string $email ): string {
				$country = '';

				if ( '' !== $email ) {
					$country = $this->country_for_email( $email );
				}

				if ( '' === $country && $user_id > 0 ) {
					$customer = $this->customer_for_user( $user_id );
					if ( $customer ) {
						$country = $this->country_for_customer( (int) $customer );
					}
				}

				if ( '' === $country ) {
					$filtered = apply_filters( 'abp_visitor_country', '', $ip, $user_id, $email );
					$country  = is_string( $filtered ) ? trim( $filtered ) : '';
				}

				if ( '' === $country && '1' === $this->config( 'geo_lookup' ) && ! $this->is_local_ip( $ip ) ) {
					$country = $this->geo_country( $ip );
				}

				return '' !== $country ? $country : 'Unknown';
			}

			private function customer_for_user( int $user_id ): int {
				if ( ! function_exists( 'edd_get_customer_by' ) ) {
					return 0;
				}

				$customer = edd_get_customer_by( 'user_id', $user_id );
				if ( $customer && ! empty( $customer->id ) ) {
					return (int) $customer->id;
				}

				return 0;
			}

			private function country_for_email( string $email ): string {
				$key = 'email:' . strtolower( $email );
				if ( isset( $this->country_cache[ $key ] ) ) {
					return $this->country_cache[ $key ];
				}

				$this->country_cache[ $key ] = '';

				if ( ! function_exists( 'edd_get_customer_by' ) ) {
					return '';
				}

				$customer = edd_get_customer_by( 'email', $email );
				if ( $customer && ! empty( $customer->id ) ) {
					$this->country_cache[ $key ] = $this->country_for_customer( (int) $customer->id );
				}

				return $this->country_cache[ $key ];
			}

			private function country_for_customer( int $customer_id ): string {
				global $wpdb;

				if ( $customer_id <= 0 ) {
					return '';
				}

				$addresses = $wpdb->prefix . 'edd_customer_addresses';
				$exists    = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $addresses ) );
				if ( $exists !== $addresses ) {
					return '';
				}

				$country = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT country FROM {$addresses} WHERE customer_id = %d AND type = %s ORDER BY is_primary DESC, id ASC LIMIT 1",
						$customer_id,
						'billing'
					)
				);

				return is_string( $country ) ? trim( $country ) : '';
			}

			private function geo_country( string $ip ): string {
				if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return '';
				}

				$cache_key = 'abp_geo_' . md5( $ip );
				$cached    = get_transient( $cache_key );

				if ( is_string( $cached ) ) {
					return $cached;
				}

				$country = '';

				$response = wp_remote_get(
					'http://ip-api.com/json/' . rawurlencode( $ip ),
					array(
						'timeout'     => 3,
						'redirection' => 0,
						'user-agent'  => 'ABP-TEAM visitor tracker/' . self::VERSION,
					)
				);

				if ( ! is_wp_error( $response ) ) {
					$data = json_decode( wp_remote_retrieve_body( $response ), true );
					if ( is_array( $data ) && isset( $data['status'] ) && 'success' === $data['status'] && ! empty( $data['country'] ) ) {
						$country = (string) $data['country'];
					}
				}

				set_transient( $cache_key, $country, '' !== $country ? 30 * DAY_IN_SECONDS : DAY_IN_SECONDS );

				return $country;
			}

			/* -----------------------------------------------------------------
			 * Logging
			 * -------------------------------------------------------------- */
			public function log_visit(): void {
				if ( ! $this->should_log() ) {
					return;
				}

				global $wpdb;

				$ip = $this->client_ip();
				if ( '' === $ip ) {
					return;
				}

				$is_bot = $this->is_bot();
				if ( $is_bot && '1' !== $this->config( 'log_bots' ) ) {
					return;
				}

				$visitor_key = $this->visitor_key();
				$device      = $this->device_info();
				$user_id     = get_current_user_id();
				$email       = $user_id > 0 ? $this->user_email( $user_id ) : '';
				$country     = $this->resolve_country( $ip, $user_id, $email );
				$visit       = $this->current_visit( $visitor_key );
				$now         = $this->now();

				$inserted = $wpdb->insert(
					self::table( 'logs' ),
					array(
						'visit_id'     => $visit['visit_id'],
						'visitor_key'  => $visitor_key,
						'ip'           => $ip,
						'user_id'      => $user_id,
						'email'        => $email,
						'country'      => $country,
						'page_url'     => $this->current_url(),
						'page_title'   => $this->current_title(),
						'referrer'     => $this->current_referrer(),
						'device'       => $device['device'],
						'browser'      => $device['browser'],
						'os'           => $device['os'],
						'duration'     => 0,
						'visit_number' => $visit['visit_number'],
						'is_bot'       => $is_bot ? 1 : 0,
						'visited_at'   => $now,
						'last_seen'    => $now,
					),
					array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s' )
				);

				if ( $inserted ) {
					$this->log_id = (int) $wpdb->insert_id;
				}
			}

			private function should_log(): bool {
				if ( '1' !== $this->config( 'tracking_enabled' ) ) {
					return false;
				}
				if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() || is_robots() || is_trackback() ) {
					return false;
				}
				if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
					return false;
				}
				if ( is_user_logged_in() && current_user_can( 'manage_options' ) && '1' !== $this->config( 'log_admins' ) ) {
					return false;
				}
				if ( $this->is_blocked( $this->client_ip() ) ) {
					return false;
				}

				$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

				return (bool) apply_filters( 'abp_visitor_should_log', true, $uri );
			}

			private function current_visit( string $visitor_key ): array {
				global $wpdb;

				$table = self::table( 'logs' );
				$row   = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT visit_id, visit_number, visited_at FROM {$table} WHERE visitor_key = %s ORDER BY id DESC LIMIT 1",
						$visitor_key
					),
					ARRAY_A
				);

				if ( ! $row ) {
					return array(
						'visit_id'     => wp_generate_uuid4(),
						'visit_number' => 1,
					);
				}

				$gap  = max( 1, (int) $this->config( 'session_gap' ) ) * MINUTE_IN_SECONDS;
				$last = ! empty( $row['visited_at'] ) ? strtotime( $row['visited_at'] ) : 0;

				if ( $last && ( current_time( 'timestamp' ) - $last ) <= $gap ) {
					return array(
						'visit_id'     => (string) $row['visit_id'],
						'visit_number' => (int) $row['visit_number'],
					);
				}

				return array(
					'visit_id'     => wp_generate_uuid4(),
					'visit_number' => (int) $row['visit_number'] + 1,
				);
			}

			private function user_email( int $user_id ): string {
				$user = get_userdata( $user_id );
				if ( $user instanceof WP_User ) {
					return (string) $user->user_email;
				}
				return '';
			}

			private function current_url(): string {
				$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';

				$parts = wp_parse_url( home_url() );
				$host  = isset( $parts['host'] ) ? $parts['host'] : '';
				$port  = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
				$scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : 'http';

				if ( '' !== $host ) {
					$url = $scheme . '://' . $host . $port . $uri;
				} else {
					$url = home_url( $uri );
				}

				return substr( esc_url_raw( $url ), 0, 255 );
			}

			private function current_title(): string {
				$title = is_singular() ? (string) get_the_title() : (string) wp_get_document_title();

				return substr( wp_strip_all_tags( $title ), 0, 255 );
			}

			private function current_referrer(): string {
				if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
					return '';
				}

				return substr( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), 0, 255 );
			}

			/* -----------------------------------------------------------------
			 * Email capture
			 * -------------------------------------------------------------- */
			public static function capture_email( string $email, int $user_id = 0, string $country = '' ): void {
				global $wpdb;

				$email = sanitize_email( $email );
				if ( ! is_email( $email ) ) {
					return;
				}

				$instance    = self::instance();
				$visitor_key = $instance->visitor_key();
				$table       = self::table( 'logs' );

				$where = $wpdb->prepare( 'visitor_key = %s AND email = %s', $visitor_key, '' );
				$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET email = %s WHERE {$where}", $email ) );

				if ( $user_id > 0 ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET user_id = %d WHERE visitor_key = %s AND user_id = 0", $user_id, $visitor_key ) );
				}

				if ( '' !== $country ) {
					$country_where = $wpdb->prepare( 'visitor_key = %s AND ( country = %s OR country = %s )', $visitor_key, '', 'Unknown' );
					$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET country = %s WHERE {$country_where}", $country ) );
				}
			}

			public function capture_purchase_email( $order_id = 0, $payment = null, $customer = null ): void {
				$email = '';

				if ( is_object( $payment ) && isset( $payment->email ) ) {
					$email = (string) $payment->email;
				} elseif ( is_array( $payment ) && isset( $payment['email'] ) ) {
					$email = (string) $payment['email'];
				}

				if ( '' === $email ) {
					return;
				}

				$country = '';
				if ( is_object( $customer ) && ! empty( $customer->id ) ) {
					$country = $this->country_for_customer( (int) $customer->id );
				}

				self::capture_email( $email, 0, $country );
			}

			public function capture_registered_email( $user_id ): void {
				$user_id = (int) $user_id;
				if ( $user_id <= 0 ) {
					return;
				}

				$email = $this->user_email( $user_id );
				if ( '' === $email ) {
					return;
				}

				self::capture_email( $email, $user_id );
			}

			/* -----------------------------------------------------------------
			 * Front end tracker
			 * -------------------------------------------------------------- */
			public function print_tracker(): void {
				if ( $this->log_id <= 0 || '1' !== $this->config( 'track_duration' ) ) {
					return;
				}

				wp_enqueue_script( 'abp_visitor_tracker', ABP_URL . 'assets/js/abp_visitor_tracker.js', array(), $this->asset_version(), true );
				wp_localize_script(
					'abp_visitor_tracker',
					'abpVisitorData',
					array(
						'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
						'nonce'     => wp_create_nonce( self::NONCE ),
						'logId'     => (int) $this->log_id,
						'heartbeat' => max( 0, (int) $this->config( 'heartbeat' ) ),
					)
				);
			}

			public function ajax_track(): void {
				$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
				if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
					wp_send_json_error( array( 'message' => 'Invalid token' ), 403 );
				}

				global $wpdb;

				$log_id = isset( $_REQUEST['log_id'] ) ? absint( $_REQUEST['log_id'] ) : 0;
				$type   = isset( $_REQUEST['type'] ) ? sanitize_key( wp_unslash( $_REQUEST['type'] ) ) : 'duration';

				$visitor_key = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';
				if ( $log_id <= 0 || '' === $visitor_key ) {
					wp_send_json_success( array( 'ok' => false ) );
				}

				$table = self::table( 'logs' );
				$now   = $this->now();

				if ( 'duration' === $type ) {
					$duration = isset( $_REQUEST['duration'] ) ? absint( $_REQUEST['duration'] ) : 0;
					$duration = min( $duration, DAY_IN_SECONDS );
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$table} SET duration = GREATEST(duration, %d), last_seen = %s WHERE id = %d AND visitor_key = %s",
							$duration,
							$now,
							$log_id,
							$visitor_key
						)
					);
				} else {
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$table} SET last_seen = %s WHERE id = %d AND visitor_key = %s",
							$now,
							$log_id,
							$visitor_key
						)
					);
				}

				wp_send_json_success( array( 'ok' => true ) );
			}

			/* -----------------------------------------------------------------
			 * IP blocking
			 * -------------------------------------------------------------- */
			public function refresh_block_cache(): void {
				$this->blocked_cache = array();
			}

			public function is_blocked( string $ip ): bool {
				global $wpdb;

				if ( '' === $ip || $this->is_local_ip( $ip ) ) {
					return false;
				}

				if ( isset( $this->blocked_cache[ $ip ] ) ) {
					return (bool) $this->blocked_cache[ $ip ];
				}

				$table = self::table( 'blocked' );
				$row   = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT id, expires_at FROM {$table} WHERE ip = %s LIMIT 1",
						$ip
					),
					ARRAY_A
				);

				if ( ! $row ) {
					$this->blocked_cache[ $ip ] = false;

					return false;
				}

				if ( ! empty( $row['expires_at'] ) && strtotime( $row['expires_at'] ) < current_time( 'timestamp' ) ) {
					$this->blocked_cache[ $ip ] = false;

					return false;
				}

				$this->blocked_cache[ $ip ] = true;

				return true;
			}

			public function enforce_block(): void {
				if ( ! self::$installed ) {
					return;
				}
				if ( wp_doing_cron() ) {
					return;
				}

				$ip = $this->client_ip();
				if ( '' === $ip || $this->is_local_ip( $ip ) ) {
					return;
				}
				if ( ! $this->is_blocked( $ip ) ) {
					return;
				}

				wp_get_current_user();
				if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
					return;
				}

				$this->register_blocked_hit( $ip );
				$this->render_blocked_page();
			}

			private function register_blocked_hit( string $ip ): void {
				global $wpdb;

				$wpdb->query(
					$wpdb->prepare(
						'UPDATE ' . self::table( 'blocked' ) . ' SET hits = hits + 1, last_blocked_at = %s WHERE ip = %s',
						$this->now(),
						$ip
					)
				);
			}

			private function render_blocked_page(): void {
				$message = $this->config( 'blocked_message' );
				$support = class_exists( 'ABP_Function' )
					? ABP_Function::get_valid_email( get_option( 'abp_smart_target_email', 'support@abp-team.com' ) )
					: 'support@abp-team.com';
				$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
				$site    = $site ? $site : 'ABP-TEAM';

				if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
					wp_send_json_error( array( 'message' => $message ), 403 );
				}

				if ( is_admin() ) {
					wp_die( esc_html( $message ), esc_html__( 'Access denied', 'abp-plugin' ), array( 'response' => 403 ) );
				}

				status_header( 403 );
				nocache_headers();
				header( 'Content-Type: text/html; charset=utf-8' );

				?>
				<!DOCTYPE html>
				<html lang="<?php echo esc_attr( str_replace( '_', '-', get_locale() ) ); ?>">
				<head>
					<meta charset="<?php bloginfo( 'charset' ); ?>" />
					<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="robots" content="noindex,nofollow" />
					<title><?php echo esc_html( __( 'Access denied', 'abp-plugin' ) ); ?> &mdash; <?php echo esc_html( $site ); ?></title>
					<style>
						*{box-sizing:border-box}
						body{margin:0;padding:40px 18px;background:#eef2f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1e293b;line-height:1.65;-webkit-text-size-adjust:100%}
						.card{max-width:620px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;box-shadow:0 22px 50px rgba(15,23,42,.10)}
						.head{padding:34px 34px 28px;background:linear-gradient(135deg,#0b1220 0%,#123a6b 55%,#2563eb 100%);color:#fff}
						.badge{display:inline-block;padding:6px 14px;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.32);color:#dbeafe;font-size:10.5px;font-weight:700;letter-spacing:1.6px}
						.head h1{margin:18px 0 6px;font-size:24px;line-height:1.3}
						.head p{margin:0;color:#c7d2fe;font-size:14.5px}
						.body{padding:30px 34px}
						.body p{margin:0 0 14px;font-size:15px;color:#334155}
						.box{margin:22px 0 0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;font-size:13.5px;color:#475569}
						.box a{color:#2563eb}
						.foot{padding:16px 34px 26px;font-size:12px;color:#94a3b8;text-align:center}
						.btn{display:inline-block;margin-top:4px;padding:12px 26px;border-radius:10px;background:#2563eb;color:#fff;text-decoration:none;font-weight:700;font-size:14px}
					</style>
				</head>
				<body>
					<div class="card">
						<div class="head">
							<span class="badge"><?php echo esc_html__( 'ACCESS DENIED', 'abp-plugin' ); ?></span>
							<h1><?php echo esc_html__( 'Your access has been blocked', 'abp-plugin' ); ?></h1>
							<p><?php echo esc_html( $site ); ?></p>
						</div>
						<div class="body">
							<p><?php echo esc_html( $message ); ?></p>
							<div class="box">
								<?php
								printf(
									/* translators: %s: support email address */
									esc_html__( 'Need access? Write to %s from the email address or the network you use every day, and we will review the block.', 'abp-plugin' ),
									'<a href="mailto:' . esc_attr( $support ) . '">' . esc_html( $support ) . '</a>'
								);
								?>
							</div>
							<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Back to homepage', 'abp-plugin' ); ?></a>
						</div>
						<div class="foot"><?php echo esc_html( $site ); ?> &bull; <?php echo esc_html__( 'All rights reserved', 'abp-plugin' ); ?></div>
					</div>
				</body>
				</html>
				<?php
				exit;
			}

			/* -----------------------------------------------------------------
			 * Admin menu
			 * -------------------------------------------------------------- */
			public function admin_menu(): void {
				add_menu_page(
					__( 'ABP Visitor Tracking', 'abp-plugin' ),
					__( 'ABP Visitors', 'abp-plugin' ),
					'manage_options',
					self::MENU,
					array( $this, 'page_logs' ),
					'dashicons-visibility',
					27
				);

				add_submenu_page(
					self::MENU,
					__( 'Visitor Logs', 'abp-plugin' ),
					__( 'Visitor Logs', 'abp-plugin' ),
					'manage_options',
					self::MENU,
					array( $this, 'page_logs' )
				);

				add_submenu_page(
					self::MENU,
					__( 'Blocked IPs', 'abp-plugin' ),
					__( 'Blocked IPs', 'abp-plugin' ),
					'manage_options',
					self::SUB_BLOCKED,
					array( $this, 'page_blocked' )
				);

				add_submenu_page(
					self::MENU,
					__( 'Tracker Settings', 'abp-plugin' ),
					__( 'Settings', 'abp-plugin' ),
					'manage_options',
					self::SUB_SETTINGS,
					array( $this, 'page_settings' )
				);
			}

			public function admin_assets( $hook ): void {
				$hook = (string) $hook;
				if ( false === strpos( $hook, self::MENU ) ) {
					return;
				}

				wp_enqueue_style( 'abp_visitors_admin', ABP_URL . 'assets/css/abp_visitors_admin.css', array(), $this->asset_version() );
			}

			private function asset_version(): string {
				return defined( 'ABP_VERSION' ) ? (string) ABP_VERSION : self::VERSION;
			}

			/* -----------------------------------------------------------------
			 * Admin pages
			 * -------------------------------------------------------------- */
			private function filters(): array {
				return array(
					'date_from' => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
					'date_to'   => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
					'ip'        => isset( $_GET['ip'] ) ? sanitize_text_field( wp_unslash( $_GET['ip'] ) ) : '',
					'email'     => isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '',
					'country'   => isset( $_GET['country'] ) ? sanitize_text_field( wp_unslash( $_GET['country'] ) ) : '',
					'device'    => isset( $_GET['device'] ) ? sanitize_text_field( wp_unslash( $_GET['device'] ) ) : '',
					'search'    => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
				);
			}

			private function build_where( array $filters ): array {
				global $wpdb;

				$clauses = array( '1=1' );
				$args    = array();

				if ( '' !== $filters['date_from'] ) {
					$clauses[] = 'visited_at >= %s';
					$args[]    = $filters['date_from'] . ' 00:00:00';
				}
				if ( '' !== $filters['date_to'] ) {
					$clauses[] = 'visited_at <= %s';
					$args[]    = $filters['date_to'] . ' 23:59:59';
				}
				if ( '' !== $filters['ip'] ) {
					$clauses[] = 'ip LIKE %s';
					$args[]    = '%' . $wpdb->esc_like( $filters['ip'] ) . '%';
				}
				if ( '' !== $filters['email'] ) {
					$clauses[] = 'email LIKE %s';
					$args[]    = '%' . $wpdb->esc_like( $filters['email'] ) . '%';
				}
				if ( '' !== $filters['country'] ) {
					$clauses[] = 'country LIKE %s';
					$args[]    = '%' . $wpdb->esc_like( $filters['country'] ) . '%';
				}
				if ( '' !== $filters['device'] ) {
					$clauses[] = 'device = %s';
					$args[]    = $filters['device'];
				}
				if ( '' !== $filters['search'] ) {
					$like      = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
					$clauses[] = '( page_title LIKE %s OR page_url LIKE %s OR email LIKE %s OR ip LIKE %s )';
					$args      = array_merge( $args, array( $like, $like, $like, $like ) );
				}

				return array(
					'where' => implode( ' AND ', $clauses ),
					'args'  => $args,
				);
			}

			public function page_logs(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to view this page.', 'abp-plugin' ) );
				}

				global $wpdb;

				$filters = $this->filters();
				$table   = self::table( 'logs' );
				$where   = $this->build_where( $filters );

				$paged   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
				$offset  = ( $paged - 1 ) * self::PER_PAGE;
				$count_q = "SELECT COUNT(*) FROM {$table} WHERE {$where['where']}";
				$total   = (int) ( $where['args'] ? $wpdb->get_var( $wpdb->prepare( $count_q, $where['args'] ) ) : $wpdb->get_var( $count_q ) );
				$pages   = max( 1, (int) ceil( $total / self::PER_PAGE ) );

				$list_q = "SELECT * FROM {$table} WHERE {$where['where']} ORDER BY id DESC LIMIT %d OFFSET %d";
				$rows   = $wpdb->get_results( $wpdb->prepare( $list_q, array_merge( $where['args'], array( self::PER_PAGE, $offset ) ) ), ARRAY_A );
				$rows   = is_array( $rows ) ? $rows : array();

				$session_stats = $this->session_stats( $rows );
				$stats         = $this->overall_stats();
				$top_pages     = $this->top_list( 'page_url', 'page_title' );
				$top_countries = $this->top_list( 'country', 'country' );

				$devices = array(
					'Desktop' => __( 'Desktop', 'abp-plugin' ),
					'Mobile'  => __( 'Mobile', 'abp-plugin' ),
					'Tablet'  => __( 'Tablet', 'abp-plugin' ),
				);

				$notice = isset( $_GET['abp_msg'] ) ? sanitize_key( wp_unslash( $_GET['abp_msg'] ) ) : '';

				?>
				<div class="wrap abp-vwrap">
					<div class="abp-vhead">
						<div>
							<span class="abp-vkicker"><?php echo esc_html__( 'ABP Visitor Tracking', 'abp-plugin' ); ?></span>
							<h1><?php echo esc_html__( 'Visitor Logs', 'abp-plugin' ); ?></h1>
							<p><?php echo esc_html__( 'Every front-end visit with IP address, country, page, time on page and visit count.', 'abp-plugin' ); ?></p>
						</div>
						<div class="abp-vactions">
							<a class="abp-vbtn abp-vbtn-light" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU ) ); ?>"><?php echo esc_html__( 'Refresh', 'abp-plugin' ); ?></a>
							<a class="abp-vbtn abp-vbtn-primary" href="<?php echo esc_url( $this->export_url( $filters ) ); ?>"><?php echo esc_html__( 'Export CSV', 'abp-plugin' ); ?></a>
						</div>
					</div>

					<?php if ( 'blocked' === $notice ) : ?>
						<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'The IP address has been blocked.', 'abp-plugin' ); ?></p></div>
					<?php elseif ( 'deleted' === $notice ) : ?>
						<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'The log entry has been deleted.', 'abp-plugin' ); ?></p></div>
					<?php elseif ( 'cleared' === $notice ) : ?>
						<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'All visitor logs have been deleted.', 'abp-plugin' ); ?></p></div>
					<?php endif; ?>

					<div class="abp-vcards">
						<?php
						$this->stat_card( __( 'Unique visitors', 'abp-plugin' ), number_format_i18n( $stats['visitors'] ), __( 'All time', 'abp-plugin' ), 'dashicons-groups' );
						$this->stat_card( __( 'Total visits', 'abp-plugin' ), number_format_i18n( $stats['visits'] ), __( 'Sessions', 'abp-plugin' ), 'dashicons-calendar-alt' );
						$this->stat_card( __( 'Page views', 'abp-plugin' ), number_format_i18n( $stats['views'] ), __( 'Logged pages', 'abp-plugin' ), 'dashicons-media-document' );
						$this->stat_card( __( 'Today', 'abp-plugin' ), number_format_i18n( $stats['today'] ), __( 'Unique visitors', 'abp-plugin' ), 'dashicons-clock' );
						$this->stat_card( __( 'Online now', 'abp-plugin' ), number_format_i18n( $stats['online'] ), __( 'Last 5 minutes', 'abp-plugin' ), 'dashicons-pulse' );
						$this->stat_card( __( 'Time on page', 'abp-plugin' ), $this->duration( (int) $stats['avg'] ), __( 'Average per page', 'abp-plugin' ), 'dashicons-clock-alt' );
						$this->stat_card( __( 'Total watch time', 'abp-plugin' ), $this->duration( (int) $stats['duration'] ), __( 'All time', 'abp-plugin' ), 'dashicons-video-alt3' );
						$this->stat_card( __( 'Top country', 'abp-plugin' ), '' !== $stats['top_country'] ? $stats['top_country'] : '—', __( 'Most visitors', 'abp-plugin' ), 'dashicons-location-alt' );
						?>
					</div>

					<div class="abp-vgrid">
						<div class="abp-vpanel">
							<form method="get" class="abp-vfilters">
								<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU ); ?>" />
								<div class="abp-vfield">
									<label for="abp-date-from"><?php echo esc_html__( 'From', 'abp-plugin' ); ?></label>
									<input type="date" id="abp-date-from" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" />
								</div>
								<div class="abp-vfield">
									<label for="abp-date-to"><?php echo esc_html__( 'To', 'abp-plugin' ); ?></label>
									<input type="date" id="abp-date-to" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />
								</div>
								<div class="abp-vfield">
									<label for="abp-ip"><?php echo esc_html__( 'IP address', 'abp-plugin' ); ?></label>
									<input type="text" id="abp-ip" name="ip" value="<?php echo esc_attr( $filters['ip'] ); ?>" placeholder="203.0.113.10" />
								</div>
								<div class="abp-vfield">
									<label for="abp-email"><?php echo esc_html__( 'Email', 'abp-plugin' ); ?></label>
									<input type="text" id="abp-email" name="email" value="<?php echo esc_attr( $filters['email'] ); ?>" placeholder="customer@example.com" />
								</div>
								<div class="abp-vfield">
									<label for="abp-country"><?php echo esc_html__( 'Country', 'abp-plugin' ); ?></label>
									<input type="text" id="abp-country" name="country" value="<?php echo esc_attr( $filters['country'] ); ?>" />
								</div>
								<div class="abp-vfield">
									<label for="abp-device"><?php echo esc_html__( 'Device', 'abp-plugin' ); ?></label>
									<select id="abp-device" name="device">
										<option value=""><?php echo esc_html__( 'All devices', 'abp-plugin' ); ?></option>
										<?php foreach ( $devices as $key => $label ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filters['device'], $key ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="abp-vfield">
									<label for="abp-search"><?php echo esc_html__( 'Search page', 'abp-plugin' ); ?></label>
									<input type="text" id="abp-search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" />
								</div>
								<div class="abp-vfield abp-vfield-actions">
									<button type="submit" class="abp-vbtn abp-vbtn-primary"><?php echo esc_html__( 'Apply filters', 'abp-plugin' ); ?></button>
									<a class="abp-vbtn abp-vbtn-light" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU ) ); ?>"><?php echo esc_html__( 'Reset', 'abp-plugin' ); ?></a>
								</div>
							</form>
						</div>

						<div class="abp-vside">
							<div class="abp-vpanel">
								<h2><?php echo esc_html__( 'Top pages', 'abp-plugin' ); ?></h2>
								<?php $this->top_list_html( $top_pages ); ?>
							</div>
							<div class="abp-vpanel">
								<h2><?php echo esc_html__( 'Top countries', 'abp-plugin' ); ?></h2>
								<?php $this->top_list_html( $top_countries ); ?>
							</div>
						</div>
					</div>

					<div class="abp-vpanel abp-vpanel-table">
						<h2>
							<?php echo esc_html__( 'Visit history', 'abp-plugin' ); ?>
							<span class="abp-vcount"><?php echo esc_html( number_format_i18n( $total ) ); ?></span>
						</h2>

						<table class="widefat striped abp-vtable">
							<thead>
								<tr>
									<th><?php echo esc_html__( 'Date & time', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'IP address', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Country', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Email', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Page visited', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Time on page', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Visit', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Device', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Actions', 'abp-plugin' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( ! $rows ) : ?>
									<tr>
										<td colspan="9" class="abp-vempty"><?php echo esc_html__( 'No visitor has been recorded yet. Once somebody opens the site, the first entry appears here.', 'abp-plugin' ); ?></td>
									</tr>
								<?php else : ?>
									<?php foreach ( $rows as $row ) : ?>
										<?php
										$visit_pages   = isset( $session_stats['pages'][ $row['visit_id'] ] ) ? (int) $session_stats['pages'][ $row['visit_id'] ] : 1;
										$visit_seconds = isset( $session_stats['duration'][ $row['visit_id'] ] ) ? (int) $session_stats['duration'][ $row['visit_id'] ] : (int) $row['duration'];
										$total_visits  = isset( $session_stats['visits'][ $row['visitor_key'] ] ) ? (int) $session_stats['visits'][ $row['visitor_key'] ] : 1;
										?>
										<tr>
											<td class="abp-vnowrap"><?php echo esc_html( $row['visited_at'] ); ?></td>
											<td>
												<code><?php echo esc_html( $row['ip'] ); ?></code>
												<?php if ( ! empty( $row['is_bot'] ) ) : ?>
													<span class="abp-vpill abp-vpill-bot"><?php echo esc_html__( 'Bot', 'abp-plugin' ); ?></span>
												<?php endif; ?>
											</td>
											<td><?php echo esc_html( $row['country'] ); ?></td>
											<td>
												<?php if ( '' !== $row['email'] ) : ?>
													<a href="mailto:<?php echo esc_attr( $row['email'] ); ?>"><?php echo esc_html( $row['email'] ); ?></a>
												<?php else : ?>
													<span class="abp-vmuted"><?php echo esc_html__( 'Anonymous', 'abp-plugin' ); ?></span>
												<?php endif; ?>
											</td>
											<td>
												<a class="abp-vlink" href="<?php echo esc_url( $row['page_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( '' !== $row['page_title'] ? $row['page_title'] : $row['page_url'] ); ?></a>
												<span class="abp-vmuted abp-vsmall"><?php echo esc_html( $row['page_url'] ); ?></span>
											</td>
											<td><?php echo esc_html( $this->duration( (int) $row['duration'] ) ); ?></td>
											<td>
												<span class="abp-vpill">#<?php echo esc_html( (int) $row['visit_number'] ); ?></span>
												<span class="abp-vmuted abp-vsmall">
													<?php
													printf(
														/* translators: 1: total visits, 2: pages in this visit */
														esc_html__( '%1$s visits &middot; %2$s pages', 'abp-plugin' ),
														esc_html( number_format_i18n( $total_visits ) ),
														esc_html( number_format_i18n( $visit_pages ) )
													);
													?>
												</span>
												<span class="abp-vmuted abp-vsmall"><?php echo esc_html( $this->duration( $visit_seconds ) ); ?> <?php echo esc_html__( 'in this visit', 'abp-plugin' ); ?></span>
											</td>
											<td>
												<span class="abp-vpill"><?php echo esc_html( $row['device'] ); ?></span>
												<span class="abp-vmuted abp-vsmall"><?php echo esc_html( $row['browser'] . ' &middot; ' . $row['os'] ); ?></span>
											</td>
											<td>
												<div class="abp-vrow-actions">
													<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vinline">
														<?php wp_nonce_field( 'abp_visitor_block_' . $row['id'] ); ?>
														<input type="hidden" name="action" value="abp_visitor_block" />
														<input type="hidden" name="log_id" value="<?php echo esc_attr( $row['id'] ); ?>" />
														<input type="hidden" name="ip" value="<?php echo esc_attr( $row['ip'] ); ?>" />
														<button type="submit" class="abp-vlinkbtn abp-vlinkbtn-danger"><?php echo esc_html__( 'Block', 'abp-plugin' ); ?></button>
													</form>
													<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vinline">
														<?php wp_nonce_field( 'abp_visitor_log_delete_' . $row['id'] ); ?>
														<input type="hidden" name="action" value="abp_visitor_log_delete" />
														<input type="hidden" name="log_id" value="<?php echo esc_attr( $row['id'] ); ?>" />
														<button type="submit" class="abp-vlinkbtn"><?php echo esc_html__( 'Delete', 'abp-plugin' ); ?></button>
													</form>
												</div>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>

						<?php if ( $pages > 1 ) : ?>
							<div class="abp-vpager">
								<?php
								$page_args = array_filter(
									$filters,
									static function ( $value ) {
										return '' !== $value && null !== $value;
									}
								);
								$page_args['page'] = self::MENU;
								$base = add_query_arg( $page_args, admin_url( 'admin.php' ) );
								echo wp_kses_post(
									paginate_links(
										array(
											'base'      => add_query_arg( 'paged', '%#%', $base ),
											'format'    => '',
											'current'   => $paged,
											'total'     => $pages,
											'prev_text' => '&larr;',
											'next_text' => '&rarr;',
											'type'      => 'plain',
										)
									)
								);
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}

			public function page_blocked(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to view this page.', 'abp-plugin' ) );
				}

				global $wpdb;

				$table = self::table( 'blocked' );
				$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC", ARRAY_A );
				$rows  = is_array( $rows ) ? $rows : array();

				$notice = isset( $_GET['abp_msg'] ) ? sanitize_key( wp_unslash( $_GET['abp_msg'] ) ) : '';

				?>
				<div class="wrap abp-vwrap">
					<div class="abp-vhead">
						<div>
							<span class="abp-vkicker"><?php echo esc_html__( 'ABP Visitor Tracking', 'abp-plugin' ); ?></span>
							<h1><?php echo esc_html__( 'Blocked IP addresses', 'abp-plugin' ); ?></h1>
							<p><?php echo esc_html__( 'Blocked addresses cannot load any page of the site. Administrators are never locked out.', 'abp-plugin' ); ?></p>
						</div>
					</div>

					<?php if ( 'added' === $notice ) : ?>
						<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'The IP address has been blocked.', 'abp-plugin' ); ?></p></div>
					<?php elseif ( 'unblocked' === $notice ) : ?>
						<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'The IP address has been unblocked.', 'abp-plugin' ); ?></p></div>
					<?php endif; ?>

					<div class="abp-vpanel">
						<h2><?php echo esc_html__( 'Block a new address', 'abp-plugin' ); ?></h2>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vblockform">
							<?php wp_nonce_field( 'abp_visitor_block_new' ); ?>
							<input type="hidden" name="action" value="abp_visitor_block" />
							<div class="abp-vfield">
								<label for="abp-block-ip"><?php echo esc_html__( 'IP address', 'abp-plugin' ); ?></label>
								<input type="text" id="abp-block-ip" name="ip" required placeholder="203.0.113.10" />
							</div>
							<div class="abp-vfield">
								<label for="abp-block-hours"><?php echo esc_html__( 'Duration', 'abp-plugin' ); ?></label>
								<select id="abp-block-hours" name="hours">
									<option value="0"><?php echo esc_html__( 'Permanent', 'abp-plugin' ); ?></option>
									<option value="1"><?php echo esc_html__( '1 hour', 'abp-plugin' ); ?></option>
									<option value="24"><?php echo esc_html__( '24 hours', 'abp-plugin' ); ?></option>
									<option value="168"><?php echo esc_html__( '7 days', 'abp-plugin' ); ?></option>
									<option value="720"><?php echo esc_html__( '30 days', 'abp-plugin' ); ?></option>
								</select>
							</div>
							<div class="abp-vfield abp-vfield-wide">
								<label for="abp-block-reason"><?php echo esc_html__( 'Reason', 'abp-plugin' ); ?></label>
								<input type="text" id="abp-block-reason" name="reason" placeholder="<?php echo esc_attr__( 'Spam, brute force, scraper...', 'abp-plugin' ); ?>" />
							</div>
							<div class="abp-vfield abp-vfield-actions">
								<button type="submit" class="abp-vbtn abp-vbtn-danger"><?php echo esc_html__( 'Block address', 'abp-plugin' ); ?></button>
							</div>
						</form>
					</div>

					<div class="abp-vpanel abp-vpanel-table">
						<h2>
							<?php echo esc_html__( 'Blocked list', 'abp-plugin' ); ?>
							<span class="abp-vcount"><?php echo esc_html( number_format_i18n( count( $rows ) ) ); ?></span>
						</h2>
						<table class="widefat striped abp-vtable">
							<thead>
								<tr>
									<th><?php echo esc_html__( 'IP address', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Reason', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Blocked attempts', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Created', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Last blocked', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Expires', 'abp-plugin' ); ?></th>
									<th><?php echo esc_html__( 'Actions', 'abp-plugin' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( ! $rows ) : ?>
									<tr>
										<td colspan="7" class="abp-vempty"><?php echo esc_html__( 'No address is blocked right now.', 'abp-plugin' ); ?></td>
									</tr>
								<?php else : ?>
									<?php foreach ( $rows as $row ) : ?>
										<tr>
											<td><code><?php echo esc_html( $row['ip'] ); ?></code></td>
											<td><?php echo esc_html( '' !== $row['reason'] ? $row['reason'] : '—' ); ?></td>
											<td><?php echo esc_html( number_format_i18n( (int) $row['hits'] ) ); ?></td>
											<td class="abp-vnowrap"><?php echo esc_html( $row['created_at'] ); ?></td>
											<td class="abp-vnowrap"><?php echo esc_html( '' !== $row['last_blocked_at'] ? $row['last_blocked_at'] : '—' ); ?></td>
											<td class="abp-vnowrap"><?php echo esc_html( '' !== $row['expires_at'] ? $row['expires_at'] : __( 'Never', 'abp-plugin' ) ); ?></td>
											<td>
												<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vinline">
													<?php wp_nonce_field( 'abp_visitor_unblock_' . $row['id'] ); ?>
													<input type="hidden" name="action" value="abp_visitor_unblock" />
													<input type="hidden" name="block_id" value="<?php echo esc_attr( $row['id'] ); ?>" />
													<button type="submit" class="abp-vlinkbtn"><?php echo esc_html__( 'Unblock', 'abp-plugin' ); ?></button>
												</form>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php
			}

			public function page_settings(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to view this page.', 'abp-plugin' ) );
				}

				$notice = isset( $_GET['abp_msg'] ) ? sanitize_key( wp_unslash( $_GET['abp_msg'] ) ) : '';

				?>
				<div class="wrap abp-vwrap">
					<div class="abp-vhead">
						<div>
							<span class="abp-vkicker"><?php echo esc_html__( 'ABP Visitor Tracking', 'abp-plugin' ); ?></span>
							<h1><?php echo esc_html__( 'Tracker settings', 'abp-plugin' ); ?></h1>
							<p><?php echo esc_html__( 'Control what is recorded, how long the data is kept and how blocked visitors are handled.', 'abp-plugin' ); ?></p>
						</div>
					</div>

					<?php if ( 'saved' === $notice ) : ?>
						<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved.', 'abp-plugin' ); ?></p></div>
					<?php endif; ?>

					<div class="abp-vpanel">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'abp_visitor_config_save' ); ?>
							<input type="hidden" name="action" value="abp_visitor_config" />

							<h2><?php echo esc_html__( 'Recording', 'abp-plugin' ); ?></h2>

							<label class="abp-vswitch">
								<input type="checkbox" name="tracking_enabled" value="1" <?php checked( '1', $this->config( 'tracking_enabled' ) ); ?> />
								<span><?php echo esc_html__( 'Record visitor visits', 'abp-plugin' ); ?></span>
							</label>
							<label class="abp-vswitch">
								<input type="checkbox" name="track_duration" value="1" <?php checked( '1', $this->config( 'track_duration' ) ); ?> />
								<span><?php echo esc_html__( 'Measure time spent on each page', 'abp-plugin' ); ?></span>
							</label>
							<label class="abp-vswitch">
								<input type="checkbox" name="geo_lookup" value="1" <?php checked( '1', $this->config( 'geo_lookup' ) ); ?> />
								<span><?php echo esc_html__( 'Look up the country of anonymous visitors', 'abp-plugin' ); ?></span>
							</label>
							<label class="abp-vswitch">
								<input type="checkbox" name="log_bots" value="1" <?php checked( '1', $this->config( 'log_bots' ) ); ?> />
								<span><?php echo esc_html__( 'Record bots and crawlers', 'abp-plugin' ); ?></span>
							</label>
							<label class="abp-vswitch">
								<input type="checkbox" name="log_admins" value="1" <?php checked( '1', $this->config( 'log_admins' ) ); ?> />
								<span><?php echo esc_html__( 'Record my own administrator visits', 'abp-plugin' ); ?></span>
							</label>

							<h2><?php echo esc_html__( 'Schedule', 'abp-plugin' ); ?></h2>

							<div class="abp-vrow">
								<div class="abp-vfield">
									<label for="abp-session-gap"><?php echo esc_html__( 'New visit after (minutes of inactivity)', 'abp-plugin' ); ?></label>
									<input type="number" id="abp-session-gap" name="session_gap" min="1" max="1440" value="<?php echo esc_attr( $this->config( 'session_gap' ) ); ?>" />
								</div>
								<div class="abp-vfield">
									<label for="abp-retention"><?php echo esc_html__( 'Keep data for (days, 0 = forever)', 'abp-plugin' ); ?></label>
									<input type="number" id="abp-retention" name="retention_days" min="0" max="3650" value="<?php echo esc_attr( $this->config( 'retention_days' ) ); ?>" />
								</div>
								<div class="abp-vfield">
									<label for="abp-heartbeat"><?php echo esc_html__( 'Online heartbeat (seconds, 0 = off)', 'abp-plugin' ); ?></label>
									<input type="number" id="abp-heartbeat" name="heartbeat" min="0" max="600" value="<?php echo esc_attr( $this->config( 'heartbeat' ) ); ?>" />
								</div>
							</div>

							<h2><?php echo esc_html__( 'Blocked visitor message', 'abp-plugin' ); ?></h2>
							<textarea name="blocked_message" rows="4" class="abp-vtextarea"><?php echo esc_textarea( $this->config( 'blocked_message' ) ); ?></textarea>

							<p class="abp-vnote"><?php echo esc_html__( 'Administrators always keep access, and the local server address can never be blocked.', 'abp-plugin' ); ?></p>

							<button type="submit" class="abp-vbtn abp-vbtn-primary"><?php echo esc_html__( 'Save settings', 'abp-plugin' ); ?></button>
						</form>
					</div>

					<div class="abp-vpanel">
						<h2><?php echo esc_html__( 'Stored data', 'abp-plugin' ); ?></h2>
						<p class="abp-vnote"><?php echo esc_html__( 'Removing the logs is permanent. Blocked addresses are kept until they are unblocked.', 'abp-plugin' ); ?></p>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vinline">
							<?php wp_nonce_field( 'abp_visitor_clear_logs' ); ?>
							<input type="hidden" name="action" value="abp_visitor_clear" />
							<button type="submit" class="abp-vbtn abp-vbtn-danger"><?php echo esc_html__( 'Delete all visitor logs', 'abp-plugin' ); ?></button>
						</form>
					</div>
				</div>
				<?php
			}

			/* -----------------------------------------------------------------
			 * Admin helpers
			 * -------------------------------------------------------------- */
			private function stat_card( string $label, string $value, string $hint, string $icon ): void {
				?>
				<div class="abp-vcard">
					<span class="abp-vcard-icon"><span class="dashicons <?php echo esc_attr( $icon ); ?>"></span></span>
					<span class="abp-vcard-label"><?php echo esc_html( $label ); ?></span>
					<span class="abp-vcard-value"><?php echo esc_html( $value ); ?></span>
					<span class="abp-vcard-hint"><?php echo esc_html( $hint ); ?></span>
				</div>
				<?php
			}

			private function session_stats( array $rows ): array {
				global $wpdb;

				$result = array(
					'pages'    => array(),
					'duration' => array(),
					'visits'   => array(),
				);

				if ( ! $rows ) {
					return $result;
				}

				$table = self::table( 'logs' );

				$visit_ids = array();
				$keys      = array();
				foreach ( $rows as $row ) {
					$visit_ids[] = (string) $row['visit_id'];
					$keys[]      = (string) $row['visitor_key'];
				}

				$visit_ids = array_values( array_unique( array_filter( $visit_ids ) ) );
				$keys      = array_values( array_unique( array_filter( $keys ) ) );

				if ( $visit_ids ) {
					$placeholders = implode( ', ', array_fill( 0, count( $visit_ids ), '%s' ) );
					$rows_pages   = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT visit_id, COUNT(*) AS pages, COALESCE(SUM(duration),0) AS seconds FROM {$table} WHERE visit_id IN ({$placeholders}) GROUP BY visit_id",
							$visit_ids
						),
						ARRAY_A
					);
					foreach ( (array) $rows_pages as $line ) {
						$result['pages'][ $line['visit_id'] ]    = (int) $line['pages'];
						$result['duration'][ $line['visit_id'] ] = (int) $line['seconds'];
					}
				}

				if ( $keys ) {
					$placeholders = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );
					$rows_visits  = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT visitor_key, COUNT(DISTINCT visit_id) AS visits FROM {$table} WHERE visitor_key IN ({$placeholders}) GROUP BY visitor_key",
							$keys
						),
						ARRAY_A
					);
					foreach ( (array) $rows_visits as $line ) {
						$result['visits'][ $line['visitor_key'] ] = (int) $line['visits'];
					}
				}

				return $result;
			}

			private function overall_stats(): array {
				global $wpdb;

				$table = self::table( 'logs' );
				$today = date( 'Y-m-d', current_time( 'timestamp' ) );
				$since = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - self::ONLINE_WINDOW );

				$stats = array(
					'visitors'     => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT visitor_key) FROM {$table}" ),
					'visits'       => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT visit_id) FROM {$table}" ),
					'views'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ),
					'today'        => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT visitor_key) FROM {$table} WHERE visited_at >= %s", $today . ' 00:00:00' ) ),
					'online'       => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT visitor_key) FROM {$table} WHERE last_seen >= %s", $since ) ),
					'duration'     => (int) $wpdb->get_var( "SELECT COALESCE(SUM(duration),0) FROM {$table}" ),
					'avg'          => (float) $wpdb->get_var( "SELECT COALESCE(AVG(duration),0) FROM {$table}" ),
					'top_country'  => '',
				);

				$top_country = $wpdb->get_row( "SELECT country FROM {$table} WHERE country <> '' GROUP BY country ORDER BY COUNT(*) DESC LIMIT 1", ARRAY_A );
				if ( $top_country && ! empty( $top_country['country'] ) ) {
					$stats['top_country'] = (string) $top_country['country'];
				}

				return $stats;
			}

			private function top_list( string $group_field, string $label_field ): array {
				global $wpdb;

				$table = self::table( 'logs' );
				$rows  = $wpdb->get_results(
					"SELECT {$label_field} AS label, {$group_field} AS key_value, COUNT(*) AS hits FROM {$table} GROUP BY {$group_field}, {$label_field} ORDER BY hits DESC LIMIT 6",
					ARRAY_A
				);

				return is_array( $rows ) ? $rows : array();
			}

			private function top_list_html( array $rows ): void {
				if ( ! $rows ) {
					echo '<p class="abp-vmuted">' . esc_html__( 'No data recorded yet.', 'abp-plugin' ) . '</p>';
					return;
				}

				echo '<ul class="abp-vtoplist">';
				foreach ( $rows as $row ) {
					$label = '' !== (string) $row['label'] ? (string) $row['label'] : (string) $row['key_value'];
					echo '<li><span class="abp-vtoplabel">' . esc_html( $label ) . '</span><span class="abp-vtophits">' . esc_html( number_format_i18n( (int) $row['hits'] ) ) . '</span></li>';
				}
				echo '</ul>';
			}

			private function duration( int $seconds ): string {
				if ( $seconds < 1 ) {
					return '0s';
				}
				if ( $seconds < 60 ) {
					return $seconds . 's';
				}
				if ( $seconds < 3600 ) {
					return floor( $seconds / 60 ) . 'm ' . str_pad( (string) ( $seconds % 60 ), 2, '0', STR_PAD_LEFT ) . 's';
				}

				return floor( $seconds / 3600 ) . 'h ' . str_pad( (string) floor( ( $seconds % 3600 ) / 60 ), 2, '0', STR_PAD_LEFT ) . 'm';
			}

			private function export_url( array $filters ): string {
				$args = array_filter(
					$filters,
					static function ( $value ) {
						return '' !== $value && null !== $value;
					}
				);
				$args['action'] = 'abp_visitor_export';

				return wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), 'abp_visitor_export' );
			}

			/* -----------------------------------------------------------------
			 * Admin actions
			 * -------------------------------------------------------------- */
			public function post_block(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to perform this action.', 'abp-plugin' ) );
				}

				$log_id = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
				if ( $log_id > 0 ) {
					check_admin_referer( 'abp_visitor_block_' . $log_id );
				} else {
					check_admin_referer( 'abp_visitor_block_new' );
				}

				$ip     = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
				$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
				$hours  = isset( $_POST['hours'] ) ? absint( $_POST['hours'] ) : 0;

				if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					wp_safe_redirect( add_query_arg( 'abp_msg', 'error', admin_url( 'admin.php?page=' . self::SUB_BLOCKED ) ) );
					exit;
				}

				$this->block_ip( $ip, $reason, $hours );

				wp_safe_redirect( add_query_arg( 'abp_msg', 'added', admin_url( 'admin.php?page=' . self::SUB_BLOCKED ) ) );
				exit;
			}

			private function block_ip( string $ip, string $reason, int $hours ): void {
				global $wpdb;

				$table      = self::table( 'blocked' );
				$expires_at = $hours > 0 ? date( 'Y-m-d H:i:s', current_time( 'timestamp' ) + ( $hours * HOUR_IN_SECONDS ) ) : '';
				$now        = $this->now();

				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$table} (ip, reason, hits, created_at, last_blocked_at, expires_at, created_by) VALUES (%s, %s, 0, %s, NULL, %s, %d)
						ON DUPLICATE KEY UPDATE reason = VALUES(reason), expires_at = VALUES(expires_at)",
						$ip,
						$reason,
						$now,
						'' !== $expires_at ? $expires_at : null,
						get_current_user_id()
					)
				);

				$this->blocked_cache[ $ip ] = true;
			}

			public function post_unblock(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to perform this action.', 'abp-plugin' ) );
				}

				$block_id = isset( $_POST['block_id'] ) ? absint( $_POST['block_id'] ) : 0;
				check_admin_referer( 'abp_visitor_unblock_' . $block_id );

				global $wpdb;
				$wpdb->delete( self::table( 'blocked' ), array( 'id' => $block_id ), array( '%d' ) );
				$this->refresh_block_cache();

				wp_safe_redirect( add_query_arg( 'abp_msg', 'unblocked', admin_url( 'admin.php?page=' . self::SUB_BLOCKED ) ) );
				exit;
			}

			public function post_log_delete(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to perform this action.', 'abp-plugin' ) );
				}

				$log_id = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
				check_admin_referer( 'abp_visitor_log_delete_' . $log_id );

				global $wpdb;
				$wpdb->delete( self::table( 'logs' ), array( 'id' => $log_id ), array( '%d' ) );

				wp_safe_redirect( add_query_arg( 'abp_msg', 'deleted', admin_url( 'admin.php?page=' . self::MENU ) ) );
				exit;
			}

			public function post_config(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to perform this action.', 'abp-plugin' ) );
				}

				check_admin_referer( 'abp_visitor_config_save' );

				$config = array(
					'tracking_enabled' => isset( $_POST['tracking_enabled'] ) ? '1' : '0',
					'track_duration'   => isset( $_POST['track_duration'] ) ? '1' : '0',
					'geo_lookup'       => isset( $_POST['geo_lookup'] ) ? '1' : '0',
					'log_bots'         => isset( $_POST['log_bots'] ) ? '1' : '0',
					'log_admins'       => isset( $_POST['log_admins'] ) ? '1' : '0',
					'session_gap'      => isset( $_POST['session_gap'] ) ? max( 1, min( 1440, absint( $_POST['session_gap'] ) ) ) : '30',
					'retention_days'   => isset( $_POST['retention_days'] ) ? max( 0, min( 3650, absint( $_POST['retention_days'] ) ) ) : '90',
					'heartbeat'        => isset( $_POST['heartbeat'] ) ? max( 0, min( 600, absint( $_POST['heartbeat'] ) ) ) : '60',
					'blocked_message'  => isset( $_POST['blocked_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['blocked_message'] ) ) : '',
				);

				update_option( self::OPTION_CONFIG, $config );

				wp_safe_redirect( add_query_arg( 'abp_msg', 'saved', admin_url( 'admin.php?page=' . self::SUB_SETTINGS ) ) );
				exit;
			}

			public function post_clear(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to perform this action.', 'abp-plugin' ) );
				}

				check_admin_referer( 'abp_visitor_clear_logs' );

				global $wpdb;
				$table = self::table( 'logs' );
				$wpdb->query( "TRUNCATE TABLE {$table}" );

				wp_safe_redirect( add_query_arg( 'abp_msg', 'cleared', admin_url( 'admin.php?page=' . self::MENU ) ) );
				exit;
			}

			public function post_export(): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have permission to perform this action.', 'abp-plugin' ) );
				}

				check_admin_referer( 'abp_visitor_export' );

				global $wpdb;

				$filters = $this->filters();
				$where   = $this->build_where( $filters );
				$table   = self::table( 'logs' );

				$query = "SELECT visited_at, ip, country, email, page_title, page_url, referrer, device, browser, os, duration, visit_number FROM {$table} WHERE {$where['where']} ORDER BY id DESC";

				$rows = $where['args'] ? $wpdb->get_results( $wpdb->prepare( $query, $where['args'] ), ARRAY_A ) : $wpdb->get_results( $query, ARRAY_A );
				$rows = is_array( $rows ) ? $rows : array();

				nocache_headers();
				header( 'Content-Type: text/csv; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename="abp-visitor-logs-' . gmdate( 'Y-m-d-Hi' ) . '.csv"' );

				$output = fopen( 'php://output', 'w' );
				fputcsv( $output, array( 'Date & time', 'IP address', 'Country', 'Email', 'Page title', 'Page URL', 'Referrer', 'Device', 'Browser', 'OS', 'Time on page (seconds)', 'Visit number' ) );

				foreach ( $rows as $row ) {
					fputcsv(
						$output,
						array(
							$row['visited_at'],
							$row['ip'],
							$row['country'],
							$row['email'],
							$row['page_title'],
							$row['page_url'],
							$row['referrer'],
							$row['device'],
							$row['browser'],
							$row['os'],
							(int) $row['duration'],
							(int) $row['visit_number'],
						)
					);
				}

				fclose( $output );
				exit;
			}
		}
		ABP_Visitor_Tracker::instance();
	}
