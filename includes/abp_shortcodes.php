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
                if ( ! function_exists( 'edd_get_option' ) ) {
                    echo '<div class="abp-account-notice">' . esc_html__( 'ABP TEAM is required for My Account.', 'abp-team' ) . '</div>';
                }
                if ( ! is_user_logged_in() ) {
                    ?>
                    <div class="abp_area">
                        <h2 class="abp"><?php esc_html_e( 'My Account', 'abp-team' ); ?></h2>
                        <p><?php esc_html_e( 'Log in or Register to access your purchases, licenses, and downloads.', 'abp-team' ); ?></p>
                        <div class="_grid_500">
                            <div class="abp-account-card">
                                <?php
                                    if ( shortcode_exists( 'edd_login' ) ) {
                                        echo do_shortcode( '[edd_login]' );
                                    } else {
                                        wp_login_form( [
                                            'echo'     => true,
                                            'redirect' => get_permalink(),
                                            'remember' => true,
                                        ] );
                                    }
                                ?>
                                <div class="abp-auth-links">
                                    <a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>">
                                        <?php esc_html_e( 'Forgot your password?', 'abp-team' ); ?>
                                    </a>
                                </div>
                            </div>
                            <!-- Tab Content: Register Form -->
                            <?php if ( get_option( 'users_can_register' ) ) : ?>
                                <div class="abp-account-card">
                                    <?php
                                        if ( shortcode_exists( 'edd_register' ) ) {
                                            echo do_shortcode( '[edd_register]' );
                                        } else {
                                            echo '<p>' . esc_html__( 'Registration form shortcode not available.', 'abp-team' ) . '</p>';
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                } else {
                    $user = wp_get_current_user();
                    ?>
                    <div class="abp-account abp_area">
                        <div class="abp-account-header flex-header">
                            <div>
                                <h2><?php esc_html_e( 'My Account', 'abp-team' ); ?></h2>
                                <p><?php printf( esc_html__( 'Welcome back, %s!', 'abp-team' ), esc_html( $user->display_name ) ); ?></p>
                            </div>
                            <a class="_btn_warning" href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>">
                                <?php esc_html_e( 'Logout', 'abp-team' ); ?>
                            </a>
                        </div>
                        <div class="abp-account-card">
                            <div class="abp-section-heading">
                                <h3><?php esc_html_e( 'Account Overview', 'abp-team' ); ?></h3>
                            </div>
                            <div class="abp-account-details">
                                <div class="abp-detail-item _fj_between">
                                    <span><?php esc_html_e( 'Name', 'abp-team' ); ?></span>
                                    <strong><?php echo esc_html( $user->display_name ); ?></strong>
                                </div>
                                <div class="abp-detail-item _fj_between">
                                    <span><?php esc_html_e( 'Email', 'abp-team' ); ?></span>
                                    <strong><?php echo esc_html( $user->user_email ); ?></strong>
                                </div>
                                <div class="abp-detail-item _fj_between">
                                    <span><?php esc_html_e( 'Username', 'abp-team' ); ?></span>
                                    <strong><?php echo esc_html( $user->user_login ); ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php
                            if ( ! class_exists( 'Easy_Digital_Downloads' ) || ! function_exists( 'edd_get_customer_by' ) ) {
                                echo '<p>' . esc_html__( 'Easy Digital Downloads plugin is not active.', 'easy-digital-downloads' ) . '</p>';
                            } else {
                                $customer = edd_get_customer_by( 'user_id', $user->ID );
                                if ( ! $customer ) {
                                    echo '<p>' . esc_html__( 'You have no account orders yet.', 'easy-digital-downloads' ) . '</p>';
                                } else {
                                    $orders = function_exists( 'edd_get_orders' ) ? edd_get_orders(
                                        array(
                                            'customer_id'    => (int) $customer->id,
                                            'number'         => 50,
                                            'type'           => 'sale',
                                            'status__not_in' => array( 'trash', 'refunded', 'abandoned' ),
                                        )
                                    ) : array();
                                    global $wpdb;
                                    $lic_table = edd_cf_table( 'licenses' );
                                    if ( ! empty( $orders ) ) {
                                        ?>
                                        <div class="edd-cf-account-wrap">
                                            <div class="edd-cf-account-box">
                                                <div class="edd-cf-account-head">
                                                    <h3><?php esc_html_e( 'My Orders & Subscriptions', 'easy-digital-downloads' ); ?></h3>
                                                </div>
                                                <div class="edd-cf-account-body">
                                                    <table class="abp">
                                                        <thead>
                                                        <tr>
                                                            <th><?php esc_html_e( 'Order ID', 'easy-digital-downloads' ); ?></th>
                                                            <th><?php esc_html_e( 'Product / License', 'easy-digital-downloads' ); ?></th>
                                                            <th><?php esc_html_e( 'Activations', 'easy-digital-downloads' ); ?></th>
                                                            <th><?php esc_html_e( 'Expire', 'easy-digital-downloads' ); ?></th>
                                                            <th><?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?></th>
                                                            <th><?php esc_html_e( 'Files', 'easy-digital-downloads' ); ?></th>
                                                            <th><?php esc_html_e( 'Action', 'easy-digital-downloads' ); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                            foreach ( (array) $orders as $order ) :
                                                                $items = function_exists( 'edd_get_order_items' ) ? edd_get_order_items( array( 'order_id' => $order->id, 'number' => 200 ) ) : array();
                                                                if ( empty( $items ) ) {
                                                                    continue;
                                                                }
                                                                foreach ( (array) $items as $item ) :
                                                                    $product_id = (int) $item->product_id;
                                                                    $price_id = isset( $item->price_id ) ? (int) $item->price_id : null;
                                                                    $license = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$lic_table} WHERE order_id = %d AND product_id = %d LIMIT 1", $order->id, $product_id ) );
                                                                    $amount = isset( $item->total ) ? edd_currency_filter( edd_format_amount( $item->total ) ) : '';
                                                                    $product_url = get_permalink( $product_id ) ? get_permalink( $product_id ) : home_url();
                                                                    $product_label = function_exists( 'edd_get_download_name' ) ? edd_get_download_name( $product_id, $price_id ) : get_the_title( $product_id );
                                                                    $activation_count = $license ? edd_sl_activation_count( $license->id ) : 0;
                                                                    $activation_limit = $license ? edd_sl_get_license_limit( $license ) : 0;
                                                                    $activation_text = $license ? ( $activation_limit > 0 ? $activation_count . ' / ' . $activation_limit : $activation_count . ' / ∞' ) : __( '—', 'easy-digital-downloads' );
                                                                    $expiration_text = $license && $license->date_expiration ? mysql2date( get_option( 'date_format' ), $license->date_expiration ) : __( 'Lifetime', 'easy-digital-downloads' );
                                                                    // Prepare downloadable files
                                                                    $download_html  = array();
                                                                    $download_files = function_exists( 'edd_get_download_files' ) ? edd_get_download_files( $product_id, $price_id ) : array();
                                                                    if ( ! empty( $download_files ) ) {
                                                                        foreach ( (array) $download_files as $file_key => $file ) {
                                                                            $download_url = function_exists( 'edd_get_download_file_url' ) ? edd_get_download_file_url( $item, $order->email, $file_key, $product_id, $price_id ) : '';
                                                                            if ( ! empty( $download_url ) ) {
                                                                                $download_html[] = '<a class="_btn_light_active" href="' . esc_url( $download_url ) . '">' . esc_html( edd_get_file_name( $file ) ) . '</a>';
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <tr>
                                                                        <th>#<?php echo esc_html( (string) $order->id ); ?></th>
                                                                        <th>
                                                                            <div><?php echo esc_html( $product_label ); ?></div>
                                                                            <?php if ( $license ) : ?>
                                                                                <div class="edd-cf-license-meta">
                                                                                    <code><?php echo esc_html( $license->license_key ); ?></code>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </th>
                                                                        <th><?php echo esc_html( $activation_text ); ?></th>
                                                                        <th><?php echo esc_html( $expiration_text ); ?></th>
                                                                        <th><?php echo esc_html( $amount ); ?></th>
                                                                        <th>
                                                                            <?php echo ! empty( $download_html ) ? implode( '<br>', $download_html ) : esc_html__( 'No file', 'easy-digital-downloads' ); ?>
                                                                        </th>
                                                                        <th>
                                                                            <?php if ( $license && $license->date_expiration && $license->date_expiration !== 'lifetime' ) { ?>
                                                                                <a class="_btn_active" href="<?php echo esc_url( $product_url ); ?>">
                                                                                    <?php esc_html_e( 'Renew', 'easy-digital-downloads' ); ?>
                                                                                </a>
                                                                            <?php } ?>
                                                                        </th>
                                                                    </tr>
                                                                <?php
                                                                endforeach;
                                                            endforeach;
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                        ?>
                    </div>
                    <?php
                }

                return ob_get_clean();
            }

            public function product_pricing( $atts ): false|string {
                $atts       = shortcode_atts( array(
                    'id' => 0,
                ), $atts, 'edd_product_tabs' );
                $product_id = absint( $atts['id'] );
                if ( ! $product_id || 'download' !== get_post_type( $product_id ) ) {
                    return '<p style="color:red; text-align:center;">Valid Product ID required.</p>';
                }
                if ( ! edd_has_variable_prices( $product_id ) ) {
                    return '<p style="color:red; text-align:center;">This product does not have variable pricing.</p>';
                }
                $prices = edd_get_variable_prices( $product_id );
                if ( empty( $prices ) ) {
                    return '';
                }
                $annual_prices     = array_slice( $prices, 0, 3, true );
                $lifetime_prices   = array_slice( $prices, 3, 3, true );
                $features_by_index = array(
                    1 => array(
                        '1 Site License for One Year',
                        '1 Year Premium Support',
                        'One Year Priority Updates & New Features ',
                    ),
                    2 => array(
                        '5 Sites License for One Year',
                        '1 Year Premium Support',
                        'One Year Priority Updates & New Features ',
                    ),
                    3 => array(
                        '10 Sites License for One Year',
                        '1 Year Premium Support',
                        'One Year Priority Updates & New Features ',
                    ),
                    4 => array(
                        '1 Site License for Life Time',
                        'Life Time Premium Support',
                        'Life Time Priority Updates & New Features ',
                    ),
                    5 => array(
                        '5 Sites License for Life Time',
                        'Life Time Premium Support',
                        'Life Time Priority Updates & New Features ',
                    ),
                    6 => array(
                        '10 Sites License for Life Time',
                        'Life Time Premium Support',
                        'Life Time Priority Updates & New Features ',
                    ),
                );
                ob_start();
                ?>
                <div class="edd-pricing-wrapper" id="edd-pricing-<?php echo esc_attr( $product_id ); ?>">
                    <div class="edd-toggle-container">
                        <span class="edd-toggle-label active" id="label-annual-<?php echo $product_id; ?>">Annual Billing</span>
                        <label class="edd-switch">
                            <input type="checkbox" id="pricing-toggle-<?php echo $product_id; ?>" onchange="togglePricingView(<?php echo $product_id; ?>)">
                            <span class="edd-slider"></span>
                        </label>
                        <span class="edd-toggle-label" id="label-lifetime-<?php echo $product_id; ?>">Lifetime License</span>
                    </div>
                    <!-- Annual Plan View Pane -->
                    <?php if ( ! empty( $annual_prices ) ) : ?>
                        <div id="pane-annual-<?php echo $product_id; ?>" class="edd-view-pane active">
                            <div class="edd-pricing-grid">
                                <?php
                                    foreach ( $annual_prices as $price_id => $price_data ) :
                                        $is_popular = ( 1 === $price_id );
                                        $checkout_url = edd_get_checkout_uri( array( 'download' => $product_id, 'price_id' => $price_id ) );
                                        $item_features = isset( $features_by_index[ $price_id ] ) ? $features_by_index[ $price_id ] : array();
                                        ?>
                                        <div class="edd-price-card <?php echo $is_popular ? 'popular' : ''; ?>">
                                            <?php if ( $is_popular ) : ?>
                                                <div class="edd-popular-badge">Most Popular</div>
                                            <?php endif; ?>
                                            <div class="edd-card-header">
                                                <div class="edd-variation-title"><?php echo esc_html( $price_data['name'] ); ?></div>
                                                <div class="edd-variation-price"><?php echo edd_currency_filter( edd_format_amount( $price_data['amount'] ) ); ?></div>
                                            </div>
                                            <ul class="edd-feature-list">
                                                <?php foreach ( $item_features as $feature_item ) : ?>
                                                    <li><?php echo esc_html( $feature_item ); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <a href="<?php echo esc_url( $checkout_url ); ?>" class="edd-checkout-btn">Get Started</a>
                                        </div>
                                    <?php
                                    endforeach;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Lifetime Plan View Pane -->
                    <?php if ( ! empty( $lifetime_prices ) ) : ?>
                        <div id="pane-lifetime-<?php echo $product_id; ?>" class="edd-view-pane">
                            <div class="edd-pricing-grid">
                                <?php
                                    foreach ( $lifetime_prices as $price_id => $price_data ) :
                                        $is_popular = ( 4 === $price_id );
                                        $checkout_url = edd_get_checkout_uri( array( 'download' => $product_id, 'price_id' => $price_id ) );
                                        $item_features = isset( $features_by_index[ $price_id ] ) ? $features_by_index[ $price_id ] : array();
                                        ?>
                                        <div class="edd-price-card <?php echo $is_popular ? 'popular' : ''; ?>">
                                            <?php if ( $is_popular ) : ?>
                                                <div class="edd-popular-badge">Best Value</div>
                                            <?php endif; ?>
                                            <div class="edd-card-header">
                                                <div class="edd-variation-title"><?php echo esc_html( $price_data['name'] ); ?></div>
                                                <div class="edd-variation-price"><?php echo edd_currency_filter( edd_format_amount( $price_data['amount'] ) ); ?></div>
                                            </div>
                                            <ul class="edd-feature-list">
                                                <?php foreach ( $item_features as $feature_item ) : ?>
                                                    <li><?php echo esc_html( $feature_item ); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <a href="<?php echo esc_url( $checkout_url ); ?>" class="edd-checkout-btn">Get Lifetime Access</a>
                                        </div>
                                    <?php
                                    endforeach;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <script>
                    function togglePricingView(productId) {
                        var toggleInput = document.getElementById('pricing-toggle-' + productId);
                        var annualPane = document.getElementById('pane-annual-' + productId);
                        var lifetimePane = document.getElementById('pane-lifetime-' + productId);
                        var annualLabel = document.getElementById('label-annual-' + productId);
                        var lifetimeLabel = document.getElementById('label-lifetime-' + productId);
                        if (!toggleInput) return;
                        if (toggleInput.checked) {
                            if (annualPane) annualPane.classList.remove('active');
                            if (lifetimePane) lifetimePane.classList.add('active');
                            if (annualLabel) annualLabel.classList.remove('active');
                            if (lifetimeLabel) lifetimeLabel.classList.add('active');
                        } else {
                            if (lifetimePane) lifetimePane.classList.remove('active');
                            if (annualPane) annualPane.classList.add('active');
                            if (lifetimeLabel) lifetimeLabel.classList.remove('active');
                            if (annualLabel) annualLabel.classList.add('active');
                        }
                    }
                </script>
                <?php
                return ob_get_clean();
            }

            public function why_choose_us(): false|string {
                ob_start();
                ?>
                <section class="abp-wcu-section" aria-labelledby="abp-wcu-title">
                    <div class="abp-wcu-container">
                        <div class="abp-wcu-header">
                            <div class="abp-wcu-label">
                                <span class="abp-wcu-label-line"></span>
                                <span>WHY ABP-TEAM</span>
                            </div>
                            <h2 id="abp-wcu-title">Powerful WordPress Plugins Built for Booking &amp; Business Management</h2>
                            <p>ABP-TEAM creates practical WordPress plugins that help businesses manage rentals, transport bookings, reservations, ticketing, availability, and related operations from their WordPress websites.</p>
                        </div>
                        <div class="abp-wcu-grid">
                            <article class="abp-wcu-card">
                                <div class="abp-wcu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 10.5L12 3L21 10.5V21H3V10.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="M8 21V13H16V21" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </div>
                                <div class="abp-wcu-card-content">
                                    <span class="abp-wcu-number">01</span>
                                    <h3>Rental Management</h3>
                                    <p>Manage rental properties, equipment, pricing, availability, deposits, bookings, guests, and location-based inventory with a flexible WordPress rental solution.</p>
                                </div>
                            </article>
                            <article class="abp-wcu-card">
                                <div class="abp-wcu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 17L7 8H17L19 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M3 17H21V20H3V17Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <circle cx="7" cy="17" r="1.5" fill="currentColor"/>
                                        <circle cx="17" cy="17" r="1.5" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="abp-wcu-card-content">
                                    <span class="abp-wcu-number">02</span>
                                    <h3>Transport Booking</h3>
                                    <p>Build online passenger booking systems with routes, seats, ticket types, pickup and drop-off points, schedules, availability, and reservation management.</p>
                                </div>
                            </article>
                            <article class="abp-wcu-card">
                                <div class="abp-wcu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M7 2V6M17 2V6M3 9H21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M8 13H11M13 13H16M8 17H11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="abp-wcu-card-content">
                                    <span class="abp-wcu-number">03</span>
                                    <h3>Flexible Booking</h3>
                                    <p>Create booking workflows around your business with configurable pricing, rental durations, booking rules, schedules, availability controls, and reservation options.</p>
                                </div>
                            </article>
                            <article class="abp-wcu-card">
                                <div class="abp-wcu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M7 16L10 13L12.5 15.5L17 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16 10.5H17V11.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="abp-wcu-card-content">
                                    <span class="abp-wcu-number">04</span>
                                    <h3>Business-Focused Software</h3>
                                    <p>Our WordPress plugins are designed around practical business workflows, helping you manage bookings, customers, inventory, schedules, and operations more efficiently.</p>
                                </div>
                            </article>
                        </div>
                        <div class="abp-wcu-bottom">
                            <div class="abp-wcu-bottom-item">
                                <span class="abp-wcu-check">✓</span>
                                <span>Built for WordPress</span>
                            </div>
                            <div class="abp-wcu-bottom-item">
                                <span class="abp-wcu-check">✓</span>
                                <span>Booking &amp; Reservation Focused</span>
                            </div>
                            <div class="abp-wcu-bottom-item">
                                <span class="abp-wcu-check">✓</span>
                                <span>Practical Business Solutions</span>
                            </div>
                        </div>
                    </div>
                </section>
                <?php
                return ob_get_clean();
            }

            public function about_section(): false|string {
                ob_start();
                ?>
                <section class="abp-about-section" aria-labelledby="abp-about-title">
                    <div class="abp-about-container">
                        <div class="abp-about-main">
                            <div class="abp-about-content">
                                <div class="abp-about-eyebrow">
                                    <span class="abp-about-eyebrow-line"></span>
                                    <span>ABOUT ABP-TEAM</span>
                                </div>
                                <h2 id="abp-about-title">WordPress Software Built for Real Business Workflows</h2>
                                <p class="abp-about-intro">
                                    ABP-TEAM develops practical WordPress plugins that help businesses manage bookings, rentals, transportation, reservations, ticketing, availability, and day-to-day operations more efficiently.
                                </p>
                                <div class="abp-about-features">
                                    <div class="abp-about-feature">
                                        <div class="abp-about-feature-icon">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M4 20V10L12 4L20 10V20H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                                <path d="M9 20V14H15V20" stroke="currentColor" stroke-width="1.7"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3>Rental Management</h3>
                                            <p>Manage rental products, pricing, availability, bookings, deposits, guests, and inventory.</p>
                                        </div>
                                    </div>
                                    <div class="abp-about-feature">
                                        <div class="abp-about-feature-icon">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M4 16L6 8H18L20 16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M3 16H21V20H3V16Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                                <circle cx="7" cy="16" r="1.3" fill="currentColor"/>
                                                <circle cx="17" cy="16" r="1.3" fill="currentColor"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3>Transport Booking</h3>
                                            <p>Create passenger booking systems with routes, seats, tickets, schedules, and pickup points.</p>
                                        </div>
                                    </div>
                                    <div class="abp-about-feature">
                                        <div class="abp-about-feature-icon">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.7"/>
                                                <path d="M7 2V6M17 2V6M3 9H21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                <path d="M8 13H11M13 13H16M8 17H11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3>Booking &amp; Availability</h3>
                                            <p>Build flexible booking workflows with configurable rules, schedules, pricing, and availability.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="abp-about-actions">
                                    <a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="abp-about-button">
                                        <span>Learn More About ABP-TEAM</span>
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12H19M13 6L19 12L13 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <div class="abp-about-visual">
                                <div class="abp-about-orbit abp-about-orbit-one"></div>
                                <div class="abp-about-orbit abp-about-orbit-two"></div>
                                <div class="abp-about-product-card">
                                    <div class="abp-about-product-top">
                                        <div>
                                            <span class="abp-about-product-label">ABP-TEAM</span>
                                            <h3>Booking Software</h3>
                                        </div>
                                        <div class="abp-about-status">
                                            <span></span>
                                            Ready
                                        </div>
                                    </div>
                                    <div class="abp-about-product-list">
                                        <div class="abp-about-product-item">
                                            <div class="abp-about-product-icon">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M4 20V10L12 4L20 10V20H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                                    <path d="M9 20V14H15V20" stroke="currentColor" stroke-width="1.7"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <strong>Rental Management</strong>
                                                <span>Properties &amp; equipment</span>
                                            </div>
                                            <b>01</b>
                                        </div>
                                        <div class="abp-about-product-item">
                                            <div class="abp-about-product-icon">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M4 16L6 8H18L20 16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                    <path d="M3 16H21V20H3V16Z" stroke="currentColor" stroke-width="1.7"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <strong>Transport Booking</strong>
                                                <span>Routes &amp; passenger booking</span>
                                            </div>
                                            <b>02</b>
                                        </div>
                                        <div class="abp-about-product-item">
                                            <div class="abp-about-product-icon">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.7"/>
                                                    <path d="M7 2V6M17 2V6M3 9H21" stroke="currentColor" stroke-width="1.7"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <strong>Booking Management</strong>
                                                <span>Schedules &amp; availability</span>
                                            </div>
                                            <b>03</b>
                                        </div>
                                        <div class="abp-about-product-item">
                                            <div class="abp-about-product-icon">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 19V10M12 19V5M19 19V8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <strong>Business Tools</strong>
                                                <span>Flexible WordPress solutions</span>
                                            </div>
                                            <b>04</b>
                                        </div>
                                    </div>
                                    <div class="abp-about-product-footer">
                                        <span>Built for WordPress</span>
                                        <span>•</span>
                                        <span>Business-focused</span>
                                        <span>•</span>
                                        <span>Flexible</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="abp-about-trust">
                            <div class="abp-about-trust-item">
                                <strong>WordPress</strong>
                                <span>Native plugin solutions</span>
                            </div>
                            <div class="abp-about-trust-divider"></div>
                            <div class="abp-about-trust-item">
                                <strong>Booking</strong>
                                <span>Rental &amp; reservation focused</span>
                            </div>
                            <div class="abp-about-trust-divider"></div>
                            <div class="abp-about-trust-item">
                                <strong>Flexible</strong>
                                <span>Designed for different workflows</span>
                            </div>
                        </div>
                    </div>
                </section>
                <?php
                return ob_get_clean();
            }

            public function how_we_work(): false|string {
                ob_start();
                ?>
                <section class="abp-how-section" aria-labelledby="abp-how-title">
                    <div class="abp-how-container">
                        <div class="abp-how-header">
                            <div class="abp-how-eyebrow">
                                <span class="abp-how-line"></span>
                                <span>HOW WE WORK</span>
                            </div>
                            <h2 id="abp-how-title">How We Build &amp; Deliver WordPress Plugins</h2>
                            <p>
                                From planning and development to testing and ongoing updates, we follow a practical process to build reliable WordPress software for real business workflows.
                            </p>
                        </div>
                        <div class="abp-how-steps">
                            <div class="abp-how-step">
                                <div class="abp-how-step-top">
                                    <span class="abp-how-number">01</span>
                                    <div class="abp-how-icon">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 5.5C4 4.672 4.672 4 5.5 4H18.5C19.328 4 20 4.672 20 5.5V14.5C20 15.328 19.328 16 18.5 16H12L8 20V16H5.5C4.672 16 4 15.328 4 14.5V5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                            <path d="M8 8H16M8 11H13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="abp-how-step-body">
                                    <span class="abp-how-step-label">PLANNING</span>
                                    <h3>Understand the Business Workflow</h3>
                                    <p>
                                        We identify the real-world workflow a plugin needs to support, including bookings, rentals, reservations, availability, routes, schedules, customers, and other operational requirements.
                                    </p>
                                </div>
                                <div class="abp-how-step-footer">
                                    <span>01</span>
                                    <span>Planning &amp; Requirements</span>
                                </div>
                            </div>
                            <div class="abp-how-step">
                                <div class="abp-how-step-top">
                                    <span class="abp-how-number">02</span>
                                    <div class="abp-how-icon">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 4L4 8L8 12M16 4L20 8L16 12M14 3L10 13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M5 17H19M7 20H17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="abp-how-step-body">
                                    <span class="abp-how-step-label">DEVELOPMENT</span>
                                    <h3>Design &amp; Develop the Plugin</h3>
                                    <p>
                                        We design practical interfaces and develop features using structured WordPress code. Our plugins are built to be configurable, maintainable, and easy to use within WordPress.
                                    </p>
                                </div>
                                <div class="abp-how-step-footer">
                                    <span>02</span>
                                    <span>Design &amp; Development</span>
                                </div>
                            </div>
                            <div class="abp-how-step">
                                <div class="abp-how-step-top">
                                    <span class="abp-how-number">03</span>
                                    <div class="abp-how-icon">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 12L9 17L20 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M4 5V8M20 16V19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="abp-how-step-body">
                                    <span class="abp-how-step-label">QUALITY</span>
                                    <h3>Test &amp; Prepare for Release</h3>
                                    <p>
                                        Before release, we test plugin functionality, booking workflows, configuration options, compatibility, and user-facing features to help ensure a stable and reliable product.
                                    </p>
                                </div>
                                <div class="abp-how-step-footer">
                                    <span>03</span>
                                    <span>Testing &amp; Quality</span>
                                </div>
                            </div>
                            <div class="abp-how-step">
                                <div class="abp-how-step-top">
                                    <span class="abp-how-number">04</span>
                                    <div class="abp-how-icon">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 3V15M12 15L8 11M12 15L16 11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M5 18V20H19V18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="abp-how-step-body">
                                    <span class="abp-how-step-label">DELIVERY &amp; UPDATES</span>
                                    <h3>Deliver, Support &amp; Improve</h3>
                                    <p>
                                        We deliver the plugin as a ready-to-use WordPress product and continue improving it through updates, bug fixes, compatibility improvements, documentation, and customer support.
                                    </p>
                                </div>
                                <div class="abp-how-step-footer">
                                    <span>04</span>
                                    <span>Delivery &amp; Ongoing Updates</span>
                                </div>
                            </div>
                        </div>
                        <div class="abp-how-bottom">
                            <div class="abp-how-bottom-text">
                                <strong>Built around practical WordPress workflows.</strong>
                                <span>Designed to be useful, configurable, and reliable for businesses.</span>
                            </div>
                            <div class="abp-how-bottom-points">
						<span>
							<i>✓</i>
							WordPress Focused
						</span>
                                <span>
							<i>✓</i>
							Regular Updates
						</span>
                                <span>
							<i>✓</i>
							Customer Support
						</span>
                            </div>
                        </div>
                    </div>
                </section>
                <?php
                return ob_get_clean();
            }
        }
        new ABP_Shortcodes();
    }
    function edd_sl_get_license_limit( $license ) {
        if ( is_array( $license ) ) {
            $license_obj = (object) $license;
        } elseif ( is_object( $license ) ) {
            $license_obj = $license;
        } else {
            return 0;
        }
        $stored_limit = isset( $license_obj->activation_limit ) ? (int) $license_obj->activation_limit : 0;
        if ( $stored_limit > 0 ) {
            return $stored_limit;
        }
        $product_id = isset( $license_obj->product_id ) ? (int) $license_obj->product_id : 0;
        if ( ! $product_id ) {
            return 0;
        }
        $price_id = null;
        if ( isset( $license_obj->price_id ) && is_numeric( $license_obj->price_id ) ) {
            $price_id = (int) $license_obj->price_id;
        }
        if ( function_exists( 'edd_sl_get_product_license_config' ) ) {
            $config = edd_sl_get_product_license_config( $product_id, $price_id );

            return isset( $config['limit'] ) ? (int) $config['limit'] : 0;
        }

        return 0;
    }