<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    $product_id = $product_id ?? '';
    if ( class_exists( 'Easy_Digital_Downloads' ) && function_exists( 'edd_get_customer_by' ) && ! empty( $product_id ) && $product_id > 0 ) {
        $prices        = edd_get_variable_prices( $product_id );
        $product_infos = ABP_Static::product( $product_id );
        if ( ! empty( $product_infos ) ) {
            $variation_infos   = $product_infos['variation'] ?? [];
            $feature_infos     = $product_infos['feature'] ?? [];
            $pro_feature_infos = $product_infos['feature_pro'] ?? [];
            $why_chose         = $product_infos['why_chose'] ?? [];
            $use_case          = $product_infos['use_case'] ?? [];
            $short_code        = $product_infos['short_code'] ?? [];
            $about             = $product_infos['about'] ?? [];
            //echo '<pre>';print_r( ABP_Static::product($product_id) );echo '</pre>';
            $title = get_the_title( $product_id );
            ?>
            <div class="abp_area ">
                <div class="abp_container">
                    <div class="abp_row _section abp_product_title">
                        <h1 class="abp_text_center"><?php echo esc_html( $product_infos['title'] ?? '' ); ?></h1>
                        <p class="abp_text_center"><?php echo esc_html( $product_infos['sub_title'] ?? '' ); ?></p>
                    </div>
                    <div class="abp_row abp_product_price">
                        <div class="_section_fd_column_gap">
                            <div class="_all_center">
                                <div class="_gap_xs _text_center"><h3 class="abp_color_theme"><?php echo esc_html( $title ); ?></h3><?php ABP_Static::svg( 'minus_1' ); ?><h3 class="abp_color_active">Flexible Pricing Option For ALL</h3></div>
                            </div>
                            <div class="_all_center">
                                <div class="_group_content">
                                    <button type="button" class="_btn_light_theme abp_active" data-price-target="all">All (Yearly + One Time)</button>
                                    <button type="button" class="_btn_light_theme" data-price-target="annual">Yearly / Annual Billing</button>
                                    <button type="button" class="_btn_light_theme" data-price-target="one_time">One Time / Lifetime License</button>
                                </div>
                            </div>
                            <div class="price_content _grid_350">
                                <?php if ( ! empty( $prices ) ) {
                                    foreach ( $prices as $price_id => $price_data ) {
                                        $variation_info = $variation_infos[ $price_id ] ?? [];
                                        $popular        = $variation_info['popular'] ?? '';
                                        $features       = $variation_info['feature'] ?? [];
                                        $btn_class      = $popular ? '_btn_active' : '_btn_purple';
                                        ?>
                                        <div class="price_card <?php echo esc_attr( $popular ? 'popular' : '' ); ?>" data-price-tab="<?php echo esc_attr( $price_id < 4 ? 'annual' : 'one_time' ); ?>">
                                            <?php if ( $popular ) {
                                                echo '<div class="popular_badge">Most Popular</div>';
                                            } ?>
                                            <div class="_all_center_fd_column_gap_xs">
                                                <h3 class="abp_color_green_pale"><?php echo esc_html( $price_data['name'] ); ?></h3>
                                                <div class="_gap_xxs">
                                                    <h1 class="abp_color_theme"><?php echo edd_currency_filter( edd_format_amount( $price_data['amount'] ) ); ?>/</h1><sub><?php echo esc_html( $variation_info['price_sub'] ?? '' ); ?></sub>
                                                </div>
                                            </div>
                                            <div class="_divider"></div>
                                            <?php if ( ! empty( $features ) ) { ?>
                                                <h6 class="abp_color_green_pale">Features</h6>
                                                <ul class="abp_list feature_list">
                                                    <?php foreach ( $features as $feature ) { ?>
                                                        <li><?php echo esc_html( $feature ); ?></li>
                                                    <?php } ?>
                                                </ul>
                                            <?php } ?>
                                            <div class="_divider"></div>
                                            <a href="<?php echo esc_url( ABP_Function::build_edd_url( $product_id, $price_id ) ); ?>" class="<?php echo esc_attr( $btn_class ); ?>"><i class="fa-solid fa-cart-plus"></i><?php echo esc_html( $variation_info['btn_text'] ?? '' ); ?>(<?php echo edd_currency_filter( edd_format_amount( $price_data['amount'] ) ); ?>+VAT)</a>
                                        </div>
                                    <?php }
                                } ?>
                            </div>
                        </div>
                    </div>
                    <div class="abp_row _section abp_product_feature">
                        <div class="_max_1100_mar_auto">
                            <div class=" _grid_500">
                                <div class="free_list">
                                    <?php if ( ! empty( $feature_infos ) ) { ?>
                                        <div class="_gap_xs _text_center"><h5 class="abp_color_theme"><?php echo esc_html( $title ); ?></h5><?php ABP_Static::svg( 'minus_1' ); ?><h5 class="abp_color_active">Free Feature Lists</h5></div>
                                        <div class="_divider_xs"></div>
                                        <ul class="abp_list">
                                            <?php foreach ( $feature_infos as $feature ) { ?>
                                                <li><span class="<?php echo esc_html( $feature['icon'] ?? '' ); ?>"></span><?php echo esc_html( $feature['label'] ?? '' ); ?></li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>
                                </div>
                                <div class="pro_list">
                                    <?php if ( ! empty( $pro_feature_infos ) ) { ?>
                                        <div class="_gap_xs _text_center"><h5 class="abp_color_theme"><?php echo esc_html( $title ); ?></h5><?php ABP_Static::svg( 'minus_1' ); ?><h5 class="abp_color_active">PRO Feature Lists</h5></div>
                                        <div class="_divider_xs"></div>
                                        <ul class="abp_list">
                                            <?php foreach ( $pro_feature_infos as $feature ) { ?>
                                                <li><span class="<?php echo esc_html( $feature['icon'] ?? '' ); ?>"></span><?php echo esc_html( $feature['label'] ?? '' ); ?></li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ( ! empty( $about ) ) {
                        $description  = $about['description'] ?? [];
                        $features     = $about['features'] ?? [];
                        $requirements = $about['requirements'] ?? []; ?>
                        <div class="abp_row _section abp_product_about">
                            <h5 class="abp _color_burnt_orange _text_center"><?php echo esc_html( $about['sub_title'] ?? '' ); ?></h5>
                            <h3 class="abp_text_center"><?php echo esc_html( $about['title'] ?? '' ); ?></h3>
                            <?php if ( ! empty( $description ) ) {
                                foreach ( $description as $des ) { ?>
                                    <p class="abp"><?php echo esc_html( $des ); ?></p>
                                <?php }
                            } ?>
                        </div>
                    <?php } ?>
                    <?php if ( ! empty( $why_chose ) ) {
                        $infos = $why_chose['infos'] ?? []; ?>
                        <div class="abp_row _section abp_product_chose">
                            <h5 class="abp _color_burnt_orange _text_center"><?php echo esc_html( $why_chose['sub_title'] ?? '' ); ?></h5>
                            <h3 class="abp_text_center"><?php echo esc_html( $why_chose['title'] ?? '' ); ?></h3>
                            <div class=" _grid_350">
                                <?php foreach ( $infos as $feature ) { ?>
                                    <div class="chose_list" style="border-color: <?php echo esc_attr( $feature['border_color'] ?? '' ); ?>">
                                        <h1 class="abp_all_center" style="color: <?php echo esc_attr( $feature['border_color'] ?? '' ); ?>"><?php echo esc_html( $feature['icon'] ?? '' ); ?></h1>
                                        <h5 class="abp_text_center"><?php echo esc_html( $feature['title'] ?? '' ); ?></h5>
                                        <label><?php echo esc_html( $feature['description'] ?? '' ); ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ( ! empty( $use_case ) ) {
                        $infos = $use_case['infos'] ?? []; ?>
                        <div class="abp_row _section abp_product_use_case">
                            <h3 class="abp_text_center"><?php echo esc_html( $use_case['title'] ?? '' ); ?></h3>
                            <h6 class="abp_text_center"><?php echo esc_html( $use_case['sub_title'] ?? '' ); ?></h6>
                            <div class=" _grid_350">
                                <?php foreach ( $infos as $feature ) { ?>
                                    <div class="chose_list" style="background-color: <?php echo esc_attr( $feature['bg_color'] ?? '' ); ?>">
                                        <h1 class="abp_all_center"><?php echo esc_html( $feature['icon'] ?? '' ); ?></h1>
                                        <h5 class="abp_text_center"><?php echo esc_html( $feature['title'] ?? '' ); ?></h5>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ( ! empty( $short_code ) ) {
                        $infos = $short_code['infos'] ?? []; ?>
                        <div class="abp_row _section abp_product_short_code">
                            <h3 class="abp_text_center"><?php echo esc_html( $short_code['title'] ?? '' ); ?></h3>
                            <h6 class="abp_text_center"><?php echo esc_html( $short_code['sub_title'] ?? '' ); ?></h6>
                            <div class=" _grid_500">
                                <?php foreach ( $infos as $feature ) { ?>
                                    <div class="chose_list">
                                        <h3 class="abp_color_theme _text_inherit"><code class="abp_all_center"><?php echo esc_html( $feature['code'] ?? '' ); ?></code></h3>
                                        <p class="abp_text_center_color_white"><?php echo esc_html( $feature['description'] ?? '' ); ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
    }