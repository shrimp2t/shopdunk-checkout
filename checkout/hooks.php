<?php


add_action('wp_enqueue_scripts', function () {
	if (is_checkout()) {
		wp_enqueue_script('wc-cart');
		wp_enqueue_style('sd-checkout', SD_CO_URL . '/assets/css/checkout.css');
		wp_enqueue_script('sd-checkout', SD_CO_URL . '/assets/js/checkout.js', ['jquery'], false, true);

		$stores = sd_get_data_stores();
		wp_localize_script('sd-checkout', 'SD_Checkout', [
			'stores' => $stores,
			'provinces' => sd_get_data_provinces(),
			'quan_huyen' => sd_get_data_quan_huyen(),
			'phuong_xa' => sd_get_data_phuong_xa(),
		]);
	}
}, 999);


// woocommerce_order_review
// woocommerce_checkout_payment
// woocommerce_checkout_payment();
// add_filter('woocommerce_order_needs_payment', '__return_false', 999);

/**
 * Undocumented function
 *
 * @param [type] $url
 * @param WC_Order $order
 * @return void
 */
function sd_checkout_redirect_payment_page($url, $order)
{

	WC()->session->set('sd_checkout_step', 'thank-toan');

	return $order->get_checkout_payment_url(true);
}

// add_filter('woocommerce_checkout_no_payment_needed_redirect', 'sd_checkout_redirect_payment_page', 999, 2);

// remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);



add_filter('woocommerce_available_payment_gateways', function ($gateways) {
	global $wp;

	if (!isset($wp->query_vars['order-pay']) || !$wp->query_vars['order-pay']) {
		$new_g = $gateways['sd_contact_payment'];
		return [
			'sd_contact_payment' => $new_g
		];
	} else {
		unset($gateways['sd_contact_payment']);
	}

	return $gateways;
});




/**
 * Change Woocommerce Template
 *
 * @param [type] $template
 * @param [type] $template_name
 * @param [type] $args
 * @param [type] $template_path
 * @param [type] $default_path
 * @return void
 */
function sd_change_payment_template($template, $template_name, $args, $template_path, $default_path)
{

	$file = SD_CO_PATH . '/templates/' . $template_name;
	if (file_exists($file)) {
		return $file;
	}

	return $template;
}
add_filter('wc_get_template', 'sd_change_payment_template', 99, 5);



add_filter('woocommerce_valid_order_statuses_for_payment', 'sd_custom_status_valid_for_payment', 10, 2);
function sd_custom_status_valid_for_payment($statuses, $order)
{

	// Registering the custom status as valid for payment
	// $statuses[] = 'partial-payment';
	// $statuses[] = 'partial-processing';

	return $statuses;
}


add_filter('wc_order_statuses', 'sd_custom_order_status');
function sd_custom_order_status($order_statuses)
{
	$order_statuses['wc-partial-payment'] = _x('Partial payment', 'Order status', 'woocommerce');
	$order_statuses['wc-partial-processing'] = _x('Processing partial payment', 'Order status', 'woocommerce');
	return $order_statuses;
}

function sd_woocommerce_register_shop_order_post_statuses($status)
{
	$status['wc-partial-payment'] = array(
		'label'                     => _x('Partial payment', 'Order status', 'woocommerce'),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: number of orders */
		'label_count'               => _n_noop('Partial payment <span class="count">(%s)</span>', 'Partial payment <span class="count">(%s)</span>', 'woocommerce'),
	);

	$status['wc-partial-processing'] = array(
		'label'                     => _x('Processing partial payment', 'Order status', 'woocommerce'),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: number of orders */
		'label_count'               => _n_noop('Processing partial payment <span class="count">(%s)</span>', 'Processing partial payment <span class="count">(%s)</span>', 'woocommerce'),
	);
	return $status;
}

add_filter('woocommerce_register_shop_order_post_statuses', 'sd_woocommerce_register_shop_order_post_statuses');



/**
 * @see woocommerce_form_field()
 */
