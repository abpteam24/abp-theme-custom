<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABP_Hooks' ) ) {
		class ABP_Shortcodes {
			public function __construct() {
				add_shortcode( 'abp_my_account', [ $this, 'my_account' ] );
				add_shortcode( 'abp_home_page', [ $this, 'home_page' ] );
				add_shortcode( 'abp_product_page', [ $this, 'product_page' ] );
				add_action( 'edd_after_download_content', [ $this, 'product_pricing' ] );
				add_shortcode( 'abp_how_we_work', [ $this, 'how_we_work' ] );
				//add_shortcode( 'abp_free_plugin_offer', [ $this, 'free_plugin_offer' ] );
				add_action( 'wp_body_open', [ $this, 'free_plugin_offer' ] );
				add_action( 'wp_ajax_abp_free_offer_submit', [ $this, 'free_offer_submit' ] );
				add_action( 'wp_ajax_nopriv_abp_free_offer_submit', [ $this, 'free_offer_submit' ] );
			}

			public static function free_offer_plugins(): array {
				return [
					'abp-rentalforge'       => [
						'label'  => __( 'ABP RentalForge', 'abp-plugin' ),
						'review' => 'https://wordpress.org/support/plugin/abp-rentalforge/reviews/',
					],
					'abp-transport-booking' => [
						'label'  => __( 'ABP Transport Booking', 'abp-plugin' ),
						'review' => 'https://wordpress.org/support/plugin/abp-transport-booking/reviews/',
					],
				];
			}

			public static function free_offer_discounts(): array {
				return [
					'30' => [
						'percent'   => '30',
						'code'      => 'ABP30',
						'claimable' => false,
						'flag'      => __( 'Promotional Offer', 'abp-plugin' ),
						'title'     => __( '30% Off On Every Product', 'abp-plugin' ),
						'text'      => __( 'Copy the code ABP30 and apply it on the checkout page to get 30% off any plugin you purchase.', 'abp-plugin' ),
						'option'    => __( '30% Off — Code ABP30', 'abp-plugin' ),
						'points'    => [
							__( 'Valid on every ABP-TEAM product', 'abp-plugin' ),
							__( 'Copy the code and use it at checkout', 'abp-plugin' ),
							__( 'No form, no waiting', 'abp-plugin' ),
						],
					],
					'60' => [
						'percent'   => '60',
						'code'      => '',
						'claimable' => true,
						'flag'      => __( 'Special Offer', 'abp-plugin' ),
						'title'     => __( '60% Off On Every Product', 'abp-plugin' ),
						'text'      => __( 'Claim this offer and our team will email you your personal 60% discount code.', 'abp-plugin' ),
						'option'    => __( '60% Off — Special Offer', 'abp-plugin' ),
						'points'    => [
							__( 'Valid on every ABP-TEAM product', 'abp-plugin' ),
							__( 'Personal code sent to your inbox', 'abp-plugin' ),
							__( 'Issued personally by our team', 'abp-plugin' ),
						],
					],
				];
			}

			public function my_account(): false|string {
				ob_start();
				$file_path = wp_normalize_path( ABP_DIR . 'templates/my_account.php' );
				if ( file_exists( $file_path ) ) {
					include_once $file_path;
				}

				return ob_get_clean();
			}

			public function home_page(): false|string {
				ob_start();
				$file_path = wp_normalize_path( ABP_DIR . 'templates/home_page.php' );
				if ( file_exists( $file_path ) ) {
					include_once $file_path;
				}

				return ob_get_clean();
			}

			public function product_page(): false|string {
				ob_start();
				$file_path = wp_normalize_path( ABP_DIR . 'templates/product_page.php' );
				if ( file_exists( $file_path ) ) {
					include_once $file_path;
				}

				return ob_get_clean();
			}

			public function product_pricing( $product_id ): void {
				$file_path = wp_normalize_path( ABP_DIR . 'templates/product_pricing.php' );
				if ( file_exists( $file_path ) && ! empty( $product_id ) && $product_id > 0 ) {
					include_once $file_path;
				}
			}

			public function free_plugin_offer() {
				$file_path = wp_normalize_path( ABP_DIR . 'templates/free_plugin_offer.php' );
				if ( file_exists( $file_path ) ) {
					include $file_path;
				}
			}

			public function free_offer_submit(): void {
				if ( ! check_ajax_referer( 'abp_free_offer_nonce_action', 'abp_free_offer_nonce', false ) ) {
					wp_send_json_error( [ 'message' => __( 'Security token expired. Please refresh the page and try again.', 'abp-plugin' ) ], 403 );
				}
				$name   = isset( $_POST['cust_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_name'] ) ) : '';
				$email    = isset( $_POST['cust_email'] ) ? sanitize_email( wp_unslash( $_POST['cust_email'] ) ) : '';
				$plugin   = isset( $_POST['cust_plugin'] ) ? sanitize_key( wp_unslash( $_POST['cust_plugin'] ) ) : '';
				$discount = isset( $_POST['cust_discount'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_discount'] ) ) : '';

				if ( '' === $name || ! is_email( $email ) || '' === $plugin || '' === $discount ) {
					wp_send_json_error( [ 'message' => __( 'Please fill in all fields with valid information.', 'abp-plugin' ) ] );
				}

				if ( class_exists( 'ABP_IA_Visitors' ) ) {
					ABP_IA_Visitors::capture_email( $email );
				}

				$plugins = self::free_offer_plugins();
				if ( ! isset( $plugins[ $plugin ] ) ) {
					wp_send_json_error( [ 'message' => __( 'Please choose a valid plugin.', 'abp-plugin' ) ] );
				}

				$discounts = self::free_offer_discounts();
				if ( ! isset( $discounts[ $discount ] ) ) {
					wp_send_json_error( [ 'message' => __( 'Please choose a valid offer.', 'abp-plugin' ) ] );
				}

				if ( empty( $discounts[ $discount ]['claimable'] ) ) {
					wp_send_json_error( [ 'message' => __( 'This offer is a copy-and-use code. Please copy the code and apply it on the checkout page.', 'abp-plugin' ) ] );
				}

				$plugin_label = $plugins[ $plugin ]['label'];
				$review_url   = $plugins[ $plugin ]['review'];
				$percent      = $discounts[ $discount ]['percent'];
				$admin_to     = ABP_Function::get_valid_email( get_option( 'abp_smart_target_email', 'support@abp-team.com' ) );
				$site_name    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
				$site_name    = $site_name ? $site_name : 'ABP-TEAM';
				$from_email   = ABP_Function::get_from_email();
				$submitted_at = current_time( 'mysql' );

				$safe_name   = esc_html( $name );
				$safe_email  = esc_html( $email );
				$safe_label  = esc_html( $plugin_label );
				$safe_review = esc_url( $review_url );

				$mail_font   = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif";
				$brand_name  = 'ABP-TEAM';
				$safe_site   = esc_html( $site_name );

				$admin_subject = sprintf( 'New %s%% discount request - %s - %s', $percent, $plugin_label, $name );
				$admin_body    = '
				<html>
				<body style="margin:0;padding:0;background:#eef2f7;font-family:' . $mail_font . ';color:#1e293b;line-height:1.65;-webkit-text-size-adjust:100%;">
					<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:28px 12px;">
						<tr>
							<td align="center">
								<table role="presentation" width="640" cellpadding="0" cellspacing="0" style="width:100%;max-width:640px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
									<tr>
										<td style="background:#0b1220;background:linear-gradient(135deg,#0b1220 0%,#123a6b 55%,#2563eb 100%);padding:30px 32px 28px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
												<tr>
													<td style="font-size:13px;font-weight:800;color:#ffffff;letter-spacing:3px;">' . $brand_name . '</td>
													<td align="right"><span style="display:inline-block;padding:6px 14px;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.3);color:#dbeafe;font-size:10.5px;font-weight:700;letter-spacing:1.6px;">' . esc_html( $percent ) . '% OFF OFFER</span></td>
												</tr>
											</table>
											<h1 style="margin:22px 0 8px;font-size:23px;line-height:1.3;color:#ffffff;font-weight:700;">New discount request</h1>
											<p style="margin:0;font-size:14px;color:#c7d2fe;">A customer has claimed the ' . esc_html( $percent ) . '% discount offer for <b style="color:#ffffff;">' . $safe_label . '</b>.</p>
										</td>
									</tr>
									<tr>
										<td style="padding:30px 32px 8px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
												<tr>
													<td style="padding:18px 22px;">
													<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
															<tr>
																<td style="font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#64748b;">Customer</td>
																<td align="right" style="font-size:16px;font-weight:700;color:#0f172a;">' . $safe_name . '</td>
															</tr>
														</table>
														<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;">
															<tr>
																<td style="font-size:13.5px;color:#334155;"><a href="mailto:' . $safe_email . '" style="color:#2563eb;text-decoration:none;">' . $safe_email . '</a></td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="padding:14px 32px 6px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;">
												<tr>
													<td style="padding:16px 22px;border-bottom:1px solid #eef2f7;font-size:13.5px;color:#334155;">
														<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
															<tr>
																<td width="140" style="font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:#94a3b8;">Offer</td>
																<td style="font-size:14px;font-weight:700;color:#0f172a;">' . esc_html( $percent ) . '% Off</td>
															</tr>
															<tr>
																<td style="padding-top:12px;font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:#94a3b8;">Product</td>
																<td style="padding-top:12px;font-size:14px;color:#0f172a;">' . $safe_label . '</td>
															</tr>
															<tr>
																<td style="padding-top:12px;font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:#94a3b8;">Received</td>
																<td style="padding-top:12px;font-size:14px;color:#0f172a;">' . esc_html( $submitted_at ) . '</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="padding:14px 32px 30px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border-left:4px solid #2563eb;border-radius:8px;">
												<tr>
													<td style="padding:18px 22px;font-size:14px;color:#1e3a8a;">
														<b>Next action</b><br>
														Send the personal <b>' . esc_html( $percent ) . '% discount code</b> to the customer at <a href="mailto:' . $safe_email . '" style="color:#1d4ed8;">' . $safe_email . '</a> once their review is live. No code has been issued automatically.
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="padding:0 32px 30px;">
											<p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#94a3b8;">Review link to verify</p>
											<a href="' . $safe_review . '" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:14px 30px;border-radius:9px;">Open the review page</a>
											<p style="margin:14px 0 0;font-size:12px;color:#94a3b8;word-break:break-all;">' . $safe_review . '</p>
										</td>
									</tr>
									<tr>
										<td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 32px;font-size:11.5px;color:#94a3b8;text-align:center;">
											' . $safe_site . ' &bull; All rights reserved &bull; This is an automated notification generated from the website discount form.
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</body></html>';

				$admin_headers   = [];
				$admin_headers[] = 'MIME-Version: 1.0';
				$admin_headers[] = 'Content-Type: text/html; charset=UTF-8';
				$admin_headers[] = 'From: ' . $site_name . ' <' . $from_email . '>';
				$admin_headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';


				$customer_subject = sprintf( 'Your %s%% discount for %s - next steps inside', $percent, $plugin_label );
				$customer_body    = '
				<html>
				<body style="margin:0;padding:0;background:#eef2f7;font-family:' . $mail_font . ';color:#1e293b;line-height:1.65;-webkit-text-size-adjust:100%;">
					<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:28px 12px;">
						<tr>
							<td align="center">
								<table role="presentation" width="640" cellpadding="0" cellspacing="0" style="width:100%;max-width:640px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
									<tr>
										<td style="background:#0b1220;background:linear-gradient(135deg,#0b1220 0%,#123a6b 55%,#2563eb 100%);padding:32px 32px 30px;text-align:center;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
												<tr>
													<td style="font-size:13px;font-weight:800;color:#ffffff;letter-spacing:3px;text-align:center;">' . $brand_name . '</td>
												</tr>
											</table>
											<span style="display:inline-block;margin-top:16px;padding:7px 16px;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.3);color:#dbeafe;font-size:10.5px;font-weight:700;letter-spacing:1.8px;">' . esc_html( $percent ) . '% DISCOUNT OFFER</span>
											<h1 style="margin:18px 0 10px;font-size:25px;line-height:1.3;color:#ffffff;font-weight:700;">Your ' . esc_html( $percent ) . '% discount for ' . $safe_label . '</h1>
											<p style="margin:0;font-size:14.5px;color:#c7d2fe;">Hello ' . $safe_name . ', your request has been received. Here is exactly what happens next.</p>
										</td>
									</tr>
									<tr>
										<td style="padding:30px 32px 0;">
											<p style="margin:0 0 14px;font-size:16px;font-weight:700;color:#0f172a;">Thank you for claiming our ' . esc_html( $percent ) . '% discount offer.</p>
											<p style="margin:0 0 14px;font-size:14.5px;color:#475569;">We are pleased to confirm that your request for <b style="color:#1e293b;">' . $safe_label . '</b> is with our team, and that a personal ' . esc_html( $percent ) . '% discount code will be reserved for you.</p>
											<p style="margin:0 0 26px;font-size:14.5px;color:#475569;">Every code is issued personally so that each request is verified and accounted for. Completing the single step outlined below is all that is required to release your code to this inbox.</p>
										</td>
									</tr>
									<tr>
										<td style="padding:0 32px 28px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;">
												<tr>
													<td style="padding:22px 24px;border-bottom:1px solid #eef2f7;">
														<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
															<tr>
																<td width="46" valign="top"><span style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;border-radius:50%;background:#2563eb;color:#ffffff;font-size:14px;font-weight:700;">1</span></td>
																<td valign="top" style="font-size:15px;color:#0f172a;font-weight:700;">Publish a short 5-star review</td>
															</tr>
														</table>
														<p style="margin:10px 0 0 46px;font-size:13.5px;color:#64748b;">Honest feedback from real customers is what allows a small independent studio like ' . $brand_name . ' to keep improving its plugins and shipping updates. It is the only thing we ask in return for our largest discount, and it takes about a minute.</p>
														<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
															<tr>
																<td align="center" style="padding-top:16px;">
																	<a href="' . $safe_review . '" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:14px 30px;border-radius:9px;">Leave a 5-Star Review</a>
																</td>
															</tr>
														</table>
														<p style="margin:14px 0 0 46px;font-size:11.5px;color:#94a3b8;word-break:break-all;">If the button does not open, copy this link into your browser:<br><a href="' . $safe_review . '" style="color:#2563eb;">' . $safe_review . '</a></p>
													</td>
												</tr>
												<tr>
													<td style="padding:22px 24px;border-bottom:1px solid #eef2f7;">
														<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
															<tr>
																<td width="46" valign="top"><span style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;border-radius:50%;background:#2563eb;color:#ffffff;font-size:14px;font-weight:700;">2</span></td>
																<td valign="top" style="font-size:15px;color:#0f172a;font-weight:700;">We release your personal discount code</td>
															</tr>
														</table>
														<p style="margin:10px 0 0 46px;font-size:13.5px;color:#64748b;">Once the review is live, our team verifies it and sends your personal ' . esc_html( $percent ) . '% discount code to this email address. Codes are never published, so there is no waiting queue and no code-sharing risk.</p>
													</td>
												</tr>
												<tr>
													<td style="padding:22px 24px;">
														<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
															<tr>
																<td width="46" valign="top"><span style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;border-radius:50%;background:#2563eb;color:#ffffff;font-size:14px;font-weight:700;">3</span></td>
																<td valign="top" style="font-size:15px;color:#0f172a;font-weight:700;">Apply the code at checkout</td>
															</tr>
														</table>
														<p style="margin:10px 0 0 46px;font-size:13.5px;color:#64748b;">Open the <b style="color:#334155;">' . $safe_label . '</b> product page, add it to your cart, and enter your code in the discount field on the checkout page. Your ' . esc_html( $percent ) . '% discount is applied to the order total straight away, and the code also applies to any additional ' . $brand_name . ' product in the same order.</p>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="padding:0 32px 28px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border-left:4px solid #2563eb;border-radius:8px;">
												<tr>
													<td style="padding:18px 22px;font-size:13.5px;color:#1e3a8a;">
														<b>Your ' . esc_html( $percent ) . '% discount code will be sent to this email address</b> as soon as step 1 is complete. If you need anything in the meantime, simply reply to this message and a member of our team will assist you personally.
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="padding:0 32px 30px;">
											<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
												<tr>
													<td style="padding:20px 22px;">
														<p style="margin:0 0 6px;font-size:12px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#94a3b8;">Need help?</p>
														<p style="margin:0;font-size:13.5px;color:#475569;">Reply to this email, or write to <a href="mailto:' . $admin_to . '" style="color:#2563eb;">' . esc_html( $admin_to ) . '</a>. Our support team is happy to help with licensing, installation and setup.</p>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:22px 32px;text-align:center;font-size:11.5px;color:#94a3b8;">
											<p style="margin:0 0 6px;font-weight:700;color:#64748b;">' . $safe_site . ' &bull; All rights reserved</p>
											<p style="margin:0;">You received this email because you requested a discount offer on our website. This is an automated message, but a real person will read your reply.</p>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</body></html>';

				$customer_headers   = [];
				$customer_headers[] = 'MIME-Version: 1.0';
				$customer_headers[] = 'Content-Type: text/html; charset=UTF-8';
				$customer_headers[] = 'From: ' . $site_name . ' <' . $from_email . '>';
				$customer_headers[] = 'Reply-To: ' . $admin_to;

				$admin_sent    = wp_mail( $admin_to, $admin_subject, $admin_body, $admin_headers );
				$customer_sent = wp_mail( $email, $customer_subject, $customer_body, $customer_headers );

				if ( $admin_sent || $customer_sent ) {
					wp_send_json_success( [ 'message' => __( 'Success! Your request has been received. Please check your email inbox for the next step.', 'abp-plugin' ) ] );
				}
				wp_send_json_error( [ 'message' => __( 'Something went wrong while sending your request. Please try again later.', 'abp-plugin' ) ] );
			}
		}
		new ABP_Shortcodes();
	}
