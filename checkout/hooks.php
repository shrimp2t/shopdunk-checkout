<?php


add_action('wp_enqueue_scripts', function () {
	if (is_checkout()) {
		$css_ver = @filemtime(SD_CO_PATH . '/assets/css/checkout.css');
		$js_ver = @filemtime(SD_CO_PATH . '/assets/js/checkout.js');
		wp_enqueue_script('wc-cart');
		wp_enqueue_style('sd-checkout', SD_CO_URL . '/assets/css/checkout.css', [], $css_ver);
		wp_enqueue_script('sd-checkout', SD_CO_URL . '/assets/js/checkout.js', ['jquery'], $js_ver, true);

		$stores = sd_get_data_stores();
		wp_localize_script('sd-checkout', 'SD_Checkout', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'secondary_products' => sd_get_products_secondary(),
			'stores' => $stores,
			'provinces' => sd_get_data_provinces(),
			'quan_huyen' => sd_get_data_quan_huyen(),
			'phuong_xa' => sd_get_data_phuong_xa(),
		]);
	}
}, 999);


function sd_ajax_checkout_handle_action()
{
	if (isset($_GET['id'])) {
		$order_id = absint($_GET['id']);
		$order = wc_get_order($order_id);
		if ($order) {
			sd_send_order_to_odoo_webhook($order_id, $order);
			wp_redirect($order->get_checkout_payment_url(false));
			die();
		}

		wp_redirect(remove_query_arg(['action', 'id'], home_url('/')));
	}
}


function sd_ajax_retry_order()
{
	$id = wc_clean($_POST['id']);

	$res = sd_send_order_to_odoo_webhook($id, false);
	wp_send_json($res);
	die();
}

