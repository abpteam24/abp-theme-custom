<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABP_Hooks' ) ) {
		class ABP_Shortcodes {
			public function __construct() {
				add_shortcode( 'abp_my_account', [ $this, 'my_account' ] );
				add_shortcode( 'abp_home_page', [ $this, 'home_page' ] );
				add_shortcode( 'abp_product_page', [ $this, 'product_page' ] );
				add_action( 'edd_after_download_content', [ $this, 'product_pricing' ] );
				add_shortcode( 'abp_how_we_work', [ $this, 'how_we_work' ] );
			}

			public function my_account(): false|string {
				ob_start();
				$file_path = wp_normalize_path( ABP_DIR . 'templates/my_account.php' );
				if ( file_exists( $file_path ) ) {
					include_once $file_path;
				}

				return ob_get_clean();
			}

			public function home_page(): false|string {
				ob_start();
				$file_path = wp_normalize_path( ABP_DIR . 'templates/home_page.php' );
				if ( file_exists( $file_path ) ) {
					include_once $file_path;
				}

				return ob_get_clean();
			}

			public function product_page(): false|string {
				ob_start();
				$file_path = wp_normalize_path( ABP_DIR . 'templates/product_page.php' );
				if ( file_exists( $file_path ) ) {
					include_once $file_path;
				}

				return ob_get_clean();
			}

			public function product_pricing( $product_id ): void {
				$file_path = wp_normalize_path( ABP_DIR . 'templates/product_pricing.php' );
				if ( file_exists( $file_path ) && ! empty( $product_id ) && $product_id > 0 ) {
					include_once $file_path;
				}
			}
		}
		new ABP_Shortcodes();
	}