function sd_checkout_fields($groups)
{

	// $groups['billing']['label'] ='Thông tin khách hàng';
	// foreach ($groups  as $id => $f) {
	// 	var_dump($id);
	// 	var_dump($f);
	// }

	$groups['billing']['billing_title'] = [
		'label' => "Xưng danh",
		'type' => "radio",
		'options' => [
			'Anh' => 'Anh',
			'Chị' => 'Chị',
		],
		'class' => 'form-row-wide',
		'default' => 'Anh',
		'value' => 'Anh',
		'required' => true,
		'priority' => 4,
	];

	$groups['billing']['billing_first_name']['class'] = 'form-row-wide';
	$groups['shipping']['shipping_first_name']['class'] = 'form-row-wide';
	$groups['shipping']['shipping_address_1']['required'] = false;
	$groups['shipping']['shipping_state']['required'] = false;
	$groups['billing']['billing_email']['required'] = false;

	$groups['billing']['billing_country']['type'] = 'hidden';
	$groups['billing']['billing_country']['value'] = 'VN';

	unset($groups['billing']['billing_last_name']);
	unset($groups['billing']['billing_company']);

	unset($groups['billing']['billing_address_2']);
	unset($groups['billing']['billing_postcode']);
	unset($groups['billing']['billing_state']);
	unset($groups['billing']['billing_address_1']);
	unset($groups['billing']['billing_city']);


	unset($groups['shipping']['shipping_first_name']);
	unset($groups['shipping']['shipping_last_name']);
	unset($groups['shipping']['shipping_company']);
	unset($groups['shipping']['shipping_country']);
	unset($groups['shipping']['shipping_address_2']);
	unset($groups['shipping']['shipping_postcode']);
	unset($groups['shipping']['shipping_city']);
	unset($groups['shipping']['shipping_state']);


	$groups['shipping']['shipping_sd_method'] = [
		'label' => "Nhận hàng",
		'type' => "radio",
		'class' => 'form-row-wide',
		'options' => [
			'store' => 'Nhận tại cửa hàng',
			'ship' => 'Giao tận nơi',
		],
		'default' => 'store',
		'required' => false,
		'priority' => 5,
	];

	$stores = sd_get_data_stores();
	// $options = sd_array_to_select_options($stores, 'address');
	$store_groups_options = sd_groups_for_select_by($stores, 'address', 'province', 'all');

	$groups['shipping']['shipping_store_area'] = [
		'label' => "Khu vực",
		'type' => "select",
		'class' => 'form-row-wide',
		'options' => $store_groups_options['options'],
		'default' => 'Hà Nội',
		'required' => false,
		'priority' => 6,
	];



	$groups['shipping']['shipping_store_id'] = [
		'label' => "Chọn cửa hàng",
		'type' => "select",
		'class' => 'form-row-wide',
		'options' => $store_groups_options['groups']['Hà Nội']['options'],
		'default' => '001',
		'required' => false,
		'priority' => 7,
	];


	$provinces = sd_array_to_select_options(sd_get_data_provinces(), 'name');

	$groups['shipping']['shipping_province'] = [
		'label' => "Tỉnh/Thành Phố",
		'type' => "select",
		'class' => 'form-row-wide',
		'options' => $provinces,
		'default' => '01',
		'required' => false,
		'priority' => 7,
	];


	$groups['shipping']['shipping_quan_huyen'] = [
		'label' => "Quận/huyện",
		'type' => "select",
		'class' => 'form-row-wide',
		'options' => [
			'' => 'Chọn quận/huyện',
		],
		'default' => '',
		'required' => false,
		'priority' => 7,
	];
	$groups['shipping']['shipping_phuong_xa'] = [
		'label' => "Phường/Xã",
		'type' => "select",
		'class' => 'form-row-wide',
		'options' => [
			'' => 'Chọn phường/xã',
		],
		'default' => '',
		'required' => false,
		'priority' => 7,
	];


	return $groups;
}

add_filter('woocommerce_checkout_fields', 'sd_checkout_fields');


add_filter('woocommerce_cart_needs_shipping', '__return_true');



function sd_add_checkout_data($data)
{


	$shipping_method = isset($_POST['shipping_sd_method']) ? wc_clean($_POST['shipping_sd_method']) : 'store';
	$title = isset($_POST['billing_title']) ? wc_clean($_POST['billing_title']) : 'mr';

	if (!is_array($data['billing'])) {
		$data['billing'] = [];
	}
	if (!is_array($data['shipping'])) {
		$data['shipping'] = [];
	}

	$data['billing']['country'] = 'VN';
	$data['billing']['title'] = $title;
	$data['billing']['state'] = isset($_POST['']) ? '' : '';
	$data['billing']['postcode'] = '';
	$data['billing']['city'] = '';


	$data['shipping']['sd_method'] = $shipping_method;

	$data['shipping']['country'] = 'VN';
	$data['shipping']['title'] = $title;
	$data['shipping']['state'] = '';
	$data['shipping']['postcode'] = '';
	$data['shipping']['city'] = '';

	if ($shipping_method != 'ship') {
		$data['shipping']['store_area'] = isset($_POST['shipping_store_area']) ? wc_clean($_POST['shipping_store_area']) : '';
		$data['shipping']['store_id'] = isset($_POST['shipping_store_id']) ? wc_clean($_POST['shipping_store_id']) : '';
	} else {
		$data['shipping']['quan_huyen'] = isset($_POST['shipping_quan_huyen']) ? wc_clean($_POST['shipping_quan_huyen']) : '';
		$data['shipping']['province'] = isset($_POST['shipping_province']) ? wc_clean($_POST['shipping_province']) : '';
		$data['shipping']['phuong_xa'] = isset($_POST['shipping_phuong_xa']) ? wc_clean($_POST['shipping_phuong_xa']) : '';
	}

	return  $data;
}

