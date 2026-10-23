<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABP_Product_Logo' ) ) {
		/**
		 * A per product logo/image that is independent from the featured image.
		 *
		 * The logo is stored as post meta on the EDD download, so it survives
		 * plugin updates (unlike a hard coded attachment id) and can be edited
		 * from the product screen. It is used in the cart and checkout, in the
		 * price lists and anywhere the [abp_product_logo] shortcode is placed.
		 */
		class ABP_Product_Logo {
			const META     = '_abp_product_logo';
			const NONCE    = 'abp_product_logo_field';
			const MIGRATED = 'abp_product_logo_migrated';

			public function __construct() {
				add_action( 'init', array( $this, 'register_meta' ), 5 );
				add_action( 'init', array( $this, 'maybe_migrate' ), 20 );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				add_action( 'save_post_download', array( $this, 'save' ), 10, 2 );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
				add_action( 'edd_checkout_cart_item_title_after', array( $this, 'cart_item' ), 10, 2 );
				add_shortcode( 'abp_product_logo', array( $this, 'shortcode' ) );
			}

			/* -----------------------------------------------------------------
			 * Data
			 * -------------------------------------------------------------- */

			/**
			 * The logo attachment id for a product.
			 *
			 * Meta first, then the legacy id from the static array (so existing
			 * products keep their current image), then the featured image.
			 */
			public static function logo_id( $download_id ): int {
				$download_id = absint( $download_id );

				if ( $download_id <= 0 ) {
					return 0;
				}

				$id = (int) get_post_meta( $download_id, self::META, true );

				if ( $id > 0 && wp_attachment_is_image( $id ) ) {
					return $id;
				}

				$thumb = (int) get_post_thumbnail_id( $download_id );

				return $thumb > 0 ? $thumb : 0;
			}

			public static function logo_url( $download_id, $size = 'medium' ): string {
				$id = self::logo_id( $download_id );

				return $id > 0 ? (string) wp_get_attachment_image_url( $id, $size ) : '';
			}

			/**
			 * Ready to print image markup, or an empty string when there is none.
			 */
			public static function logo_html( $download_id, $size = 'medium', array $attr = array() ): string {
				$id = self::logo_id( $download_id );

				if ( $id <= 0 ) {
					return '';
				}

				$attr = wp_parse_args(
					$attr,
					array(
						'alt'   => get_the_title( $download_id ),
						'class' => 'abp-product-logo',
					)
				);

				return (string) wp_get_attachment_image( $id, $size, false, $attr );
			}

			/* -----------------------------------------------------------------
			 * Cart and checkout
			 * -------------------------------------------------------------- */

			/**
			 * Runs inside the cart item name cell on the checkout page, in both
			 * the block cart and the classic template.
			 */
			public function cart_item( $item, $key = '' ): void {
				$download_id = (int) ( $item['id'] ?? 0 );

				if ( $download_id <= 0 ) {
					return;
				}

				$html = self::logo_html(
					$download_id,
					array( 96, 96 ),
					array(
						'class'   => 'abp-cart-logo-img',
						'loading' => 'eager',
						'alt'     => '',
					)
				);

				if ( '' === $html ) {
					return;
				}

				echo '<span class="abp-cart-logo">' . $html . '</span>';
			}

			/* -----------------------------------------------------------------
			 * Shortcode
			 * -------------------------------------------------------------- */

			public function shortcode( $atts ): string {
				$atts = shortcode_atts(
					array(
						'id'    => 0,
						'size'  => 'medium',
						'class' => 'abp-product-logo',
						'link'  => 'no',
					),
					$atts,
					'abp_product_logo'
				);

				$id = absint( $atts['id'] );

				if ( $id <= 0 ) {
					$id = (int) get_the_ID();
				}

				$html = self::logo_html( $id, sanitize_key( $atts['size'] ), array( 'class' => sanitize_html_class( $atts['class'] ) ) );

				if ( '' === $html ) {
					return '';
				}

				if ( 'yes' === strtolower( (string) $atts['link'] ) ) {
					return '<a class="abp-product-logo-link" href="' . esc_url( get_permalink( $id ) ) . '">' . $html . '</a>';
				}

				return $html;
			}

			/* -----------------------------------------------------------------
			 * Admin field
			 * -------------------------------------------------------------- */

			public function register_meta(): void {
				register_post_meta(
					'download',
					self::META,
					array(
						'type'              => 'integer',
						'single'            => true,
						'default'           => 0,
						'show_in_rest'      => true,
						'sanitize_callback' => 'absint',
						'auth_callback'     => function () {
							return current_user_can( 'edit_posts' );
						},
					)
				);
			}

			public function add_meta_box( $post_type ): void {
				if ( 'download' !== $post_type ) {
					return;
				}

				add_meta_box(
					'abp_product_logo_box',
					__( 'Product Logo', 'abp-plugin' ),
					array( $this, 'render_meta_box' ),
					'download',
					'side',
					'default'
				);
			}

			public function render_meta_box( $post ): void {
				$id = (int) get_post_meta( $post->ID, self::META, true );

				wp_nonce_field( self::NONCE, self::NONCE );
				?>
				<div class="abp-logo-field">
					<div class="abp-logo-preview">
						<?php
						if ( $id > 0 ) {
							echo wp_get_attachment_image( $id, 'medium', false, array( 'style' => 'max-width:100%;height:auto' ) );
						}
						?>
					</div>
					<p>
						<button type="button" class="button abp-logo-select">
							<?php echo esc_html( $id > 0 ? __( 'Replace logo', 'abp-plugin' ) : __( 'Select logo', 'abp-plugin' ) ); ?>
						</button>
						<button type="button" class="button-link abp-logo-remove" <?php echo $id > 0 ? '' : 'style="display:none"'; ?>>
							<?php esc_html_e( 'Remove', 'abp-plugin' ); ?>
						</button>
					</p>
					<input type="hidden" name="abp_product_logo" class="abp-logo-id" value="<?php echo esc_attr( (string) $id ); ?>" />
					<p class="description">
						<?php esc_html_e( 'Shown in the cart, the checkout and the price lists. Leave empty to use the featured image.', 'abp-plugin' ); ?>
					</p>
				</div>
				<?php
			}

			public function save( $post_id, $post ): void {
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}

				if ( ! isset( $_POST[ self::NONCE ] ) ) {
					return;
				}

				$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) );

				if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
					return;
				}

				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}

				$id = isset( $_POST['abp_product_logo'] ) ? absint( $_POST['abp_product_logo'] ) : 0;

				if ( $id > 0 && 'attachment' === get_post_type( $id ) ) {
					update_post_meta( $post_id, self::META, $id );
				} else {
					delete_post_meta( $post_id, self::META );
				}
			}

			public function admin_assets( $hook ): void {
				$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

				if ( ! $screen || 'download' !== $screen->post_type ) {
					return;
				}

				wp_enqueue_media();
				wp_enqueue_script( 'jquery' );

				$labels = array(
					'title'   => __( 'Select product logo', 'abp-plugin' ),
					'button'  => __( 'Use this logo', 'abp-plugin' ),
					'select'  => __( 'Select logo', 'abp-plugin' ),
					'replace' => __( 'Replace logo', 'abp-plugin' ),
				);

				$js = 'jQuery(function($){'
					. 'var frame, labels=' . wp_json_encode( $labels ) . ';'
					. '$(document).on("click",".abp-logo-select",function(e){'
					. 'e.preventDefault();var $f=$(this).closest(".abp-logo-field");'
					. 'if(!frame){frame=wp.media({title:labels.title,button:{text:labels.button},multiple:false,library:{type:"image"}});'
					. 'frame.on("select",function(){var a=frame.state().get("selection").first().toJSON(),$t=frame.abpField;'
					. '$t.find(".abp-logo-id").val(a.id);'
					. 'var src=(a.sizes&&a.sizes.medium)?a.sizes.medium.url:a.url;'
					. '$t.find(".abp-logo-preview").html($("<img>").attr("src",src).attr("alt","").attr("style","max-width:100%;height:auto"));'
					. '$t.find(".abp-logo-remove").show();$t.find(".abp-logo-select").text(labels.replace);});}'
					. 'frame.abpField=$f;frame.open();});'
					. '$(document).on("click",".abp-logo-remove",function(e){'
					. 'e.preventDefault();var $f=$(this).closest(".abp-logo-field");'
					. '$f.find(".abp-logo-id").val("");$f.find(".abp-logo-preview").empty();'
					. '$(this).hide();$f.find(".abp-logo-select").text(labels.select);});'
					. '});';

				wp_add_inline_script( 'jquery', $js );
				wp_add_inline_style(
					'wp-admin',
					'.abp-logo-preview{margin:0 0 8px;min-height:20px}.abp-logo-preview img{max-width:100%;height:auto;border:1px solid #dcdcde;border-radius:4px;background:#fff;padding:6px}.abp-logo-field .description{margin-top:6px}'
				);
			}

			/**
			 * Move the logo ids that used to be hard coded in the static array
			 * into the product meta, so it becomes editable from the admin.
			 */
			public function maybe_migrate(): void {
				if ( get_option( self::MIGRATED ) ) {
					return;
				}

				update_option( self::MIGRATED, 1, false );

				if ( ! class_exists( 'ABP_Static' ) || ! post_type_exists( 'download' ) ) {
					return;
				}

				$ids = get_posts(
					array(
						'post_type'   => 'download',
						'post_status' => 'any',
						'numberposts' => -1,
						'fields'      => 'ids',
					)
				);

				foreach ( $ids as $id ) {
					if ( get_post_meta( $id, self::META, true ) ) {
						continue;
					}

					$info = ABP_Static::product( $id );
					$logo = $info['logo'] ?? '';

					if ( is_numeric( $logo ) && 'attachment' === get_post_type( (int) $logo ) ) {
						update_post_meta( $id, self::META, (int) $logo );
					}
				}
			}
		}
		new ABP_Product_Logo();
	}
