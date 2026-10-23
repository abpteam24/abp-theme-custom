<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ABP_IA_Visitors' ) ) {

	/**
	 * Visitor list for ABP, built directly on Independent Analytics data.
	 *
	 * Independent Analytics is the single source of truth. The few fields the
	 * visitor list needs but Independent Analytics does not keep (raw IP, user
	 * agent, email, WordPress user id and the customer key) are stored as
	 * extra columns on the Independent Analytics sessions table. There is no
	 * second data store.
	 *
	 * The whole feature stays dormant until Independent Analytics is active.
	 */
	class ABP_IA_Visitors {

		const VERSION        = '2.0.0';
		const EXT_OPTION     = 'abp_ia_schema_version';
		const EXT_VERSION    = '2.0.0';
		const LEGACY_OPTION  = 'abp_ia_legacy_dropped';
		const CAPTURE_OPTION = 'abp_ia_capture_details';
		const EMAIL_PREFIX   = 'abp_ia_pending_email_';
		const NONCE          = 'abp_ia_visitors';
		const MENU           = 'abp-visitors';
		const SUB_BLOCKED    = 'abp-visitors-blocked';
		const SUB_SETTINGS   = 'abp-visitors-settings';
		const PER_PAGE       = 25;
		const ONLINE_MINUTES = 5;
		const EMAIL_TTL      = 604800;

		/**
		 * Columns an earlier build added to the Independent Analytics sessions
		 * table. They are moved into our own table and dropped from IA's table,
		 * so an Independent Analytics update can never erase them.
		 */
		const LEGACY_COLUMNS = array( 'abp_ip', 'abp_user_agent', 'abp_email', 'abp_user_id', 'abp_customer_key' );

		/** Tables written by the previous ABP tracker, removed on upgrade. */
		const LEGACY_TABLES = array( 'visitor_logs', 'blocked_ips' );

		private static $instance = null;
		private static $active   = null;

		public static function instance(): self {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			add_action( 'init', array( $this, 'maybe_upgrade' ), 2 );
			add_filter( 'rest_request_after_callbacks', array( $this, 'capture_request' ), 10, 3 );
			add_action( 'edd_complete_purchase', array( $this, 'capture_purchase_email' ), 10, 3 );
			add_action( 'user_register', array( $this, 'capture_registered_email' ), 20, 1 );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
			add_action( 'admin_notices', array( $this, 'missing_analytics_notice' ) );
			add_action( 'admin_post_abp_ia_block', array( $this, 'post_block' ) );
			add_action( 'admin_post_abp_ia_unblock', array( $this, 'post_unblock' ) );
			add_action( 'admin_post_abp_ia_export', array( $this, 'post_export' ) );
			add_action( 'admin_post_abp_ia_settings', array( $this, 'post_settings' ) );
			add_action( 'admin_post_abp_ia_clear', array( $this, 'post_clear' ) );
		}

		/* -----------------------------------------------------------------
		 * Independent Analytics detection
		 * -------------------------------------------------------------- */

		/**
		 * Independent Analytics must be loaded and its sessions table present.
		 */
		public static function is_active(): bool {
			if ( null !== self::$active ) {
				return self::$active;
			}

			self::$active = false;

			if ( ! defined( 'IAWP_VERSION' ) ) {
				return false;
			}

			global $wpdb;
			$table = self::ia_table( 'sessions' );

			self::$active = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

			return self::$active;
		}

		/**
		 * Table name of one Independent Analytics table.
		 */
		public static function ia_table( string $name ): string {
			global $wpdb;

			return $wpdb->prefix . 'independent_analytics_' . $name;
		}

		/**
		 * The salt Independent Analytics uses for its visitor hashes.
		 */
		public static function salt(): string {
			return (string) get_option( 'iawp_salt', '' );
		}

		/**
		 * The salt Independent Analytics uses for REST payload signatures.
		 */
		public static function payload_salt(): string {
			return (string) get_option( 'iawp_request_payload_salt', '' );
		}

		/* -----------------------------------------------------------------
		 * Storage
		 * -------------------------------------------------------------- */

		public function maybe_upgrade(): void {
			if ( ! get_option( self::LEGACY_OPTION ) ) {
				self::drop_legacy();
				update_option( self::LEGACY_OPTION, time(), false );
			}

			if ( ! self::is_active() ) {
				return;
			}

			if ( get_option( self::EXT_OPTION ) !== self::EXT_VERSION ) {
				self::install_schema();
			} elseif ( ! get_transient( 'abp_ia_meta_ok' ) ) {
				global $wpdb;

				if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', self::meta_table() ) ) ) {
					self::install_schema();
				}

				set_transient( 'abp_ia_meta_ok', 1, HOUR_IN_SECONDS );
			}

			self::maybe_prune();
		}

		/**
		 * The ABP owned table that keeps the fields Independent Analytics does
		 * not store.
		 *
		 * Independent Analytics recreates its own tables during updates. Keeping
		 * the extra data in a table we own, linked by session_id, means an IA
		 * update can never drop it.
		 */
		public static function meta_table(): string {
			global $wpdb;

			return $wpdb->prefix . 'ia_meta';
		}

		/**
		 * Remove the tables of the retired standalone tracker.
		 */
		public static function drop_legacy(): void {
			global $wpdb;

			foreach ( self::LEGACY_TABLES as $suffix ) {
				$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $suffix );
			}

			delete_option( 'abp_visitor_db_version' );
			delete_option( 'abp_visitor_config' );

			if ( wp_next_scheduled( 'abp_visitor_daily_cleanup' ) ) {
				wp_clear_scheduled_hook( 'abp_visitor_daily_cleanup' );
			}
		}

		/**
		 * Create our own meta table and move any data the earlier build wrote on
		 * the Independent Analytics sessions table into it.
		 */
		public static function install_schema(): void {
			global $wpdb;

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$table   = self::meta_table();
			$charset = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				session_id bigint(20) unsigned NOT NULL,
				visitor_id bigint(20) unsigned NOT NULL DEFAULT 0,
				abp_ip varchar(45) NOT NULL DEFAULT '',
				abp_user_agent varchar(255) NOT NULL DEFAULT '',
				abp_email varchar(190) NOT NULL DEFAULT '',
				abp_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				abp_customer_key varchar(64) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY session_id (session_id),
				KEY visitor_id (visitor_id),
				KEY abp_ip (abp_ip),
				KEY abp_customer_key (abp_customer_key),
				KEY created_at (created_at)
			) {$charset};";

			dbDelta( $sql );

			self::migrate_legacy_columns();

			update_option( self::EXT_OPTION, self::EXT_VERSION, false );
		}

		/**
		 * Copy the values an earlier build stored on IA's sessions table into
		 * our table, then remove those columns so IA owns its schema again.
		 */
		private static function migrate_legacy_columns(): void {
			global $wpdb;

			$sessions = self::ia_table( 'sessions' );
			$columns  = $wpdb->get_col( "SHOW COLUMNS FROM {$sessions}", 0 );

			if ( ! is_array( $columns ) || ! $columns ) {
				return;
			}

			$columns = array_map( 'strtolower', $columns );

			if ( ! in_array( 'abp_ip', $columns, true ) ) {
				return;
			}

			$table = self::meta_table();

			$wpdb->query(
				"INSERT IGNORE INTO {$table}
					( session_id, visitor_id, abp_ip, abp_user_agent, abp_email, abp_user_id, abp_customer_key, created_at, updated_at )
				SELECT s.session_id, s.visitor_id, s.abp_ip, s.abp_user_agent, s.abp_email, s.abp_user_id, s.abp_customer_key, s.created_at, s.created_at
				FROM {$sessions} s
				WHERE s.abp_ip <> '' OR s.abp_email <> '' OR s.abp_user_id > 0"
			);

			if ( '' !== (string) $wpdb->last_error ) {
				return;
			}

			$drops = array();

			foreach ( self::LEGACY_COLUMNS as $name ) {
				if ( in_array( $name, $columns, true ) ) {
					$drops[] = "DROP COLUMN {$name}";
				}
			}

			if ( $drops ) {
				$wpdb->query( "ALTER TABLE {$sessions} " . implode( ', ', $drops ) );
			}
		}

		/**
		 * Drop meta rows whose Independent Analytics session no longer exists,
		 * at most once a day. This keeps the table clean after IA retention
		 * clears old sessions or rebuilds its tables.
		 */
		private static function maybe_prune(): void {
			$last = (int) get_option( 'abp_ia_meta_pruned', 0 );

			if ( $last > 0 && ( time() - $last ) < DAY_IN_SECONDS ) {
				return;
			}

			update_option( 'abp_ia_meta_pruned', time(), false );

			global $wpdb;

			$meta     = self::meta_table();
			$sessions = self::ia_table( 'sessions' );

			$wpdb->query(
				"DELETE m FROM {$meta} m
				LEFT JOIN {$sessions} s ON s.session_id = m.session_id
				WHERE s.session_id IS NULL"
			);
		}

		public static function activate(): void {
			self::drop_legacy();
			update_option( self::LEGACY_OPTION, time(), false );

			if ( self::is_active() ) {
				self::install_schema();
			}
		}

		/* -----------------------------------------------------------------
		 * Request helpers
		 * -------------------------------------------------------------- */

		/**
		 * The IP address Independent Analytics hashes the visitor with.
		 *
		 * The value is deliberately not trimmed: Independent Analytics uses
		 * the first comma separated header value verbatim.
		 */
		public static function request_ip(): string {
			$headers = array(
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'REMOTE_ADDR',
				'HTTP_CF_CONNECTING_IP',
				'HTTP_CLIENT_IP',
				'HTTP_INCAP_CLIENT_IP',
			);

			$custom = apply_filters( 'iawp_header_with_ip_address', null );
			if ( is_string( $custom ) && preg_match( '/^[a-zA-Z_]+$/', $custom ) ) {
				array_unshift( $headers, $custom );
			}

			foreach ( $headers as $header ) {
				if ( ! isset( $_SERVER[ $header ] ) ) {
					continue;
				}

				$value = explode( ',', (string) wp_unslash( $_SERVER[ $header ] ) );
				$ip    = $value[0];

				if ( '' !== $ip ) {
					return $ip;
				}
			}

			return '';
		}

		public static function request_user_agent(): string {
			return isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
		}

		/**
		 * Reproduce the Independent Analytics visitor hash.
		 */
		public static function visitor_hash( string $ip, string $user_agent ): string {
			$salt = self::salt();

			if ( '' === $salt || '' === $ip ) {
				return '';
			}

			return md5( $salt . $ip . $user_agent );
		}

		public static function visitor_id_for( string $ip, string $user_agent ): int {
			$hash = self::visitor_hash( $ip, $user_agent );

			if ( '' === $hash ) {
				return 0;
			}

			global $wpdb;
			$table = self::ia_table( 'visitors' );

			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT visitor_id FROM {$table} WHERE hash = %s LIMIT 1", $hash ) );
		}

		/**
		 * The customer key a visit belongs to.
		 *
		 * A signed in account wins over an email, an email wins over the IP
		 * address and the IP address wins over the analytics visitor. This way
		 * the same customer resolves to one single list entry.
		 */
		public static function customer_key( int $user_id, string $email, string $ip, int $visitor_id ): string {
			if ( $user_id > 0 ) {
				return 'u:' . $user_id;
			}

			if ( '' !== $email ) {
				return 'e:' . strtolower( $email );
			}

			if ( '' !== $ip ) {
				return 'i:' . trim( $ip );
			}

			return 'v:' . $visitor_id;
		}

		/**
		 * The same rule as customer_key(), expressed in SQL.
		 */
		/**
		 * The same rule as customer_key(), expressed in SQL.
		 *
		 * The meta alias holds the captured fields, the session alias holds the
		 * analytics visitor id used as the final fallback.
		 */
		private static function key_sql( string $meta = 'm', string $session = 's' ): string {
			return "CASE
				WHEN {$meta}.abp_user_id > 0 THEN CONCAT('u:', {$meta}.abp_user_id)
				WHEN {$meta}.abp_email <> '' THEN CONCAT('e:', {$meta}.abp_email)
				WHEN {$meta}.abp_ip <> '' THEN CONCAT('i:', {$meta}.abp_ip)
				ELSE CONCAT('v:', {$session}.visitor_id)
			END";
		}

		/**
		 * Join our meta table to an Independent Analytics session query.
		 *
		 * The visitor id is part of the condition on purpose: if Independent
		 * Analytics ever rebuilds its table and reuses a session id, a stale
		 * meta row can never attach itself to an unrelated session.
		 */
		private static function meta_join( string $session_alias = 's', string $meta_alias = 'm' ): string {
			return ' LEFT JOIN ' . self::meta_table() . " {$meta_alias}
				ON {$meta_alias}.session_id = {$session_alias}.session_id
				AND {$meta_alias}.visitor_id = {$session_alias}.visitor_id";
		}

		/**
		 * Every label table Independent Analytics keeps around a session.
		 */
		private static function label_joins( string $alias = 's' ): string {
			global $wpdb;
			$p = $wpdb->prefix . 'independent_analytics_';

			return " LEFT JOIN {$p}countries c ON c.country_id = {$alias}.country_id
				LEFT JOIN {$p}cities ci ON ci.city_id = {$alias}.city_id
				LEFT JOIN {$p}device_types dt ON dt.device_type_id = {$alias}.device_type_id
				LEFT JOIN {$p}device_oss dos ON dos.device_os_id = {$alias}.device_os_id
				LEFT JOIN {$p}device_browsers db ON db.device_browser_id = {$alias}.device_browser_id
				LEFT JOIN {$p}referrers r ON r.id = {$alias}.referrer_id
				LEFT JOIN {$p}referrer_types rt ON rt.id = r.referrer_type_id
				LEFT JOIN {$p}campaigns camp ON camp.campaign_id = {$alias}.campaign_id
				LEFT JOIN {$p}utm_sources us ON us.id = camp.utm_source_id
				LEFT JOIN {$p}utm_mediums um ON um.id = camp.utm_medium_id
				LEFT JOIN {$p}utm_campaigns uc ON uc.id = camp.utm_campaign_id ";
		}

		/* -----------------------------------------------------------------
		 * Capture
		 * -------------------------------------------------------------- */

		/**
		 * Store the extra fields on the session Independent Analytics just wrote.
		 */
		public function capture_request( $response, $handler, $request ) {
			if ( ! $request instanceof WP_REST_Request ) {
				return $response;
			}

			if ( '/iawp/search' !== $request->get_route() ) {
				return $response;
			}

			if ( ! self::is_active() ) {
				return $response;
			}

			if ( '1' !== $this->option( self::CAPTURE_OPTION, '1' ) ) {
				return $response;
			}

			$data = ( $response instanceof WP_REST_Response ) ? $response->get_data() : null;

			if ( ! is_array( $data ) || empty( $data['success'] ) ) {
				return $response;
			}

			$this->capture_current_view();

			return $response;
		}

		/**
		 * Write IP, user agent, email, account and customer key on the session.
		 */
		private function capture_current_view(): void {
			$ip = self::request_ip();
			$ua = self::request_user_agent();

			if ( '' === $ip ) {
				return;
			}

			$visitor_id = self::visitor_id_for( $ip, $ua );

			if ( $visitor_id <= 0 ) {
				return;
			}

			$session_id = $this->recent_session_id( $visitor_id );

			if ( $session_id <= 0 ) {
				return;
			}

			$user_id = get_current_user_id();
			$email   = '';

			if ( $user_id > 0 ) {
				$user  = get_userdata( $user_id );
				$email = $user instanceof WP_User ? (string) $user->user_email : '';
			}

			if ( '' === $email ) {
				$email = $this->pending_email( $visitor_id );
			}
			$email = strtolower( sanitize_email( $email ) );

			$this->store_meta( $session_id, $visitor_id, $ip, $ua, $email, $user_id );
		}

		/**
		 * Insert or refresh the ABP meta row of one session.
		 */
		private function store_meta( int $session_id, int $visitor_id, string $ip, string $ua, string $email, int $user_id ): void {
			global $wpdb;

			$ip    = trim( $ip );
			$key   = self::customer_key( $user_id, $email, $ip, $visitor_id );
			$now   = gmdate( 'Y-m-d H:i:s' );
			$table = self::meta_table();

			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$table}
						( session_id, visitor_id, abp_ip, abp_user_agent, abp_email, abp_user_id, abp_customer_key, created_at, updated_at )
					VALUES ( %d, %d, %s, %s, %s, %d, %s, %s, %s )
					ON DUPLICATE KEY UPDATE
						visitor_id = VALUES(visitor_id),
						abp_ip = VALUES(abp_ip),
						abp_user_agent = VALUES(abp_user_agent),
						abp_email = VALUES(abp_email),
						abp_user_id = VALUES(abp_user_id),
						abp_customer_key = VALUES(abp_customer_key),
						updated_at = VALUES(updated_at)",
					$session_id,
					$visitor_id,
					substr( $ip, 0, 45 ),
					substr( $ua, 0, 255 ),
					substr( $email, 0, 190 ),
					$user_id,
					substr( $key, 0, 64 ),
					$now,
					$now
				)
			);
		}

		/**
		 * The session that owns the view Independent Analytics just created.
		 */
		private function recent_session_id( int $visitor_id ): int {
			global $wpdb;

			$views    = self::ia_table( 'views' );
			$sessions = self::ia_table( 'sessions' );

			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT v.session_id FROM {$views} v
					INNER JOIN {$sessions} s ON s.session_id = v.session_id
					WHERE s.visitor_id = %d
					AND v.viewed_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 10 MINUTE)
					ORDER BY v.id DESC LIMIT 1",
					$visitor_id
				)
			);
		}

		/**
		 * Attach a known email address to the current customer.
		 *
		 * Called by the support form, the free offer form, EDD purchases and
		 * new registrations. The email is kept on the Independent Analytics
		 * session and remembered for the visitor for a week, so later sessions
		 * keep the same customer key.
		 */
		public static function capture_email( string $email, int $user_id = 0 ): void {
			if ( ! self::is_active() ) {
				return;
			}

			$email = strtolower( sanitize_email( $email ) );

			if ( ! is_email( $email ) ) {
				return;
			}

			$instance   = self::instance();
			$ip         = self::request_ip();
			$ua         = self::request_user_agent();
			$visitor_id = self::visitor_id_for( $ip, $ua );

			if ( $user_id <= 0 ) {
				$user_id = get_current_user_id();
			}

			if ( $visitor_id > 0 ) {
				$instance->set_pending_email( $visitor_id, $email );
				$instance->apply_email( $visitor_id, $email, $user_id );
			}
		}

		/**
		 * Push an email (and account) onto every session this visitor owns.
		 */
		private function apply_email( int $visitor_id, string $email, int $user_id ): void {
			global $wpdb;

			$table = self::meta_table();
			$rows  = $wpdb->get_results(
				$wpdb->prepare( "SELECT session_id, abp_ip FROM {$table} WHERE visitor_id = %d", $visitor_id ),
				ARRAY_A
			);

			$now = gmdate( 'Y-m-d H:i:s' );

			foreach ( (array) $rows as $row ) {
				$ip  = (string) $row['abp_ip'];
				$key = self::customer_key( $user_id, $email, $ip, $visitor_id );

				$wpdb->update(
					$table,
					array(
						'abp_email'        => substr( $email, 0, 190 ),
						'abp_user_id'      => $user_id,
						'abp_customer_key' => substr( $key, 0, 64 ),
						'updated_at'       => $now,
					),
					array( 'session_id' => (int) $row['session_id'] ),
					array( '%s', '%d', '%s', '%s' ),
					array( '%d' )
				);
			}

			$session_id = $this->recent_session_id( $visitor_id );

			if ( $session_id > 0 ) {
				$this->store_meta( $session_id, $visitor_id, self::request_ip(), self::request_user_agent(), $email, $user_id );
			}
		}

		private function pending_email( int $visitor_id ): string {
			$email = get_transient( self::EMAIL_PREFIX . $visitor_id );

			return is_string( $email ) ? $email : '';
		}

		private function set_pending_email( int $visitor_id, string $email ): void {
			set_transient( self::EMAIL_PREFIX . $visitor_id, $email, self::EMAIL_TTL );
		}

		public function capture_purchase_email( $order_id = 0, $payment = null, $customer = null ): void {
			$email = '';

			if ( is_object( $payment ) && isset( $payment->email ) ) {
				$email = (string) $payment->email;
			} elseif ( is_array( $payment ) && isset( $payment['email'] ) ) {
				$email = (string) $payment['email'];
			}

			if ( '' === $email && is_object( $customer ) && isset( $customer->email ) ) {
				$email = (string) $customer->email;
			}

			if ( '' === $email ) {
				return;
			}

			self::capture_email( $email );
		}

		public function capture_registered_email( $user_id ): void {
			$user_id = (int) $user_id;

			if ( $user_id <= 0 ) {
				return;
			}

			$user  = get_userdata( $user_id );
			$email = $user instanceof WP_User ? (string) $user->user_email : '';

			if ( '' === $email ) {
				return;
			}

			self::capture_email( $email, $user_id );
		}

		/* -----------------------------------------------------------------
		 * Admin
		 * -------------------------------------------------------------- */

		public function admin_menu(): void {
			if ( ! self::is_active() ) {
				return;
			}

			add_menu_page(
				__( 'Visitor Tracking', 'abp-plugin' ),
				__( 'ABP Visitors', 'abp-plugin' ),
				'manage_options',
				self::MENU,
				array( $this, 'page_customers' ),
				'dashicons-visibility',
				27
			);

			add_submenu_page(
				self::MENU,
				__( 'Visitors', 'abp-plugin' ),
				__( 'Visitors', 'abp-plugin' ),
				'manage_options',
				self::MENU,
				array( $this, 'page_customers' )
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
				__( 'Visitor Settings', 'abp-plugin' ),
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

			$style_path = ABP_DIR . 'assets/css/abp_visitors_admin.css';
			$version    = file_exists( $style_path ) ? (string) filemtime( $style_path ) : ( defined( 'ABP_VERSION' ) ? (string) ABP_VERSION : self::VERSION );

			wp_enqueue_style( 'abp_visitors_admin', ABP_URL . 'assets/css/abp_visitors_admin.css', array(), $version );

			wp_register_script( 'abp_visitors_admin', false, array(), $version, true );
			wp_enqueue_script( 'abp_visitors_admin' );
			wp_add_inline_script( 'abp_visitors_admin', $this->modal_script() );
		}

		/**
		 * Open and close the overlay lists without a page reload.
		 */
		private function modal_script(): string {
			$script = <<<'JS'
(function () {
	var openers = '[data-abp-modal-open]';
	var closers = '[data-abp-modal-close]';
	var lastFocus = null;

	function open( id ) {
		var modal = document.getElementById( id );
		if ( ! modal ) { return; }
		lastFocus = document.activeElement;
		modal.hidden = false;
		document.body.classList.add( 'abp-vmodal-open' );
		var close = modal.querySelector( '.abp-vmodal-close' );
		if ( close ) { close.focus(); }
	}

	function close() {
		var modals = document.querySelectorAll( '.abp-vmodal' );
		for ( var i = 0; i < modals.length; i++ ) {
			modals[ i ].hidden = true;
		}
		document.body.classList.remove( 'abp-vmodal-open' );
		if ( lastFocus && lastFocus.focus ) { lastFocus.focus(); }
		lastFocus = null;
	}

	document.addEventListener( 'click', function ( event ) {
		var opener = event.target.closest ? event.target.closest( openers ) : null;
		if ( opener ) {
			event.preventDefault();
			open( opener.getAttribute( 'data-abp-modal-open' ) );
			return;
		}
		if ( event.target.closest && event.target.closest( closers ) ) {
			event.preventDefault();
			close();
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' ) { close(); }
	} );
})();
JS;

			return $script;
		}

		public function missing_analytics_notice(): void {
			if ( self::is_active() || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( ! $screen || 'plugins' !== $screen->id ) {
				return;
			}

			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html__( 'ABP Visitors reads its data from Independent Analytics. Install and activate Independent Analytics to use the visitor list.', 'abp-plugin' )
			);
		}

		private function option( string $key, string $default = '' ): string {
			$value = get_option( $key, $default );

			return is_string( $value ) ? $value : $default;
		}

		/* -----------------------------------------------------------------
		 * Filters
		 * -------------------------------------------------------------- */

		private function filters(): array {
			return array(
				'date_from' => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
				'date_to'   => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
				'ip'        => isset( $_GET['ip'] ) ? sanitize_text_field( wp_unslash( $_GET['ip'] ) ) : '',
				'email'     => isset( $_GET['email'] ) ? sanitize_text_field( wp_unslash( $_GET['email'] ) ) : '',
				'country'   => isset( $_GET['country'] ) ? sanitize_text_field( wp_unslash( $_GET['country'] ) ) : '',
				'device'    => isset( $_GET['device'] ) ? sanitize_text_field( wp_unslash( $_GET['device'] ) ) : '',
				'search'    => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
			);
		}

		private function utc( string $local ): string {
			$timestamp = strtotime( $local );

			if ( ! $timestamp ) {
				return $local;
			}

			$offset = (float) get_option( 'gmt_offset', 0 );

			return gmdate( 'Y-m-d H:i:s', $timestamp - (int) round( $offset * HOUR_IN_SECONDS ) );
		}

		private function local( string $utc ): string {
			$timestamp = strtotime( $utc );

			if ( ! $timestamp ) {
				return $utc;
			}

			$offset = (float) get_option( 'gmt_offset', 0 );

			return date( 'Y-m-d H:i:s', $timestamp + (int) round( $offset * HOUR_IN_SECONDS ) );
		}

		/**
		 * Shared WHERE clause for the customer queries.
		 */
		private function build_where( array $filters, string $meta = 'm' ): array {
			global $wpdb;

			$clauses = array( '1=1' );
			$args    = array();

			if ( '' !== $filters['date_from'] ) {
				$clauses[] = 's.created_at >= %s';
				$args[]    = $this->utc( $filters['date_from'] . ' 00:00:00' );
			}

			if ( '' !== $filters['date_to'] ) {
				$clauses[] = 's.created_at <= %s';
				$args[]    = $this->utc( $filters['date_to'] . ' 23:59:59' );
			}

			if ( '' !== $filters['ip'] ) {
				$clauses[] = "{$meta}.abp_ip LIKE %s";
				$args[]    = '%' . $wpdb->esc_like( $filters['ip'] ) . '%';
			}

			if ( '' !== $filters['email'] ) {
				$clauses[] = "{$meta}.abp_email LIKE %s";
				$args[]    = '%' . $wpdb->esc_like( strtolower( $filters['email'] ) ) . '%';
			}

			if ( '' !== $filters['country'] ) {
				$clauses[] = 'c.country LIKE %s';
				$args[]    = '%' . $wpdb->esc_like( $filters['country'] ) . '%';
			}

			if ( '' !== $filters['device'] ) {
				$clauses[] = 'dt.device_type = %s';
				$args[]    = $filters['device'];
			}

			if ( '' !== $filters['search'] ) {
				$like      = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
				$clauses[] = "( {$meta}.abp_email LIKE %s OR {$meta}.abp_ip LIKE %s OR {$meta}.abp_user_agent LIKE %s OR {$meta}.abp_customer_key LIKE %s )";
				array_push( $args, $like, $like, $like, $like );
			}

			return array(
				'where' => implode( ' AND ', $clauses ),
				'args'  => $args,
			);
		}

		/* -----------------------------------------------------------------
		 * Queries
		 * -------------------------------------------------------------- */

		/**
		 * One row per customer, newest first.
		 */
		private function customer_rows( array $filters, int $limit, int $offset ): array {
			global $wpdb;

			$sessions = self::ia_table( 'sessions' );
			$meta     = self::meta_join( 's', 'm' );
			$key      = self::key_sql( 'm' );
			$joins    = self::label_joins( 's' );
			$where    = $this->build_where( $filters );

			$sql = "SELECT {$key} AS customer_key,
					COUNT(DISTINCT s.session_id) AS sessions,
					COUNT(DISTINCT s.visitor_id) AS visitors,
					COALESCE(SUM(s.total_views), 0) AS views,
					MIN(s.created_at) AS first_seen,
					MAX(IFNULL(s.ended_at, s.created_at)) AS last_seen,
					MAX(m.abp_user_id) AS user_id,
					MAX(m.abp_email) AS email,
					MAX(m.abp_ip) AS ip
				FROM {$sessions} s
				{$meta}
				{$joins}
				WHERE {$where['where']}
				GROUP BY customer_key
				ORDER BY last_seen DESC
				LIMIT %d OFFSET %d";

			$args = array_merge( $where['args'], array( $limit, $offset ) );
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A );

			return is_array( $rows ) ? $rows : array();
		}

		private function customer_count( array $filters ): int {
			global $wpdb;

			$sessions = self::ia_table( 'sessions' );
			$meta     = self::meta_join( 's', 'm' );
			$key      = self::key_sql( 'm' );
			$joins    = self::label_joins( 's' );
			$where    = $this->build_where( $filters );

			$sql = "SELECT COUNT(*) FROM (
					SELECT {$key} AS customer_key
					FROM {$sessions} s
					{$meta}
					{$joins}
					WHERE {$where['where']}
					GROUP BY customer_key
				) AS customers";

			return (int) ( $where['args']
				? $wpdb->get_var( $wpdb->prepare( $sql, $where['args'] ) )
				: $wpdb->get_var( $sql ) );
		}

		/**
		 * The most recent session of every customer on the current page.
		 *
		 * @param array $keys Customer keys.
		 * @return array Customer key => session row.
		 */
		private function customer_latest( array $keys ): array {
			$keys = array_values( array_filter( array_map( 'strval', $keys ) ) );

			if ( ! $keys ) {
				return array();
			}

			global $wpdb;

			$sessions     = self::ia_table( 'sessions' );
			$meta         = self::meta_join( 's', 'm' );
			$meta_sub     = self::meta_join( 's2', 'm2' );
			$key          = self::key_sql( 'm' );
			$key_sub      = self::key_sql( 'm2', 's2' );
			$joins        = self::label_joins( 's' );
			$placeholders = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );

			$sql = "SELECT s.*, {$key} AS customer_key,
					m.abp_ip, m.abp_user_agent, m.abp_email, m.abp_user_id,
					c.country AS country_name, c.country_code,
					ci.city AS city_name, ci.subdivision,
					dt.device_type, dos.device_os, db.device_browser,
					r.referrer AS referrer_label, r.domain AS referrer_domain, rt.referrer_type,
					lp.title AS landing_page_title,
					us.utm_source, um.utm_medium, uc.utm_campaign
				FROM {$sessions} s
				{$meta}
				{$joins}
				LEFT JOIN {$wpdb->prefix}independent_analytics_landing_pages lp ON lp.id = camp.landing_page_id
				WHERE s.session_id IN (
					SELECT MAX(s2.session_id) FROM {$sessions} s2
					{$meta_sub}
					WHERE {$key_sub} IN ( {$placeholders} )
					GROUP BY {$key_sub}
				)";

			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $keys ), ARRAY_A );
			$map  = array();

			foreach ( (array) $rows as $row ) {
				$map[ (string) $row['customer_key'] ] = $row;
			}

			return $map;
		}

		private function customer_summary( string $customer_key ): array {
			global $wpdb;

			$sessions = self::ia_table( 'sessions' );
			$meta     = self::meta_join( 's', 'm' );
			$key      = self::key_sql( 'm' );

			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT {$key} AS customer_key,
						COUNT(DISTINCT s.session_id) AS sessions,
						COUNT(DISTINCT s.visitor_id) AS visitors,
						COALESCE(SUM(s.total_views), 0) AS views,
						MIN(s.created_at) AS first_seen,
						MAX(IFNULL(s.ended_at, s.created_at)) AS last_seen,
						MAX(m.abp_user_id) AS user_id,
						MAX(m.abp_email) AS email,
						MAX(m.abp_ip) AS ip
					FROM {$sessions} s
					{$meta}
					WHERE {$key} = %s
					GROUP BY customer_key",
					$customer_key
				),
				ARRAY_A
			);

			return is_array( $row ) ? $row : array();
		}

		private function customer_sessions( string $customer_key, int $limit = 100 ): array {
			global $wpdb;

			$sessions = self::ia_table( 'sessions' );
			$meta     = self::meta_join( 's', 'm' );
			$key      = self::key_sql( 'm' );
			$joins    = self::label_joins( 's' );

			$sql = "SELECT s.*, m.abp_ip, m.abp_user_agent, m.abp_email, m.abp_user_id,
					c.country AS country_name, c.country_code,
					ci.city AS city_name, dt.device_type, dos.device_os, db.device_browser,
					r.referrer AS referrer_label, r.domain AS referrer_domain, rt.referrer_type,
					us.utm_source, um.utm_medium, uc.utm_campaign
				FROM {$sessions} s
				{$meta}
				{$joins}
				WHERE {$key} = %s
				ORDER BY s.created_at DESC, s.session_id DESC
				LIMIT %d";

			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $customer_key, $limit ), ARRAY_A );

			return is_array( $rows ) ? $rows : array();
		}

		private function views_for_sessions( array $session_ids, int $limit = 500 ): array {
			$session_ids = array_values( array_filter( array_map( 'absint', $session_ids ) ) );

			if ( ! $session_ids ) {
				return array();
			}

			global $wpdb;

			$views     = self::ia_table( 'views' );
			$resources = self::ia_table( 'resources' );
			$placeholders = implode( ', ', array_fill( 0, count( $session_ids ), '%d' ) );

			$sql = "SELECT v.id, v.session_id, v.viewed_at, v.page, v.resource_id,
					r.resource, r.cached_title, r.cached_url, r.cached_type, r.cached_type_label
				FROM {$views} v
				LEFT JOIN {$resources} r ON r.id = v.resource_id
				WHERE v.session_id IN ( {$placeholders} )
				ORDER BY v.viewed_at DESC, v.id DESC
				LIMIT %d";

			$args = array_merge( $session_ids, array( $limit ) );
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A );

			return is_array( $rows ) ? $rows : array();
		}

		/**
		 * Headline numbers, all time, straight from Independent Analytics.
		 */
		private function stats(): array {
			global $wpdb;

			$sessions = self::ia_table( 'sessions' );
			$meta     = self::meta_join( 's', 'm' );
			$key      = self::key_sql( 'm' );

			$customers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM ( SELECT {$key} AS k FROM {$sessions} s {$meta} GROUP BY k ) t" );
			$totals    = $wpdb->get_row( "SELECT COUNT(*) AS sessions, COALESCE(SUM(total_views), 0) AS views FROM {$sessions}", ARRAY_A );
			$today     = $this->utc( date( 'Y-m-d', current_time( 'timestamp' ) ) . ' 00:00:00' );

			$today_customers = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM ( SELECT {$key} AS k FROM {$sessions} s {$meta} WHERE s.created_at >= %s GROUP BY k ) t",
					$today
				)
			);

			$online = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$sessions} WHERE IFNULL(ended_at, created_at) > DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d MINUTE)",
					self::ONLINE_MINUTES
				)
			);

			$top_country = (string) $wpdb->get_var(
				"SELECT c.country FROM {$sessions} s
				INNER JOIN {$wpdb->prefix}independent_analytics_countries c ON c.country_id = s.country_id
				GROUP BY c.country ORDER BY COUNT(*) DESC LIMIT 1"
			);

			$session_count = isset( $totals['sessions'] ) ? (int) $totals['sessions'] : 0;
			$view_count    = isset( $totals['views'] ) ? (int) $totals['views'] : 0;

			return array(
				'customers'   => $customers,
				'sessions'    => $session_count,
				'views'       => $view_count,
				'per_session' => $session_count > 0 ? round( $view_count / $session_count, 1 ) : 0,
				'today'       => $today_customers,
				'online'      => $online,
				'top_country' => $top_country,
			);
		}

		private function top_pages( int $limit = 8 ): array {
			global $wpdb;

			$views     = self::ia_table( 'views' );
			$resources = self::ia_table( 'resources' );

			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT COALESCE(NULLIF(r.cached_title, ''), NULLIF(r.resource, ''), 'Unknown page') AS label,
						COUNT(*) AS hits
					FROM {$views} v
					LEFT JOIN {$resources} r ON r.id = v.resource_id
					GROUP BY v.resource_id
					ORDER BY hits DESC
					LIMIT %d",
					$limit
				),
				ARRAY_A
			);

			return is_array( $rows ) ? $rows : array();
		}

		private function top_countries( int $limit = 8 ): array {
			global $wpdb;

			$sessions  = self::ia_table( 'sessions' );
			$countries = self::ia_table( 'countries' );

			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT c.country AS label, COUNT(DISTINCT s.visitor_id) AS hits
					FROM {$sessions} s
					INNER JOIN {$countries} c ON c.country_id = s.country_id
					GROUP BY c.country
					ORDER BY hits DESC
					LIMIT %d",
					$limit
				),
				ARRAY_A
			);

			return is_array( $rows ) ? $rows : array();
		}

		/* -----------------------------------------------------------------
		 * Visitors page
		 * -------------------------------------------------------------- */

		public function page_customers(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to view this page.', 'abp-plugin' ) );
			}

			if ( ! self::is_active() ) {
				$this->render_inactive();

				return;
			}

			if ( isset( $_GET['customer'] ) ) {
				$this->page_customer( sanitize_text_field( wp_unslash( $_GET['customer'] ) ) );

				return;
			}

			$filters = $this->filters();
			$paged   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
			$total   = $this->customer_count( $filters );
			$pages   = max( 1, (int) ceil( $total / self::PER_PAGE ) );
			$offset  = ( $paged - 1 ) * self::PER_PAGE;
			$rows    = $this->customer_rows( $filters, self::PER_PAGE, $offset );
			$latest  = $this->customer_latest( wp_list_pluck( $rows, 'customer_key' ) );

			$stats         = $this->stats();
			$top_pages     = $this->top_pages( 100 );
			$top_countries = $this->top_countries( 100 );
			$notice        = isset( $_GET['abp_msg'] ) ? sanitize_key( wp_unslash( $_GET['abp_msg'] ) ) : '';
			$devices       = array( 'Desktop', 'Mobile', 'Tablet' );

			?>
			<div class="wrap abp-vwrap">
				<div class="abp-vhead">
					<div>
						<span class="abp-vkicker"><?php esc_html_e( 'Visitor Tracking', 'abp-plugin' ); ?></span>
						<h1><?php esc_html_e( 'Visitors', 'abp-plugin' ); ?></h1>
						<p><?php esc_html_e( 'One row per customer. Everything shown here comes from Independent Analytics; the extra columns are stored on the analytics session itself.', 'abp-plugin' ); ?></p>
					</div>
					<div class="abp-vactions">
						<a class="abp-vbtn abp-vbtn-light" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU ) ); ?>"><?php esc_html_e( 'Refresh', 'abp-plugin' ); ?></a>
						<a class="abp-vbtn abp-vbtn-primary" href="<?php echo esc_url( $this->export_url( $filters ) ); ?>"><?php esc_html_e( 'Export CSV', 'abp-plugin' ); ?></a>
					</div>
				</div>

				<?php $this->notice( $notice ); ?>

				<div class="abp-vcards">
					<?php
					$this->stat_card( __( 'Customers', 'abp-plugin' ), number_format_i18n( $stats['customers'] ), __( 'All time', 'abp-plugin' ), 'dashicons-groups' );
					$this->stat_card( __( 'Sessions', 'abp-plugin' ), number_format_i18n( $stats['sessions'] ), __( 'All time', 'abp-plugin' ), 'dashicons-calendar-alt' );
					$this->stat_card( __( 'Page views', 'abp-plugin' ), number_format_i18n( $stats['views'] ), __( 'All time', 'abp-plugin' ), 'dashicons-media-document', 'abp-modal-pages', __( 'Top pages', 'abp-plugin' ) );
					$this->stat_card( __( 'Pages per session', 'abp-plugin' ), number_format_i18n( $stats['per_session'], 1 ), __( 'Average', 'abp-plugin' ), 'dashicons-chart-bar' );
					$this->stat_card( __( 'New today', 'abp-plugin' ), number_format_i18n( $stats['today'] ), __( 'Customers', 'abp-plugin' ), 'dashicons-clock' );
					$this->stat_card( __( 'Online now', 'abp-plugin' ), number_format_i18n( $stats['online'] ), sprintf( __( 'Last %d minutes', 'abp-plugin' ), self::ONLINE_MINUTES ), 'dashicons-pulse' );
					$this->stat_card( __( 'Top country', 'abp-plugin' ), '' !== $stats['top_country'] ? $stats['top_country'] : '—', __( 'Most visitors', 'abp-plugin' ), 'dashicons-location-alt', 'abp-modal-countries', __( 'Top countries', 'abp-plugin' ) );
					?>
				</div>

				<div class="abp-vpanel">
					<form method="get" class="abp-vfilters">
							<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU ); ?>" />
							<div class="abp-vfield">
								<label for="abp-date-from"><?php esc_html_e( 'From', 'abp-plugin' ); ?></label>
								<input type="date" id="abp-date-from" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" />
							</div>
							<div class="abp-vfield">
								<label for="abp-date-to"><?php esc_html_e( 'To', 'abp-plugin' ); ?></label>
								<input type="date" id="abp-date-to" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />
							</div>
							<div class="abp-vfield">
								<label for="abp-ip"><?php esc_html_e( 'IP address', 'abp-plugin' ); ?></label>
								<input type="text" id="abp-ip" name="ip" value="<?php echo esc_attr( $filters['ip'] ); ?>" placeholder="203.0.113.10" />
							</div>
							<div class="abp-vfield">
								<label for="abp-email"><?php esc_html_e( 'Email', 'abp-plugin' ); ?></label>
								<input type="text" id="abp-email" name="email" value="<?php echo esc_attr( $filters['email'] ); ?>" placeholder="customer@example.com" />
							</div>
							<div class="abp-vfield">
								<label for="abp-country"><?php esc_html_e( 'Country', 'abp-plugin' ); ?></label>
								<input type="text" id="abp-country" name="country" value="<?php echo esc_attr( $filters['country'] ); ?>" />
							</div>
							<div class="abp-vfield">
								<label for="abp-device"><?php esc_html_e( 'Device', 'abp-plugin' ); ?></label>
								<select id="abp-device" name="device">
									<option value=""><?php esc_html_e( 'All devices', 'abp-plugin' ); ?></option>
									<?php foreach ( $devices as $device ) : ?>
										<option value="<?php echo esc_attr( $device ); ?>" <?php selected( $filters['device'], $device ); ?>><?php echo esc_html( $device ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="abp-vfield">
								<label for="abp-search"><?php esc_html_e( 'Search', 'abp-plugin' ); ?></label>
								<input type="text" id="abp-search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="<?php esc_attr_e( 'Email, IP or browser', 'abp-plugin' ); ?>" />
							</div>
							<div class="abp-vfield abp-vfield-actions">
								<button type="submit" class="abp-vbtn abp-vbtn-primary"><?php esc_html_e( 'Apply filters', 'abp-plugin' ); ?></button>
								<a class="abp-vbtn abp-vbtn-light" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU ) ); ?>"><?php esc_html_e( 'Reset', 'abp-plugin' ); ?></a>
							</div>
					</form>
				</div>

				<div class="abp-vpanel abp-vpanel-table">
					<h2>
						<?php esc_html_e( 'Customers', 'abp-plugin' ); ?>
						<span class="abp-vcount"><?php echo esc_html( number_format_i18n( $total ) ); ?></span>
					</h2>

					<table class="widefat striped abp-vtable">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Customer', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'IP address', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Location', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Sessions', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Page views', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'First seen', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Last seen', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Device', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'abp-plugin' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( ! $rows ) : ?>
								<tr>
									<td colspan="9" class="abp-vempty"><?php esc_html_e( 'No visits yet. Once Independent Analytics records a visit it appears here as one customer row.', 'abp-plugin' ); ?></td>
								</tr>
							<?php else : ?>
								<?php foreach ( $rows as $row ) : ?>
									<?php
									$key     = (string) $row['customer_key'];
									$session = isset( $latest[ $key ] ) ? $latest[ $key ] : array();
									$email   = (string) $row['email'];
									$user_id = (int) $row['user_id'];
									$ip      = (string) $row['ip'];
									?>
									<tr>
										<td>
											<?php if ( '' !== $email ) : ?>
												<a class="abp-vlink" href="<?php echo esc_url( $this->customer_url( $key ) ); ?>"><?php echo esc_html( $email ); ?></a>
											<?php elseif ( $user_id > 0 ) : ?>
												<a class="abp-vlink" href="<?php echo esc_url( $this->customer_url( $key ) ); ?>"><?php echo esc_html( sprintf( __( 'Account #%d', 'abp-plugin' ), $user_id ) ); ?></a>
											<?php else : ?>
												<a class="abp-vlink" href="<?php echo esc_url( $this->customer_url( $key ) ); ?>"><?php esc_html_e( 'Anonymous', 'abp-plugin' ); ?></a>
											<?php endif; ?>
											<span class="abp-vmuted abp-vsmall">
												<?php
												if ( $user_id > 0 ) {
													echo esc_html( sprintf( __( 'Account #%d', 'abp-plugin' ), $user_id ) );
												} else {
													echo esc_html( sprintf( __( '%s analytics visitors', 'abp-plugin' ), number_format_i18n( (int) $row['visitors'] ) ) );
												}
												?>
											</span>
										</td>
										<td>
											<?php if ( '' !== $ip ) : ?>
												<code><?php echo esc_html( $ip ); ?></code>
											<?php else : ?>
												<span class="abp-vmuted"><?php esc_html_e( 'Not captured', 'abp-plugin' ); ?></span>
											<?php endif; ?>
										</td>
										<td>
											<?php echo esc_html( ! empty( $session['country_name'] ) ? $session['country_name'] : '—' ); ?>
											<?php if ( ! empty( $session['city_name'] ) ) : ?>
												<span class="abp-vmuted abp-vsmall"><?php echo esc_html( $session['city_name'] ); ?></span>
											<?php endif; ?>
										</td>
										<td><span class="abp-vpill"><?php echo esc_html( number_format_i18n( (int) $row['sessions'] ) ); ?></span></td>
										<td><strong><?php echo esc_html( number_format_i18n( (int) $row['views'] ) ); ?></strong></td>
										<td class="abp-vnowrap"><?php echo esc_html( $this->local( (string) $row['first_seen'] ) ); ?></td>
										<td class="abp-vnowrap"><?php echo esc_html( $this->local( (string) $row['last_seen'] ) ); ?></td>
										<td>
											<?php if ( ! empty( $session['device_type'] ) ) : ?>
												<span class="abp-vpill"><?php echo esc_html( $session['device_type'] ); ?></span>
												<span class="abp-vmuted abp-vsmall">
													<?php
													echo esc_html(
														trim(
															(string) ( $session['device_browser'] ?? '' ) . ( ! empty( $session['device_os'] ) ? ' · ' . $session['device_os'] : '' )
														)
													);
													?>
												</span>
											<?php else : ?>
												<span class="abp-vmuted">—</span>
											<?php endif; ?>
										</td>
										<td>
											<div class="abp-vrow-actions">
												<a class="abp-vlinkbtn" href="<?php echo esc_url( $this->customer_url( $key ) ); ?>"><?php esc_html_e( 'View', 'abp-plugin' ); ?></a>
												<?php if ( '' !== $ip ) : ?>
													<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vinline">
														<?php wp_nonce_field( self::NONCE ); ?>
														<input type="hidden" name="action" value="abp_ia_block" />
														<input type="hidden" name="ip" value="<?php echo esc_attr( $ip ); ?>" />
														<button type="submit" class="abp-vlinkbtn abp-vlinkbtn-danger"><?php esc_html_e( 'Block', 'abp-plugin' ); ?></button>
													</form>
												<?php endif; ?>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>

					<?php $this->pagination( $filters, $paged, $pages ); ?>
				</div>
			</div>

			<?php
			$this->top_modal( 'abp-modal-pages', __( 'Top pages', 'abp-plugin' ), $top_pages, __( 'page views', 'abp-plugin' ) );
			$this->top_modal( 'abp-modal-countries', __( 'Top countries', 'abp-plugin' ), $top_countries, __( 'visitors', 'abp-plugin' ) );
			?>
			<?php
		}

		/**
		 * One customer with every session and page view.
		 */
		private function page_customer( string $customer_key ): void {
			if ( ! preg_match( '/^[ueiv]:.{0,190}$/', $customer_key ) ) {
				?>
				<div class="wrap abp-vwrap">
					<div class="abp-vpanel"><p class="abp-vempty"><?php esc_html_e( 'This customer could not be found.', 'abp-plugin' ); ?></p></div>
				</div>
				<?php
				return;
			}

			$summary = $this->customer_summary( $customer_key );

			if ( ! $summary ) {
				?>
				<div class="wrap abp-vwrap">
					<div class="abp-vpanel">
						<p class="abp-vempty"><?php esc_html_e( 'This customer has no visits in Independent Analytics any more.', 'abp-plugin' ); ?></p>
						<p><a class="abp-vbtn abp-vbtn-light" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU ) ); ?>"><?php esc_html_e( 'Back to visitors', 'abp-plugin' ); ?></a></p>
					</div>
				</div>
				<?php
				return;
			}

			$sessions     = $this->customer_sessions( $customer_key );
			$session_ids  = wp_list_pluck( $sessions, 'session_id' );
			$views        = $this->views_for_sessions( $session_ids );
			$session_map  = array();

			foreach ( $sessions as $session ) {
				$session_map[ (int) $session['session_id'] ] = $session;
			}

			$email   = (string) $summary['email'];
			$user_id = (int) $summary['user_id'];
			$ip      = (string) $summary['ip'];
			$notice  = isset( $_GET['abp_msg'] ) ? sanitize_key( wp_unslash( $_GET['abp_msg'] ) ) : '';
			?>
			<div class="wrap abp-vwrap">
				<div class="abp-vhead">
					<div>
						<span class="abp-vkicker"><?php esc_html_e( 'Customer', 'abp-plugin' ); ?></span>
						<h1>
							<?php
							if ( '' !== $email ) {
								echo esc_html( $email );
							} elseif ( $user_id > 0 ) {
								echo esc_html( sprintf( __( 'Account #%d', 'abp-plugin' ), $user_id ) );
							} else {
								esc_html_e( 'Anonymous customer', 'abp-plugin' );
							}
							?>
						</h1>
						<p>
							<?php echo esc_html( sprintf( __( '%1$s sessions and %2$s page views, from %3$s analytics visitors.', 'abp-plugin' ), number_format_i18n( (int) $summary['sessions'] ), number_format_i18n( (int) $summary['views'] ), number_format_i18n( (int) $summary['visitors'] ) ) ); ?>
						</p>
					</div>
					<div class="abp-vactions">
						<a class="abp-vbtn abp-vbtn-light" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU ) ); ?>"><?php esc_html_e( 'Back to visitors', 'abp-plugin' ); ?></a>
						<?php if ( '' !== $ip ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vinline">
								<?php wp_nonce_field( self::NONCE ); ?>
								<input type="hidden" name="action" value="abp_ia_block" />
								<input type="hidden" name="ip" value="<?php echo esc_attr( $ip ); ?>" />
								<input type="hidden" name="back" value="<?php echo esc_attr( $this->customer_url( $customer_key ) ); ?>" />
								<button type="submit" class="abp-vbtn abp-vbtn-danger"><?php esc_html_e( 'Block this IP', 'abp-plugin' ); ?></button>
							</form>
						<?php endif; ?>
					</div>
				</div>

				<?php $this->notice( $notice ); ?>

				<div class="abp-vcards">
					<?php
					$this->stat_card( __( 'Sessions', 'abp-plugin' ), number_format_i18n( (int) $summary['sessions'] ), __( 'Analytics sessions', 'abp-plugin' ), 'dashicons-calendar-alt' );
					$this->stat_card( __( 'Page views', 'abp-plugin' ), number_format_i18n( (int) $summary['views'] ), __( 'Analytics views', 'abp-plugin' ), 'dashicons-media-document' );
					$this->stat_card( __( 'First seen', 'abp-plugin' ), $this->local( (string) $summary['first_seen'] ), '', 'dashicons-clock' );
					$this->stat_card( __( 'Last seen', 'abp-plugin' ), $this->local( (string) $summary['last_seen'] ), '', 'dashicons-update' );
					$this->stat_card( __( 'IP address', 'abp-plugin' ), '' !== $ip ? $ip : '—', __( 'Captured by ABP', 'abp-plugin' ), 'dashicons-networking' );
					$this->stat_card( __( 'Analytics visitors', 'abp-plugin' ), number_format_i18n( (int) $summary['visitors'] ), __( 'Merged into this customer', 'abp-plugin' ), 'dashicons-groups' );
					?>
				</div>

				<div class="abp-vpanel abp-vpanel-table">
					<h2>
						<?php esc_html_e( 'Sessions', 'abp-plugin' ); ?>
						<span class="abp-vcount"><?php echo esc_html( number_format_i18n( count( $sessions ) ) ); ?></span>
					</h2>

					<table class="widefat striped abp-vtable">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Started', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Views', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Country', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Device', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Source', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'User agent', 'abp-plugin' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( ! $sessions ) : ?>
								<tr><td colspan="6" class="abp-vempty"><?php esc_html_e( 'No sessions.', 'abp-plugin' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $sessions as $session ) : ?>
									<tr>
										<td class="abp-vnowrap"><?php echo esc_html( $this->local( (string) $session['created_at'] ) ); ?></td>
										<td><span class="abp-vpill"><?php echo esc_html( number_format_i18n( (int) $session['total_views'] ) ); ?></span></td>
										<td>
											<?php echo esc_html( ! empty( $session['country_name'] ) ? $session['country_name'] : '—' ); ?>
											<?php if ( ! empty( $session['city_name'] ) ) : ?>
												<span class="abp-vmuted abp-vsmall"><?php echo esc_html( $session['city_name'] ); ?></span>
											<?php endif; ?>
										</td>
										<td>
											<span class="abp-vpill"><?php echo esc_html( ! empty( $session['device_type'] ) ? $session['device_type'] : '—' ); ?></span>
											<span class="abp-vmuted abp-vsmall"><?php echo esc_html( trim( (string) ( $session['device_browser'] ?? '' ) . ( ! empty( $session['device_os'] ) ? ' · ' . $session['device_os'] : '' ) ) ); ?></span>
										</td>
										<td>
											<?php $source = $this->source_label( $session ); ?>
											<?php echo esc_html( '' !== $source ? $source : '—' ); ?>
										</td>
										<td><span class="abp-vmuted abp-vsmall"><?php echo esc_html( $this->short_agent( (string) $session['abp_user_agent'] ) ); ?></span></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="abp-vpanel abp-vpanel-table">
					<h2>
						<?php esc_html_e( 'Page views', 'abp-plugin' ); ?>
						<span class="abp-vcount"><?php echo esc_html( number_format_i18n( count( $views ) ) ); ?></span>
					</h2>

					<table class="widefat striped abp-vtable">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Date & time', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Page', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Session', 'abp-plugin' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( ! $views ) : ?>
								<tr><td colspan="3" class="abp-vempty"><?php esc_html_e( 'No page views.', 'abp-plugin' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $views as $view ) : ?>
									<?php $session_id = (int) $view['session_id']; ?>
									<tr>
										<td class="abp-vnowrap"><?php echo esc_html( $this->local( (string) $view['viewed_at'] ) ); ?></td>
										<td>
											<?php $label = '' !== (string) $view['cached_title'] ? (string) $view['cached_title'] : (string) $view['resource']; ?>
											<?php if ( '' !== (string) $view['cached_url'] ) : ?>
												<a class="abp-vlink" href="<?php echo esc_url( $view['cached_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( '' !== $label ? $label : __( 'Unknown page', 'abp-plugin' ) ); ?></a>
												<span class="abp-vmuted abp-vsmall"><?php echo esc_html( $view['cached_url'] ); ?></span>
											<?php else : ?>
												<?php echo esc_html( '' !== $label ? $label : __( 'Unknown page', 'abp-plugin' ) ); ?>
											<?php endif; ?>
										</td>
										<td>
											<span class="abp-vpill">#<?php echo esc_html( $session_id ); ?></span>
											<span class="abp-vmuted abp-vsmall"><?php echo esc_html( $this->local( (string) ( $session_map[ $session_id ]['created_at'] ?? '' ) ) ); ?></span>
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

		/* -----------------------------------------------------------------
		 * Blocked IPs (stored in Independent Analytics)
		 * -------------------------------------------------------------- */

		private function blocked_ips(): array {
			$ips = get_option( 'iawp_blocked_ips', array() );
			$ips = is_array( $ips ) ? $ips : array();

			return array_values( array_filter( array_map( 'strval', $ips ), 'strlen' ) );
		}

		public function page_blocked(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to view this page.', 'abp-plugin' ) );
			}

			if ( ! self::is_active() ) {
				$this->render_inactive();

				return;
			}

			$ips    = $this->blocked_ips();
			$notice = isset( $_GET['abp_msg'] ) ? sanitize_key( wp_unslash( $_GET['abp_msg'] ) ) : '';

			?>
			<div class="wrap abp-vwrap">
				<div class="abp-vhead">
					<div>
						<span class="abp-vkicker"><?php esc_html_e( 'Visitor Tracking', 'abp-plugin' ); ?></span>
						<h1><?php esc_html_e( 'Blocked IPs', 'abp-plugin' ); ?></h1>
						<p><?php esc_html_e( 'The list below is the ignore list of Independent Analytics, so blocked addresses stop being tracked everywhere at once.', 'abp-plugin' ); ?></p>
					</div>
				</div>

				<?php $this->notice( $notice ); ?>

				<div class="abp-vpanel">
					<h2><?php esc_html_e( 'Block an address', 'abp-plugin' ); ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vblockform">
						<?php wp_nonce_field( self::NONCE ); ?>
						<input type="hidden" name="action" value="abp_ia_block" />
						<div class="abp-vfield">
							<label for="abp-block-ip"><?php esc_html_e( 'IP address or range', 'abp-plugin' ); ?></label>
							<input type="text" id="abp-block-ip" name="ip" placeholder="203.0.113.10" required />
						</div>
						<div class="abp-vfield">
							<button type="submit" class="abp-vbtn abp-vbtn-primary"><?php esc_html_e( 'Block address', 'abp-plugin' ); ?></button>
						</div>
					</form>
					<p class="abp-vnote"><?php esc_html_e( 'Single addresses and CIDR ranges are both supported. Blocking uses the same list as Independent Analytics → Settings → Ignore IP Addresses.', 'abp-plugin' ); ?></p>
				</div>

				<div class="abp-vpanel abp-vpanel-table">
					<h2>
						<?php esc_html_e( 'Blocked addresses', 'abp-plugin' ); ?>
						<span class="abp-vcount"><?php echo esc_html( number_format_i18n( count( $ips ) ) ); ?></span>
					</h2>

					<table class="widefat striped abp-vtable">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Address', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Customers', 'abp-plugin' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'abp-plugin' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( ! $ips ) : ?>
								<tr><td colspan="3" class="abp-vempty"><?php esc_html_e( 'No address is blocked.', 'abp-plugin' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $ips as $ip ) : ?>
									<tr>
										<td><code><?php echo esc_html( $ip ); ?></code></td>
										<td><a class="abp-vlink" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU . '&ip=' . rawurlencode( $ip ) ) ); ?>"><?php esc_html_e( 'Show visits', 'abp-plugin' ); ?></a></td>
										<td>
											<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="abp-vinline">
												<?php wp_nonce_field( self::NONCE ); ?>
												<input type="hidden" name="action" value="abp_ia_unblock" />
												<input type="hidden" name="ip" value="<?php echo esc_attr( $ip ); ?>" />
												<button type="submit" class="abp-vlinkbtn"><?php esc_html_e( 'Unblock', 'abp-plugin' ); ?></button>
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

		/* -----------------------------------------------------------------
		 * Settings page
		 * -------------------------------------------------------------- */

		public function page_settings(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to view this page.', 'abp-plugin' ) );
			}

			if ( ! self::is_active() ) {
				$this->render_inactive();

				return;
			}

			global $wpdb;

			$table   = self::meta_table();
			$exists  = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			$missing = $exists ? array() : array( $table );

			$capture = $this->option( self::CAPTURE_OPTION, '1' );
			$notice  = isset( $_GET['abp_msg'] ) ? sanitize_key( wp_unslash( $_GET['abp_msg'] ) ) : '';

			?>
			<div class="wrap abp-vwrap">
				<div class="abp-vhead">
					<div>
						<span class="abp-vkicker"><?php esc_html_e( 'Visitor Tracking', 'abp-plugin' ); ?></span>
						<h1><?php esc_html_e( 'Settings', 'abp-plugin' ); ?></h1>
						<p><?php esc_html_e( 'Visits live in Independent Analytics. The few fields the customer list needs go into an ABP owned table, so an Independent Analytics update can never erase them.', 'abp-plugin' ); ?></p>
					</div>
				</div>

				<?php $this->notice( $notice ); ?>

				<div class="abp-vgrid">
					<div>
						<div class="abp-vpanel">
							<h2><?php esc_html_e( 'Capture', 'abp-plugin' ); ?></h2>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( self::NONCE ); ?>
								<input type="hidden" name="action" value="abp_ia_settings" />
								<label class="abp-vswitch">
									<input type="checkbox" name="capture_details" value="1" <?php checked( $capture, '1' ); ?> />
									<span><?php esc_html_e( 'Store IP address, user agent, email and account next to the analytics session', 'abp-plugin' ); ?></span>
								</label>
								<p class="abp-vnote"><?php esc_html_e( 'Independent Analytics only stores an anonymous visitor hash. Turning this off leaves the whole visitor list working, but the customer rows fall back to the analytics visitor hash.', 'abp-plugin' ); ?></p>
								<button type="submit" class="abp-vbtn abp-vbtn-primary"><?php esc_html_e( 'Save settings', 'abp-plugin' ); ?></button>
							</form>
						</div>

						<div class="abp-vpanel">
							<h2><?php esc_html_e( 'Maintenance', 'abp-plugin' ); ?></h2>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Remove the captured extra fields from every analytics session?', 'abp-plugin' ) ); ?>');">
								<?php wp_nonce_field( self::NONCE ); ?>
								<input type="hidden" name="action" value="abp_ia_clear" />
								<p class="abp-vnote"><?php esc_html_e( 'Clears the ABP owned customer details table. The Independent Analytics visits are never touched.', 'abp-plugin' ); ?></p>
								<button type="submit" class="abp-vbtn abp-vbtn-danger"><?php esc_html_e( 'Clear captured details', 'abp-plugin' ); ?></button>
							</form>
						</div>
					</div>

					<div class="abp-vside">
						<div class="abp-vpanel">
							<h2><?php esc_html_e( 'Status', 'abp-plugin' ); ?></h2>
							<ul class="abp-vtoplist">
								<li><span class="abp-vtoplabel"><?php esc_html_e( 'Independent Analytics', 'abp-plugin' ); ?></span><span class="abp-vtophits"><?php echo esc_html( defined( 'IAWP_VERSION' ) ? IAWP_VERSION : '—' ); ?></span></li>
								<li><span class="abp-vtoplabel"><?php esc_html_e( 'Data source', 'abp-plugin' ); ?></span><span class="abp-vtophits"><?php esc_html_e( 'Analytics tables', 'abp-plugin' ); ?></span></li>
								<li><span class="abp-vtoplabel"><?php esc_html_e( 'ABP details table', 'abp-plugin' ); ?></span><span class="abp-vtophits"><?php echo $missing ? esc_html__( 'Missing', 'abp-plugin' ) : esc_html__( 'Installed', 'abp-plugin' ); ?></span></li>
								<li><span class="abp-vtoplabel"><?php esc_html_e( 'Legacy ABP tables', 'abp-plugin' ); ?></span><span class="abp-vtophits"><?php echo get_option( self::LEGACY_OPTION ) ? esc_html__( 'Removed', 'abp-plugin' ) : esc_html__( 'Pending', 'abp-plugin' ); ?></span></li>
							</ul>
							<?php if ( $missing ) : ?>
								<p class="abp-vnote"><?php echo esc_html( implode( ', ', $missing ) ); ?></p>
							<?php endif; ?>
						</div>

						<div class="abp-vpanel">
							<h2><?php esc_html_e( 'How grouping works', 'abp-plugin' ); ?></h2>
							<p class="abp-vnote"><?php esc_html_e( 'A customer is matched by account first, then email, then IP address, and finally the anonymous analytics visitor. Every session that matches becomes one row, so the view count answers how often that customer came back.', 'abp-plugin' ); ?></p>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/* -----------------------------------------------------------------
		 * Admin actions
		 * -------------------------------------------------------------- */

		public function post_block(): void {
			$this->guard();

			$ip = isset( $_POST['ip'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['ip'] ) ) ) : '';
			$back = isset( $_POST['back'] ) ? esc_url_raw( wp_unslash( $_POST['back'] ) ) : '';

			if ( '' === $ip ) {
				$this->redirect( $back, 'invalid' );
			}

			$ips = $this->blocked_ips();

			if ( ! in_array( $ip, $ips, true ) ) {
				$ips[] = $ip;
				update_option( 'iawp_blocked_ips', $ips );
			}

			$this->redirect( $back, 'blocked' );
		}

		public function post_unblock(): void {
			$this->guard();

			$ip   = isset( $_POST['ip'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['ip'] ) ) ) : '';
			$ips  = $this->blocked_ips();
			$kept = array();

			foreach ( $ips as $blocked ) {
				if ( $blocked !== $ip ) {
					$kept[] = $blocked;
				}
			}

			update_option( 'iawp_blocked_ips', $kept );

			$this->redirect( admin_url( 'admin.php?page=' . self::SUB_BLOCKED ), 'unblocked' );
		}

		public function post_settings(): void {
			$this->guard();

			$capture = isset( $_POST['capture_details'] ) ? '1' : '0';
			update_option( self::CAPTURE_OPTION, $capture, false );

			$this->redirect( admin_url( 'admin.php?page=' . self::SUB_SETTINGS ), 'saved' );
		}

		public function post_clear(): void {
			$this->guard();

			global $wpdb;

			$table = self::meta_table();

			$wpdb->query( "DELETE FROM {$table}" );

			$this->redirect( admin_url( 'admin.php?page=' . self::SUB_SETTINGS ), 'cleared' );
		}

		public function post_export(): void {
			$this->guard();

			if ( ! self::is_active() ) {
				wp_die( esc_html__( 'Independent Analytics is not active.', 'abp-plugin' ) );
			}

			$filters = $this->filters();
			$rows    = $this->customer_rows( $filters, 10000, 0 );

			nocache_headers();
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="abp-customers-' . gmdate( 'Y-m-d' ) . '.csv"' );

			$output = fopen( 'php://output', 'w' );
			fwrite( $output, "\xEF\xBB\xBF" );

			fputcsv(
				$output,
				array(
					'Customer',
					'Email',
					'Account',
					'IP address',
					'Sessions',
					'Page views',
					'Analytics visitors',
					'First seen',
					'Last seen',
				)
			);

			foreach ( $rows as $row ) {
				$email   = (string) $row['email'];
				$user_id = (int) $row['user_id'];

				fputcsv(
					$output,
					array(
						'' !== $email ? $email : ( $user_id > 0 ? 'Account #' . $user_id : (string) $row['customer_key'] ),
						$email,
						$user_id > 0 ? $user_id : '',
						(string) $row['ip'],
						(int) $row['sessions'],
						(int) $row['views'],
						(int) $row['visitors'],
						$this->local( (string) $row['first_seen'] ),
						$this->local( (string) $row['last_seen'] ),
					)
				);
			}

			fclose( $output );
			exit;
		}

		private function guard(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to do this.', 'abp-plugin' ) );
			}

			check_admin_referer( self::NONCE );
		}

		private function redirect( string $fallback, string $message ): void {
			$url = '' !== $fallback ? add_query_arg( 'abp_msg', $message, $fallback ) : admin_url( 'admin.php?page=' . self::MENU . '&abp_msg=' . $message );

			wp_safe_redirect( $url );
			exit;
		}

		/* -----------------------------------------------------------------
		 * Rendering helpers
		 * -------------------------------------------------------------- */

		private function export_url( array $filters ): string {
			$args = array_filter(
				$filters,
				static function ( $value ) {
					return '' !== $value && null !== $value;
				}
			);

			$args['action'] = 'abp_ia_export';

			return wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), self::NONCE );
		}

		private function customer_url( string $key ): string {
			return admin_url( 'admin.php?page=' . self::MENU . '&customer=' . rawurlencode( $key ) );
		}

		private function pagination( array $filters, int $paged, int $pages ): void {
			if ( $pages <= 1 ) {
				return;
			}

			$args = array_filter(
				$filters,
				static function ( $value ) {
					return '' !== $value && null !== $value;
				}
			);

			$args['page'] = self::MENU;
			$base         = add_query_arg( $args, admin_url( 'admin.php' ) );

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
		}

		private function notice( string $notice ): void {
			$messages = array(
				'blocked'   => __( 'The address has been blocked. Independent Analytics will ignore it from now on.', 'abp-plugin' ),
				'unblocked' => __( 'The address has been unblocked.', 'abp-plugin' ),
				'saved'     => __( 'Settings saved.', 'abp-plugin' ),
				'cleared'   => __( 'The captured extra details have been cleared.', 'abp-plugin' ),
				'invalid'   => __( 'Please enter a valid IP address.', 'abp-plugin' ),
			);

			if ( ! isset( $messages[ $notice ] ) ) {
				return;
			}

			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $messages[ $notice ] ) );
		}

		private function stat_card( string $label, string $value, string $hint, string $icon, string $modal_target = '', string $modal_label = '' ): void {
			?>
			<div class="abp-vcard">
				<span class="abp-vcard-icon"><span class="dashicons <?php echo esc_attr( $icon ); ?>"></span></span>
				<span class="abp-vcard-label"><?php echo esc_html( $label ); ?></span>
				<span class="abp-vcard-value"><?php echo esc_html( $value ); ?></span>
				<?php if ( '' !== $hint ) : ?>
					<span class="abp-vcard-hint"><?php echo esc_html( $hint ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $modal_target ) : ?>
					<button type="button" class="abp-vcard-action" data-abp-modal-open="<?php echo esc_attr( $modal_target ); ?>">
						<?php echo esc_html( $modal_label ); ?>
						<span class="dashicons dashicons-external" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Overlay showing a full top list on top of the current screen.
		 *
		 * @param string $id    Unique DOM id used by the opener button.
		 * @param string $title Dialog title.
		 * @param array  $rows  Rows with label/hits.
		 * @param string $unit  Accessible hint for the hit counter.
		 */
		private function top_modal( string $id, string $title, array $rows, string $unit ): void {
			?>
			<div class="abp-vmodal" id="<?php echo esc_attr( $id ); ?>" hidden>
				<div class="abp-vmodal-backdrop" data-abp-modal-close></div>
				<div class="abp-vmodal-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
					<div class="abp-vmodal-head">
						<h2 id="<?php echo esc_attr( $id ); ?>-title">
							<?php echo esc_html( $title ); ?>
							<span class="abp-vmodal-count"><?php echo esc_html( sprintf( _n( '%s entry', '%s entries', count( $rows ), 'abp-plugin' ), number_format_i18n( count( $rows ) ) ) ); ?></span>
						</h2>
						<button type="button" class="abp-vmodal-close" data-abp-modal-close aria-label="<?php esc_attr_e( 'Close', 'abp-plugin' ); ?>">&times;</button>
					</div>
					<div class="abp-vmodal-body">
						<?php if ( $rows ) : ?>
							<ul class="abp-vtoplist" aria-label="<?php echo esc_attr( sprintf( '%s — %s', $title, $unit ) ); ?>">
								<?php foreach ( $rows as $row ) : ?>
									<li>
										<span class="abp-vtoplabel"><?php echo esc_html( (string) $row['label'] ); ?></span>
										<span class="abp-vtophits"><?php echo esc_html( number_format_i18n( (int) $row['hits'] ) ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p class="abp-vempty"><?php esc_html_e( 'Nothing recorded yet.', 'abp-plugin' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}

		private function source_label( array $session ): string {
			$parts = array();

			if ( ! empty( $session['referrer_type'] ) && 'Direct' !== $session['referrer_type'] ) {
				$parts[] = (string) $session['referrer_type'];
			}

			if ( ! empty( $session['referrer_domain'] ) ) {
				$parts[] = (string) $session['referrer_domain'];
			} elseif ( ! empty( $session['referrer_label'] ) && 'Direct' !== $session['referrer_label'] ) {
				$parts[] = (string) $session['referrer_label'];
			}

			if ( ! empty( $session['utm_source'] ) ) {
				$parts[] = 'utm: ' . $session['utm_source'];
			}

			if ( ! empty( $session['utm_campaign'] ) ) {
				$parts[] = 'campaign: ' . $session['utm_campaign'];
			}

			return implode( ' · ', array_unique( $parts ) );
		}

		private function short_agent( string $agent ): string {
			if ( '' === $agent ) {
				return '—';
			}

			return strlen( $agent ) > 90 ? substr( $agent, 0, 90 ) . '…' : $agent;
		}

		private function render_inactive(): void {
			?>
			<div class="wrap abp-vwrap">
				<div class="abp-vpanel" style="margin-top:20px">
					<h2><?php esc_html_e( 'Independent Analytics is required', 'abp-plugin' ); ?></h2>
					<p class="abp-vnote"><?php esc_html_e( 'The visitor list is built entirely from Independent Analytics data. Install and activate Independent Analytics and this page starts working again automatically.', 'abp-plugin' ); ?></p>
				</div>
			</div>
			<?php
		}
	}

	ABP_IA_Visitors::instance();
}
