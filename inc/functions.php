<?php


function sd_get_value_from_array($key, $data, $default = null)
{
	if (!is_array($data)) {
		return $default;
	}

	return isset($data[$key]) ?  $data[$key] : $default;
}

function sd_get_data_stores()
{

	$data_file = SD_CO_PATH . '/inc/data/data-stores.php';
	$data = include($data_file);
	if (is_array($data)) {
		return $data;
	}
	$array = [];
	$file = fopen(SD_CO_PATH . '/csv/stores.csv', 'r');
	$i = 0;
	while (($line = fgetcsv($file)) !== FALSE) {
		if ($i == 0) {
			$i++;
			continue;
		}
		$code = $line[1];
		$state = $line[0];
		$address = $line[2];
		$account = $line[4];
		$array[$code] = [
			'code' => $code,
			'province' => $state,
			'address' => $address,
			'account' => $account,
		];
		$i++;
	}
	fclose($file);
	$code = "<?php\n return " . var_export($array, true) . ';';
	file_put_contents($data_file, $code);
	return 	$array;
}


function sd_get_data_provinces()
{

	$data_file = SD_CO_PATH . '/inc/data/data-tinh.php';
	$data = include($data_file);
	if (is_array($data)) {
		return $data;
	}

	$file = fopen(SD_CO_PATH . '/csv/tinh.csv', 'r');
	$array = [];
	$i = 0;
	while (($line = fgetcsv($file)) !== FALSE) {
		if ($i == 0) {
			$i++;
			continue;
		}

		$code = $line[0];
		$name = $line[1];
		if (!$code) {
			continue;
		}
		$name = str_replace(['Tỉnh', 'tỉnh', 'Thành phố', 'Thành phố'], '', $name);

		$array[$code] = [
			'code' => $code,
			'name' => trim($name),
		];
		$i++;
	}
	fclose($file);

	$code = "<?php\n return " . var_export($array, true) . ';';
	file_put_contents($data_file, $code);
	return 	$array;

	return 	$array;
}


function sd_get_data_quan_huyen()
{

	$data_file = SD_CO_PATH . '/inc/data/data-quan-huyen.php';
	$data = include($data_file);
	if (is_array($data)) {
		return $data;
	}

	$file = fopen(SD_CO_PATH . '/csv/quan-huyen.csv', 'r');
	$array = [];
	$i = 0;
	while (($line = fgetcsv($file)) !== FALSE) {
		if ($i == 0) {
			$i++;
			continue;
		}
		$code = $line[0];
		$name = $line[1];
		$id_tinh = $line[4];
		$tinh = $line[5];

		if (!$code) {
			continue;
		}

		$tinh = str_replace(['Tỉnh', 'tỉnh', 'Thành phố', 'Thành phố'], '', $tinh);

		$array[$code] = [
			'code' => $code,
			'name' => $name,
			'province_id' => $name,
			'province_id' => $id_tinh,
			'province' => trim($tinh),
		];
		$i++;
	}
	fclose($file);

	$code = "<?php\n return " . var_export($array, true) . ';';
	file_put_contents($data_file, $code);
	return 	$array;

	return 	$array;
}
function sd_get_data_phuong_xa()
{

	$data_file = SD_CO_PATH . '/inc/data/data-phuong-xa.php';
	$data = include($data_file);
	if (is_array($data)) {
		return $data;
	}

	$file = fopen(SD_CO_PATH . '/csv/phuong-xa.csv', 'r');
	$array = [];
	$i = 0;
	while (($line = fgetcsv($file)) !== FALSE) {
		if ($i == 0) {
			$i++;
			continue;
		}
		$code = $line[0];
		$name = $line[1];
		$id_px = $line[4];
		$id_tinh = $line[6];


		if (!$code) {
			continue;
		}

		$array[$code] = [
			'code' => $code,
			'name' => $name,
			'province_id' => $id_tinh,
			'id_px' => $id_px,
		];
		$i++;
	}
	fclose($file);

	$code = "<?php\n return " . var_export($array, true) . ';';
	file_put_contents($data_file, $code);
	return 	$array;

	return 	$array;
}



