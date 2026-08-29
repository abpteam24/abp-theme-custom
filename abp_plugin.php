<?php
	/**
	 * Plugin Name: ABP Plugin
	 * Description: ABP Custom Plugin.
	 * Version: 1.0.0
	 * Author: abpteam
	 * Author URI: https://abp-team.com
	 * Text Domain: abp-plugin
	 * Domain Path: /languages
	 * WC requires at least: 8.0.0
	 *  WC tested up to: latest
	 *  Requires PHP: 7.4
	 *  Requires MySQL: 5.7+
	 *  License: GPLv3
	 *  License URI: https://www.gnu.org/licenses/gpl-3.0.html
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABP_Plugin' ) ) {
		class ABP_Plugin {
			public function __construct() {
				add_action( 'admin_init', function () {
					if ( ! function_exists( 'is_plugin_active' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
				} );
				$this->load_plugin();
			}

			private function load_plugin(): void {
				if ( ! defined( 'ABP_PLUGIN_FILE' ) ) {
					define( 'ABP_PLUGIN_FILE', __FILE__ );
				}
				if ( ! defined( 'ABP_VERSION' ) ) {
					// Keep in sync with the plugin header. Update here when bumping plugin version.
					define( 'ABP_VERSION', '1.0.0' );
				}
				if ( ! defined( 'ABP_DIR' ) ) {
					define( 'ABP_DIR', plugin_dir_path( __FILE__ ) );
				}
				if ( ! defined( 'ABP_URL' ) ) {
					define( 'ABP_URL', plugin_dir_url( __FILE__ ) );
				}
				if ( ! defined( 'ABP_BASE' ) ) {
					define( 'ABP_BASE', plugin_basename( __FILE__ ) );
				}
				if ( ! defined( 'ABP_BLANK_IMG_URL' ) ) {
					define( 'ABP_BLANK_IMG_URL', ABP_URL . 'assets/images/blank_image.png' );
				}
				$dep_file = ABP_DIR . 'includes/abp_dependencies.php';
				if ( file_exists( $dep_file ) ) {
					require_once $dep_file;
				} else {
					// Avoid fatal error if the dependency file is missing and show admin notice.
					add_action( 'admin_notices', function() use ( $dep_file ) {
						/* translators: %s: missing file path */
						printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( sprintf( __( 'ABP Plugin error: missing file %s', 'abp-plugin' ), $dep_file ) ) );
					} );
				}
				// Load translations on plugins_loaded to ensure localization functions are available.
				add_action( 'plugins_loaded', function() {
					load_plugin_textdomain( 'abp-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
				} );
			
			}
		}
		new ABP_Plugin();
		register_activation_hook( __FILE__, function () {
			if ( class_exists( 'ABP_Dependencies' ) ) {
				ABP_Dependencies::activation();
			}
		} );
		register_deactivation_hook( __FILE__, function () {
			if ( class_exists( 'ABP_Dependencies' ) ) {
				ABP_Dependencies::deactivate();
			}
		} );
	}