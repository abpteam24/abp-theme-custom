<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$offer_plugins   = ABP_Shortcodes::free_offer_plugins();
	$offer_discounts = ABP_Shortcodes::free_offer_discounts();
	$offer_uid       = 'abp_offer_' . wp_rand( 1000, 9999 );
?>
<div class="abp_area">
	<div class="abp_container">
		<div class="abp_row _section abp_free_offer">
			<div class="abp-offer-head">
				<span class="abp-offer-badge"><span class="abp-offer-badge-dot"></span><?php esc_html_e( 'Promotional Offer', 'abp-plugin' ); ?></span>
				<h2 class="abp-offer-title"><?php esc_html_e( 'Exclusive Discounts On All Products', 'abp-plugin' ); ?></h2>
				<p class="abp-offer-text"><?php esc_html_e( 'Two exclusive discount offers that work on every ABP-TEAM product. Copy the 30% code and use it at checkout, or claim the 60% offer and we will send your personal code by email.', 'abp-plugin' ); ?></p>
			</div>

			<div class="abp-offer-grid">
				<?php foreach ( $offer_discounts as $key => $discount ) : ?>
					<div class="abp-offer-card<?php echo ( ! empty( $discount['claimable'] ) ) ? ' abp-offer-card--hot' : ''; ?>">
						<span class="abp-offer-flag"><?php echo esc_html( $discount['flag'] ); ?></span>
						<div class="abp-offer-percent">
							<?php echo esc_html( $discount['percent'] ); ?><span>%</span>
						</div>
						<h3 class="abp-offer-card-title"><?php echo esc_html( $discount['title'] ); ?></h3>
						<p class="abp-offer-card-text"><?php echo esc_html( $discount['text'] ); ?></p>
						<?php if ( ! empty( $discount['code'] ) ) : ?>
							<div class="abp-offer-code">
								<span class="abp-offer-code-text"><?php echo esc_html( $discount['code'] ); ?></span>
								<button type="button" class="abp-offer-code-copy" data-abp-offer-copy="<?php echo esc_attr( $discount['code'] ); ?>"><?php esc_html_e( 'Copy', 'abp-plugin' ); ?></button>
							</div>
						<?php else : ?>
							<div class="abp-offer-code abp-offer-code--none">
								<span class="abp-offer-code-text"><?php esc_html_e( 'Personal code by email', 'abp-plugin' ); ?></span>
							</div>
						<?php endif; ?>
						<ul class="abp-offer-list">
							<?php foreach ( $discount['points'] as $point ) : ?>
								<li><span class="abp-offer-check">&#10003;</span><?php echo esc_html( $point ); ?></li>
							<?php endforeach; ?>
						</ul>
						<div class="abp-offer-actions">
							<?php if ( ! empty( $discount['claimable'] ) ) : ?>
								<button type="button" class="_btn_active abp-offer-cta" data-abp-offer-open="<?php echo esc_attr( $offer_uid ); ?>" data-abp-offer-discount="<?php echo esc_attr( $key ); ?>">
									<?php esc_html_e( 'Claim Offer', 'abp-plugin' ); ?> <span>|</span><i class="fa-solid fa-arrow-right-long"></i>
								</button>
							<?php else : ?>
								<button type="button" class="_btn_active abp-offer-cta abp-offer-cta--copy" data-abp-offer-copy="<?php echo esc_attr( $discount['code'] ); ?>">
									<?php echo esc_html( sprintf( __( 'Copy Code %s', 'abp-plugin' ), $discount['code'] ) ); ?>
								</button>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<p class="abp-offer-footnote"><?php esc_html_e( 'The 30% code can be used straight away on the checkout page. For the 60% offer, claim it with the button above and our team will email your personal discount code to you.', 'abp-plugin' ); ?></p>
		</div>
	</div>
</div>

