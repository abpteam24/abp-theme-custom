<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABP_Hooks' ) ) {
        class ABP_Hooks {
            public function __construct() {
                add_shortcode( 'abp_why_choose_us', [ $this, 'why_choose_us' ] );
                add_shortcode( 'abp_about_section', [ $this, 'about_section' ] );
                add_shortcode( 'abp_how_we_work', [ $this, 'how_we_work' ] );
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
        new ABP_Hooks();
    }