<?php

class WC_SD_Bank_Transfer_Payment_Gateway extends WC_Payment_Gateway
{

	private $order_status;

	public function __construct()
	{
		$this->id = 'sd_other_payment';
		$this->method_title = __('Custom Payment', 'woocommerce-other-payment-gateway');
		$this->title = __('Custom Payment', 'woocommerce-other-payment-gateway');
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option('enabled');
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->hide_text_box = $this->get_option('hide_text_box');
		$this->text_box_required = $this->get_option('text_box_required');
		$this->order_status = $this->get_option('order_status');


		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
	}

	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __('Enable/Disable', 'woocommerce-other-payment-gateway'),
				'type' 			=> 'checkbox',
				'label' 		=> __('Enable Custom Payment', 'woocommerce-other-payment-gateway'),
				'default' 		=> 'yes'
			),
			'title' => array(
				'title' 		=> __('Method Title', 'woocommerce-other-payment-gateway'),
				'type' 			=> 'text',
				'description' 	=> __('This controls the title', 'woocommerce-other-payment-gateway'),
				'default'		=> __('Chuyển khoản ngân hàng', 'woocommerce-other-payment-gateway'),
				'desc_tip'		=> true,
			),
			'description' => array(
				'title' => __('Customer Message', 'woocommerce-other-payment-gateway'),
				'type' => 'textarea',
				'css' => 'width:500px;',
				'default' => '',
				'description' 	=> __('The message which you want it to appear to the customer in the checkout page.', 'woocommerce-other-payment-gateway'),
			),
			
		);
	}
	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_options()
	{
?>
		<h3><?php _e('Custom Payment Settings', 'woocommerce-other-payment-gateway'); ?></h3>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<table class="form-table">
						<?php $this->generate_settings_html(); ?>
					</table>
					<!--/.form-table-->
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">

						<div class="postbox ">
							<h3 class="hndle"><span><i class="dashicons dashicons-update"></i>&nbsp;&nbsp;Upgrade to Pro</span></h3>
							<hr>
							<div class="inside">
								<div class="support-widget">
									ewqewqeww

								</div>
							</div>
						</div>



					</div>
				</div>
			</div>
		</div>

	<?php
	}

	public function validate_fields()
	{
		if ($this->text_box_required === 'no') {
			return true;
		}

		$textbox_value = (isset($_POST['other_payment-admin-note'])) ? trim($_POST['other_payment-admin-note']) : '';
		if ($textbox_value === '') {
			wc_add_notice(__('Please, complete the payment information.', 'woocommerce-custom-payment-gateway'), 'error');
			return false;
		}
		return true;
	}

	public function process_payment($order_id)
	{
		global $woocommerce;
		$order = new WC_Order($order_id);

		// Mark as on-hold (we're awaiting the cheque)
		// $order->update_status($this->order_status, __('Awaiting payment', 'woocommerce-other-payment-gateway'));
		// Reduce stock levels
		wc_reduce_stock_levels($order_id);

		// we received the payment
		$order->payment_complete();

		// some notes to customer (replace true with false to make it private)
		// $order->add_order_note('Hey, your order is paid! Thank you!', true);

		// Empty cart
		$woocommerce->cart->empty_cart();


		if (isset($_POST[$this->id . '-admin-note']) && trim($_POST[$this->id . '-admin-note']) != '') {
			$order->add_order_note(esc_html($_POST[$this->id . '-admin-note']), 1);
		}

		// Remove cart
		$woocommerce->cart->empty_cart();
		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url($order),
			// 'redirect' => home_url('/?ok=true')
		);
	}

	public function payment_fields()
	{

		global $wp;

		$order = null;

		// if (isset($wp->query_vars['order-pay'])) {
		// 	$order = wc_get_order($wp->query_vars['order-pay']);
		// }
		global $sd_payment_order_extra;
	?>
		<fieldset>
			<p class="form-row form-row-wide">
				<label for="<?php echo $this->id; ?>-admin-note"><?php echo ($this->description); ?> <?php if ($this->text_box_required === 'yes') : ?> <span class="required">*</span> <?php endif; ?></label>
				<?php
				if ($sd_payment_order_extra) {

					$account_number =  $sd_payment_order_extra['store_data']['account'];
				?>
					<p>
						<img src="https://img.vietqr.io/image/970407-<?php echo esc_attr($account_number) ?>-HHxRqO.jpg?amount=1000000&addInfo=" alt="" />
					</p>
					<p>
						Số tài khoản: <?php echo esc_html($account_number); ?>
					</p>
					<p>
						Ngân hàng: TECHCOMBANK - CN ĐÔNG ĐÔ, HÀ NỘI
					</p>
					<p>
						Chủ tài khoản: CÔNG TY CỔ PHẦN HESMAN VIỆT NAM.
					</p>
					<p>
						Nội dung chuyển khoản: <?php echo esc_html($sd_payment_order_extra['payment_message']); ?>
					</p>

				<?php
				}

		?>
		</p>
		<div class="clear"></div>
		</fieldset>
<?php
	}
}