<div class="abp-offer-modal" id="<?php echo esc_attr( $offer_uid ); ?>" aria-hidden="true">
	<div class="abp-offer-overlay" data-abp-offer-close></div>
	<div class="abp-offer-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $offer_uid ); ?>_title">
		<button type="button" class="abp-offer-close" data-abp-offer-close aria-label="<?php esc_attr_e( 'Close', 'abp-plugin' ); ?>">&times;</button>
		<div class="abp-offer-dialog-head">
			<span class="abp-offer-dialog-icon">&#127873;</span>
			<span class="abp-offer-dialog-chip">
				<?php
				$claim_discount = '';
				foreach ( $offer_discounts as $d_key => $d_data ) {
					if ( ! empty( $d_data['claimable'] ) ) {
						$claim_discount = $d_data;
						break;
					}
				}
				?>
				<?php echo esc_html( $claim_discount ? $claim_discount['percent'] . '% Off - ' . $claim_discount['flag'] : __( 'Discount Offer', 'abp-plugin' ) ); ?>
			</span>
			<h3 id="<?php echo esc_attr( $offer_uid ); ?>_title"><?php esc_html_e( 'Claim Your Discount Offer', 'abp-plugin' ); ?></h3>
			<p><?php esc_html_e( 'Complete the form below and our team will email your personal discount code together with everything you need to redeem it.', 'abp-plugin' ); ?></p>
		</div>
		<div class="abp-offer-response" id="<?php echo esc_attr( $offer_uid ); ?>_response"></div>
		<form class="abp-offer-form" id="<?php echo esc_attr( $offer_uid ); ?>_form">
			<?php wp_nonce_field( 'abp_free_offer_nonce_action', 'abp_free_offer_nonce', false ); ?>
			<input type="hidden" name="cust_discount" id="<?php echo esc_attr( $offer_uid ); ?>_discount" value="<?php echo esc_attr( $claim_discount ? array_search( $claim_discount, $offer_discounts, true ) : '60' ); ?>">
			<div class="abp-offer-field">
				<label for="<?php echo esc_attr( $offer_uid ); ?>_plugin"><?php esc_html_e( 'Select Product', 'abp-plugin' ); ?> <span>*</span></label>
				<select name="cust_plugin" id="<?php echo esc_attr( $offer_uid ); ?>_plugin" required>
					<option value=""><?php esc_html_e( '- Choose a product -', 'abp-plugin' ); ?></option>
					<?php foreach ( $offer_plugins as $slug => $data ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $data['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="abp-offer-field">
				<label for="<?php echo esc_attr( $offer_uid ); ?>_name"><?php esc_html_e( 'Your Name', 'abp-plugin' ); ?> <span>*</span></label>
				<input type="text" name="cust_name" id="<?php echo esc_attr( $offer_uid ); ?>_name" placeholder="<?php esc_attr_e( 'Enter your full name', 'abp-plugin' ); ?>" required>
			</div>
			<div class="abp-offer-field">
				<label for="<?php echo esc_attr( $offer_uid ); ?>_email"><?php esc_html_e( 'Your Email', 'abp-plugin' ); ?> <span>*</span></label>
				<input type="email" name="cust_email" id="<?php echo esc_attr( $offer_uid ); ?>_email" placeholder="<?php esc_attr_e( 'you@example.com', 'abp-plugin' ); ?>" required>
			</div>
			<button type="submit" class="abp-offer-submit"><?php esc_html_e( 'Send Me My Discount Code', 'abp-plugin' ); ?></button>
			<p class="abp-offer-note"><?php esc_html_e( 'We use your details only to deliver your discount and to support you. No spam, ever.', 'abp-plugin' ); ?></p>
		</form>
	</div>
</div>

<style>
	div.abp_area .abp_free_offer { background:#0B0D10; }
	div.abp_area .abp-offer-head { text-align:center; max-width:760px; margin:0 auto; }
	div.abp_area .abp-offer-badge { display:inline-flex; align-items:center; gap:9px; padding:8px 18px; border-radius:30px; background:rgba(37,99,235,.15); border:1px solid rgba(37,99,235,.4); color:#93c5fd; font-size:11px; font-weight:800; letter-spacing:2px; text-transform:uppercase; }
	div.abp_area .abp-offer-badge-dot { width:7px; height:7px; border-radius:50%; background:#22c55e; box-shadow:0 0 0 4px rgba(34,197,94,.18); }
	div.abp_area .abp-offer-title { margin:22px 0 14px; color:#fff; font-size:40px; line-height:1.15; font-weight:800; letter-spacing:-1px; }
	div.abp_area .abp-offer-text { margin:0 auto; color:#9DA4AE; font-size:16px; line-height:1.75; }
	div.abp_area .abp-offer-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:26px; max-width:900px; margin:44px auto 0; }
	div.abp_area .abp-offer-card { position:relative; overflow:hidden; display:flex; flex-direction:column; align-items:center; text-align:center; padding:38px 30px 34px; border-radius:24px; background:radial-gradient(ellipse at top,#1b2340 0%,#101322 70%); border:1px solid rgba(255,255,255,.10); transition:transform .3s ease, border-color .3s ease, box-shadow .3s ease; }
	div.abp_area .abp-offer-card:hover { transform:translateY(-6px); border-color:rgba(37,99,235,.55); box-shadow:0 26px 50px rgba(0,0,0,.35); }
	div.abp_area .abp-offer-card--hot { border-color:rgba(37,99,235,.45); background:radial-gradient(ellipse at top,#1e2a52 0%,#0d1226 72%); }
	div.abp_area .abp-offer-flag { display:inline-block; padding:6px 15px; border-radius:20px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); color:#cbd5e1; font-size:10px; font-weight:800; letter-spacing:1.8px; text-transform:uppercase; }
	div.abp_area .abp-offer-card--hot .abp-offer-flag { background:rgba(37,99,235,.18); border-color:rgba(37,99,235,.45); color:#93c5fd; }
	div.abp_area .abp-offer-percent { margin:20px 0 4px; color:#fff; font-size:66px; line-height:1; font-weight:800; letter-spacing:-3px; }
	div.abp_area .abp-offer-percent span { font-size:30px; font-weight:700; vertical-align:super; margin-left:2px; }
	div.abp_area .abp-offer-card-title { margin:0 0 8px; color:#fff; font-size:19px; font-weight:700; }
	div.abp_area .abp-offer-card-text { margin:0 0 20px; max-width:330px; color:#9DA4AE; font-size:14px; line-height:1.7; }
	div.abp_area .abp-offer-code { display:inline-flex; align-items:center; gap:10px; padding:9px 10px 9px 18px; border-radius:11px; background:rgba(255,255,255,.06); border:1px dashed rgba(255,255,255,.28); }
	div.abp_area .abp-offer-code--none { padding:9px 18px; border-style:solid; border-color:rgba(37,99,235,.4); background:rgba(37,99,235,.12); }
	div.abp_area .abp-offer-code--none .abp-offer-code-text { font-size:13px; letter-spacing:.6px; text-transform:none; color:#93c5fd; }
	div.abp_area .abp-offer-code-text { color:#fff; font-size:19px; font-weight:800; letter-spacing:2px; }
	div.abp_area .abp-offer-code-copy { padding:6px 12px; border:none; border-radius:7px; background:rgba(255,255,255,.1); color:#cbd5e1; font-size:11px; font-weight:700; text-transform:none; cursor:pointer; transition:.2s; }
	div.abp_area .abp-offer-code-copy:hover { background:rgba(255,255,255,.2); color:#fff; }
	div.abp_area .abp-offer-list { display:flex; flex-direction:column; gap:11px; width:100%; margin:24px 0 28px; padding:0; list-style:none; text-align:left; }
	div.abp_area .abp-offer-list li { display:flex; align-items:center; gap:10px; color:#cbd5e1; font-size:13.5px; font-weight:600; }
	div.abp_area .abp-offer-check { flex:0 0 22px; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; background:rgba(37,99,235,.2); border:1px solid rgba(37,99,235,.45); color:#93c5fd; font-size:11px; }
	div.abp_area .abp-offer-actions { display:flex; justify-content:center; align-items:center; width:100%; margin-top:auto; }
	div.abp_area .abp-offer-cta { display:inline-flex !important; align-items:center; justify-content:center; gap:8px; margin:0 auto; padding:14px 30px; border:none; border-radius:10px; background:var(--abp_color_active); color:#fff; font-size:14.5px; font-weight:700; text-transform:none; text-align:center; text-decoration:none; cursor:pointer; transition:.25s; box-shadow:0 12px 26px rgba(14,107,183,.28); }
	div.abp_area .abp-offer-cta:hover { transform:translateY(-2px); box-shadow:0 16px 32px rgba(14,107,183,.38); }
	div.abp_area .abp-offer-cta--copy { background:#16a34a; box-shadow:0 12px 26px rgba(22,163,74,.28); }
	div.abp_area .abp-offer-cta--copy:hover { box-shadow:0 16px 32px rgba(22,163,74,.38); }
	div.abp_area .abp-offer-cta span { opacity:.6; }
	div.abp_area .abp-offer-footnote { max-width:760px; margin:30px auto 0; text-align:center; color:#767E8A; font-size:13px; line-height:1.7; }

	.abp-offer-modal { position:fixed; inset:0; z-index:99999; display:none; align-items:center; justify-content:center; padding:20px; }
	.abp-offer-modal.abp-offer-open { display:flex; }
	.abp-offer-overlay { position:absolute; inset:0; background:rgba(8,11,20,.72); backdrop-filter:blur(3px); }
	.abp-offer-dialog { position:relative; width:100%; max-width:480px; max-height:92vh; overflow-y:auto; background:#fff; border-radius:20px; padding:36px 34px 30px; box-shadow:0 40px 90px rgba(2,6,23,.5); animation:abpOfferPop .28s ease; box-sizing:border-box; }
	@keyframes abpOfferPop { from { opacity:0; transform:translateY(22px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
	.abp-offer-close { position:absolute; top:16px; right:18px; width:34px; height:34px; border:none; border-radius:50%; background:#f1f5f9; color:#475569; font-size:22px; line-height:1; cursor:pointer; transition:.2s; }
	.abp-offer-close:hover { background:#e2e8f0; color:#0f172a; }
	.abp-offer-dialog-head { text-align:center; margin-bottom:22px; }
	.abp-offer-dialog-icon { font-size:30px; }
	.abp-offer-dialog-chip { display:inline-block; margin-top:12px; padding:6px 15px; border-radius:20px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; font-size:11px; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; }
	.abp-offer-dialog-head h3 { margin:12px 0 8px; color:#0f172a; font-size:22px; font-weight:800; }
	.abp-offer-dialog-head p { margin:0; color:#64748b; font-size:13.5px; line-height:1.6; }
	.abp-offer-response { display:none; padding:13px 16px; border-radius:10px; margin-bottom:18px; font-size:13.5px; font-weight:600; text-align:center; }
	.abp-offer-field { margin-bottom:16px; }
	.abp-offer-field label { display:block; margin-bottom:7px; color:#334155; font-size:13px; font-weight:700; }
	.abp-offer-field label span { color:#dc2626; }
	.abp-offer-field input,
	.abp-offer-field select { width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:9px; background:#f8fafc; color:#0f172a; font-size:14px; box-sizing:border-box; transition:.2s; }
	.abp-offer-field input:focus,
	.abp-offer-field select:focus { outline:none; border-color:#2563eb; background:#fff; box-shadow:0 0 0 4px rgba(37,99,235,.1); }
	.abp-offer-submit { width:100%; margin-top:6px; padding:14px; border:none; border-radius:10px; background:#2563eb; color:#fff; font-size:15px; font-weight:700; text-transform:none; cursor:pointer; transition:.2s; }
	.abp-offer-submit:hover { background:#1d4ed8; }
	.abp-offer-submit:disabled { opacity:.65; cursor:default; }
	.abp-offer-note { margin:14px 0 0; color:#94a3b8; font-size:11.5px; text-align:center; }

	@media (max-width:900px) {
		div.abp_area .abp-offer-grid { grid-template-columns:1fr; max-width:460px; }
	}
	@media (max-width:700px) {
		div.abp_area .abp-offer-title { font-size:28px; }
		div.abp_area .abp-offer-text { font-size:14.5px; }
		div.abp_area .abp-offer-card { padding:30px 20px 28px; border-radius:20px; }
		div.abp_area .abp-offer-percent { font-size:54px; }
		.abp-offer-dialog { padding:30px 22px 24px; border-radius:16px; }
	}
	@media (max-width:420px) {
		div.abp_area .abp-offer-title { font-size:24px; }
		div.abp_area .abp-offer-percent { font-size:46px; }
		div.abp_area .abp-offer-cta { width:100%; }
	}
</style>

<script>
	(function ($) {
		"use strict";
		$(function () {
			var $modal = $('#<?php echo esc_js( $offer_uid ); ?>');
			var $form = $('#<?php echo esc_js( $offer_uid ); ?>_form');
			var $response = $('#<?php echo esc_js( $offer_uid ); ?>_response');

			$('[data-abp-offer-open="<?php echo esc_js( $offer_uid ); ?>"]').on('click', function () {
				$modal.addClass('abp-offer-open').attr('aria-hidden', 'false');
				$response.hide();
			});

			$modal.find('[data-abp-offer-close]').on('click', function () {
				$modal.removeClass('abp-offer-open').attr('aria-hidden', 'true');
			});

			$(document).on('keyup.abpOffer', function (e) {
				if (e.key === 'Escape') {
					$modal.removeClass('abp-offer-open').attr('aria-hidden', 'true');
				}
			});

			$('[data-abp-offer-copy]').on('click', function () {
				var code = $(this).attr('data-abp-offer-copy');
				var $btn = $(this);
				var $label = $btn.find('span').length ? $btn.find('span') : $btn;
				var original = $label.text();
				var done = function () {
					$label.text('<?php echo esc_js( __( 'Copied!', 'abp-plugin' ) ); ?>');
					setTimeout(function () { $label.text(original); }, 1600);
				};
				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText(code).then(done, done);
				} else {
					var $tmp = $('<input type="text" />').val(code).appendTo('body').select();
					try { document.execCommand('copy'); } catch (err) {}
					$tmp.remove();
					done();
				}
			});

			$form.on('submit', function (e) {
				e.preventDefault();
				var form = this;
				var fd = new FormData(form);
				fd.append('action', 'abp_free_offer_submit');

				var $btn = $form.find('.abp-offer-submit');
				$response.hide();
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Sending...', 'abp-plugin' ) ); ?>');

				$.ajax({
					url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
					type: 'POST',
					data: fd,
					contentType: false,
					processData: false,
					dataType: 'json',
					success: function(res) {
						var message = (res && res.data && res.data.message) ? res.data.message : '<?php echo esc_js( __( 'Unexpected response from server. Please try again.', 'abp-plugin' ) ); ?>';
						$response.show().text(message);
						if (res && res.success) {
							$response.css({ background: '#dcfce7', color: '#166534', border: '1px solid #bbf7d0' });
							form.reset();
						} else {
							$response.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fca5a5' });
						}
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Send Me My Discount Code', 'abp-plugin' ) ); ?>');
					},
					error: function() {
						$response.show().text('<?php echo esc_js( __( 'We could not reach the server. Please try again in a moment.', 'abp-plugin' ) ); ?>').css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fca5a5' });
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Send Me My Discount Code', 'abp-plugin' ) ); ?>');
					}
				});
			});
		});
	}(jQuery));
</script>
