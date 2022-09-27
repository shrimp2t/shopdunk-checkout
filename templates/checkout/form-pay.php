<?php

/**
 * Pay for order form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;

/**
 * @see WC_Order
 */
$totals = $order->get_order_item_totals(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
// $order_button_text = 'Thanh toán';
$extra = sd_get_order_extra_data($order);
$GLOBALS['sd_payment_order_extra'] = $extra;

?>
<form id="order_review" method="post">

	<div class="checkout-box  payment-header">
		<div class="payment-icon-success"></div>
		<h2>Đặt hàng thành công.</h2>
		<p><?php echo esc_html(sprintf('Cám ơn %s %s đã cho ShopDunk cơ hội được phục vụ', $extra['billing_title'], $order->get_billing_first_name())) ?></p>
	</div>

	<div class="checkout-box  payment-billing">
		<p>ID đơng hàng: <strong><?php echo esc_html($extra['odoo_order_id']); ?></strong></p>
		<?php if ($extra['shipping_method'] == 'ship') { ?>
			<p>Giao hàng tận nơi: <strong><?php echo esc_html($extra['full_shipping_address']); ?></strong></p>
		<?php } else { ?>
			<p>Nhận tại cửa hàng: <strong><?php echo esc_html($extra['store_name']); ?></strong></p>
		<?php } ?>
		<p>Tổng tiền: <strong><?php echo wc_price($order->get_total()); ?></strong></p>
	</div>

	<?php if (in_array($order->get_status(), ['pending'])) { ?>
		<div class="checkout-box  payment-order-status">Đơn hàng chưa được thanh toán.</div>
	<?php } ?>


	<?php
	$allow_pay = sd_allow_partial_pay($order);

	$part_amount =  get_option('sd_partial_order_amount');

	?>
	<?php if ($allow_pay) { ?>
		<div class="checkout-box payment-order-amount">
			<h2>Chọn số tiền muốn thanh toán</h2>
			<div>
				<label class="amount-box">
					<input type="radio" checked name="pay_amount" value="part">
					<span>Thanh toán trước</span>
					<span class="amount"><?php echo wc_price($part_amount); ?></span>
				</label>
				<label class="amount-box">
					<input type="radio" name="pay_amount" value="all">
					<span>Toàn bộ đơn hàng</span>
					<span class="amount"><?php echo wc_price($order->get_total()); ?></span>
				</label>
			</div>
		</div>
	<?php } ?>


	<div id="checkout-payment-gate-ways" class="checkout-box  payment-box">
		<h2>Phương thức thanh toán</h2>
		<div id="payment">
			<?php if ($order->needs_payment()) : ?>
				<ul class="wc_payment_methods payment_methods methods">
					<?php
					if (!empty($available_gateways)) {
						foreach ($available_gateways as $gateway) {
							wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
						}
					} else {
						echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters('woocommerce_no_available_payment_methods_message', esc_html__('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')) . '</li>'; // @codingStandardsIgnoreLine
					}
					?>
				</ul>
			<?php endif; ?>
		</div>
	</div>


	<div class="fcheckout-box  form-row">
		<input type="hidden" name="woocommerce_pay" value="1" />

		<?php wc_get_template('checkout/terms.php'); ?>

		<?php do_action('woocommerce_pay_order_before_submit'); ?>

		<?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="button alt" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // @codingStandardsIgnoreLine 
		?>
		<?php do_action('woocommerce_pay_order_after_submit'); ?>
		<?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
	</div>


	<div class="payment-order-items">
		<h2>Sản phẩm đã đặt</h2>
		<table class="shop_table">

			<tbody>
				<?php if (count($order->get_items()) > 0) : ?>
					<?php foreach ($order->get_items() as $item_id => $item) : ?>
						<?php
						if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
							continue;
						}
						?>
						<tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
							<td class="product-name">
								<?php
								echo wp_kses_post(apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false));

								do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);

								wc_display_item_meta($item);

								do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
								?>
							</td>
							<td class="product-quantity"><?php echo apply_filters('woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', esc_html($item->get_quantity())) . '</strong>', $item); ?></td><?php // @codingStandardsIgnoreLine 
																																																															?>
							<td class="product-subtotal"><?php echo $order->get_formatted_line_subtotal($item); ?></td><?php // @codingStandardsIgnoreLine 
																														?>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

</form>