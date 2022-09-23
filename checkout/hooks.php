<?php


add_action('wp_enqueue_scripts', function () {
	if (is_checkout()) {
		wp_enqueue_script('wc-cart');
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
	$statuses[] = 'partial-payment';

	return $statuses;
}


add_filter('wc_order_statuses', 'sd_custom_order_status');
function sd_custom_order_status($order_statuses)
{
	$order_statuses['wc-partial-payment'] = _x('Partial payment', 'Order status', 'woocommerce');
	return $order_statuses;
}


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


function get_csv_array()
{
	$file = fopen(SD_CO_PATH . '/tech-accounts.csv', 'r');
	$array = [];
	while (($line = fgetcsv($file)) !== FALSE) {
		//$line is an array of the csv elements
		$code = $line[1];
		$state = $line[0];
		$address = $line[2];
		$account = $line[4];
		$array[$code] = [
			'code' => $code,
			'state' => $state,
			'address' => $address,
			'account' => $account,
		];
	}
	fclose($file);

	return 	$array;
}

/**
 * Undocumented function
 *
 * @param [type] $order_id
 * @param WC_Order $order
 * @return void
 */
function sd_send_order_to_odoo_webhook($order_id, $order =  null)
{

	// $url = 'https://eotw38jsq4ccm0l.m.pipedream.net';
	$url = SD_API_ORDERS;
	$version = str_replace('wp_api_', '', 'wp_api_v3');
	$payload = wc()->api->get_endpoint_data("/wc/{$version}/orders/" . $order_id);

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
		];
	}

	$address = [$payload['billing']['address_1']];
	$address[] = $payload['billing']['city'];
	$address[] = $payload['billing']['state'];

	$odoo_data = [
		'web_id' => $payload['id'],
		'created_via' => 'webshop',
		'pos_id' => '',
		'customer_id' => '',
		'customer_note' => $payload['customer_note'],
		'status' => 'quotation',
		'currency' => 'VND',
		'billing' => [
			"name" => trim($payload['billing']['first_name'] . ' ' . $payload['billing']['last_name']),
			"phone" => $payload['billing']['phone'],
			"email" => $payload['billing']['email'],
			"state" => $payload['billing']['state'],
			"city" => $payload['billing']['city'],
			"address" => join(" ", $address),
			"country" => "VN"
		],
		'line_items' => $data_lines,
		'note_items' => [],
	];

	$r = wp_remote_post($url, [
		'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
		'body'        => json_encode($odoo_data),
		'method'      => 'POST',
		'data_format' => 'body',
	]);


	// $bank_accounts = get_csv_array();
	$body = json_decode(wp_remote_retrieve_body($r), true);
	if (!$order) {
		$order = wc_get_order($order_id);
	}
	$order->add_meta_data('_odoo_order__test', 'OK', true);
	if (isset($_GET['debug'])) {
		var_dump($payload);
		var_dump($body);
	}

	if (isset($body['id'])) {
		$order->add_meta_data('_odoo_order_id', $body['id'], true);
		$order->add_meta_data('_odoo_order_payment_message', $body['payment_message'], true);
	}
}

if (isset($_GET['debug'])) {
	add_action('wp', function () {
		sd_send_order_to_odoo_webhook(2305);
		die();
	});
}
