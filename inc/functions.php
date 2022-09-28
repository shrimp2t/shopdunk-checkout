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

	$meta_extra = $order->get_meta('_sd_extra_info', true);
	if (!is_array($meta_extra)) {
		$meta_extra = [];
	}

	$store_id = sd_get_value_from_array('store_id', $meta_extra, '');
	$shipping_province_id = sd_get_value_from_array('shipping_province_id', $meta_extra, '');
	$shipping_qh_id = sd_get_value_from_array('shipping_qh_id', $meta_extra, '');
	$shipping_px_id = sd_get_value_from_array('shipping_px_id', $meta_extra, '');

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
		'total' => floatval($order->get_total()),
		'pay_amount' => floatval($order->get_meta('_sd_pay_amount', true)),
		'pay_method' => $order->get_meta('_sd_pay_method', true),
		'full_shipping_address' => '',
		'shipping_province_name' => isset($provinces[$shipping_province_id]) ? $provinces[$shipping_province_id]['name'] : '',
		'shipping_qh_name' => isset($quan_huyen[$shipping_qh_id]) ? $quan_huyen[$shipping_qh_id]['name'] : '',
		'shipping_px_name' => isset($phuong_xa[$shipping_px_id]) ? $phuong_xa[$shipping_px_id]['name'] : '',
		'shipping_address' => $order->get_shipping_address_1(),
	];

	$extra['store_id'] = $store_id;
	$store =  isset($stores[$store_id]) ? $stores[$store_id] : [];
	$store = wp_parse_args($store, [
		'code' => '',
		'province' => '',
		'address' => '',
		'account' => '',
	]);
	foreach ($store as $k => $v) {
		$extra['store__' . $k] = $v;
	}

	$extra['full_shipping_address'] = join(', ', array_filter([$address, $extra['shipping_px_name'], $extra['shipping_qh_name'], $extra['shipping_province_name']]));

	$extra = array_merge($meta_extra, $extra);

	$ship_keys = [
		'full_shipping_address', 
		'shipping_province_name', 
		'shipping_qh_name', 
		'shipping_px_name', 
		'shipping_province_id', 
		'shipping_qh_id', 
		'shipping_px_id', 
		'shipping_address', 
	];

	$store_keys = [
		'store_id', 
		'store_area', 
		'store__code', 
		'store__province', 
		'store__address', 
		'store__account', 
	];

	if ( $extra['shipping_method'] == 'ship' ) {
		foreach( $store_keys as $k ) {
			$extra[ $k ] = '';
		}
	} else {
		foreach( $ship_keys as $k ) {
			$extra[ $k ] = '';
		}
	}

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
