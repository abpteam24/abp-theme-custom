<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( class_exists( 'Easy_Digital_Downloads' ) && function_exists( 'edd_get_customer_by' ) ) {
		if ( ! is_user_logged_in() ) { ?>
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
				?>
            </div>
			<?php
		}
	}