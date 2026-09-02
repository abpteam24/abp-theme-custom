<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABP_Hooks' ) ) {
        class ABP_Shortcodes {
            public function __construct() {
                add_shortcode( 'abp_my_account', [ $this, 'my_account' ] );
                add_shortcode( 'abp_product_pricing', [ $this, 'product_pricing' ] );
                add_shortcode( 'abp_why_choose_us', [ $this, 'why_choose_us' ] );
                add_shortcode( 'abp_about_section', [ $this, 'about_section' ] );
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

            public function product_pricing( $atts ): false|string {
                ob_start();
                $file_path = wp_normalize_path( ABP_DIR . 'templates/product_pricing.php' );
                if ( file_exists( $file_path ) ) {
                    include_once $file_path;
                }

                return ob_get_clean();
            }

            public function why_choose_us(): false|string {
                ob_start();
                $file_path = wp_normalize_path( ABP_DIR . 'templates/why_choose_us.php' );
                if ( file_exists( $file_path ) ) {
                    include_once $file_path;
                }

                return ob_get_clean();
            }

            public function about_section(): false|string {
                ob_start();
                $file_path = wp_normalize_path( ABP_DIR . 'templates/about_section.php' );
                if ( file_exists( $file_path ) ) {
                    include_once $file_path;
                }

                return ob_get_clean();
            }

            public function how_we_work(): false|string {
                ob_start();
                $file_path = wp_normalize_path( ABP_DIR . 'templates/how_we_work.php' );
                if ( file_exists( $file_path ) ) {
                    include_once $file_path;
                }

                return ob_get_clean();
            }
        }
        new ABP_Shortcodes();
    }
