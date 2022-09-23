<?php

class WC_SD_Contact_Payment_Gateway extends WC_Payment_Gateway
{

	private $order_status;

	public function __construct()
	{
		$this->id = 'sd_contact_payment';
		$this->method_title = __('Contact Payment', 'woocommerce-other-payment-gateway');
		$this->title = __('Contact Payment', 'woocommerce-other-payment-gateway');
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
				'default'		=> __('Contact Payment', 'woocommerce-other-payment-gateway'),
				'desc_tip'		=> true,
			),
			'description' => array(
				'title' => __('Customer Message', 'woocommerce-other-payment-gateway'),
				'type' => 'textarea',
				'css' => 'width:500px;',
				'default' => 'None of the other payment options are suitable for you? please drop us a note about your favourable payment option and we will contact you as soon as possible.',
				'description' 	=> __('The message which you want it to appear to the customer in the checkout page.', 'woocommerce-other-payment-gateway'),
			),
			'text_box_required' => array(
				'title' 		=> __('Make the text field required', 'woocommerce-other-payment-gateway'),
				'type' 			=> 'checkbox',
				'label' 		=> __('Make the text field required', 'woocommerce-other-payment-gateway'),
				'default' 		=> 'no'
			),
			'hide_text_box' => array(
				'title' 		=> __('Hide The Payment Field', 'woocommerce-other-payment-gateway'),
				'type' 			=> 'checkbox',
				'label' 		=> __('Hide', 'woocommerce-other-payment-gateway'),
				'default' 		=> 'no',
				'description' 	=> __('If you do not need to show the text box for customers at all, enable this option.', 'woocommerce-other-payment-gateway'),
			),
			'order_status' => array(
				'title' => __('Order Status After The Checkout', 'woocommerce-other-payment-gateway'),
				'type' => 'select',
				'options' => wc_get_order_statuses(),
				'default' => 'wc-on-hold',
				'description' 	=> __('The default order status if this gateway used in payment.', 'woocommerce-other-payment-gateway'),
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



		// some notes to customer (replace true with false to make it private)
		// $order->add_order_note('Hey, your order is paid! Thank you!', true);


		// Remove cart
		// $woocommerce->cart->empty_cart();
		// Return thankyou redirect

		WC()->session->set('sd_checkout_step', 'thank-toan');

		$url =  add_query_arg([
			'pay_for_order' => $order->get_id(),
			'order-pay' => $order->get_id(),
			'key' =>  $order->get_order_key()
		], $this->get_return_url($order));

		return array(
			'result' => 'success',
			'redirect' =>  $order->get_checkout_payment_url(false),
			// 'redirect' => home_url('/?ok=true')
		);
	}
}