// add_filter('woocommerce_checkout_posted_data', 'sd_add_checkout_data',  9999);

function sd_change_order_total($number)
{
	if (WC()->session->get('_sd_pay_method') === 'part') {
		$number = WC()->session->get('_sd_pay_amount');
	}
	return $number;
}

function sd_woocommerce_payment_complete_order_status($status)
{
	if (WC()->session->get('_sd_pay_method') === 'part') {
		// $status = 'wc-partial-payment';
		$status = 'wc-partial-processing';
	}

	// var_dump( $status ); die();
	return $status;
}

function sd_woocommerce_valid_order_statuses_for_payment_complete($statuses)
{
	// $statuses [] = 'partial-payment';
	return $$statuses;
}
// add_filter('woocommerce_valid_order_statuses_for_payment_complete', 'sd_woocommerce_valid_order_statuses_for_payment_complete');

/**
 * Undocumented function
 *
 * @param WC_Order $order
 * @return void
 */
function sd_woocommerce_before_pay_action($order)
{

	$pay_amount = floatval(get_option('sd_partial_order_amount'));
	if ($pay_amount > 0 && isset($_POST['pay_amount']) && $_POST['pay_amount'] == 'part') {
		WC()->session->set('_sd_all_total', $order->get_total());
		WC()->session->set('_sd_pay_amount', $pay_amount);
		WC()->session->set('_sd_pay_method', 'part');
		$order->add_meta_data('_sd_all_total', $order->get_total(), true);
		$order->add_meta_data('_sd_pay_amount', $pay_amount, true);
		$order->add_meta_data('_sd_pay_method', 'part', true);
		$order->add_order_note(sprintf('Khách hàng đặt cọc trước: %s.', wc_price($pay_amount)));
	} else {
		WC()->session->set('_sd_pay_method', 'full');
		$order->delete_meta_data('_sd_all_total');
		$order->delete_meta_data('_sd_pay_amount');
		WC()->session->set('_sd_all_total', null);
		WC()->session->set('_sd_pay_amount', null);
		WC()->session->set('_sd_pay_method', null);
	}

	add_filter('woocommerce_order_get_total', 'sd_change_order_total');
	add_filter('woocommerce_payment_complete_order_status', 'sd_woocommerce_payment_complete_order_status',  999);
}
function sd_woocommerce_after_pay_action($order)
{

	WC()->session->set('_sd_all_total', null);
	WC()->session->set('_sd_pay_amount', null);
	WC()->session->set('_sd_pay_method', null);


	remove_action('woocommerce_order_get_total', 'sd_change_order_total');
	remove_action('woocommerce_payment_complete_order_status', 'sd_woocommerce_payment_complete_order_status');
}
add_action('woocommerce_before_pay_action', 'sd_woocommerce_before_pay_action', 99);
add_action('woocommerce_after_pay_action', 'sd_woocommerce_after_pay_action', 99);



$hooks = [
	'order.created'    => array(
		'woocommerce_new_order',
	),
	'order.updated'    => array(
		'woocommerce_update_order',
		'woocommerce_order_refunded',
	),
	'order.deleted'    => array(
		'wp_trash_post',
	),
	'order.restored'   => array(
		'untrashed_post',
	),
];

foreach ($hooks as $event => $hooks) {
	foreach ($hooks as $hook) {
		add_action($hook, 'sd_send_order_to_odoo_webhook', 10, 2);
	}
}

/**
 * Undocumented function
 *
 * @param WP_REST_Response $data
 * @return void
 */
function sd_woocommerce_rest_prepare_shop_order_object($response, $object)
{
	$extra = sd_get_order_extra_data($object);
	$response->data['extra'] = $extra;
	return $response;
}
add_filter('woocommerce_rest_prepare_shop_order_object', 'sd_woocommerce_rest_prepare_shop_order_object', 99, 3);

