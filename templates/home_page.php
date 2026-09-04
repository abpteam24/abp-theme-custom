<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    $page_id = get_the_ID();
?>
<div class="abp_area">
    <div class="abp_container">
        <div class="abp_row _section abp_home_title">
            <h1 class="abp_text_center">Build Smarter. Grow Faster with Powerful WordPress Software</h1>
            <h6 class="abp_text_center">Professional WordPress Plugins for Business Operations</h6>
            <p class="abp_text_center">ABP-TEAM develops professional WordPress software that helps businesses configure and manage their own products, customers, orders, inventory, pricing, schedules, and operational workflows directly from WordPress.</p>
            <h6 class="abp_text_center">We develop and sell downloadable WordPress software for businesses.</h6>
            <a href="<?php echo esc_url( home_url( '/support-desk/' ) ); ?>" target="_blank" rel="noopener" class="_btn_light_active">Need Any Help ? <span>|</span><i class="fa-solid fa-arrow-right-long"></i></a>
            <div><img class="_img_control" src="<?php echo esc_url( ABP_Function::get_image_url( $page_id ) ); ?>" alt="<?php echo esc_attr( get_the_title( $page_id ) ); ?>"/></div>
        </div>
        <div class="abp_row _section abp_home_product">
            <h6 class="abp_text_center _color_active">Practical Software for Growing Businesses</h6>
            <h3 class="abp_text_center _color_5">Business Management Tools for WordPress</h3>
            <p class="abp_text_center">Our software products provide configurable tools that help businesses manage products, customers, orders, inventory, pricing, schedules, availability, and other operational processes directly from their own WordPress websites.</p>
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
                                    $logo              = $product_infos['logo'] ?? '';
                                    $variation_info    = $variation_infos[1] ?? [];
                                    $price_data        = $prices[1] ?? [];
                                    ?>
                                    <div class="price_card">
                                        <div class="_all_center_fd_column_gap_xs">
                                            <div><img class="_img_control _h_200" src="<?php echo esc_url( ABP_Function::get_image_url( '', $logo ) ); ?>" alt="<?php echo esc_attr( $title ); ?>"/></div>
                                            <h3 class="abp_color_green_pale"><?php echo esc_html( $title ); ?></h3>
                                            <p class="abp_padding_xs_bg_light_border"><?php echo esc_html( $product_infos['des'] ?? '' ); ?></p>
                                        </div>
                                        <?php if ( ! empty( $pro_feature_infos ) ) { ?>
                                            <div class="_divider_xxs"></div>
                                            <h5 class="abp_color_active">PRO Feature Lists</h5>
                                            <ul class="abp_list">
                                                <?php foreach ( $pro_feature_infos as $feature ) { ?>
                                                    <li><span class="<?php echo esc_attr( $feature['icon'] ?? '' ); ?>"></span><?php echo esc_html( $feature['label'] ?? '' ); ?></li>
                                                <?php } ?>
                                            </ul>
                                        <?php } ?>
                                        <div class="_divider_xs"></div>
                                        <div class="_f_wrap_gap_xs_fj_between">
                                            <a href="<?php echo esc_url( get_the_permalink( $product_id ) ); ?>" class="_btn_active">Learn More<span>|</span><i class="fa-solid fa-arrow-right-long"></i></a>
                                            <a href="<?php echo esc_url( ABP_Function::build_edd_url( $product_id, 1 ) ); ?>" class="_btn_purple"><i class="fa-solid fa-cart-plus"></i><?php echo esc_html( $variation_info['btn_text'] ?? '' ); ?>(<?php echo esc_html( edd_currency_filter( edd_format_amount( $price_data['amount'] ) ) ); ?>+VAT)</a>
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
                    <h4 class="abp_color_purple">WordPress Software Built for Real Business Workflows</h4>
                    <p class="abp">ABP-TEAM develops practical WordPress plugins that help businesses manage their own products, customers, orders, inventory, pricing, schedules, and day-to-day operational workflows more efficiently.</p>
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
                                <p>Manage products, pricing, availability, inventory, customer information, and configurable business settings.</p>
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
                                <h3>Business Software</h3>
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
                    <strong>Business</strong>
                    <span>Practical workflow tools</span>
                </div>
                <div class="abp-about-trust-divider"></div>
                <div class="abp-about-trust-item">
                    <strong>Flexible</strong>
                    <span>Designed for different workflows</span>
                </div>
            </div>
        </div>
        <div class="abp_row _section abp_home_why">
            <div class="_fd_column_gap_xs">
                <div class="abp-wcu-label">
                    <span class="abp-wcu-label-line"></span>
                    <span>WHY ABP-TEAM</span>
                </div>
                <h4 class="abp_color_purple">Powerful WordPress Plugins Built for Business Management</h4>
                <p class="abp">ABP-TEAM creates practical WordPress plugins that help businesses manage products, customers, inventory, pricing, schedules, availability, and related operational workflows from their own WordPress websites.</p>
            </div>
            <div class="abp-wcu-grid">
                <article class="abp-wcu-card">
                    <div class="abp-wcu-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 10.5L12 3L21 10.5V21H3V10.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M8 21V13H16V21" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>
                    <div class="why_item">
                        <h5 class="abp_color_white">Product Management</h5>
                        <p class="abp">Manage products, pricing, inventory, availability, customer information, and business-specific settings with flexible WordPress tools.</p>
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
                    <div class="why_item">
                        <h5 class="abp_color_light">Workflow Management</h5>
                        <p class="abp">Build configurable workflows around your business processes with practical tools for managing daily operations in WordPress.</p>
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
                    <div class="why_item">
                        <h5 class="abp_color_light">Scheduling &amp; Availability</h5>
                        <p class="abp">Create configurable schedules, operating periods, availability rules, and time-based settings for different business workflows.</p>
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
                    <div class="why_item">
                        <h5 class="abp_color_light">Business-Focused Software</h5>
                        <p class="abp">Our WordPress plugins are designed around practical business workflows, helping you manage products, customers, inventory, schedules, and operations efficiently.</p>
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
                    <span>Practical Business Solutions</span>
                </div>
            </div>
        </div>
        <div class="abp_row _section abp_home_how">
            <div class="_fd_column_gap_xs">
                <div class="abp-wcu-label">
                    <span class="abp-wcu-label-line"></span>
                    <span>HOW WE WORK</span>
                </div>
                <h4 class="abp_color_purple">How We Build &amp; Deliver WordPress Plugins</h4>
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
                        <p>We identify the business workflow a plugin needs to support, including products, customers, inventory, pricing, schedules, availability, and other operational requirements.</p>
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
                        <p>We design practical interfaces and develop features using structured WordPress code. Our plugins are built to be configurable, maintainable, and easy to use within WordPress.</p>
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
                        <p>Before release, we test plugin functionality, configuration options, compatibility, performance, and user-facing features to help ensure a stable and reliable product.</p>
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
                        <p>We deliver the plugin as a ready-to-use WordPress product and continue improving it through updates, bug fixes, compatibility improvements, documentation, and customer support.</p>
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
    </div>
</div>
<?php
