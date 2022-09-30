<?php

class WC_SD_Contact_Payment_Gateway extends WC_Payment_Gateway
{

	private $order_status;

	public function __construct()
	{
		$this->id = 'sd_contact_payment';
		$this->method_title = __('ShopDunk Contact Payment', 'woocommerce-other-payment-gateway');
		$this->title = __('Liên hệ thanh toán', 'woocommerce-other-payment-gateway');
		$this->has_fields = false;
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
				'label' 		=> __('Enable Contact Payment', 'woocommerce-other-payment-gateway'),
				'default' 		=> 'yes'
			),

			'title' => array(
				'title' 		=> __('Method Title', 'woocommerce-other-payment-gateway'),
				'type' 			=> 'text',
				'description' 	=> __('This controls the title', 'woocommerce-other-payment-gateway'),
				'default'		=> __('Liên hệ thành toán', 'woocommerce-other-payment-gateway'),
				'desc_tip'		=> true,
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
	}

	public function validate_fields()
	{
		// if ($this->text_box_required === 'no') {
		// 	return true;
		// }

		// $textbox_value = (isset($_POST['other_payment-admin-note'])) ? trim($_POST['other_payment-admin-note']) : '';
		// if ($textbox_value === '') {
		// 	wc_add_notice(__('Please, complete the payment information.', 'woocommerce-custom-payment-gateway'), 'error');
		// 	return false;
		// }
		// return true;
	}

	public function process_payment($order_id)
	{
		global $woocommerce;
		$order = new WC_Order($order_id);

		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status('pending', __('Awaiting payment', 'woocommerce-other-payment-gateway'));
		// Reduce stock levels
		// wc_reduce_stock_levels($order_id);

		// Remove cart
		$woocommerce->cart->empty_cart();
		// Return thankyou redirect

		WC()->session->set('sd_checkout_step', 'thank-toan');
		// sd_send_order_to_odoo_webhook($order->get_id(), $order);
		//  $order->get_checkout_payment_url(false)

		return array(
			'result' => 'success',
			//'id' => $order->id,
			//'_odoo' => get_get_payload_for_odoo( $order ),
			'redirect' => add_query_arg(['id'=> $order->get_id(), 'action'=> 'sd_checkout_handle_action'], admin_url('admin-ajax.php')),
			// 'redirect' => $order->get_checkout_payment_url(false)
		);
	}
}
