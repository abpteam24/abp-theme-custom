<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$offer_plugins   = ABP_Shortcodes::free_offer_plugins();
	$offer_discounts = ABP_Shortcodes::free_offer_discounts();
	$offer_instant   = null;
	$offer_claim     = null;
	foreach ( $offer_discounts as $offer_row ) {
		if ( ! empty( $offer_row['claimable'] ) ) {
			$offer_claim = $offer_row;
		} elseif ( ! empty( $offer_row['code'] ) ) {
			$offer_instant = $offer_row;
		}
	}
	$offer_claim_key = $offer_claim ? (string) array_search( $offer_claim, $offer_discounts, true ) : '';
?>
<div class="abp-offer-bar" id="abp-offer-bar">
	<div class="abp-offer-bar-inner">
		<span class="abp-offer-bar-badge"><span class="abp-offer-bar-dot"></span><?php esc_html_e( 'Limited Time Offer', 'abp-plugin' ); ?></span>
		<h2 class="abp-offer-bar-title"><?php esc_html_e( 'Exclusive Discounts On All Products', 'abp-plugin' ); ?></h2>
		<div class="abp-offer-bar-actions">
			<?php if ( $offer_instant ) : ?>
				<span class="abp-offer-bar-code">
					<span class="abp-offer-bar-code-label"><?php esc_html_e( 'Use code', 'abp-plugin' ); ?></span>
					<span class="abp-offer-bar-code-value"><?php echo esc_html( $offer_instant['code'] ); ?></span>
					<button type="button" class="abp-offer-bar-copy" data-abp-offer-copy="<?php echo esc_attr( $offer_instant['code'] ); ?>"><?php esc_html_e( 'Copy', 'abp-plugin' ); ?></button>
				</span>
			<?php endif; ?>
			<?php if ( $offer_claim ) : ?>
				<button type="button" class="abp-offer-bar-cta" data-abp-offer-open>
					<span class="abp-offer-bar-cta-off"><?php echo esc_html( $offer_claim['percent'] ); ?>% <?php esc_html_e( 'Off', 'abp-plugin' ); ?></span>
					<span><?php esc_html_e( 'Claim Offer', 'abp-plugin' ); ?></span>
					<i class="fa-solid fa-arrow-right-long"></i>
				</button>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="abp-offer-modal" id="abp-offer-modal" aria-hidden="true">
	<div class="abp-offer-overlay" data-abp-offer-close></div>
	<div class="abp-offer-dialog" role="dialog" aria-modal="true" aria-labelledby="abp-offer-title">
		<button type="button" class="abp-offer-close" data-abp-offer-close aria-label="<?php esc_attr_e( 'Close', 'abp-plugin' ); ?>">&times;</button>
		<div class="abp-offer-dialog-head">
			<span class="abp-offer-dialog-icon">&#127873;</span>
			<span class="abp-offer-dialog-chip">
				<?php echo esc_html( $offer_claim ? $offer_claim['percent'] . '% Off - ' . $offer_claim['flag'] : __( 'Discount Offer', 'abp-plugin' ) ); ?>
			</span>
			<h3 id="abp-offer-title"><?php esc_html_e( 'Claim Your Discount Offer', 'abp-plugin' ); ?></h3>
			<p><?php esc_html_e( 'Complete the form below and our team will email your personal discount code together with everything you need to redeem it.', 'abp-plugin' ); ?></p>
		</div>
		<div class="abp-offer-response" id="abp-offer-response"></div>
		<form class="abp-offer-form" id="abp-offer-form">
			<?php wp_nonce_field( 'abp_free_offer_nonce_action', 'abp_free_offer_nonce', false ); ?>
			<input type="hidden" name="cust_discount" id="abp-offer-discount" value="<?php echo esc_attr( $offer_claim_key ); ?>">
			<div class="abp-offer-field">
				<label for="abp-offer-plugin"><?php esc_html_e( 'Select Product', 'abp-plugin' ); ?> <span>*</span></label>
				<select name="cust_plugin" id="abp-offer-plugin" required>
					<option value=""><?php esc_html_e( '- Choose a product -', 'abp-plugin' ); ?></option>
					<?php foreach ( $offer_plugins as $slug => $data ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $data['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="abp-offer-field">
				<label for="abp-offer-name"><?php esc_html_e( 'Your Name', 'abp-plugin' ); ?> <span>*</span></label>
				<input type="text" name="cust_name" id="abp-offer-name" placeholder="<?php esc_attr_e( 'Enter your full name', 'abp-plugin' ); ?>" required>
			</div>
			<div class="abp-offer-field">
				<label for="abp-offer-email"><?php esc_html_e( 'Your Email', 'abp-plugin' ); ?> <span>*</span></label>
				<input type="email" name="cust_email" id="abp-offer-email" placeholder="<?php esc_attr_e( 'you@example.com', 'abp-plugin' ); ?>" required>
			</div>
			<button type="submit" class="abp-offer-submit"><?php esc_html_e( 'Send Me My Discount Code', 'abp-plugin' ); ?></button>
			<p class="abp-offer-note"><?php esc_html_e( 'We use your details only to deliver your discount and to support you. No spam, ever.', 'abp-plugin' ); ?></p>
		</form>
	</div>
</div>
