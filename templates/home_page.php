<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    $page_id = get_the_ID();
?>
    <div class="abp_area">
        <div class="abp_container">
            <div class="abp_row _section abp_home_title">
                <span class="abp-home-eyebrow">ABP-TEAM • WORDPRESS SOFTWARE</span>
                <h1 class="abp_text_center">Build Smarter. Grow Faster with Powerful WordPress Software</h1>
                <p class="abp_text_center abp-home-lede">Downloadable WordPress plugins for products, orders, inventory, pricing, and scheduling — installed and managed on your own site.</p>
                <p class="abp_text_center abp-home-subtext">ABP-TEAM builds practical WordPress software for the operational work that runs behind a business: what you sell, how you price it, when it is available, and how your team keeps it moving.</p>
                <div class="abp-home-actions">
                    <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>" class="_btn_active">Explore Our Products <span>|</span><i class="fa-solid fa-arrow-right-long"></i></a>
                    <a href="<?php echo esc_url( home_url( '/support-desk/' ) ); ?>" class="_btn_light_active">Get Product Support <span>|</span><i class="fa-solid fa-arrow-right-long"></i></a>
                </div>
                <ul class="abp-home-badges" aria-label="What every ABP-TEAM plugin includes">
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Built for WordPress</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Instant download</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Regular updates</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Product support</li>
                </ul>
                <?php if ( has_post_thumbnail( $page_id ) ) { ?>
                    <div>
                        <?php
                            echo get_the_post_thumbnail(
                                $page_id,
                                'full',
                                array(
                                    'class'         => '_img_control',
                                    'loading'       => 'eager',
                                    'fetchpriority' => 'high',
                                    'decoding'      => 'async',
                                )
                            );
                        ?>
                    </div>
                <?php } ?>
            </div>
            <div class="abp_row _section abp_home_product">
                <span class="abp-home-section-label">OUR SOFTWARE</span>
                <h2 class="abp_text_center _color_5">Practical Software for Growing Businesses</h2>
                <p class="abp_text_center">Each plugin focuses on one part of your operation, so you can install only what your business needs and configure it around the way you already work.</p>
                <div class="price_content _grid_500">
                    <?php
                        if ( class_exists( 'Easy_Digital_Downloads' ) && function_exists( 'edd_get_customer_by' ) ) {
                            $all_product_ids = ABP_Function::get_post_id();
                            if ( ! empty( $all_product_ids ) ) {
                                foreach ( $all_product_ids as $product_id ) {
                                    $prices        = edd_get_variable_prices( $product_id );
                                    $product_infos = ABP_Static::product( $product_id );
                                    if ( ! empty( $product_infos ) ) {
                                        $pro_feature_infos = $product_infos['feature_pro'] ?? [];
                                        $title             = get_the_title( $product_id );
                                        $variation_infos   = $product_infos['variation'] ?? [];
                                        $variation_info    = $variation_infos[1] ?? [];
                                        $price_data        = $prices[1] ?? [];
                                        ?>
                                        <div class="price_card">
                                            <div>
                                                <div class="_all_center_fd_column_gap_xs">
                                                    <div>
                                                        <?php
                                                            if ( class_exists( 'ABP_Product_Logo' ) ) {
                                                                echo ABP_Product_Logo::logo_html(
                                                                    $product_id,
                                                                    'medium',
                                                                    array(
                                                                        'class'    => '_img_control _h_200',
                                                                        'alt'      => $title,
                                                                        'loading'  => 'lazy',
                                                                        'decoding' => 'async',
                                                                    )
                                                                );
                                                            }
                                                        ?>
                                                    </div>
                                                    <h3 class="abp_color_green_pale"><?php echo esc_html( $title ); ?></h3>
                                                    <p class="abp_padding_xs_bg_light_border"><?php echo esc_html( $product_infos['des'] ?? '' ); ?></p>
                                                </div>
                                                <?php if ( ! empty( $pro_feature_infos ) ) { ?>
                                                    <div class="_divider_xxs"></div>
                                                    <p class="abp_color_active _fs_h5 abp-pro-label">PRO Features</p>
                                                    <ul class="abp_list">
                                                        <?php foreach ( $pro_feature_infos as $feature ) { ?>
                                                            <li><span class="<?php echo esc_attr( $feature['icon'] ?? '' ); ?>"></span><?php echo esc_html( $feature['label'] ?? '' ); ?></li>
                                                        <?php } ?>
                                                    </ul>
                                                <?php } ?>
                                            </div>
                                            <div>
                                                <div class="_divider_xs"></div>
                                                <div class="_f_wrap_gap_xs_fj_between">
                                                    <a href="<?php echo esc_url( get_the_permalink( $product_id ) ); ?>" class="_btn_active">View Product <span>|</span><i class="fa-solid fa-arrow-right-long"></i></a>
                                                    <a href="<?php echo esc_url( ABP_Function::build_edd_url( $product_id, 1 ) ); ?>" class="_btn_purple"><i class="fa-solid fa-cart-plus"></i><?php echo esc_html( $variation_info['btn_text'] ?? '' ); ?>(<?php echo esc_html( edd_currency_filter( edd_format_amount( $price_data['amount'] ) ) ); ?>+VAT)</a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                        }
                    ?>
                </div>
            </div>
            <div class="abp_row _section abp_home_about">
                <div class="_grid_500">
                    <div class="_fd_column_gap">
                        <div class="abp-about-eyebrow">
                            <span class="abp-about-eyebrow-line"></span>
                            <span>ABOUT ABP-TEAM</span>
                        </div>
                        <h2 class="abp_color_purple">WordPress Software Built for Real Business Workflows</h2>
                        <p class="abp">ABP-TEAM develops practical WordPress plugins that help businesses manage their own products, customers, orders, inventory, pricing, schedules, availability, and day-to-day operational workflows more efficiently.</p>
                        <div class="abp-about-features">
                            <div class="abp-about-feature">
                                <div class="abp-about-feature-icon">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 20V10L12 4L20 10V20H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                        <path d="M9 20V14H15V20" stroke="currentColor" stroke-width="1.7"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3>Product Management</h3>
                                    <p>Manage products, pricing, inventory, availability, customer information, and configurable business settings.</p>
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
                                    <h3>Workflow Management</h3>
                                    <p>Configure practical WordPress workflows around the specific operational needs of your business.</p>
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
                                    <h3>Scheduling &amp; Availability</h3>
                                    <p>Create configurable schedules, operating periods, availability rules, and other time-based business settings.</p>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="_btn_theme">
                            <span>Learn More About ABP-TEAM</span>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12H19M13 6L19 12L13 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                    <div class="abp-about-visual">
                        <div class="abp-about-orbit abp-about-orbit-one"></div>
                        <div class="abp-about-orbit abp-about-orbit-two"></div>
                        <div class="abp-about-product-card">
                            <div class="abp-about-product-top">
                                <div>
                                    <span class="abp-about-product-label">ABP-TEAM</span>
                                    <p class="abp-about-product-heading">WordPress Software</p>
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
                                        <strong>Product Management</strong>
                                        <span>Products &amp; inventory</span>
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
                                        <strong>Workflow Management</strong>
                                        <span>Configurable business processes</span>
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
                                        <strong>Scheduling Tools</strong>
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
                                        <span>Flexible WordPress tools</span>
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
                        <span>Native plugin software</span>
                    </div>
                    <div class="abp-about-trust-divider"></div>
                    <div class="abp-about-trust-item">
                        <strong>Business</strong>
                        <span>Practical workflow tools</span>
                    </div>
                    <div class="abp-about-trust-divider"></div>
                    <div class="abp-about-trust-item">
                        <strong>Flexible</strong>
                        <span>Built for different workflows</span>
                    </div>
                </div>
            </div>
            <div class="abp_row _section abp_home_why">
                <div class="_fd_column_gap_xs">
                    <div class="abp-wcu-label">
                        <span class="abp-wcu-label-line"></span>
                        <span>WHY ABP-TEAM</span>
                    </div>
                    <h2 class="abp_color_purple">Powerful WordPress Plugins Built for Business Management</h2>
                    <p class="abp">Every plugin is designed around a specific operational job, so the settings you see map to decisions you already make — not to abstract features you have to interpret.</p>
                </div>
                <div class="abp-wcu-grid">
                    <article class="abp-wcu-card">
                        <div class="abp-wcu-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M3 10.5L12 3L21 10.5V21H3V10.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M8 21V13H16V21" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <div class="why_item">
                            <h3 class="abp_color_white">Built Around Your Operation</h3>
                            <p class="abp">Your products, pricing rules, availability, and customer records stay in one place instead of being spread across spreadsheets and separate tools.</p>
                        </div>
                    </article>
                    <article class="abp-wcu-card">
                        <div class="abp-wcu-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M5 17L7 8H17L19 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 17H21V20H3V17Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <circle cx="7" cy="17" r="1.5" fill="currentColor"/>
                                <circle cx="17" cy="17" r="1.5" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="why_item">
                            <h3 class="abp_color_light">No Locked-In Platform</h3>
                            <p class="abp">The software runs on your own WordPress site, so the data, accounts, and settings stay under your control.</p>
                        </div>
                    </article>
                    <article class="abp-wcu-card">
                        <div class="abp-wcu-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M7 2V6M17 2V6M3 9H21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M8 13H11M13 13H16M8 17H11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="why_item">
                            <h3 class="abp_color_light">Configured by You, Not Us</h3>
                            <p class="abp">Settings map to real business decisions — how you price, when you are available, and what your team is allowed to change — rather than fixed templates.</p>
                        </div>
                    </article>
                    <article class="abp-wcu-card">
                        <div class="abp-wcu-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M7 16L10 13L12.5 15.5L17 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="why_item">
                            <h3 class="abp_color_light">Updates and Support Included</h3>
                            <p class="abp">Every licence includes product support and updates for its term, covering fixes, compatibility work, and new features.</p>
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
                        <span>Business-Focused Software</span>
                    </div>
                    <div class="abp-wcu-bottom-item">
                        <span class="abp-wcu-check">✓</span>
                        <span>Regular Updates</span>
                    </div>
                </div>
            </div>
            <div class="abp_row _section abp_home_how">
                <div class="_fd_column_gap_xs">
                    <div class="abp-wcu-label">
                        <span class="abp-wcu-label-line"></span>
                        <span>HOW WE WORK</span>
                    </div>
                    <h2 class="abp_color_purple">How We Build &amp; Deliver WordPress Plugins</h2>
                    <p class="abp">From planning and development to testing and ongoing updates, we follow a practical process to build reliable WordPress software for real business workflows.</p>
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
                            <p>We identify the workflow a plugin needs to support, including products, customers, inventory, pricing, schedules, availability, and other operational requirements.</p>
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
                            <p>We design practical interfaces and develop structured WordPress software that is configurable, maintainable, and easy to use.</p>
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
                                </svg>
                            </div>
                        </div>
                        <div class="abp-how-step-body">
                            <span class="abp-how-step-label">QUALITY</span>
                            <h3>Test &amp; Prepare for Release</h3>
                            <p>Before release, we test functionality, configuration options, compatibility, performance, and user-facing features to help ensure a stable product.</p>
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
                            <p>We deliver ready-to-use WordPress software and continue improving it through updates, bug fixes, compatibility improvements, documentation, and customer support.</p>
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
                        <span><i>✓</i> WordPress Focused</span>
                        <span><i>✓</i> Regular Updates</span>
                        <span><i>✓</i> Customer Support</span>
                    </div>
                </div>
            </div>
            <div class="abp_row _section abp_home_faq">
                <?php
                    $abp_home_faqs = array(
                        array(
                            'q' => __( 'What do I receive when I buy a plugin?', 'abp-plugin' ),
                            'a' => __( 'You receive the downloadable plugin package for the licence you select, plus product support and updates for that licence period.', 'abp-plugin' ),
                        ),
                        array(
                            'q' => __( 'Do I need WooCommerce to use your plugins?', 'abp-plugin' ),
                            'a' => __( 'ABP RentalForge and ABP Transport Booking run on WooCommerce, which handles payments, taxes, coupons, and orders for those products. Other ABP-TEAM tools are built for WordPress itself.', 'abp-plugin' ),
                        ),
                        array(
                            'q' => __( 'Where is the software installed?', 'abp-plugin' ),
                            'a' => __( 'On your own WordPress site. You install the plugin, configure it in the WordPress admin, and keep full control of the data it manages.', 'abp-plugin' ),
                        ),
                        array(
                            'q' => __( 'Are updates included?', 'abp-plugin' ),
                            'a' => __( 'Updates are released during the licence period and cover bug fixes, compatibility improvements, and new features for the product you purchased.', 'abp-plugin' ),
                        ),
                        array(
                            'q' => __( 'How do I get help with a product?', 'abp-plugin' ),
                            'a' => __( 'Use the product support desk to send us your question. Include your site details and the steps that led to the issue so we can look into it.', 'abp-plugin' ),
                        ),
                    );

                    $abp_faq_schema = array(
                        '@context'   => 'https://schema.org',
                        '@type'      => 'FAQPage',
                        'mainEntity' => array(),
                    );

                    foreach ( $abp_home_faqs as $abp_faq ) {
                        $abp_faq_schema['mainEntity'][] = array(
                            '@type'          => 'Question',
                            'name'           => $abp_faq['q'],
                            'acceptedAnswer' => array(
                                '@type' => 'Answer',
                                'text'  => $abp_faq['a'],
                            ),
                        );
                    }
                ?>
                <span class="abp-home-section-label">QUESTIONS</span>
                <h2 class="abp_color_purple">Answers Before You Buy</h2>
                <p class="abp">Short answers to the questions we get most about licences, requirements, updates, and support.</p>
                <div class="abp-faq-list">
                    <?php $abp_faq_index = 0; ?>
                    <?php foreach ( $abp_home_faqs as $abp_faq ) { ?>
                        <?php $abp_faq_index++; ?>
                        <details class="abp-faq-item"<?php echo 1 === $abp_faq_index ? ' open' : ''; ?>>
                            <summary>
                                <h3><?php echo esc_html( $abp_faq['q'] ); ?></h3>
                                <span class="abp-faq-marker" aria-hidden="true"></span>
                            </summary>
                            <p><?php echo esc_html( $abp_faq['a'] ); ?></p>
                        </details>
                    <?php } ?>
                </div>
                <script type="application/ld+json"><?php echo wp_json_encode( $abp_faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
            </div>
            <div class="abp_row _section abp_home_trust">
                <div class="abp-home-section-label">WHY CHOOSE ABP-TEAM</div>
                <h2 class="abp_color_purple">Software You Can Build Your Workflow Around</h2>
                <p class="abp">Our products are designed as downloadable WordPress software that businesses can install, configure, and manage on their own websites.</p>
                <div class="abp-trust-grid">
                    <div class="abp-trust-card">
                        <strong>WordPress First</strong>
                        <span>Built specifically for the WordPress ecosystem.</span>
                    </div>
                    <div class="abp-trust-card">
                        <strong>Flexible Configuration</strong>
                        <span>Adapt features and settings to different operational workflows.</span>
                    </div>
                    <div class="abp-trust-card">
                        <strong>Regular Improvements</strong>
                        <span>Continuous updates, fixes, compatibility improvements, and new features.</span>
                    </div>
                    <div class="abp-trust-card">
                        <strong>Dedicated Support</strong>
                        <span>Product-focused assistance when you need help with our software.</span>
                    </div>
                </div>
                <div class="abp-home-final-cta">
                    <div>
                        <span>ABP-TEAM</span>
                        <h3>Explore Our WordPress Software</h3>
                        <p>Discover practical plugins built to help businesses manage their workflows directly from WordPress.</p>
                    </div>
                    <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>" class="_btn_theme">View All Products <span>|</span><i class="fa-solid fa-arrow-right-long"></i></a>
                </div>
            </div>
        </div>
    </div>
<?php