<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    $atts       = $atts ?? [];
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
    // $collapse_id       = '#abp_price_option_' . $product_id;
    $collapse_id_annual = '#abp_price_option_annual_' . $product_id;
    $collapse_id_lt     = '#abp_price_option_lt_' . $product_id;
?>
    <div class="abp_area">
        <div class="abp_container _bg_light abp_price_section">
            <div class="_all_center">
                <div class="_gap_xs _text_center"><h3 class="abp_color_theme"><?php echo esc_html( get_the_title( $product_id ) ); ?></h3><?php ABP_Static::svg( 'minus_1' ); ?><h3 class="abp_color_active">Flexible Pricing Option For ALL</h3></div>
            </div>
            <div class="_all_center">
                <div class="_group_content">
                    <button type="button" class="_btn_light_theme abp_active" data-price-target="all">All (Yearly + One Time)</button>
                    <button type="button" class="_btn_light_theme" data-price-target="annual">Yearly / Annual Billing</button>
                    <button type="button" class="_btn_light_theme" data-price-target="one_time">One Time / Lifetime License</button>
                </div>
            </div>
            <div class="price_content edd-pricing-grid">
                <?php foreach ( $prices as $price_id=>$price_data ) { ?>
                    <div class="edd-price-card <?php echo esc_attr( ($price_id==1 || $price_id==4)?'popular':'' ); ?>" data-price-tab="<?php echo esc_attr( $price_id<4?'annual':'one_time' ); ?>">
                        <div class="_all_center_fd_column_gap_xs">
                            <h3 class="abp_color_green_pale"><?php echo esc_html( $price_data['name'] ); ?></h3>
                            <h1 class="abp_color_theme"><?php echo edd_currency_filter( edd_format_amount( $price_data['amount'] ) ); ?>/<small>Year + VAT</small></h1>
                        </div>
                        <div class="_divider"></div>
                    </div>
                    <?php } ?>
            </div>
        </div>
    </div>
    <div class="abp_area abp_price_section" id="edd-pricing-<?php echo esc_attr( $product_id ); ?>">
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
                            $btn_class = '_btn_purple';
                            $is_popular = ( 1 === $price_id );
                            $item_features = isset( $features_by_index[ $price_id ] ) ? $features_by_index[ $price_id ] : array();
                            ?>
                            <div class="edd-price-card <?php echo $is_popular ? 'popular' : ''; ?>">
                                <?php if ( $is_popular ) :
                                    $btn_class = '_btn_active'; ?>
                                    <div class="edd-popular-badge">Most Popular</div>
                                <?php endif; ?>
                                <div class="_all_center_fd_column_gap_xs">
                                    <h3 class="abp_color_green_pale"><?php echo esc_html( $price_data['name'] ); ?></h3>
                                    <h1 class="abp_color_theme"><?php echo edd_currency_filter( edd_format_amount( $price_data['amount'] ) ); ?>/<small>Year + VAT</small></h1>
                                </div>
                                <div class="_divider"></div>
                                <ul class="edd-feature-list">
                                    <?php foreach ( $item_features as $feature_item ) : ?>
                                        <li><?php echo esc_html( $feature_item ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="<?php echo esc_url( ABP_Function::build_edd_url( $product_id, $price_id ) ); ?>" class="<?php echo esc_attr( $btn_class ); ?>"><i class="fa-solid fa-cart-shopping"></i>Get Started</a>
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
                            $btn_class = '_btn_purple';
                            $is_popular = ( 4 === $price_id );
                            $item_features = isset( $features_by_index[ $price_id ] ) ? $features_by_index[ $price_id ] : array();
                            ?>
                            <div class="edd-price-card <?php echo esc_attr( $is_popular ? 'popular' : '' ); ?>">
                                <?php if ( $is_popular ) :
                                    $btn_class = '_btn_active'; ?>
                                    <div class="edd-popular-badge">Best Value</div>
                                <?php endif; ?>
                                <div class="edd-card-header">
                                    <h3 class="abp_color_purple"><?php echo esc_html( $price_data['name'] ); ?></h3>
                                    <h1 class="abp_color_theme"><?php echo edd_currency_filter( edd_format_amount( $price_data['amount'] ) ); ?>/<small>One Time + VAT</small></h1>
                                </div>
                                <ul class="edd-feature-list">
                                    <?php foreach ( $item_features as $feature_item ) : ?>
                                        <li><?php echo esc_html( $feature_item ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="<?php echo esc_url( ABP_Function::build_edd_url( $product_id, $price_id ) ); ?>" class="<?php echo esc_attr( $btn_class ); ?>"><i class="fa-solid fa-cart-plus"></i>Get Lifetime Access</a>
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