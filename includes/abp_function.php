<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABP_Function')) {
		class ABP_Function {
			public function __construct() {
			}

			public static function get_post_info($post_id, $key, $default = '') {
				$data = get_post_meta($post_id, $key, true) ?: $default;
				return self::data_sanitize($data);
			}
			public static function data_sanitize($data) {
				$data = maybe_unserialize($data);
				if (is_string($data)) {
					$data = maybe_unserialize($data);
					if (is_array($data)) {
						$data = self::data_sanitize($data);
					} else {
						$data = sanitize_text_field(stripslashes(wp_strip_all_tags($data)));
					}
				} elseif (is_array($data)) {
					foreach ($data as &$value) {
						if (is_array($value)) {
							$value = self::data_sanitize($value);
						} else {
							$value = sanitize_text_field(stripslashes(wp_strip_all_tags($value)));
						}
					}
				}
				return $data;
			}
			public static function get_all_meta($post_id = 0): array {
				$all_data = [];
				if ($post_id > 0) {
					$all_data['post_title'] = get_the_title($post_id);
					$all_data['post_id'] = $post_id;
					$metas = get_post_meta($post_id);
					if (!empty($metas) && sizeof($metas) > 0) {
						foreach ($metas as $key => $meta) {
							$all_data[$key] = self::data_sanitize($meta[0]);
						}
					}
					$all_data['capacity'] = self::get_total_qty($post_id, $all_data);
				}
				return $all_data;
			}
			public static function get_taxonomy($name): array|WP_Error|string {
				return get_terms(array('taxonomy' => $name, 'hide_empty' => false));
			}
			public static function get_all_term_data($term_name): array {
				$all_data = [];
				$taxonomies = self::get_taxonomy($term_name);
				if (!empty($taxonomies) && is_array($taxonomies) && sizeof($taxonomies) > 0) {
					foreach ($taxonomies as $taxonomy) {
						$all_data[$taxonomy->term_id] = $taxonomy->name;
					}
				}
				return $all_data;
			}
			public static function get_option($option, $default = []) {
				$option_data = get_option(sanitize_key($option));
				return !empty($option_data) ? $option_data : $default;
			}
			public static function get_options($option, $key, $default = '') {
				$options = get_option(sanitize_key($option));
				if (isset($options[$key]) && $options[$key]) {
					$default = $options[$key];
				}
				return $default;
			}
			public static function build_url($value = '', $extra_args = []): string {
				$default_args = [
					'page' => ABP_Function::slug(),
					'tab' => $value,
					'_abp_nonce' => wp_create_nonce('abp_url_action'),
				];
				$final_args = array_merge($default_args, $extra_args);
				return add_query_arg($final_args, admin_url('admin.php'));
			}
			public static function get_image_url($post_id = '', $image_id = '', $size = 'full'): bool|string {
				$image_id = $post_id && $post_id > 0 ? get_post_thumbnail_id($post_id) : $image_id;
				return wp_get_attachment_image_url($image_id, $size);
			}
			public static function get_page_by_slug($slug): bool|WP_Post {
				if ($pages = get_pages()) {
					foreach ($pages as $page) {
						if ($slug === $page->post_name) {
							return $page;
						}
					}
				}
				return false;
			}
			public static function get_id_by_slug($page_slug): ?int {
				$page = get_page_by_path($page_slug);
				return $page?->ID;
			}
			public static function get_user_role($user_ID): string {
				global $wp_roles;
				$user_role_list = '';
				$user_data = get_userdata($user_ID);
				$user_role_slug = $user_data->roles;
				if (is_array($user_role_slug) && sizeof($user_role_slug) > 0) {
					$user_count = 0;
					foreach ($user_role_slug as $user_role) {
						$user_count++;
						if ($user_count > 1) {
							$user_role_list .= ", ";
						}
						$user_role_list .= translate_user_role($wp_roles->roles[$user_role]['name']);
					}
				}
				return $user_role_list;
			}

			//============== Transport Function===============//
		}
		new ABP_Function();
	}