add_action('wp_ajax_sd_retry_order', 'sd_ajax_retry_order');
add_action('wp_ajax_nopriv_sd_retry_order', 'sd_ajax_retry_order');
add_action('wp_ajax_sd_checkout_handle_action', 'sd_ajax_checkout_handle_action');
add_action('wp_ajax_nopriv_sd_checkout_handle_action', 'sd_ajax_checkout_handle_action');


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
add_filter('wc_get_template', 'sd_change_payment_template', 9999, 5);



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

	$session_data = WC()->session->get('sd_checkout_data', []);

	$groups['billing']['sd_billing_title'] = [
		'label' => "Xưng danh",
		'type' => "radio",
		'options' => [
			'Anh' => 'Anh',
			'Chị' => 'Chị',
		],
		'class' => 'form-row-wide',
		'default' => sd_get_value_from_array('sd_billing_title', $session_data, 'Anh'),
		'value' => 'Anh',
		'required' => true,
		'priority' => 4,
	];

	$groups['billing']['billing_first_name']['class'] = 'form-row-wide';
	$groups['shipping']['shipping_first_name']['class'] = 'form-row-wide';
	$groups['shipping']['shipping_address_1']['required'] = false;
	$groups['shipping']['shipping_address_1']['class'] = 'form-row-half';
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


	$groups['order']['order_comments']['type'] = 'text';


	$groups['shipping']['sd_shipping_method'] = [
		'label' => "Nhận hàng",
		'description' => "<span class='note'>Lưu ý*:</span> Mọi đơn hàng giao tận nơi cần thanh toán 100% giá trị trước khi giao hàng.",
		'type' => "radio",
		'class' => 'form-row-wide',
		'options' => [
			'store' => 'Nhận tại',
			'ship' => 'Giao tận nơi',
		],
		'default' =>  sd_get_value_from_array('sd_shipping_method', $session_data, 'store'),
		'required' => false,
		'priority' => 5,
	];

	$stores = sd_get_data_stores();
	// $options = sd_array_to_select_options($stores, 'address');
	$store_groups_options = sd_groups_for_select_by($stores, 'address', 'province', 'all');

	$groups['shipping']['sd_store_area'] = [
		'label' => "Khu vực",
		'type' => "select",
		'class' => 'form-row-wide',
		'options' => $store_groups_options['options'],
		'default' => sd_get_value_from_array('sd_store_area', $session_data, 'Hà Nội'),
		'required' => false,
		'priority' => 6,
	];

	$groups['shipping']['sd_store_id'] = [
		'label' => "Chọn cửa hàng",
		'type' => "select",
		'class' => 'form-row-wide',
		'options' => $store_groups_options['groups']['Hà Nội']['options'],
		'default' => sd_get_value_from_array('sd_store_id', $session_data, '001'),
		'required' => false,
		'priority' => 7,
	];


	$provinces = sd_array_to_select_options(sd_get_data_provinces(), 'name');
	$groups['shipping']['sd_shipping_province_id'] = [
		'label' => "Tỉnh/Thành Phố",
		'type' => "select",
		'class' => 'form-row-half',
		'options' => $provinces,
		'default' => sd_get_value_from_array('sd_shipping_province_id', $session_data, '01'),
		'required' => false,
		'priority' => 7,
	];

	$groups['shipping']['sd_shipping_qh_id'] = [
		'label' => "Quận/huyện",
		'type' => "select",
		'class' => 'form-row-half',
		'options' => [
			'' => 'Chọn quận/huyện',
		],
		'default' => '',
		'required' => false,
		'priority' => 7,
	];
	$groups['shipping']['sd_shipping_px_id'] = [
		'label' => "Phường/Xã",
		'type' => "select",
		'class' => 'form-row-half',
		'options' => [
			'' => 'Chọn phường/xã',
		],
		'default' => '',
		'required' => false,
		'priority' => 7,
	];


	$groups['secondary'] = [];
	$groups['secondary']['secondary_check'] = [
		'label' => "Sản phẩm thay thế",
		'description' => "Khi sản phẩm không còn hàng, tôi sẽ mua sản phẩm thay thế bên dưới.",
		'type' => "checkbox",
		'class' => 'form-row-wide',
		'default' => sd_get_value_from_array('secondary_check', $session_data, ''),
		'required' => false,
		'priority' => 7,
	];



	$products =  sd_get_products_secondary();
	$product_options = array_combine(array_keys($products), array_keys($products));
	$product_options = array_merge(['' => 'Chọn sản phẩm'], $product_options);

	for ($i = 1; $i <= 2; $i++) {
		$groups['secondary']['secondary_p' . $i . '_name'] = [
			'label' => "Sản phẩm " . $i,
			'type' => "select",
			'options' => $product_options,
			'custom_attributes' => [
				'data-i' => $i
			],
			'class' => 'secondary_ps_field secondary_ps_name form-row-wide',
			'default' => sd_get_value_from_array('secondary_p' . $i . '_name', $session_data, ''),
			'required' => false,
			'priority' => 7,
		];
		$groups['secondary']['secondary_p' . $i . '_color'] = [
			'label' => "",
			'type' => "select",
			'options' => [
				'' => 'Chọn màu'
			],
			'custom_attributes' => [
				'data-i' => $i
			],
			'class' => 'secondary_ps_field form-row-half',
			'default' => sd_get_value_from_array('secondary_p' . $i . '_color', $session_data, ''),
			'required' => false,
			'priority' => 7,
		];
		$groups['secondary']['secondary_p' . $i . '_storage'] = [
			'label' => "",
			'type' => "select",
			'options' => [
				'' => 'Chọn dung lượng'
			],
			'custom_attributes' => [
				'data-i' => $i
			],
			'class' => 'secondary_ps_field form-row-half',
			'default' => sd_get_value_from_array('secondary_p' . $i . '_storage', $session_data, ''),
			'required' => false,
			'priority' => 7,
		];
	}


	$groups['more'] = [];

	$groups['shipping']['more_shipping_info'] = [
		'label' => "Gọi người khác nhận hàng (nếu có)",
		'type' => "checkbox",
		'class' => 'form-row-wide',
		'default' => sd_get_value_from_array('more_shipping_info', $session_data, ''),
		'required' => false,
		'priority' => 88
	];

	$groups['shipping']['shipping_first_name'] = [
		'placeholder' => "Họ và tên người nhận",
		'type' => "text",
		'class' => 'more_shipping_field form-row-half',
		'default' => '',
		'required' => false,
		'priority' => 88
	];

	$groups['shipping']['shipping_phone'] = [
		'placeholder' => "Số điện thoại",
		'type' => "text",
		'class' => 'more_shipping_field form-row-half',
		'default' => '',
		'required' => false,
		'priority' => 88
	];

	$groups['shipping']['vat_check'] = [
		'label' => "Xuất hóa đơn công ty",
		'type' => "checkbox",
		'class' => 'form-row-wide',
		'default' => sd_get_value_from_array('vat_check', $session_data, ''),
		'required' => false,
		'priority' => 99,
	];

	$groups['shipping']['vat_cty'] = [
		'placeholder' => "Tên công ty",
		'type' => "text",
		'class' => 'vat_field form-row-wide',
		'default' => sd_get_value_from_array('vat_cty', $session_data, ''),
		'required' => false,
		'priority' => 99,
	];

	$groups['shipping']['vat_address'] = [
		'placeholder' => "Địa chỉ công ty",
		'type' => "text",
		'class' => 'vat_field form-row-wide',
		'default' => sd_get_value_from_array('vat_address', $session_data, ''),
		'required' => false,
		'priority' => 99,
	];

	$groups['shipping']['vat_tax_num'] = [
		'placeholder' => "Mã số thuế",
		'type' => "text",
		'class' => 'vat_field form-row-wide',
		'default' => sd_get_value_from_array('vat_tax_num', $session_data, ''),
		'required' => false,
		'priority' => 99,
	];






	return $groups;
}

