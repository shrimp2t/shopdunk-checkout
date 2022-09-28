<?php

/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

defined('ABSPATH') || exit;


$show_purchase_note    = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();

$totals = $order->get_order_item_totals();
?>

<div class="sd-thankyou-wrap woocommerce-order">

	<?php
	if ($order) :

		$extra = sd_get_order_extra_data($order);
		// $GLOBALS['sd_checkout_order_extra'] = $extra;
		do_action('woocommerce_before_thankyou', $order->get_id());
	?>

		<div class="thankyou-box thank-box order-stt-<?php echo esc_attr($order->get_status()); ?>">
			<div class="icon-success"></div>

			<?php if ($order->has_status('failed')) : ?>
				<h2>Mua hàng thất bại!</h2>
				<p>Chúng tôi rất tiếc việc mua hàng đã thất bại. Xin thử lại!</p>
				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
					<a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
				</p>
			<?php else : ?>
				<h2>Mua hàng thành công!</h2>
				<p><?php
					echo esc_html(sprintf('Cám ơn %s %s đã tin tưởng và tạo cơ hội để chúng tôi được phục vụ.', $extra['billing_title'], $order->get_billing_first_name())) ?></p>
				</p>
			<?php endif; ?>

		</div>

		<div class="order-box-wrap">


			<div class="order-box">
				<div class="order-box-l">Thông tin đơn hàng</div>
				<div class="order-box-v">
					<div><strong><?php echo esc_html(trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name())) ?></strong></div>
					<div>SĐT: <?php echo esc_html(trim($order->get_billing_phone())) ?></div>
					<div>#: <strong><?php echo esc_html($extra['odoo_order_id'] ? $extra['odoo_order_id'] : $extra['id']); ?></strong></div>
					<div><?php echo wc_format_datetime($order->get_date_created()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
							?></div>
				</div>
			</div>

			<div class="order-box">
				<div class="order-box-l">Sản phẩm đã thanh toán</div>
				<div class="order-box-v">
					<?php
					$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
					foreach ($order_items as $item_id => $item) {
						$product = $item->get_product();

						wc_get_template(
							'order/order-thankyou-details-item.php',
							array(
								'order'              => $order,
								'item_id'            => $item_id,
								'item'               => $item,
								'show_purchase_note' => $show_purchase_note,
								'purchase_note'      => $product ? $product->get_purchase_note() : '',
								'product'            => $product,
							)
						);
					}
					?>
				</div>
			</div>

			<?php if ($show_purchase_note && $product->get_purchase_note()) : ?>
				<div class="order-box note">
					<div class="order-box-l">Ghi chú</div>
					<div class="order-box-v">
						<?php echo wpautop(do_shortcode(wp_kses_post($product->get_purchase_note()))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?>
					</div>
				</div>
			<?php endif; ?>

			<?php
			$payment = $totals['payment_method'];
			?>

			<div class="order-box note">
				<div class="order-box-l">Phương thức thanh toán</div>
				<div class="order-box-v">
					<?php echo esc_html($payment['value']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					?>
				</div>
			</div>

			<div class="order-box note">
				<div class="order-box-l">Số tiền thanh toán</div>
				<div class="order-box-v">
					<?php echo $totals['order_total']['value']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					?>
				</div>
			</div>

			<?php if (in_array($order->get_status(), ['partial-processing', 'partial-payment'])) { ?>
				<div class="order-box note">
					<div class="order-box-l">Số tiền đã cọc</div>
					<div class="order-box-v">
						<?php echo wc_price($extra['pay_amount']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?>
					</div>
				</div>
			<?php } ?>


			<div class="order-box address">
				<div class="order-box-l">Địa chỉ nhận hàng</div>
				<div class="order-box-v">
					<?php
					if ($extra['shipping_method'] == 'ship') {
						echo esc_html($extra['full_shipping_address']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					} else {
						echo esc_html($extra['store__address']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					}
					?>
				</div>
			</div>


			<?php if ($extra['secondary_check']) : ?>
				<div class="order-box address">
					<div class="order-box-l">Sản phẩm thay thế</div>
					<div class="order-box-v">
						<?php for ($i = 1; $i <= 2; $i++) :
							if ( $extra['secondary_p'.$i.'_name']   ):
							?>
							<div class="secondary-product">
								<div class="n">Sản phẩm <?php echo $i; ?>: <strong><?php echo esc_html( $extra['secondary_p'.$i.'_name'] ); ?></strong></div>
								<div class="c">Màu: <span><?php echo esc_html( $extra['secondary_p'.$i.'_color'] ); ?></span></div>
								<div class="s">Dung lượng: <span><?php echo esc_html( $extra['secondary_p'.$i.'_storage'] ); ?></span></div>
							</div>
						<?php endif; ?>
						<?php endfor; ?>
					</div>
				</div>
			<?php endif; ?>


			<?php if ($extra['more_shipping_info']) : ?>
				<div class="order-box address">
					<div class="order-box-l">Người nhận hàng</div>
					<div class="order-box-v">
						<div><strong><?php echo esc_html($order->get_shipping_first_name()) ?></strong></div>
						<div>SĐT: <strong><?php echo esc_html($order->get_shipping_phone()) ?></strong></div>
					</div>
				</div>
			<?php endif; ?>


			<?php if ($extra['vat_check']) : ?>
				<div class="order-box vat-box">
					<div class="order-box-l">Xuất hóa đơn đỏ</div>
					<div class="order-box-v">
						<div>Công ty: <strong><?php echo esc_html($extra['vat_cty']); ?></strong></div>
						<div>Địa chỉ: <strong><?php echo esc_html($extra['vat_address']); ?></strong></div>
						<div>Mã số thuế: <strong><?php echo esc_html($extra['vat_tax_num']); ?></strong></div>
					</div>
				</div>
			<?php endif; ?>



			<div class="order-box noti">
				<div class="order-box-c">
					<p>Lưu ý*: Quý khách vui lòng lưu lại thông tin để thuận tiện cho việc nhận hàng.<br />
						ShopDunk sẽ liên hệ sớm nhất đến Quý khách ngay khi có sản phẩm.<br />
						Số tiền đặt cọc của quý khách sẽ được trừ thằng vào giá sản phẩm khi Quý khách nhận hàng và thanh toán.
					</p>
				</div>
			</div>



		</div>

	<?php else : ?>
		<p>Đơn hàng không tồn tại.</p>
	<?php endif; ?>

</div>