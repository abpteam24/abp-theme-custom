<?php
/**
 * ABP My Account (Fixed & Complete Version)
 * Shortcode: [abp_my_account]
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

final class ABP_My_Account {

    public static function init() {
        add_shortcode('abp_my_account', [__CLASS__, 'render']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        
        // সাবস্ক্রাইবার বা কাস্টমারদের এডমিন প্যানেল (wp-admin) থেকে রিডাইরেক্ট করে মাই একাউন্ট পেজে পাঠানো
        add_action('admin_init', [__CLASS__, 'block_admin_access']);
    }

    public static function block_admin_access() {
        if (is_admin() && !defined('DOING_AJAX') && current_user_can('subscriber')) {
            wp_safe_redirect(site_url('/my-account/')); // আপনার মাই একাউন্ট পেজের URL দিন
            exit;
        }
    }

    public static function render() {
        if (!function_exists('edd_get_option')) {
            return '<div class="abp-account-notice">' . esc_html__('Easy Digital Downloads is required for My Account.', 'abp-team') . '</div>';
        }

        if (!is_user_logged_in()) {
            return self::render_logged_out();
        }

        return self::render_logged_in();
    }

    public static function enqueue_assets() {
        if (!is_page()) return;

        global $post;
        if (!$post || !has_shortcode($post->post_content, 'abp_my_account')) return;

        wp_register_style('abp-my-account', false, [], '1.2.1');
        wp_enqueue_style('abp-my-account');
        wp_add_inline_style('abp-my-account', self::get_css());
        
        $js = "
        jQuery(document).ready(function($) {
            $('.abp-tabs').on('click', '.abp-tab-btn', function(e) {
                e.preventDefault();
                var targetTab = $(this).attr('href');
                $('.abp-tab-btn').removeClass('active');
                $('.abp-tab-content').removeClass('active');
                $(this).addClass('active');
                $(targetTab).addClass('active');
            });
        });
        ";
        
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $js);
    }

    private static function render_logged_out() {
        ob_start();
        ?>
        <div class="abp-account abp-auth-container">
            <div class="abp-account-header text-center">
                <h2><?php esc_html_e('My Account', 'abp-team'); ?></h2>
                <p><?php esc_html_e('Log in or Register to access your purchases, licenses, and downloads.', 'abp-team'); ?></p>
            </div>

            <!-- Login & Register Tabs for Logged-Out Users -->
            <div class="abp-tabs">
                <a href="#tab-auth-login" class="abp-tab-btn active"><?php esc_html_e('Login', 'abp-team'); ?></a>
                <?php if (get_option('users_can_register')) : ?>
                    <a href="#tab-auth-register" class="abp-tab-btn"><?php esc_html_e('Register', 'abp-team'); ?></a>
                <?php endif; ?>
            </div>

            <!-- Tab Content: Login Form -->
            <div id="tab-auth-login" class="abp-tab-content active">
                <div class="abp-account-card">
                    <h3><?php esc_html_e('Login to Your Account', 'abp-team'); ?></h3>
                    <?php
                    if (shortcode_exists('edd_login')) {
                        echo do_shortcode('[edd_login]');
                    } else {
                        wp_login_form([
                            'echo'     => true,
                            'redirect' => get_permalink(),
                            'remember' => true,
                        ]);
                    }
                    ?>
                    <div class="abp-auth-links">
                        <a href="<?php echo esc_url(wp_lostpassword_url(get_permalink())); ?>">
                            <?php esc_html_e('Forgot your password?', 'abp-team'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Register Form -->
            <?php if (get_option('users_can_register')) : ?>
                <div id="tab-auth-register" class="abp-tab-content">
                    <div class="abp-account-card">
                        <h3><?php esc_html_e('Create a New Account', 'abp-team'); ?></h3>
                        <?php
                        if (shortcode_exists('edd_register')) {
                            echo do_shortcode('[edd_register]');
                        } else {
                            echo '<p>' . esc_html__('Registration form shortcode not available.', 'abp-team') . '</p>';
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }

    private static function render_logged_in() {
        $user = wp_get_current_user();
        ob_start();
        ?>
        <div class="abp-account">
            <div class="abp-account-header flex-header">
                <div>
                    <h2><?php esc_html_e('My Account', 'abp-team'); ?></h2>
                    <p><?php printf(esc_html__('Welcome back, %s!', 'abp-team'), esc_html($user->display_name)); ?></p>
                </div>
                <a class="abp-logout-button" href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>">
                    <?php esc_html_e('Logout', 'abp-team'); ?>
                </a>
            </div>

            <div class="abp-tabs">
                <a href="#tab-dashboard" class="abp-tab-btn active"><?php esc_html_e('Dashboard', 'abp-team'); ?></a>
                <a href="#tab-orders" class="abp-tab-btn"><?php esc_html_e('Orders & Invoices', 'abp-team'); ?></a>
                <a href="#tab-licenses" class="abp-tab-btn"><?php esc_html_e('Licenses', 'abp-team'); ?></a>
                <a href="#tab-downloads" class="abp-tab-btn"><?php esc_html_e('Downloads', 'abp-team'); ?></a>
            </div>

            <div id="tab-dashboard" class="abp-tab-content active">
                <?php echo self::render_account_details(); ?>
            </div>

            <div id="tab-orders" class="abp-tab-content">
                <?php echo self::render_purchases(); ?>
            </div>

            <div id="tab-licenses" class="abp-tab-content">
                <?php echo self::render_license_keys(); ?>
            </div>

            <div id="tab-downloads" class="abp-tab-content">
                <?php echo self::render_downloads(); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function render_account_details() {
        $user = wp_get_current_user();
        ob_start();
        ?>
        <div class="abp-account-card">
            <div class="abp-section-heading">
                <h3><?php esc_html_e('Account Overview', 'abp-team'); ?></h3>
            </div>
            <div class="abp-account-details">
                <div class="abp-detail-item">
                    <span><?php esc_html_e('Name', 'abp-team'); ?></span>
                    <strong><?php echo esc_html($user->display_name); ?></strong>
                </div>
                <div class="abp-detail-item">
                    <span><?php esc_html_e('Email', 'abp-team'); ?></span>
                    <strong><?php echo esc_html($user->user_email); ?></strong>
                </div>
                <div class="abp-detail-item">
                    <span><?php esc_html_e('Username', 'abp-team'); ?></span>
                    <strong><?php echo esc_html($user->user_login); ?></strong>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function render_purchases() {
        if (!function_exists('edd_get_orders')) {
            return self::empty_card('My Purchases', 'Purchase information is currently unavailable.');
        }

        $user_id = get_current_user_id();
        
        // প্রথমে User ID দিয়ে কাস্টমার খোঁজা, না পেলে Email দিয়ে ট্রাই করা
        $customer = function_exists('edd_get_customer_by') ? edd_get_customer_by('user_id', $user_id) : false;
        if (!$customer && function_exists('edd_get_customer_by')) {
            $user = wp_get_current_user();
            $customer = edd_get_customer_by('email', $user->user_email);
        }

        if (!$customer) {
            return self::empty_card('My Purchases', 'You have no purchases yet.');
        }

        $orders = edd_get_orders([
            'customer' => $customer->id,
            'status'   => ['complete', 'publish'],
            'number'   => 50,
        ]);

        if (empty($orders)) {
            return self::empty_card('My Purchases', 'You have no completed purchases yet.');
        }

        ob_start();
        ?>
        <div class="abp-account-card abp-purchases">
            <div class="abp-section-heading">
                <h3><?php esc_html_e('Order History', 'abp-team'); ?></h3>
            </div>
            <div class="abp-purchases-table-wrap">
                <table class="abp-purchases-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Order ID', 'abp-team'); ?></th>
                            <th><?php esc_html_e('Product(s)', 'abp-team'); ?></th>
                            <th><?php esc_html_e('Date', 'abp-team'); ?></th>
                            <th><?php esc_html_e('Total', 'abp-team'); ?></th>
                            <th><?php esc_html_e('Invoice / Action', 'abp-team'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order) : ?>
                            <?php 
                            $order_id   = absint($order->id);
                            $order_date = !empty($order->date_created) ? $order->date_created : '';
                            $total      = edd_currency_filter(edd_format_amount($order->total), $order->currency);
                            $items      = !empty($order->items) ? $order->items : [];
                            
                            // রিসিট বা ইনভয়েস পেজের লিঙ্ক তৈরি
                            $receipt_url = function_exists('edd_get_success_page_url') ? edd_get_success_page_url('payment_key=' . $order->payment_key) : '#';
                            ?>
                            <tr>
                                <td data-label="Order ID"><strong>#<?php echo esc_html($order_id); ?></strong></td>
                                <td data-label="Product">
                                    <?php 
                                    $names = [];
                                    foreach ($items as $item) {
                                        $names[] = get_the_title($item->product_id);
                                    }
                                    echo esc_html(implode(', ', array_filter($names)));
                                    ?>
                                </td>
                                <td data-label="Date">
                                    <?php echo esc_html($order_date ? date_i18n(get_option('date_format'), strtotime($order_date)) : '—'); ?>
                                </td>
                                <td data-label="Total"><strong><?php echo esc_html($total); ?></strong></td>
                                <td data-label="Invoice / Action">
                                    <a class="abp-download-button" href="<?php echo esc_url($receipt_url); ?>" target="_blank">
                                        <?php esc_html_e('View Receipt', 'abp-team'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function render_downloads() {
        if (!shortcode_exists('edd_user_downloads')) {
            return self::empty_card('Your Downloads', 'Downloads shortcode is not available.');
        }

        ob_start();
        ?>
        <div class="abp-account-card abp-downloads">
            <div class="abp-section-heading">
                <h3><?php esc_html_e('Your Downloads', 'abp-team'); ?></h3>
            </div>
            <?php echo do_shortcode('[edd_user_downloads]'); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function render_license_keys() {
        if (!shortcode_exists('edd_license_keys')) {
            return self::empty_card('Licenses', 'Software Licensing plugin is not active.');
        }

        ob_start();
        ?>
        <div class="abp-account-card abp-license-keys">
            <div class="abp-section-heading">
                <h3><?php esc_html_e('Manage Licenses', 'abp-team'); ?></h3>
            </div>
            <?php echo do_shortcode('[edd_license_keys]'); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function empty_card($title, $message) {
        return '<div class="abp-account-card"><h3>' . esc_html($title) . '</h3><p class="abp-muted">' . esc_html($message) . '</p></div>';
    }

    private static function get_css() {
        return '
        .abp-account { max-width: 1200px; margin: 40px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .abp-account-header { margin-bottom: 24px; }
        .text-center { text-align: center; }
        .flex-header { display: flex; justify-content: space-between; align-items: center; }
        .abp-account-header h2 { margin: 0 0 6px; font-size: 28px; color: #1e1e1e; }
        .abp-account-header p { margin: 0; color: #666; }
        
        .abp-tabs { display: flex; gap: 10px; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; }
        .abp-tab-btn { padding: 10px 18px; text-decoration: none; color: #64748b; font-weight: 600; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; cursor: pointer; }
        .abp-tab-btn.active, .abp-tab-btn:hover { color: #4f46e5; border-bottom-color: #4f46e5; }
        
        .abp-tab-content { display: none !important; }
        .abp-tab-content.active { display: block !important; }

        .abp-account-card { padding: 24px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .abp-account-details { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; }
        .abp-detail-item { padding: 14px; background: #f8fafc; border-radius: 8px; }
        .abp-detail-item span { display: block; font-size: 12px; color: #64748b; margin-bottom: 4px; }
        .abp-detail-item strong { color: #0f172a; font-size: 15px; }
        
        .abp-auth-links { display: flex; justify-content: space-between; margin-top: 16px; font-size: 14px; }
        .abp-purchases-table { width: 100%; border-collapse: collapse; text-align: left; }
        .abp-purchases-table th { background: #f8fafc; padding: 12px; font-size: 13px; color: #475569; border-bottom: 1px solid #e2e8f0; }
        .abp-purchases-table td { padding: 14px 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .abp-download-button { display: inline-block; padding: 6px 14px; background: #4f46e5; color: #fff; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 500; }
        .abp-download-button:hover { background: #4338ca; }
        .abp-logout-button { padding: 8px 16px; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #334155; font-weight: 500; }
        .abp-muted { color: #64748b; font-size: 14px; }
        
        @media (max-width: 768px) {
            .abp-tabs { overflow-x: auto; white-space: nowrap; }
            .abp-account-details { grid-template-columns: 1fr; }
            .flex-header { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
        ';
    }
}

ABP_My_Account::init();