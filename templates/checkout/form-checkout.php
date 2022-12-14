<?php

/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_checkout_form', $checkout);


// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}

wc_get_template('cart/cart.php');

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

	<div class="checkout-section">
		<?php if ($checkout->get_checkout_fields()) : ?>

			<?php do_action('woocommerce_checkout_before_customer_details'); ?>

			<div class="col2-set-a" id="customer_details">

				<?php
				if (sd_has_allow_prepay_items()) :
					$fields = $checkout->get_checkout_fields('secondary'); ?>
					<?php if ($fields) : ?>
						<div class="sd-products-secondary-fields__field-wrapper">
							<?php

							foreach ($fields as $key => $field) {
								woocommerce_form_field($key, $field, $checkout->get_value($key));
							}
							?>
						</div>
				<?php
					endif;
				endif;
				?>

				<?php do_action('woocommerce_checkout_billing'); ?>
				<?php do_action('woocommerce_checkout_shipping'); ?>

			</div>

			<?php do_action('woocommerce_checkout_after_customer_details'); ?>

			<?php $fields = $checkout->get_checkout_fields('more'); ?>
			<?php if ($fields) : ?>
				<div class="sd-more-fields__field-wrapper">
					<?php
					foreach ($fields as $key => $field) {
						woocommerce_form_field($key, $field, $checkout->get_value($key));
					}
					?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

		<?php /*
		<h3 id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h3>
		<?php do_action('woocommerce_checkout_before_order_review'); ?>
		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php do_action('woocommerce_checkout_order_review'); ?>
		</div>
		*/ ?>


	</div>

	<div class="checkout-section">

	<?php 
	
	do_action('woocommerce_checkout_after_order_review');
	woocommerce_checkout_payment();
	
	?>

	</div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>


<script>
	jQuery(function($) {
		$(document.body).on('update_checkout', function() {
			console.log('Update_Checkout');
			// $(document).trigger('wc_update_cart');
		});

		$(document.body).on('removed_coupon_in_checkout', function() {
			$('.woocommerce-form-coupon').removeAttr('style');
		});
	});
</script>