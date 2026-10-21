<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABP_Dependencies')) {
		class ABP_Dependencies {
			public function __construct() {
				$this->load_file();
				add_action('admin_enqueue_scripts', array($this, 'global_enqueue'), 90);
				add_action('wp_enqueue_scripts', array($this, 'global_enqueue'), 90);

				add_action('upgrader_process_complete', [$this, 'flush_rewrite']);
				add_action('admin_init', [$this, 'user_blacklist']);
			}
			public function user_blacklist(): void {
				if ( is_admin() && ! defined( 'DOING_AJAX' ) && current_user_can( 'subscriber' ) ) {
					wp_safe_redirect( site_url( '/my-account/' ) ); // আপনার মাই একাউন্ট পেজের URL দিন
					exit;
				}
			}
			private function load_file(): void {
				$files = array(
					'includes/abp-smart-support.php',
					'includes/abp_static.php',
					'includes/abp_function.php',
					'includes/abp_account_security.php',
					'includes/abp_layout.php',
					'includes/abp_shortcodes.php',
				);
				foreach ( $files as $f ) {
					$path = ABP_DIR . $f;
					if ( file_exists( $path ) ) {
						require_once $path;
					} else {
						add_action( 'admin_notices', function() use ( $path ) {
							printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html( sprintf( __( 'ABP Plugin warning: missing file %s', 'abp-plugin' ), $path ) ) );
						} );
					}
				}
			}

			public function global_enqueue(): void {
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('abp_jquery_ui', ABP_URL . 'assets/css/jquery-ui.min.css', array(), '1.13.2');
				wp_enqueue_style('abp_font_awesome', ABP_URL . 'assets/css/font_awesome.min.css', array(), '5.15.4');
				wp_enqueue_style('abp_custom', ABP_URL . 'assets/css/abp_custom.css', array(), time());
				wp_enqueue_style('abp_lib', ABP_URL . 'assets/css/abp_lib.css', array(), time());
				wp_enqueue_script('abp_lib', ABP_URL . 'assets/js/abp_lib.js', array('jquery'), time(), true);

			}
			public static function activation(): void {
				if ( class_exists( 'ABP_Account_Security' ) ) {
					ABP_Account_Security::activate();
				}
				flush_rewrite_rules();
			}
			public static function deactivate(): void {
				flush_rewrite_rules();
			}
			public function flush_rewrite(): void {
				flush_rewrite_rules();
			}
		}
		new ABP_Dependencies();
	}