/**
 * Undocumented function
 *
 * @param [type] $order_id
 * @param WC_Order $order
 * @return void
 */
function sd_send_order_to_odoo_webhook($order_id, $order =  null)
{

	$version = str_replace('wp_api_', '', 'wp_api_v3');
	$payload = wc()->api->get_endpoint_data("/wc/{$version}/orders/" . $order_id);

	if (!$order) {
		$order = wc_get_order($order_id);
	}

	// var_dump($payload);
	// wp_remote_post($url, [
	// 	'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
	// 	'body'        => json_encode($payload),
	// 	'method'      => 'POST',
	// 	'data_format' => 'body',
	// ]);


	$data_lines = [];
	foreach ($payload['line_items'] as $item) {
		$data_lines[] = [
			'sku' => $item['sku'],
			'price' => $item['price'],
			"product_id" => "",
			"variant_id" => "",
			"quantity" => $item['quantity'],
			"temp_price" => "",
			"subtotal" => "",
			"total_tax" => '',
			"subtotal_tax" => '',
			"discount_amount" => '',
			"discount_percent" => '',
			"total" => $item['total'],
			"shipping_total" => '',
			"discount_total" => '',
		];
	}

	$notes = [
		'customer_note' => 'Khách hàng ghi chú:' . $payload['customer_note'],
	];

	$billing = [
		'title' => $payload['extra']['billing_title'],
		"name" => trim($payload['billing']['first_name'] . ' ' . $payload['billing']['last_name']),
		"phone" => $payload['billing']['phone'],
		"email" => $payload['billing']['email'],
		"state" => '',
		"city" => '',
		"address" => '',
		"country" => "VN"
	];

	if ($payload['extra']['shipping_method'] == 'ship') {
		$billing['state'] = absint($payload['extra']['province_id']);
		$billing['city'] = absint($payload['extra']['qh_id']);
		$address = '';
		if ($payload['billing']['address_1']) {
			$address_array[] = $payload['billing']['address_1'];
			$address = $payload['billing']['address_1'];
		} else if ($payload['shipping']['address_1']) {
			$address_array[] = $payload['shipping']['address_1'];
			$address = $payload['shipping']['address_1'];
		}

		$address .= ', ' . $payload['extra']['px_name'];
		$billing['address'] = $address;
		$notes = [
			'shipping_method' => "Phương thức nhận hàng: Giao tận nơi.",
			'shipping_address' => "Địa chỉ giao hàng: " . $payload['extra']['full_shipping_address'],
		];

		$store_id = ''; // Default store
	} else {
		$notes = [
			'shipping_method' => "Phương thức nhận hàng: Giao tại cửa hàng.",
		];
	}

	$shipping = $billing;
	$shipping['method'] = $payload['extra']['shipping_method'];

	$odoo_data = [
		'web_id' => $payload['id'],
		'created_via' => 'webshop',
		'pos_id' => $store_id,
		'seller_id' => '',
		'customer_id' => '',
		'customer_note' => join("\n", $notes),
		'status' => 'quotation',
		'currency' => 'VND',
		'billing' => $billing,
		'shipping' => $shipping,
		'line_items' => $data_lines,
		'note_items' => [],
	];

	$url = get_option('sd_odoo_api_url');
	$token = get_option('sd_odoo_api_token');

	$r = wp_remote_post($url, [
		'headers'     => array(
			'Content-Type' => 'application/json; charset=utf-8',
			'access-token' => $token
		),
		'body'        => json_encode($odoo_data),
		'method'      => 'POST',
		'data_format' => 'body',
	]);

	$code = wp_remote_retrieve_response_code($r);
	$body = json_decode(wp_remote_retrieve_body($r), true);

	$body['id'] = rand(1111111, 9999999);
	$body['payment_message'] = 'M668' . $store_id . $body['id'];

	if (isset($_GET['debug'])) {
		var_dump(get_option('sd_partial_order_amount'));
		var_dump($order->get_status('edit'));
		var_dump($payload);
		var_dump($body);
		var_dump($odoo_data);
	}

	if (isset($body['id'])) {
		$order->add_meta_data('_odoo_order_id', $body['id'], true);
		$order->add_meta_data('_odoo_order_payment_message', $body['payment_message'], true);
		$order->save_meta_data();
	}
}

if (isset($_GET['debug'])) {
	add_action('wp', function () {
		sd_send_order_to_odoo_webhook($_GET['debug']);
		die();
	});
}