add_filter('woocommerce_checkout_fields', 'sd_checkout_fields');


add_filter('woocommerce_cart_needs_shipping', '__return_true', 9999);
add_filter('woocommerce_cart_needs_shipping_address', '__return_true', 9999);



function sd_add_checkout_data($data)
{

	if (isset($data['vat_check']) && $data['vat_check'] == 1) {
		if (!isset($data['vat_cty']) || !$data['vat_cty']) {
			wc_add_notice('Xuất hóa đơn đỏ thiếu tên công ty.', 'error');
		}
		if (!isset($data['vat_address']) || !$data['vat_address']) {
			wc_add_notice('Xuất hóa đơn đỏ thiếu địa chỉ công ty.', 'error');
		}
		if (!isset($data['vat_tax_num']) || !$data['vat_tax_num']) {
			wc_add_notice('Xuất hóa đơn đỏ thiếu mã số thuế.', 'error');
		}
	}

	if (isset($data['sd_shipping_method']) &&  $data['sd_shipping_method'] == 'ship') {

		if (!isset($data['sd_shipping_province_id']) || !$data['sd_shipping_province_id']) {
			wc_add_notice('Chọn tỉnh/thành phố.', 'error');
		}
		if (!isset($data['sd_shipping_qh_id']) || !$data['sd_shipping_qh_id']) {
			wc_add_notice('Chọn quận/huyện.', 'error');
		}
		if (!isset($data['sd_shipping_px_id']) || !$data['sd_shipping_qh_id']) {
			wc_add_notice('Chọn xã/phường.', 'error');
		}
		if (!isset($data['shipping_address_1']) || !$data['shipping_address_1']) {
			wc_add_notice('Thiếu địa chỉ nhận hàng.', 'error');
		}
	} else {
		if (!isset($data['sd_store_id']) || !$data['sd_store_id']) {
			wc_add_notice('Hãy chọn cửa hàng.', 'error');
		}
	}

	// // wc_add_notice();
	// var_dump($data);
	// die();

	// Validate field here


	WC()->session->set('sd_checkout_data', $data);
	return  $data;
}

add_filter('woocommerce_checkout_posted_data', 'sd_add_checkout_data',  9999);


// add_action( 'woocommerce_checkout_create_order', $order, $data );

/**
 * Undocumented function
 *
 * @param WC_Order $order
 * @param array $data
 * @return void
 */
function sd_woocommerce_checkout_create_order($order, $data = [])
{
	$more_keys = [
		// sản phẩm thay thế.
		'secondary_check',
		'secondary_p1_name',
		'secondary_p1_color',
		'secondary_p1_storage',
		'secondary_p2_name',
		'secondary_p2_color',
		'secondary_p2_storage',

		// Vat
		'vat_check',
		'vat_cty',
		'vat_address',
		'vat_tax_num',

		// Billing
		'sd_billing_title',

		// Shipping
		'more_shipping_info',
		'sd_shipping_method',
		'sd_store_area',
		'sd_store_id',
		'sd_shipping_province_id',
		'sd_shipping_qh_id',
		'sd_shipping_px_id',
		'more_shipping_info',
	];

	$meta_data = [];
	foreach ($more_keys as $k) {
		if (substr($k, 0, 3) == 'sd_') {
			$meta_key = substr($k, 3);
		} else {
			$meta_key = $k;
		}
		$meta_data[$meta_key] = isset($data[$k]) ?  $data[$k] : '';
	}

	$order->add_meta_data('_sd_extra_info', $meta_data, true);
}
add_action('woocommerce_checkout_create_order', 'sd_woocommerce_checkout_create_order', 99, 2);




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
 * Thanh toán một phần.
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
// add_action('woocommerce_before_pay_action', 'sd_woocommerce_before_pay_action', 99);
// add_action('woocommerce_after_pay_action', 'sd_woocommerce_after_pay_action', 99);


