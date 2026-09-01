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
				wp_enqueue_style('abp_lib', ABP_URL . 'assets/css/abp_lib.css', array(), ABP_VERSION);
				wp_enqueue_script('abp_lib', ABP_URL . 'assets/js/abp_lib.js', array('jquery'), ABP_VERSION, true);
				$colors = ABP_Function::get_option('abp_color');
				//echo '<pre>';                print_r($colors);                echo '</pre>';
				$available = !empty($colors['available']) ? $colors['available'] : "#D4EDDA";
				$sold = !empty($colors['sold']) ? $colors['sold'] : "#F8D7DA";
				$booked = !empty($colors['booked']) ? $colors['booked'] : "#6C757D";
				$selected = !empty($colors['selected']) ? $colors['selected'] : "#007BFF";
				$abp_css_var = ABP_Function::get_option('abp_css_var');
				$default_color = ($abp_css_var['color_default'] ?? null) ?: '#303030';
				$color_theme = ($abp_css_var['color_theme'] ?? null) ?: '#95951c';
				$alternate_color = ($abp_css_var['color_theme_alternate'] ?? null) ?: '#fff';
				$color_warning = ($abp_css_var['color_warning'] ?? null) ?: '#E67C30';
				$bg_section = ($abp_css_var['bg_section'] ?? null) ?: '#FAFCFE';
				$bg_button = ($abp_css_var['bg_button'] ?? null) ?: '#222';
				$color_button = ($abp_css_var['color_button'] ?? null) ?: $alternate_color;
				$color_theme_ee = $color_theme . 'ee';
				$color_theme_cc = $color_theme . 'cc';
				$color_theme_aa = $color_theme . 'aa';
				$color_theme_88 = $color_theme . '88';
				$color_theme_77 = $color_theme . '77';
				$default_br = !empty($abp_css_var['br_default']) ? $abp_css_var['br_default'] . 'px' : '5px';
				$br_xl = !empty($abp_css_var['br_default']) ? $abp_css_var['br_default'] * 2 . 'px' : '10px';
				$fs_h1 = !empty($abp_css_var['fs_h1']) ? $abp_css_var['fs_h1'] . 'px' : '30px';
				$fs_h2 = !empty($abp_css_var['fs_h2']) ? $abp_css_var['fs_h2'] . 'px' : '26px';
				$fs_h3 = !empty($abp_css_var['fs_h3']) ? $abp_css_var['fs_h3'] . 'px' : '24px';
				$fs_h4 = !empty($abp_css_var['fs_h4']) ? $abp_css_var['fs_h4'] . 'px' : '20px';
				$fs_h5 = !empty($abp_css_var['fs_h5']) ? $abp_css_var['fs_h5'] . 'px' : '17px';
				$fs_h6 = !empty($abp_css_var['fs_h6']) ? $abp_css_var['fs_h6'] . 'px' : '15px';
				$fs_label = !empty($abp_css_var['fs_label']) ? $abp_css_var['fs_label'] . 'px' : '14px';
				$default_fs = !empty($abp_css_var['fs_default']) ? $abp_css_var['fs_default'] . 'px' : '12px';
				$button_fs = !empty($abp_css_var['fs_button']) ? $abp_css_var['fs_button'] . 'px' : '14px';
				$off = esc_html__('OFF', 'abp-transport-booking');
				$on = esc_html__('ON', 'abp-transport-booking');
				$abp_var =
					":root {
						--tb_br: {$default_br};						
						--tb_br_xl: {$br_xl};						
						--tb_text_off:'{$off}';
						--tb_text_on: '{$on}';
						--tb_fs: {$default_fs};				
						--tb_fs_label: {$fs_label};
						--tb_fs_h6: {$fs_h6};
						--tb_fs_h5: {$fs_h5};
						--tb_fs_h4: {$fs_h4};
						--tb_fs_h3: {$fs_h3};
						--tb_fs_h2: {$fs_h2};
						--tb_fs_h1: {$fs_h1};						
						--tb_button_bg: {$bg_button};
						--tb_button_color: {$color_button};
						--tb_button_fs: {$button_fs};						
						--tb_color_default: {$default_color};						
						--tb_color_section: {$bg_section};
						--tb_color_theme: {$color_theme};
						--tb_color_theme_ee: {$color_theme_ee};
						--tb_color_theme_cc: {$color_theme_cc};
						--tb_color_theme_aa: {$color_theme_aa};
						--tb_color_theme_88: {$color_theme_88};
						--tb_color_theme_77: {$color_theme_77};
						--tb_color_theme_alter: {$alternate_color};
						--tb_color_warning:{$color_warning};						
						--tb_color_available:{$available};						
						--tb_color_sold:{$sold};						
						--tb_color_booked:{$booked};						
						--tb_color_seclected:{$selected};						
					}";
				wp_add_inline_style('abp_lib', wp_kses_post($abp_var));
			}
			public static function activation(): void {
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