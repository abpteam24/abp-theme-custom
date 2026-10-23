<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
			if ( class_exists( 'Easy_Digital_Downloads' ) && function_exists( 'edd_get_customer_by' ) ) {
				$abp_pending_user = is_user_logged_in() && function_exists( 'edd_user_pending_verification' ) && edd_user_pending_verification( get_current_user_id() );
				if ( ! is_user_logged_in() ) { ?>
            <div class="abp_area">
                <h2 class="abp"><?php esc_html_e( 'My Account', 'abp-team' ); ?></h2>
                <p><?php esc_html_e( 'Log in or Register to access your purchases, licenses, and downloads.', 'abp-team' ); ?></p>
				<?php if ( isset( $_GET['edd-verify-success'] ) ) : ?>
					<div class="abp-notice abp-notice--success">
						<strong><?php esc_html_e( 'Email verified', 'abp-team' ); ?></strong>
						<span><?php esc_html_e( 'Your email address is confirmed. You can now sign in to your account.', 'abp-team' ); ?></span>
					</div>
				<?php endif; ?>
				<?php
					$abp_verify_notice = class_exists( 'ABP_Account_Security' ) ? ABP_Account_Security::get_verify_notice() : array();
					if ( ! empty( $abp_verify_notice ) ) :
						?>
					<div class="abp-notice abp-notice--info" id="abp-verify-sent-notice" role="status" aria-live="polite">
						<strong><?php esc_html_e( 'Verification email sent', 'abp-team' ); ?></strong>
						<span><?php esc_html_e( 'We have sent a verification link to your email address. Please open your inbox (and the spam folder), click the link to verify your email, and then sign in.', 'abp-team' ); ?></span>
					</div>
				<?php endif; ?>
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
									$abp_account_url = get_permalink( get_queried_object_id() );
									echo do_shortcode( $abp_account_url ? sprintf( '[edd_register redirect="%s"]', esc_url_raw( $abp_account_url ) ) : '[edd_register]' );
								} else {
									echo '<p>' . esc_html__( 'Registration form shortcode not available.', 'abp-team' ) . '</p>';
								}
							?>
							<p class="abp-muted"><?php esc_html_e( 'We will send a verification link to your email address. You can sign in once your email is verified.', 'abp-team' ); ?></p>
                        </div>
					<?php endif; ?>
                </div>
				<div class="abp-security-note">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M9 12l2 2 4-4"></path></svg>
					<span><?php esc_html_e( 'Protected by anti-bot security checks. Every sign-in and registration is verified before it is accepted.', 'abp-team' ); ?></span>
				</div>
				<div class="abp-verify-box">
					<h3><?php esc_html_e( 'Already registered? Verify your email', 'abp-team' ); ?></h3>
					<p><?php esc_html_e( 'We send a verification link when you create an account. Enter your email address and we will send a fresh link so you can activate your account.', 'abp-team' ); ?></p>
					<form class="abp-verify-form" id="abp-verify-form" method="post" novalidate>
						<input type="email" id="abp-verify-email" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'abp-team' ); ?>" autocomplete="email" required />
						<button type="submit" class="_btn_active"><?php esc_html_e( 'Send verification link', 'abp-team' ); ?></button>
					</form>
					<div class="abp-verify-message" id="abp-verify-message" role="status" aria-live="polite"></div>
				</div>
				<script>
				(function($){
					$(function(){
						var $form = $('#abp-verify-form');
						if (!$form.length) { return; }
						var ajaxUrl = <?php echo wp_json_encode( admin_url('admin-ajax.php') ); ?>;
						var nonce = <?php echo wp_json_encode( wp_create_nonce( 'abp_resend_verification' ) ); ?>;
						var sending = <?php echo wp_json_encode( __( 'Sending...', 'abp-team' ) ); ?>;
						var failed = <?php echo wp_json_encode( __( 'Something went wrong. Please try again.', 'abp-team' ) ); ?>;
						$form.on('submit', function(e){
							e.preventDefault();
							var $msg = $('#abp-verify-message');
							var $btn = $form.find('button');
							var email = $.trim($('#abp-verify-email').val() || '');
							if (!email) {
								$msg.addClass('is-visible is-error').text( <?php echo wp_json_encode( __( 'Please enter a valid email address.', 'abp-team' ) ); ?> );
								return;
							}
							$btn.prop('disabled', true);
							$msg.removeClass('is-error is-success').addClass('is-visible').text(sending);
							$.post(ajaxUrl, {
								action: 'abp_resend_verification',
								nonce: nonce,
								email: email
							}).done(function(res){
								if (res && res.success && res.data && res.data.message) {
									$msg.addClass('is-success').text(res.data.message);
								} else {
									$msg.addClass('is-error').text((res && res.data && res.data.message) ? res.data.message : failed);
								}
							}).fail(function(){
								$msg.addClass('is-error').text(failed);
							}).always(function(){
								$btn.prop('disabled', false);
							});
						});
					});
				})(jQuery);
				</script>
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
				<?php if ( $abp_pending_user ) : ?>
					<div class="abp-notice abp-notice--warning">
						<strong><?php esc_html_e( 'Your email address is not verified yet', 'abp-team' ); ?></strong>
						<span><?php esc_html_e( 'Please confirm your email address to unlock purchases, licenses and downloads.', 'abp-team' ); ?></span>
					</div>
				<?php endif; ?>
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
																<?php echo (! empty( $download_html ) && $license && $license->license_key)  ? implode( '<br>', $download_html ) : esc_html__( 'No file', 'easy-digital-downloads' ); ?>
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