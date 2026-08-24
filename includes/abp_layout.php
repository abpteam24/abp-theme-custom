<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABP_Layout')) {
        class ABP_Layout {
            public function __construct() {
            }

            //==============================//
            public static function button_add($button_text, $class = '', $button_class = ''): void {
                $class = $class ?: 'add_new_hook';
                $button_class = $button_class ?: '_btn_light_active_xs';
                $button_text = $button_text ?: __('Add New', 'abp-transport-booking');
                ?>
                <button class="<?php echo esc_attr($button_class . ' ' . $class); ?>" type="button">
                    <?php ABP_Static::svg('plus');
                        echo esc_html($button_text); ?>
                </button>
                <?php
            }
            public static function button_edit($class_edit = 'edit_hook'): void {
                ?>
                <button class="_btn_light_navy_blue_xs <?php echo esc_attr($class_edit); ?>" type="button" title="<?php esc_attr_e('Edit This Item', 'abp-transport-booking'); ?>">
                    <?php ABP_Static::svg('edit'); ?>
                </button>
                <?php
            }
            public static function button_delete($class = 'delete_hook'): void {
                ?>
                <button class="_btn_light_danger_xxs <?php echo esc_attr($class); ?>" type="button" title="<?php esc_attr_e('Delete This Item', 'abp-transport-booking'); ?>"><?php ABP_Static::svg('close_1'); ?></button>
                <?php
            }
            public static function button_save($text = '', $class = ''): void {
                $class = $class ?: '_btn_green_pale_xs';
                $text = $text ?: __('save', 'abp-transport-booking');
                ?>
                <button class="<?php echo esc_attr($class); ?>" type="submit">
                    <?php ABP_Static::svg('save');
                        echo esc_html($text); ?>
                </button>
                <?php
            }
            public static function button_sort(): void {
                ?>
                <div class="_btn_light_info_xxs sortable_handle" type="button" title="<?php esc_attr_e('Move This Item', 'abp-transport-booking'); ?>">
                    <?php ABP_Static::svg('drag'); ?>
                </div>
                <?php
            }
            public static function button_delete_sort_edit(): void {
                ?>
                <div class="_all_center">
                    <div class="_group_content">
                        <?php
                            self::button_edit();
                            self::button_sort();
                            self::button_delete();
                        ?>
                    </div>
                </div>
                <?php
            }
            public static function button_delete_sort(): void {
                ?>
                <div class="_all_center">
                    <div class="_group_content">
                        <?php
                            self::button_sort();
                            self::button_delete();
                        ?>
                    </div>
                </div>
                <?php
            }
            public static function button_global_save($action, $text = '', $class = ''): void {
                if (!empty($action)) {
                    $class = $class ?: '_btn_green_pale_xs';
                    $text = $text ?: __('save', 'abp-transport-booking');
                    ?>
                    <button class="<?php echo esc_attr($class); ?>" type="button" onclick="abp_save_global('<?php echo esc_attr($action); ?>',this)">
                        <?php ABP_Static::svg('save');
                            echo esc_html($text); ?>
                    </button>
                    <?php
                }
            }
            public static function button_global_popup($action, $text = '', $class = ''): void {
                if (!empty($action)) {
                    $class = $class ?: '_btn_light_active_xs';
                    $text = $text ?: __('Add New', 'abp-transport-booking');
                    ?>
                    <button type="button" class="<?php echo esc_attr($class) ?>" onclick="abp_popup_open_global('<?php echo esc_attr($action); ?>')">
                        <?php ABP_Static::svg('plus');
                            echo esc_html($text); ?>
                    </button>
                    <?php
                }
            }
            //=============================//
            public static function info_text($key = '', $data = ''): void {
                $data = empty($data) ? ABP_Static::array_info($key) : $data;
                if ($data) {
                    ?>
                    <div class="info_text load_more">
                        ℹ️ &nbsp;<?php echo wp_kses_post($data); ?>
                        <span class="load_more_action" data-less="<?php esc_html_e('.... Less ', 'abp-transport-booking'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transport-booking'); ?>"><?php esc_html_e('.... More', 'abp-transport-booking'); ?></span>
                    </div>
                    <?php
                }
            }
            public static function load_more($data = ''): void {
                if ($data) {
                    ?>
                    <div class="load_more">
                        <?php echo wp_kses_post($data); ?>
                        <span class="load_more_action" data-less="<?php esc_html_e('.... Less ', 'abp-transport-booking'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transport-booking'); ?>"><?php esc_html_e('.... More', 'abp-transport-booking'); ?></span>
                    </div>
                    <?php
                }
            }
            public static function layout_warning_info($key): void {
                $data = ABP_Static::array_info($key);
                if ($data) {
                    echo '<div class="_section_bg_warning_mar_zero"><h4 class="abp_text_center_color_white">' . esc_html($data) . '</h4></div>';
                }
            }
            public static function layout_warning_info_xs($key, $data = ''): void {
                $data = empty($data) ? ABP_Static::array_info($key) : $data;
                if ($data) {
                    echo '<div class="abp_text_center_color_white_bg_warning_padding_xxs_fs_label">' . esc_html($data) . '</div>';
                }
            }
            public static function layout_info_xs($key, $data = ''): void {
                $data = empty($data) ? ABP_Static::array_info($key) : $data;
                if ($data) {
                    echo '<div class="abp_bg_info_padding_xxs _all_center _color_5">' . esc_html($data) . '</div>';
                }
            }
            public static function on(): bool|string {
                ob_start();
                ?>
                <strong class="abp_color_theme"> <?php esc_html_e('ON', 'abp-transport-booking'); ?></strong>
                <?php
                return ob_get_clean();
            }
            public static function off(): bool|string {
                ob_start();
                ?>
                <strong class="abp_color_theme"> <?php esc_html_e('OFF', 'abp-transport-booking'); ?></strong>
                <?php
                return ob_get_clean();
            }

        }
        new ABP_Layout();
    }