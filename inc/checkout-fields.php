<?php


/**
 * Thay đổi input của trang checkout.
 * 
 * @see woocommerce_form_field()
 */
function sd_checkout_fields($groups)
{

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


/**
 * Validate checkout fields.
 *
 * @param [type] $data
 * @return void
 */
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