/*
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
*/

// add_action('woocommerce_new_order', 'sd_send_order_to_odoo_webhook', 10, 2);
// add_action('woocommerce_checkout_order_processed', 'sd_send_order_to_odoo_webhook', 10, 1);
add_action('woocommerce_checkout_order_created', 'sd_send_order_to_odoo_webhook', 10, 1);

// add_action('wp_loaded', 'sd_checkout_handle_action', 10, 1);



/**
 * Undocumented function
 *
 * @param WP_REST_Response $response
 * @param WC_Order $object
 * @return void
 */
function sd_woocommerce_rest_prepare_shop_order_object($response, $object)
{
	$extra = sd_get_order_extra_data($object);
	$more_data = $object->get_meta('_sd_more_info', true);
	$more_data  = is_array($more_data) ? $more_data : [];
	$extra = array_merge($extra, $more_data);
	$response->data['extra'] = $extra;
	return $response;
}
add_filter('woocommerce_rest_prepare_shop_order_object', 'sd_woocommerce_rest_prepare_shop_order_object', 99, 3);




/**
 * Undocumented function
 *
 * @param WC_Order $order
 * @param [type] $retry_id
 * @return void
 */
function get_get_payload_for_odoo($order, $retry_id = null)
{

	if (!is_a($order, 'WC_Order')) {
		$order = wc_get_order($order);
	}

	// $version = str_replace('wp_api_', '', 'wp_api_v3');
	// $payload = wc()->api->get_endpoint_data("/wc/{$version}/orders/" . $order->get_id());
	$payload = $order->get_base_data();
	$payload['extra'] = sd_get_order_extra_data($order);

	$extra = $payload['extra'];
	$data_lines = [];
	$item_notes = [];

	$i = 1;
	foreach ($order->get_items('line_item') as $item) {
		// var_dump($item);
		$item_product = $item->get_product();
		$line_item = [
			'name' => "Sản phẩm {$i}: " . $item_product->get_name(),
			'sku' => 'SKU: ' . $item_product->get_sku(),
		];
		$tt = [];
		foreach ($item->get_formatted_meta_data() as  $mt) {
			$mt = (array) $mt;
			if (substr($mt['key'], 0, 1) != '_') {
				$tt[] = wp_strip_all_tags($mt['display_key'], true) . ": " . wp_strip_all_tags($mt['display_value'], true);
			}
		}

		if (!empty($tt)) {
			$line_item['mt'] =  join('<br/>', $tt);
		}

		$item_notes[] = join('<br/>', $line_item);

		$i++;

		$data_lines[] = [
			'sku' => $item_product->get_sku(),
			'price' => floatval($item_product->get_price()),
			"product_id" => "",
			"variant_id" => "",
			"quantity" => $item->get_quantity(),
			"temp_price" => "",
			"subtotal" => "",
			"total_tax" => '',
			"subtotal_tax" => '',
			"discount_amount" => '',
			"discount_percent" => '',
			"total" => floatval($item->get_total()),
			"shipping_total" => '',
			"discount_total" => '',
		];
	}

	$notes = [
		'customer_note' => '<strong>Khách hàng ghi chú:</strong>' . sd_get_value_from_array('customer_note',  $payload, 'Không có'),
		'name_title' => '<strong>Xưng danh:</strong> ' . $extra['billing_title'],
	];


	if ($extra['secondary_check']) {
		$sp = '';
		for ($i = 1; $i <= 2; $i++) {
			if ($extra['secondary_p' . $i . '_name']) {
				$sp .= "\nSản phẩm $i: " . esc_html($extra['secondary_p' . $i . '_name']) . ", Màu: " . esc_html($extra['secondary_p' . $i . '_color']) . ", Dung lượng: " . $extra['secondary_p' . $i . '_storage'];
			}
		}

		if ($sp) {
			$notes['sptt'] = "<strong>Sản phẩm thay thế:</strong> " . $sp;
		}
	}

	$billing = [
		'title' => $extra['billing_title'],
		"name" => trim($payload['billing']['first_name'] . ' ' . $payload['billing']['last_name']),
		"phone" => $payload['billing']['phone'],
		"email" => $payload['billing']['email'],
		"state" => '',
		"city" => '',
		"address" => '',
		"country" => "VN"
	];

	$store_id = $extra['store_id'];

	if ($extra['shipping_method'] == 'ship') {
		$billing['state'] = "{$extra['shipping_province_id']}";
		$billing['city'] = "{$extra['shipping_qh_id']}";
		$address = '';
		if ($payload['billing']['address_1']) {
			$address_array[] = $payload['billing']['address_1'];
			$address = $payload['billing']['address_1'];
		} else if ($payload['shipping']['address_1']) {
			$address_array[] = $payload['shipping']['address_1'];
			$address = $payload['shipping']['address_1'];
		}

		$address .= ', ' . $extra['shipping_px_name'];
		$billing['address'] = $address;
		$notes['shipping'] = "Phương thức nhận hàng: Giao tận nơi.\n" .
			"Địa chỉ giao hàng: " . $extra['full_shipping_address'];

		if ($extra['more_shipping_info']) {
			$notes['shipping'] .= "\nNgười nhận hàng: " . esc_html($order->get_shipping_first_name()) .
				"\nSĐT người nhận: " . $order->get_shipping_phone();
		}

		$store_id = ''; // Default store
	} else {
		$notes['shipping'] = "Phương thức nhận hàng: Giao tại cửa hàng.\n" .
			"Cửa hàng: " . esc_html($extra['store__address']);
	}

	$red_invoice = [];

	if ($extra['vat_check']) {
		$notes['vat'] =  "<strong>Xuất hóa đơn đỏ</strong>:" .
			"\nTên công ty: " . esc_html($extra['vat_cty']) .
			"\nĐịa chỉ: " . esc_html($extra['vat_address']) .
			"\nMã số thuế: " . esc_html($extra['vat_tax_num']);

		$red_invoice = [
			'name' => $extra['vat_cty'],
			'address' => $extra['vat_address'],
			'vat_num' => $extra['vat_tax_num'],
		];
	}

	$shipping = $payload['shipping'];
	$shipping['address'] = $shipping['address_1'];
	if (!isset($shipping['name']) || !$shipping['name']) {
		$shipping['name'] = $billing['name'];
	}
	if (!$shipping['phone']) {
		$shipping['phone'] = $billing['phone'];
	}

	if ($extra['more_shipping_info']) {
		$shipping['name'] = trim($payload['shipping']['first_name'] . ' ' . $payload['shipping']['last_name']);
		$shipping['phone'] = $order->get_shipping_phone();
	}

	$shipping['method'] = $extra['shipping_method'];

	$web_id = $payload['id'];
	if ($retry_id) {
		$web_id .= '-' . $retry_id;
	}

	$notes['line_items'] = "<strong>Sản phẩm đã đặt:</strong><br/> " . join("<br/><br/>", $item_notes);

	// var_dump( $notes['line_items']);
	$notes['customer'] = "<strong>Thông tin khách hàng:</strong><br/> Tên: " . esc_html($billing['name']) .
		"<br/>Phone: " . esc_html($billing['phone']) .
		"<br/>Email: " . esc_html($billing['email']); // 

	$notes['web_id'] = 'Nguồn: ' . $web_id . " - " . home_url('/');

	$odoo_data = [
		// 'web_id' => $web_id,
		'web_id' => '',
		'created_via' => 'webshop',
		'pos_id' => $store_id,
		'seller_id' => '',
		'customer_id' => '',
		'customer_note' => str_replace("\n", "<br/>", join("<br/><br/>", $notes)),
		'status' => 'quotation',
		'currency' => 'VND',
		'billing' => $billing,
		'shipping' => $shipping,
		'line_items' => $data_lines,
		'note_items' => [],
		'extra' => $extra,
	];

	if (!empty($red_invoice)) {
		$odoo_data['red_invoice'] = $red_invoice;
	}

	return $odoo_data;
}

