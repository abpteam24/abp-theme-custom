<?php
	/**
	 * Plugin Name: ABP Plugin
	 * Description: WooCommerce transport booking for bus, ferry, shuttle and coach services with seat plans, ticket types, routes, schedules and return trips.
	 * Version: 1.0.2
	 * Author: abpteam
	 * Author URI: https://abp-team.com
	 * Text Domain: abp-transport-booking
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
					define( 'ABP_VERSION', '1.0.0' );
				}
				if ( ! defined( 'ABP_DIR' ) ) {
					define( 'ABP_DIR', plugin_dir_path( __FILE__ ) );
				}
				if ( ! defined( 'ABP_URL' ) ) {
					define( 'ABP_URL', plugin_dir_url( __FILE__ ) );
				}
				if ( ! defined( 'ABP_BASE' ) ) {
					define( 'ABP_BASE', basename( __FILE__ ) );
				}
				if ( ! defined( 'ABP_BLANK_IMG_URL' ) ) {
					define( 'ABP_BLANK_IMG_URL', ABP_URL . 'assets/images/blank_image.png' );
				}
				require_once ABP_DIR . 'includes/abp_dependencies.php';

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