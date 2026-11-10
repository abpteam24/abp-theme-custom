<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    $page_id = get_the_ID();
?>
    <div class="abp_area">
        <div class="abp_container">
            <div class="abp_home_title">
                <span class="abp-home-eyebrow">ABP-TEAM • WORDPRESS SOFTWARE</span>
                <h1 class="abp_text_center">Build Smarter. Grow Faster with Powerful WordPress Software</h1>
                <p class="abp_text_center abp-home-lede _fs_h5">Downloadable WordPress plugins for products, orders, inventory, pricing, and scheduling — installed and managed on your own site.</p>
                <p class="abp_text_center abp-home-subtext _fs_h6">ABP-TEAM builds practical WordPress software for the operational work that runs behind a business: what you sell, how you price it, when it is available, and how your team keeps it moving.</p>
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
                    <div class="abp-home-media">
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
            <div class="abp_home_product">
                <div class="abp-home-product-head">
                    <span class="abp-home-section-label">OUR SOFTWARE</span>
                    <h2 class="abp_text_center abp_home-product-title _fs_h2">Practical Software for Growing Businesses</h2>
                    <p class="abp_text_center abp">Each plugin focuses on one part of your operation, so you can install only what your business needs and configure it around the way you already work.</p>
                </div>
                <div class="price_content _grid_500 abp-home-product-grid">
                    <?php
                        if ( class_exists( 'Easy_Digital_Downloads' ) && function_exists( 'edd_get_customer_by' ) ) {
                            $all_product_ids = ABP_Function::get_post_id();
                            if ( ! empty( $all_product_ids ) ) {
                                foreach ( $all_product_ids as $product_id ) {
                                    $prices        = edd_get_variable_prices( $product_id );
                                    $product_infos = ABP_Static::product( $product_id );
                                    if ( ! empty( $product_infos ) ) {
                                        $pro_feature_infos = $product_infos['feature_pro'] ?? [];
                                        $home_features     = array_slice( $pro_feature_infos, 0, 6 );
                                        $more_features     = max( 0, count( $pro_feature_infos ) - count( $home_features ) );
                                        $title             = get_the_title( $product_id );
                                        $variation_infos   = $product_infos['variation'] ?? [];
                                        $variation_info    = $variation_infos[1] ?? [];
                                        $price_data        = $prices[1] ?? [];
                                        $price_amount      = edd_currency_filter( edd_format_amount( $price_data['amount'] ?? 0 ) );
                                        $logo_html         = '';
                                        if ( class_exists( 'ABP_Product_Logo' ) ) {
                                            $logo_html = ABP_Product_Logo::logo_html(
                                                $product_id,
                                                'medium',
                                                array(
                                                    'class'    => 'abp-home-card-logo',
                                                    'alt'      => $title,
                                                    'loading'  => 'lazy',
                                                    'decoding' => 'async',
                                                )
                                            );
                                        }
                                        ?>
                                        <div class="price_card abp-home-card">
                                            <div class="abp-home-card-media">
                                                <?php if ( ! empty( $logo_html ) ) { ?>
                                                    <?php echo $logo_html; ?>
                                                <?php } else { ?>
                                                    <span class="abp-home-card-fallback"><i class="fa-solid fa-cube"></i></span>
                                                <?php } ?>
                                            </div>
                                            <div class="abp-home-card-body">
                                                <span class="abp-home-card-kicker _fs_small">WooCommerce Plugin</span>
                                                <h3 class="abp_color_green_pale _fs_h5"><?php echo esc_html( $title ); ?></h3>
                                                <p class="abp abp-home-card-des"><?php echo esc_html( $product_infos['des'] ?? '' ); ?></p>
                                            </div>
                                            <?php if ( ! empty( $pro_feature_infos ) ) { ?>
                                                <div class="abp-home-card-features">
                                                    <p class="abp_color_active _fs_small abp-pro-label">PRO Features</p>
                                                    <ul class="abp_list abp-home-card-list" id="abp-home-features-<?php echo esc_attr( $product_id ); ?>" data-abp-home-features>
                                                        <?php foreach ( $pro_feature_infos as $feature_index => $feature ) { ?>
                                                            <li<?php echo $feature_index >= 6 ? ' class="abp-home-card-extra" hidden' : ''; ?>><span class="<?php echo esc_attr( $feature['icon'] ?? '' ); ?>"></span><?php echo esc_html( $feature['label'] ?? '' ); ?></li>
                                                        <?php } ?>
                                                    </ul>
                                                    <?php if ( $more_features > 0 ) { ?>
                                                        <button type="button" class="abp-home-card-more _fs_small" data-abp-home-more data-less-text="Show fewer features" aria-expanded="false" aria-controls="abp-home-features-<?php echo esc_attr( $product_id ); ?>">
                                                            <span class="abp-home-card-more-text">+<?php echo esc_html( $more_features ); ?> more PRO features included</span>
                                                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                                        </button>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                            <div class="abp-home-card-footer">
                                                <div class="abp-home-card-price">
                                                    <span class="abp-home-card-price-label _fs_small">Starting at</span>
                                                    <span class="abp_price_amount _fs_h4"><?php echo esc_html( $price_amount ); ?></span>
                                                    <sub class="_fs_small">/ <?php echo esc_html( $variation_info['price_sub'] ?? '' ); ?></sub>
                                                </div>
                                                <div class="abp-home-card-actions">
                                                    <a href="<?php echo esc_url( get_the_permalink( $product_id ) ); ?>" class="_btn_active">View Product <span>|</span><i class="fa-solid fa-arrow-right-long"></i></a>
                                                    <a href="<?php echo esc_url( ABP_Function::build_edd_url( $product_id, 1 ) ); ?>" class="_btn_purple"><i class="fa-solid fa-cart-plus"></i><?php echo esc_html( $variation_info['btn_text'] ?? '' ); ?> (<?php echo esc_html( $price_amount ); ?>+VAT)</a>
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
            <div class="abp_home_about">
                <div class="abp-about-grid">
                    <div class="abp-about-copy">
                        <div class="abp-about-eyebrow">
                            <span class="abp-about-eyebrow-line"></span>
                            <span>ABOUT ABP-TEAM</span>
                        </div>
                        <h2 class="abp-about-title _fs_h2">WordPress Software Built for Real Business Workflows</h2>
                        <p class="abp abp-about-lede">ABP-TEAM develops practical WordPress plugins that help businesses manage their own products, customers, orders, inventory, pricing, schedules, availability, and day-to-day operational workflows more efficiently.</p>
                        <div class="abp-about-features">
                            <div class="abp-about-feature">
                                <div class="abp-about-feature-icon">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 20V10L12 4L20 10V20H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                        <path d="M9 20V14H15V20" stroke="currentColor" stroke-width="1.7"/>
                                    </svg>
                                </div>
                                <div class="abp-about-feature-body">
                                    <h3 class="abp-about-feature-title _fs_h6">Product Management</h3>
                                    <p class="abp">Manage products, pricing, inventory, availability, customer information, and configurable business settings.</p>
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
                                <div class="abp-about-feature-body">
                                    <h3 class="abp-about-feature-title _fs_h6">Workflow Management</h3>
                                    <p class="abp">Configure practical WordPress workflows around the specific operational needs of your business.</p>
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
                                <div class="abp-about-feature-body">
                                    <h3 class="abp-about-feature-title _fs_h6">Scheduling &amp; Availability</h3>
                                    <p class="abp">Create configurable schedules, operating periods, availability rules, and other time-based business settings.</p>
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
                        <div class="abp-about-orbit abp-about-orbit-one" aria-hidden="true"></div>
                        <div class="abp-about-orbit abp-about-orbit-two" aria-hidden="true"></div>
                        <div class="abp-about-product-card">
                            <div class="abp-about-product-top">
                                <div>
                                    <span class="abp-about-product-label">ABP-TEAM</span>
                                    <p class="abp-about-product-heading _fs_h4">WordPress Software</p>
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
                        <span class="abp-about-trust-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 2L3 7V12C3 17 7 21 12 22C17 21 21 17 21 12V7L12 2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div class="abp-about-trust-body">
                            <strong>WordPress</strong>
                            <span>Native plugin software</span>
                        </div>
                    </div>
                    <div class="abp-about-trust-item">
                        <span class="abp-about-trust-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M4 20V10L12 4L20 10V20H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 20V14H15V20" stroke="currentColor" stroke-width="1.7"/></svg>
                        </span>
                        <div class="abp-about-trust-body">
                            <strong>Business</strong>
                            <span>Practical workflow tools</span>
                        </div>
                    </div>
                    <div class="abp-about-trust-item">
                        <span class="abp-about-trust-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M3 12H7L9 6L12 18L15 9L17 12H21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div class="abp-about-trust-body">
                            <strong>Flexible</strong>
                            <span>Built for different workflows</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="abp_home_why">
                <div class="abp-why-head">
                    <div class="abp-why-label">
                        <span class="abp-why-label-line"></span>
                        <span>WHY ABP-TEAM</span>
                    </div>
                    <h2 class="abp-why-title _fs_h2">Powerful WordPress Plugins Built for Business Management</h2>
                    <p class="abp abp-why-lede">Every plugin is designed around a specific operational job, so the settings you see map to decisions you already make — not to abstract features you have to interpret.</p>
                </div>
                <div class="abp-why-grid">
                    <article class="abp-why-card">
                        <div class="abp-why-card-top">
                            <div class="abp-why-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M3 10.5L12 3L21 10.5V21H3V10.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M8 21V13H16V21" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </div>
                            <span class="abp-why-index" aria-hidden="true">01</span>
                        </div>
                        <div class="abp-why-body">
                            <h3 class="abp-why-card-title _fs_h4">Built Around Your Operation</h3>
                            <p class="abp">Your products, pricing rules, availability, and customer records stay in one place instead of being spread across spreadsheets and separate tools.</p>
                        </div>
                    </article>
                    <article class="abp-why-card">
                        <div class="abp-why-card-top">
                            <div class="abp-why-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M5 17L7 8H17L19 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M3 17H21V20H3V17Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <circle cx="7" cy="17" r="1.5" fill="currentColor"/>
                                    <circle cx="17" cy="17" r="1.5" fill="currentColor"/>
                                </svg>
                            </div>
                            <span class="abp-why-index" aria-hidden="true">02</span>
                        </div>
                        <div class="abp-why-body">
                            <h3 class="abp-why-card-title _fs_h4">No Locked-In Platform</h3>
                            <p class="abp">The software runs on your own WordPress site, so the data, accounts, and settings stay under your control.</p>
                        </div>
                    </article>
                    <article class="abp-why-card">
                        <div class="abp-why-card-top">
                            <div class="abp-why-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M7 2V6M17 2V6M3 9H21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M8 13H11M13 13H16M8 17H11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="abp-why-index" aria-hidden="true">03</span>
                        </div>
                        <div class="abp-why-body">
                            <h3 class="abp-why-card-title _fs_h4">Configured by You, Not Us</h3>
                            <p class="abp">Settings map to real business decisions — how you price, when you are available, and what your team is allowed to change — rather than fixed templates.</p>
                        </div>
                    </article>
                    <article class="abp-why-card">
                        <div class="abp-why-card-top">
                            <div class="abp-why-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M7 16L10 13L12.5 15.5L17 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span class="abp-why-index" aria-hidden="true">04</span>
                        </div>
                        <div class="abp-why-body">
                            <h3 class="abp-why-card-title _fs_h4">Updates and Support Included</h3>
                            <p class="abp">Every licence includes product support and updates for its term, covering fixes, compatibility work, and new features.</p>
                        </div>
                    </article>
                </div>
                <div class="abp-why-bottom">
                    <div class="abp-why-bottom-item">
                        <span class="abp-why-check" aria-hidden="true">✓</span>
                        <span>Built for WordPress</span>
                    </div>
                    <div class="abp-why-bottom-item">
                        <span class="abp-why-check" aria-hidden="true">✓</span>
                        <span>Business-Focused Software</span>
                    </div>
                    <div class="abp-why-bottom-item">
                        <span class="abp-why-check" aria-hidden="true">✓</span>
                        <span>Regular Updates</span>
                    </div>
                </div>
            </div>
            <div class="abp_home_how">
                <div class="abp-how-head">
                    <div class="abp-how-head-copy">
                        <div class="abp-how-label">
                            <span class="abp-how-label-line"></span>
                            <span>HOW WE WORK</span>
                        </div>
                        <h2 class="abp-how-title _fs_h2">How We Build &amp; Deliver WordPress Plugins</h2>
                    </div>
                    <p class="abp abp-how-lede _fs_h6">From planning and development to testing and ongoing updates, we follow a practical process to build reliable WordPress software for real business workflows.</p>
                </div>
                <div class="abp-how-steps">
                    <article class="abp-how-step">
                        <div class="abp-how-step-top">
                            <span class="abp-how-number" aria-hidden="true">01</span>
                            <div class="abp-how-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 5.5C4 4.672 4.672 4 5.5 4H18.5C19.328 4 20 4.672 20 5.5V14.5C20 15.328 19.328 16 18.5 16H12L8 20V16H5.5C4.672 16 4 15.328 4 14.5V5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    <path d="M8 8H16M8 11H13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="abp-how-step-body">
                            <span class="abp-how-step-label">PLANNING</span>
                            <h3 class="abp-how-step-title _fs_h4">Understand the Business Workflow</h3>
                            <p class="abp">We identify the workflow a plugin needs to support, including products, customers, inventory, pricing, schedules, availability, and other operational requirements.</p>
                        </div>
                        <div class="abp-how-step-footer">
                            <span>Planning &amp; Requirements</span>
                            <span class="abp-how-step-arrow" aria-hidden="true">→</span>
                        </div>
                    </article>
                    <article class="abp-how-step">
                        <div class="abp-how-step-top">
                            <span class="abp-how-number" aria-hidden="true">02</span>
                            <div class="abp-how-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M8 4L4 8L8 12M16 4L20 8L16 12M14 3L10 13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M5 17H19M7 20H17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="abp-how-step-body">
                            <span class="abp-how-step-label">DEVELOPMENT</span>
                            <h3 class="abp-how-step-title _fs_h4">Design &amp; Develop the Plugin</h3>
                            <p class="abp">We design practical interfaces and develop structured WordPress software that is configurable, maintainable, and easy to use.</p>
                        </div>
                        <div class="abp-how-step-footer">
                            <span>Design &amp; Development</span>
                            <span class="abp-how-step-arrow" aria-hidden="true">→</span>
                        </div>
                    </article>
                    <article class="abp-how-step">
                        <div class="abp-how-step-top">
                            <span class="abp-how-number" aria-hidden="true">03</span>
                            <div class="abp-how-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 12L9 17L20 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="abp-how-step-body">
                            <span class="abp-how-step-label">QUALITY</span>
                            <h3 class="abp-how-step-title _fs_h4">Test &amp; Prepare for Release</h3>
                            <p class="abp">Before release, we test functionality, configuration options, compatibility, performance, and user-facing features to help ensure a stable product.</p>
                        </div>
                        <div class="abp-how-step-footer">
                            <span>Testing &amp; Quality</span>
                            <span class="abp-how-step-arrow" aria-hidden="true">→</span>
                        </div>
                    </article>
                    <article class="abp-how-step">
                        <div class="abp-how-step-top">
                            <span class="abp-how-number" aria-hidden="true">04</span>
                            <div class="abp-how-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3V15M12 15L8 11M12 15L16 11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M5 18V20H19V18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="abp-how-step-body">
                            <span class="abp-how-step-label">DELIVERY &amp; UPDATES</span>
                            <h3 class="abp-how-step-title _fs_h4">Deliver, Support &amp; Improve</h3>
                            <p class="abp">We deliver ready-to-use WordPress software and continue improving it through updates, bug fixes, compatibility improvements, documentation, and customer support.</p>
                        </div>
                        <div class="abp-how-step-footer">
                            <span>Delivery &amp; Ongoing Updates</span>
                            <span class="abp-how-step-arrow" aria-hidden="true">✓</span>
                        </div>
                    </article>
                </div>
                <div class="abp-how-bottom">
                    <div class="abp-how-bottom-text">
                        <strong>Built around practical WordPress workflows.</strong>
                        <span>Designed to be useful, configurable, and reliable for businesses.</span>
                    </div>
                    <div class="abp-how-bottom-points">
                        <span><i aria-hidden="true">✓</i> WordPress Focused</span>
                        <span><i aria-hidden="true">✓</i> Regular Updates</span>
                        <span><i aria-hidden="true">✓</i> Customer Support</span>
                    </div>
                </div>
            </div>
            <div class="abp_home_faq">
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
                <div class="abp-faq-layout">
                    <div class="abp-faq-head">
                        <div class="abp-faq-label">
                            <span class="abp-faq-label-line"></span>
                            <span>QUESTIONS</span>
                        </div>
                        <h2 class="abp-faq-title _fs_h2">Answers Before You Buy</h2>
                        <p class="abp abp-faq-lede _fs_h6">Short answers to the questions we get most about licences, requirements, updates, and support.</p>
                        <div class="abp-faq-help">
                            <div class="abp-faq-help-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 5.5C4 4.672 4.672 4 5.5 4H18.5C19.328 4 20 4.672 20 5.5V14.5C20 15.328 19.328 16 18.5 16H12L8 20V16H5.5C4.672 16 4 15.328 4 14.5V5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    <path d="M8 8H16M8 11H13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="abp-faq-help-body">
                                <strong>Still have questions?</strong>
                                <span>Our product support desk can help with setup, licensing, and technical issues.</span>
                                <a class="abp-faq-help-link" href="<?php echo esc_url( home_url( '/support-desk/' ) ); ?>">Visit Support Desk <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="abp-faq-list">
                        <?php $abp_faq_index = 0; ?>
                        <?php foreach ( $abp_home_faqs as $abp_faq ) { ?>
                            <?php $abp_faq_index++; ?>
                            <details class="abp-faq-item"<?php echo 1 === $abp_faq_index ? ' open' : ''; ?>>
                                <summary>
                                    <span class="abp-faq-index" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $abp_faq_index ) ); ?></span>
                                    <h3 class="_fs_h6"><?php echo esc_html( $abp_faq['q'] ); ?></h3>
                                    <span class="abp-faq-marker" aria-hidden="true"></span>
                                </summary>
                                <p class="abp"><?php echo esc_html( $abp_faq['a'] ); ?></p>
                            </details>
                        <?php } ?>
                    </div>
                </div>
                <script type="application/ld+json"><?php echo wp_json_encode( $abp_faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
            </div>
            <div class="abp_home_trust">
                <div class="abp-trust-head">
                    <div class="abp-trust-label">
                        <span class="abp-trust-label-line"></span>
                        <span>WHY CHOOSE ABP-TEAM</span>
                    </div>
                    <h2 class="abp-trust-title _fs_h2">Software You Can Build Your Workflow Around</h2>
                    <p class="abp abp-trust-lede _fs_h6">Our products are designed as downloadable WordPress software that businesses can install, configure, and manage on their own websites.</p>
                </div>
                <div class="abp-trust-grid">
                    <article class="abp-trust-card">
                        <span class="abp-trust-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="8.4" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M6.3 9.4 8.7 15.6 10.9 9.4 13.1 15.6 16.2 9.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div class="abp-trust-body">
                            <h3 class="_fs_h6">WordPress First</h3>
                            <p class="abp">Built specifically for the WordPress ecosystem.</p>
                        </div>
                    </article>
                    <article class="abp-trust-card">
                        <span class="abp-trust-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4.5 7H19.5M4.5 12H19.5M4.5 17H19.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <circle cx="9" cy="7" r="2.2" fill="#0F1116" stroke="currentColor" stroke-width="1.6"/>
                                <circle cx="15" cy="12" r="2.2" fill="#0F1116" stroke="currentColor" stroke-width="1.6"/>
                                <circle cx="10" cy="17" r="2.2" fill="#0F1116" stroke="currentColor" stroke-width="1.6"/>
                            </svg>
                        </span>
                        <div class="abp-trust-body">
                            <h3 class="_fs_h6">Flexible Configuration</h3>
                            <p class="abp">Adapt features and settings to different operational workflows.</p>
                        </div>
                    </article>
                    <article class="abp-trust-card">
                        <span class="abp-trust-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M19.2 12A7.2 7.2 0 0 1 7 17.1M4.8 12A7.2 7.2 0 0 1 17 6.9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M17.6 3.6v3.7h-3.7M6.4 20.4v-3.7h3.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div class="abp-trust-body">
                            <h3 class="_fs_h6">Regular Improvements</h3>
                            <p class="abp">Continuous updates, fixes, compatibility improvements, and new features.</p>
                        </div>
                    </article>
                    <article class="abp-trust-card">
                        <span class="abp-trust-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4.6 13v-1a7.4 7.4 0 0 1 14.8 0v1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <rect x="3.2" y="12.2" width="3.8" height="6.2" rx="1.9" stroke="currentColor" stroke-width="1.6"/>
                                <rect x="17" y="12.2" width="3.8" height="6.2" rx="1.9" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M18.9 18.2v.6a2.4 2.4 0 0 1-2.4 2.4H13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <div class="abp-trust-body">
                            <h3 class="_fs_h6">Dedicated Support</h3>
                            <p class="abp">Product-focused assistance when you need help with our software.</p>
                        </div>
                    </article>
                </div>
                <div class="abp-home-final-cta">
                    <div class="abp-cta-copy">
                        <span class="abp-cta-eyebrow">ABP-TEAM</span>
                        <h3 class="abp-cta-title _fs_h4">Explore Our WordPress Software</h3>
                        <p class="abp abp-cta-text _fs_h6">Discover practical plugins built to help businesses manage their workflows directly from WordPress.</p>
                    </div>
                    <div class="abp-cta-action">
                        <a class="abp-cta-btn" href="<?php echo esc_url( home_url( '/products/' ) ); ?>">View All Products <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i></a>
                        <span class="abp-cta-note">One-time and yearly licence options</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php