/**
 * Undocumented function
 *
 * @param [type] $order_id
 * @param WC_Order $order
 * @return void
 */
function sd_send_order_to_odoo_webhook($order_id, $order =  null, $retry_id =  'auto')
{

	if (!is_a($order, 'WC_Order')) {
		$order = wc_get_order($order_id);
	}

	if (!$order) {
		return [
			'success' => false,
			'body' => [
				'id' => '',
			],
			'status_code' => '',
			'message' => 'Order not found',
		];
	}

	$existing_id = $order->get_meta('_odoo_order_id', true);
	if ($existing_id) {
		return [
			'success' => true,
			'body' => [
				'id' => $existing_id,
			],
			'status_code' => '',
			'message' => '',
		];
	}

	if ($retry_id == 'auto' || is_object($retry_id) || is_array($retry_id)) {
		$url_data = parse_url(home_url('/'));
		$retry_id = $url_data['host'] . '-' . time();
	}

	$odoo_data = get_get_payload_for_odoo($order, $retry_id);
	$url = get_option('sd_odoo_api_url');
	$token = get_option('sd_odoo_api_token');

	$r = wp_remote_post($url, [
		'headers'     => array(
			'Content-Type' => 'application/json',
			'access-token' => $token
		),
		'body'        => json_encode($odoo_data),
		'method'      => 'POST',
		'data_format' => 'body',
		'timeout'     => 120,
		'redirection' => 5,
	]);

	$http_code = wp_remote_retrieve_response_code($r);
	$raw_body =  wp_remote_retrieve_body($r);
	$body = json_decode($raw_body, true);

	if ($retry_id) {
		$order->add_meta_data('_odoo_retry_id', $retry_id, true);
		$has_save_meta = true;
	}

	$order->add_meta_data('_odoo_request_data', json_encode($odoo_data), true);
	$order->add_meta_data('_odoo_response', $url . '|' . $http_code . '|' . $raw_body, true);
	$has_save_meta = true;

	$success = false;
	$message = '';
	$id = false;
	if (!isset($body['error'])) {
		$success = true;
		// Try to get id;

		$payment_message = false;
		if (isset($body['id']) && $body['id']) {
			$id =  $body['id'];
			$payment_message =  $body['payment_message'];
		}
		if (!$id && isset($body['result']['data']['id'])) {
			$id = $body['result']['data']['id'];
			$payment_message = $body['result']['data']['payment_message'];
		}

		if ($id) {
			$order->add_meta_data('_odoo_order_id', $id, true);
			$order->add_meta_data('_odoo_order_payment_message', $payment_message, true);
			$has_save_meta = true;
		}
	} else {
		if (isset($body['message'])) {
			$message = $body['message'];
		} else if (isset($body['error'])) {
			if (isset($body['error']['message'])) {
				$message = $body['error']['message'];
			}
			if (!$message) {
				$message = json_encode($body['error']);
			}
		}
		if (!$message) {
			$message = json_encode($body);
		}
	}

	if ($has_save_meta) {
		$order->save_meta_data();
	}

	$webhook_urls =  get_option('sd_webhook_urls');
	$webhook_urls  = explode("\n", $webhook_urls);
	$webhook_urls  = array_filter(array_map('trim', $webhook_urls));
	if (!empty($webhook_urls)) {
		foreach ($webhook_urls as $url) {
			$r = wp_remote_post($url, [
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'body'        => json_encode($odoo_data),
				'method'      => 'POST',
				'data_format' => 'body',
				'timeout'     => 120,
				'redirection' => 5,
			]);
			// var_dump( $url );
			// var_dump( $r );
			// var_dump( $odoo_data );
			// die();
		}
	}


	$body['id'] = $id;
	return [
		'success' => $success,
		'body' => $body,
		'status_code' => $http_code,
		'message' => $message,
		//'odoo_data' => $odoo_data,
	];
}

if (isset($_GET['debug_all'])) {
	add_action('wp', function () {
		echo json_encode(get_get_payload_for_odoo($_GET['debug_all']), JSON_PRETTY_PRINT);
		die();
	});
}


if (isset($_GET['debug'])) {
	add_action('wp', function () {
		echo json_encode(get_get_payload_for_odoo($_GET['debug']), JSON_PRETTY_PRINT);
		die();
	});
}

if (isset($_GET['debug_meta'])) {
	add_action('wp', function () {
		echo get_post_meta($_GET['debug_meta'], '_odoo_request_data', true);
		die();
	});
}
if (isset($_GET['debug_payload'])) {
	add_action('wp', function () {
		$version = str_replace('wp_api_', '', 'wp_api_v3');
		$payload = wc()->api->get_endpoint_data("/wc/{$version}/orders/" . $_GET['debug_payload']);
		echo json_encode($payload, JSON_PRETTY_PRINT);
		die();
	});
}
if (isset($_GET['debug_dx'])) {
	add_action('wp', function () {
		sd_load_dia_gioi_vn();
		die();
	});
}