function sd_groups_for_select_by($array, $label_field, $by_field, $return = 'all')
{
	$groups = [];
	$options = [];
	foreach ($array as $id => $a) {
		$by_value = isset($a[$by_field]) ? $a[$by_field] : '';
		if (!isset($groups[$by_value])) {
			$options[$by_value] = $by_value;
			$groups[$by_value] = [
				'label' => $by_value,
				'options' => [],
			];
		}
		$groups[$by_value]['options'][$id] = isset($a[$label_field]) ? $a[$label_field] : '';
	}

	switch ($return) {
		case 'all':
			return [
				'groups' => $groups,
				'options' => $options,
			];
			break;
		case 'options':
			return  $options;
			break;

		default:
			return $groups;
	}
}


function sd_array_to_select_options($array, $label_field)
{
	$options = [];
	foreach ($array as $id => $v) {
		$options[$id] = isset($v[$label_field]) ? $v[$label_field] : '';
	}
	return $options;
}


/**
 * Undocumented function
 *
 * @param WC_Order $order
 * @return void
 */
function sd_get_order_extra_data($order)
{
	$stores = sd_get_data_stores();
	$provinces = sd_get_data_provinces();
	$quan_huyen = sd_get_data_quan_huyen();
	$phuong_xa = sd_get_data_phuong_xa();

	$store_id = $order->get_meta('_shipping_store_id', true);
	$store_area = $order->get_meta('_shipping_store_area', true);
	$shipping_method = $order->get_meta('_shipping_sd_method', true);
	$billing_title = $order->get_meta('_billing_title', true);
	$shipping_province = $order->get_meta('_shipping_province', true);
	$shipping_quan_huyen = $order->get_meta('_shipping_quan_huyen', true);
	$shipping_phuong_xa = $order->get_meta('_shipping_phuong_xa', true);
	$odoo_order_id = $order->get_meta('_odoo_order_id', true);
	$odoo_order_payment_message = $order->get_meta('_odoo_order_payment_message', true);


	$address = '';
	if ($order->get_billing_address_1()) {
		$address =  $order->get_billing_address_1();
	} else if ($order->get_shipping_address_1()) {
		$address = $order->get_shipping_address_1();
	}


	$extra =  [
		'id' => $order->get_id(),
		'odoo_order_id' => $odoo_order_id,
		'payment_message' => $odoo_order_payment_message,
		'store_id' => $store_id,
		'store_name' => isset($stores[$store_id]) ? $stores[$store_id]['address'] : '',
		'store_data' => isset($stores[$store_id]) ? $stores[$store_id] : false,
		'billing_title' => $billing_title ? $billing_title : 'Anh',
		'total' => $order->get_total(),
		'pay_amount' => $order->get_meta('_sd_pay_amount', true),
		'pay_method' => $order->get_meta('_sd_pay_method', true),
		'store_area' => $store_area,
		'full_shipping_address' => '',
		'shipping_method' => $shipping_method,
		'province_id' => $shipping_province,
		'province_name' => isset($provinces[$shipping_province]) ? $provinces[$shipping_province]['name'] : '',
		'qh_id' => $shipping_quan_huyen,
		'qh_name' => isset($quan_huyen[$shipping_quan_huyen]) ? $quan_huyen[$shipping_quan_huyen]['name'] : '',
		'px_id' => $shipping_phuong_xa,
		'px_name' => isset($phuong_xa[$shipping_phuong_xa]) ? $phuong_xa[$shipping_phuong_xa]['name'] : '',
	];

	$extra['full_shipping_address'] = join(', ', [$address, $extra['px_name'], $extra['qh_name'], $extra['province_name']]);

	return $extra;
}


function sd_allow_partial_pay($order)
{
	$items = $order->get_items('line_item');
	$allowed_skus = explode("\n", get_option('sd_partial_pay_allowed_skus'));
	$allowed_skus = array_map('trim', $allowed_skus);
	$allowed_skus = array_filter($allowed_skus);
	if (empty($allowed_skus)) {
		return false;
	}
	/**
	 * @see WC_Order_Item_Product
	 */
	foreach ($items as $item) {
		$item_p = $item->get_product();
		$sku = $item_p->get_sku();
		if (in_array($sku, $allowed_skus)) {
			return true;
		}
	}

	return false;
}
