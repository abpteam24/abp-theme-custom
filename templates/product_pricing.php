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
                        <?php
                        if ( class_exists( 'ABP_Product_Logo' ) ) {
                            echo ABP_Product_Logo::logo_html( $product_id, 'medium', array( 'class' => 'abp-price-logo', 'alt' => $title ) );
                        }
                        ?>
                        <h1 class="abp_text_center"><?php echo esc_html( $product_infos['title'] ?? '' ); ?></h1>
                        <p class="abp_text_center"><?php echo esc_html( $product_infos['sub_title'] ?? '' ); ?></p>
                    </div>
                    <div class="abp_row abp_product_price">
                        <div class="_section_fd_column_gap">
                            <div class="_all_center">
                                <div class="_gap_xs _text_center"><p class="abp_section_eyebrow abp_color_theme"><?php echo esc_html( $title ); ?></p><?php ABP_Static::svg( 'minus_1' ); ?><h2 class="abp_section_heading abp_color_active">Flexible Pricing Option For ALL</h2></div>
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
                                                    <p class="abp_price_amount abp_color_theme"><?php echo edd_currency_filter( edd_format_amount( $price_data['amount'] ) ); ?>/</p><sub><?php echo esc_html( $variation_info['price_sub'] ?? '' ); ?></sub>
                                                </div>
                                            </div>
                                            <div class="_divider"></div>
                                            <?php if ( ! empty( $features ) ) { ?>
                                                <h4 class="abp_feature_label abp_color_green_pale">Features</h4>
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
                            <?php if ( ! empty( $feature_infos ) || ! empty( $pro_feature_infos ) ) { ?>
                                <div class="abp_feature_intro">
                                    <p class="abp_section_eyebrow abp_color_theme"><?php echo esc_html( $title ); ?></p>
                                    <?php ABP_Static::svg( 'minus_1' ); ?>
                                    <h2 class="abp_section_heading abp_color_active">Free vs. PRO Features</h2>
                                    <p class="abp_feature_subtitle"><?php echo esc_html__( 'See exactly what is included in the free version and what unlocks with PRO.', 'abp-plugin' ); ?></p>
                                </div>
                            <?php } ?>
                            <div class="_grid_500 abp_feature_grid">
                                <?php if ( ! empty( $feature_infos ) ) { ?>
                                    <div class="free_list">
                                        <div class="abp_feature_card_head">
                                            <div class="abp_feature_head_main">
                                                <span class="abp_feature_badge is-free"><?php echo esc_html__( 'Free', 'abp-plugin' ); ?></span>
                                                <h3 class="abp_feature_card_title"><?php echo esc_html__( 'Free Features', 'abp-plugin' ); ?></h3>
                                            </div>
                                            <span class="abp_feature_count"><?php echo esc_html( count( $feature_infos ) ); ?> <?php echo esc_html__( 'features', 'abp-plugin' ); ?></span>
                                        </div>
                                        <ul class="abp_list">
                                            <?php foreach ( $feature_infos as $feature ) { ?>
                                                <li><span class="<?php echo esc_html( $feature['icon'] ?? '' ); ?>"></span><?php echo esc_html( $feature['label'] ?? '' ); ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                                <?php if ( ! empty( $pro_feature_infos ) ) { ?>
                                    <div class="pro_list">
                                        <div class="abp_feature_card_head">
                                            <div class="abp_feature_head_main">
                                                <span class="abp_feature_badge is-pro">PRO</span>
                                                <h3 class="abp_feature_card_title"><?php echo esc_html__( 'PRO Features', 'abp-plugin' ); ?></h3>
                                            </div>
                                            <span class="abp_feature_count"><?php echo esc_html( count( $pro_feature_infos ) ); ?> <?php echo esc_html__( 'features', 'abp-plugin' ); ?></span>
                                        </div>
                                        <ul class="abp_list">
                                            <?php foreach ( $pro_feature_infos as $feature ) { ?>
                                                <li><span class="<?php echo esc_html( $feature['icon'] ?? '' ); ?>"></span><?php echo esc_html( $feature['label'] ?? '' ); ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php if ( ! empty( $about ) ) {
                        $description  = $about['description'] ?? [];
                        $features     = $about['features'] ?? [];
                        $requirements = $about['requirements'] ?? []; ?>
                        <div class="abp_row _section abp_product_about">
                            <p class="abp_section_eyebrow abp _color_burnt_orange _text_center"><?php echo esc_html( $about['sub_title'] ?? '' ); ?></p>
                            <h2 class="abp_section_heading abp_text_center"><?php echo esc_html( $about['title'] ?? '' ); ?></h2>
                            <?php if ( ! empty( $description ) ) {
                                foreach ( $description as $des ) { ?>
                                    <p class="abp"><?php echo esc_html( $des ); ?></p>
                                <?php }
                            } ?>
                        </div>
                    <?php } ?>
                    <?php if ( ! empty( $why_chose ) ) {
                        $infos     = $why_chose['infos'] ?? [];
                        $total     = count( $infos );
                        $pro_total = count( array_filter( $infos, static function ( $item ) {
                            return ! empty( $item['pro'] );
                        } ) );
                        ?>
                        <div class="abp_row _section abp_product_chose">
                            <div class="abp_section_intro">
                                <p class="abp_section_eyebrow"><?php echo esc_html( $why_chose['sub_title'] ?? '' ); ?></p>
                                <?php ABP_Static::svg( 'minus_1' ); ?>
                                <h2 class="abp_section_heading"><?php echo esc_html( $why_chose['title'] ?? '' ); ?></h2>
                                <div class="abp_section_meta">
                                    <span class="abp_section_meta_count"><?php echo esc_html( sprintf( _n( '%s capability', '%s capabilities', $total, 'abp-plugin' ), number_format_i18n( $total ) ) ); ?></span>
                                    <?php if ( $pro_total > 0 ) { ?>
                                        <span class="abp_section_meta_pro"><?php echo esc_html( sprintf( _n( '%s PRO feature', '%s PRO features', $pro_total, 'abp-plugin' ), number_format_i18n( $pro_total ) ) ); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="_grid_350">
                                <?php foreach ( $infos as $feature ) {
                                    $is_pro = ! empty( $feature['pro'] ); ?>
                                    <div class="chose_list<?php echo $is_pro ? ' is-pro' : ''; ?>" style="--chose-accent: <?php echo esc_attr( $feature['border_color'] ?? '#7B5EA7' ); ?>">
                                        <?php if ( $is_pro ) { ?>
                                            <span class="abp-pro-label">PRO</span>
                                        <?php } ?>
                                        <div class="abp_chose_card_head">
                                            <div class="abp_all_center abp_card_icon"><?php echo esc_html( $feature['icon'] ?? '' ); ?></div>
                                            <h3><?php echo esc_html( $feature['title'] ?? '' ); ?></h3>
                                        </div>
                                        <label><?php echo esc_html( $feature['description'] ?? '' ); ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ( ! empty( $use_case ) ) {
                        $infos = $use_case['infos'] ?? [];
                        $total = count( $infos ); ?>
                        <div class="abp_row _section abp_product_use_case">
                            <div class="abp_section_intro">
                                <p class="abp_section_eyebrow"><?php echo esc_html__( 'Use Cases', 'abp-plugin' ); ?></p>
                                <?php ABP_Static::svg( 'minus_1' ); ?>
                                <h2 class="abp_section_heading"><?php echo esc_html( $use_case['title'] ?? '' ); ?></h2>
                                <p class="abp_section_subheading"><?php echo esc_html( $use_case['sub_title'] ?? '' ); ?></p>
                                <div class="abp_section_meta">
                                    <span class="abp_section_meta_count"><?php echo esc_html( sprintf( _n( '%s use case', '%s use cases', $total, 'abp-plugin' ), number_format_i18n( $total ) ) ); ?></span>
                                </div>
                            </div>
                            <div class="_grid_350">
                                <?php foreach ( $infos as $feature ) { ?>
                                    <div class="chose_list" style="--use-bg: <?php echo esc_attr( $feature['bg_color'] ?? '#F4F6FB' ); ?>">
                                        <div class="abp_all_center abp_card_icon"><?php echo esc_html( $feature['icon'] ?? '' ); ?></div>
                                        <h3><?php echo esc_html( $feature['title'] ?? '' ); ?></h3>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ( ! empty( $short_code ) ) {
                        $infos = $short_code['infos'] ?? []; ?>
                        <div class="abp_row _section abp_product_short_code">
                            <h2 class="abp_section_heading abp_text_center"><?php echo esc_html( $short_code['title'] ?? '' ); ?></h2>
                            <p class="abp_section_subheading abp_text_center"><?php echo esc_html( $short_code['sub_title'] ?? '' ); ?></p>
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
                    <?php
                        $sliders = $product_infos['slider'] ?? '';
                        if ( ! empty( $sliders ) ) {
                            $slider_folder = $sliders['file'] ?? '';
                            $upload_dir    = wp_upload_dir();
                            $slider_path   = trailingslashit( $upload_dir['basedir'] ) . $slider_folder;
                            $slider_images = is_dir( $slider_path ) ? glob( trailingslashit( $slider_path ) . '*.{jpg,jpeg,png,gif,webp,bmp,JPG,JPEG,PNG,GIF,WEBP,BMP}', GLOB_BRACE ) : [];
                            if ( ! empty( $slider_images ) ) {
                                $slider_images = array_values( array_filter( is_array( $slider_images ) ? $slider_images : [], 'is_file' ) );
                                sort( $slider_images );
                                $slider_base_url = trailingslashit( $upload_dir['baseurl'] ) . $slider_folder . '/';
                                $slider_count    = count( $slider_images );
                                $gallery_batch   = 12;
                                $gallery_items   = [];
                                foreach ( $slider_images as $slider_image ) {
                                    $slider_file   = basename( $slider_image );
                                    $slider_name   = pathinfo( $slider_file, PATHINFO_FILENAME );
                                    $slider_label  = trim( preg_replace( '/^[\d\s.\-_]+/', '', $slider_name ) );
                                    $slider_label  = $slider_label ? $slider_label : $slider_name;
                                    $slider_label  = ucwords( trim( str_replace( [ '-', '_' ], ' ', $slider_label ) ) );
                                    $slider_size   = @getimagesize( $slider_image );
                                    $gallery_items[] = [
                                        'url'    => $slider_base_url . rawurlencode( $slider_file ),
                                        'label'  => $slider_label,
                                        'width'  => ! empty( $slider_size[0] ) ? (int) $slider_size[0] : 1600,
                                        'height' => ! empty( $slider_size[1] ) ? (int) $slider_size[1] : 1000,
                                    ];
                                }
                                ?>
                                <div class="abp_row _section abp_product_gallery" data-abp-gallery data-batch="<?php echo esc_attr( $gallery_batch ); ?>">
                                    <div class="_fd_column_gap_xs_w_full">
                                        <p class="abp_section_eyebrow abp-gallery-eyebrow">Screenshot Gallery</p>
                                        <h2 class="abp_section_heading abp-gallery-title"><?php echo esc_html( $sliders['title'] ?? '' ); ?></h2>
                                        <p class="abp-gallery-subtitle"><?php echo esc_html( $sliders['sub_title'] ?? '' ); ?></p>
                                    </div>
                                    <div class="abp-gallery-wrap">
                                        <div class="abp-gallery" data-abp-gallery-grid>
                                            <?php foreach ( $gallery_items as $index => $gallery_item ) { ?>
                                                <button type="button" class="abp-gallery-item" data-abp-gallery-item data-index="<?php echo esc_attr( $index ); ?>" data-src="<?php echo esc_url( $gallery_item['url'] ); ?>" data-caption="<?php echo esc_attr( $gallery_item['label'] ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Open screenshot: %s', $gallery_item['label'] ) ); ?>">
                                                    <img src="<?php echo esc_url( $gallery_item['url'] ); ?>" alt="<?php echo esc_attr( $title . ' — ' . $gallery_item['label'] . ' screenshot' ); ?>" width="<?php echo esc_attr( $gallery_item['width'] ); ?>" height="<?php echo esc_attr( $gallery_item['height'] ); ?>" loading="lazy" decoding="async">
                                                    <span class="abp-gallery-caption"><?php echo esc_html( $gallery_item['label'] ); ?></span>
                                                </button>
                                            <?php } ?>
                                        </div>
                                        <div class="abp-gallery-footer">
                                            <button type="button" class="abp-gallery-more" data-abp-gallery-more<?php echo $slider_count > $gallery_batch ? '' : ' hidden'; ?>>Load More (<?php echo esc_html( min( $gallery_batch, max( 0, $slider_count - $gallery_batch ) ) ); ?>)</button>
                                            <p class="abp-gallery-status" data-abp-gallery-status aria-live="polite">Showing <?php echo esc_html( min( $gallery_batch, $slider_count ) ); ?> of <?php echo esc_html( $slider_count ); ?> screenshots</p>
                                        </div>
                                    </div>
                                    <div class="abp-lightbox" data-abp-lightbox role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $title . ' screenshot viewer' ); ?>" aria-hidden="true" hidden>
                                        <div class="abp-lightbox-backdrop" data-abp-lightbox-close></div>
                                        <div class="abp-lightbox-panel">
                                            <div class="abp-lightbox-bar">
                                                <span class="abp-lightbox-counter" data-abp-lightbox-counter></span>
                                                <button type="button" class="abp-lightbox-close" data-abp-lightbox-close aria-label="Close screenshot viewer"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <button type="button" class="abp-lightbox-nav is-prev" data-abp-lightbox-prev aria-label="Previous screenshot"><span aria-hidden="true">&#8249;</span></button>
                                            <button type="button" class="abp-lightbox-nav is-next" data-abp-lightbox-next aria-label="Next screenshot"><span aria-hidden="true">&#8250;</span></button>
                                            <figure class="abp-lightbox-figure">
                                                <img class="abp-lightbox-image" data-abp-lightbox-image alt="" decoding="async">
                                                <figcaption class="abp-lightbox-caption" data-abp-lightbox-caption></figcaption>
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        } ?>
                </div>
            </div>
            <?php
        }
    }