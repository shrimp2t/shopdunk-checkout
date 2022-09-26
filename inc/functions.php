<?php

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
