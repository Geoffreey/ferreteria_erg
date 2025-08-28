<?php
/*
| -----------------------------------------------------
| PRODUCT NAME: 	Modern POS
| -----------------------------------------------------
| AUTHOR:			geoffdeep.pw
| -----------------------------------------------------
| EMAIL:			info@geoffdeep.pw
| -----------------------------------------------------
| COPYRIGHT:		RESERVED BY geoffdeep
| -----------------------------------------------------
| WEBSITE:			http://geoffdeep.pw
| -----------------------------------------------------
*/
class ModelTalla extends Model 
{
	public function addTalla($data) 
	{
    	$statement = $this->db->prepare("INSERT INTO `tallas` (talla_name, code_name, talla_details, talla_image, created_at) VALUES (?, ?, ?, ?, ?)");
    	$statement->execute(array($data['talla_name'], $data['code_name'], $data['talla_details'], $data['talla_image'], date_time()));
    	$talla_id = $this->db->lastInsertId();
    	if (isset($data['talla_store'])) {
			foreach ($data['talla_store'] as $store_id) {
				$statement = $this->db->prepare("INSERT INTO `talla_to_store` SET `talla_id` = ?, `store_id` = ?");
				$statement->execute(array((int)$talla_id, (int)$store_id));
			}
		}
		$this->updateStatus($talla_id, $data['status']);
		$this->updateSortOrder($talla_id, $data['sort_order']);
    	return $talla_id;   
	}

	public function updateStatus($talla_id, $status, $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();
		$statement = $this->db->prepare("UPDATE `talla_to_store` SET `status` = ? WHERE `store_id` = ? AND `talla_id` = ?");
		$statement->execute(array((int)$status, $store_id, (int)$talla_id));
	}

	public function updateSortOrder($talla_id, $sort_order, $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();
		$statement = $this->db->prepare("UPDATE `talla_to_store` SET `sort_order` = ? WHERE `store_id` = ? AND `talla_id` = ?");
		$statement->execute(array((int)$sort_order, $store_id, (int)$talla_id));
	}

	public function editTalla($talla_id, $data) 
	{
    	$statement = $this->db->prepare("UPDATE `tallas` SET `talla_name` = ?, `code_name` = ?, `talla_details` = ?, `talla_image` = ? WHERE `talla_id` = ? ");
    	$statement->execute(array($data['talla_name'], $data['code_name'], $data['talla_details'], $data['talla_image'], $talla_id));
		
		// Insert Talla into store
    	if (isset($data['talla_store'])) 
    	{
    		$store_ids = array();
			foreach ($data['talla_store'] as $store_id) {
				$statement = $this->db->prepare("SELECT * FROM `talla_to_store` WHERE `store_id` = ? AND `talla_id` = ?");
			    $statement->execute(array($store_id, $talla_id));
			    $talla = $statement->fetch(PDO::FETCH_ASSOC);
			    if (!$talla) {
			    	$statement = $this->db->prepare("INSERT INTO `talla_to_store` SET `talla_id` = ?, `store_id` = ?");
					$statement->execute(array((int)$talla_id, (int)$store_id));
			    }
			    $store_ids[] = $store_id;
			}

			// Delete unwanted store
			if (!empty($store_ids)) {

				$unremoved_store_ids = array();

				// get unwanted stores
				$statement = $this->db->prepare("SELECT * FROM `talla_to_store` WHERE `store_id` NOT IN (" . implode(',', $store_ids) . ")");
				$statement->execute();
				$unwanted_stores = $statement->fetchAll(PDO::FETCH_ASSOC);
				foreach ($unwanted_stores as $store) {

					$store_id = $store['store_id'];
					
					// Fetch purchase invoice id
				    $statement = $this->db->prepare("SELECT * FROM `product_to_store` as p2s WHERE `store_id` = ? AND `talla_id` = ?");
				    $statement->execute(array($store_id, $talla_id));
				    $item_available = $statement->fetch(PDO::FETCH_ASSOC);

				     // If item available then store in variable
				    if ($item_available) {
				      $unremoved_store_ids[$item_available['store_id']] = store_field('name', $item_available['store_id']);
				      continue;
				    }

				    // Delete unwanted store link
					$statement = $this->db->prepare("DELETE FROM `talla_to_store` WHERE `store_id` = ? AND `talla_id` = ?");
					$statement->execute(array($store_id, $talla_id));

				}

				if (!empty($unremoved_store_ids)) {

					throw new Exception('The talla belongs to the stores(s) "' . implode(', ', $unremoved_store_ids) . '" has products, so its can not be removed');
				}				
			}
		}

		$this->updateStatus($talla_id, $data['status']);
		$this->updateSortOrder($talla_id, $data['sort_order']);

    	return $talla_id;
	}

	public function getTallaIdByCode($code_name)
	{
		$statement = $this->db->prepare("SELECT `talla_id` FROM `tallas` WHERE `code_name` = ?");
		$statement->execute(array($code_name));
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		return isset($row['talla_id']) ? $row['talla_id'] : null;
	}

	public function deleteTalla($talla_id) 
	{
    	$statement = $this->db->prepare("DELETE FROM `tallas` WHERE `talla_id` = ? LIMIT 1");
    	$statement->execute(array($talla_id));
        return $talla_id;
	}

	public function getTalla($talla_id, $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();
		$statement = $this->db->prepare("SELECT * FROM `tallas`
			LEFT JOIN `talla_to_store` as b2s ON (`tallas`.`talla_id` = `b2s`.`talla_id`)  
	    	WHERE `b2s`.`store_id` = ? AND `tallas`.`talla_id` = ?");
	  	$statement->execute(array($store_id, $talla_id));
	    $talla = $statement->fetch(PDO::FETCH_ASSOC);

	    // Fetch stores related to talla
	    $statement = $this->db->prepare("SELECT `store_id` FROM `talla_to_store` WHERE `talla_id` = ?");
	    $statement->execute(array($talla_id));
	    $all_stores = $statement->fetchAll(PDO::FETCH_ASSOC);
	    $stores = array();
	    foreach ($all_stores as $store) {
	    	$stores[] = $store['store_id'];
	    }

	    $talla['stores'] = $stores;

	    return $talla;
	}

	public function getTallas($data = array(), $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();

		$sql = "SELECT * FROM `tallas` LEFT JOIN `talla_to_store` b2s ON (`tallas`.`talla_id` = `b2s`.`talla_id`) WHERE `b2s`.`store_id` = ? AND `b2s`.`status` = ?";

		if (isset($data['filter_name'])) {
			$sql .= " AND `talla_name` LIKE '" . $data['filter_name'] . "%'";
		}

		$sql .= " GROUP BY `tallas`.`talla_id`";

		$sort_data = array(
			'talla_name'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY talla_name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$statement = $this->db->prepare($sql);
		$statement->execute(array($store_id, 1));

		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSellingPrice($talla_id, $from, $to, $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();

		$where_query = "`selling_info`.`inv_type` != 'due_paid' AND `selling_item`.`talla_id` = ? AND `selling_item`.`store_id` = ?";
		$where_query .= date_range_filter($from, $to);

		$statement = $this->db->prepare("SELECT SUM(`selling_price`.`discount_amount`) as discount, SUM(`selling_price`.`subtotal`) as total FROM `selling_info` 
			LEFT JOIN `selling_item` ON (`selling_info`.`invoice_id` = `selling_item`.`invoice_id`) 
			LEFT JOIN `selling_price` ON (`selling_info`.`invoice_id` = `selling_price`.`invoice_id`) 
			WHERE $where_query");

		$statement->execute(array($talla_id, $store_id));
		$invoice = $statement->fetch(PDO::FETCH_ASSOC);

		return (int)($invoice['total'] - $invoice['discount']);
	}

	public function getpurchasePrice($talla_id, $from, $to, $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();

		$where_query = "`purchase_info`.`inv_type` != 'others' AND `purchase_item`.`talla_id` = ? AND `purchase_item`.`store_id` = ?";
		$where_query .= date_range_filter2($from, $to);

		$statement = $this->db->prepare("SELECT SUM(`purchase_price`.`paid_amount`) as total FROM `purchase_info` 
			LEFT JOIN `purchase_item` ON (`purchase_info`.`invoice_id` = `purchase_item`.`invoice_id`) 
			LEFT JOIN `purchase_price` ON (`purchase_info`.`invoice_id` = `purchase_price`.`invoice_id`) 
			WHERE $where_query");
		$statement->execute(array($talla_id, $store_id));
		$purchase_price = $statement->fetch(PDO::FETCH_ASSOC);

		return (int)$purchase_price['total'];
	}

	public function getBelongsStore($talla_id)
	{
		$statement = $this->db->prepare("SELECT * FROM `talla_to_store` WHERE `talla_id` = ?");
		$statement->execute(array($talla_id));

		return $statement->fetchAll(PDO::FETCH_ASSOC);

	}

	public function totalSell($talla_id, $from = null, $to = null, $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();
		$where_query = "`selling_info`.`store_id` = $store_id AND `selling_item`.`talla_id` = $talla_id";
		if (from()) {
			$where_query .= date_range_filter($from, $to);
		}
		$statement = $this->db->prepare("SELECT SUM(`selling_item`.`item_total`) AS total FROM `selling_info` LEFT JOIN `selling_item` ON (`selling_info`.`invoice_id` = `selling_item`.`invoice_id`) WHERE $where_query GROUP BY `selling_item`.`talla_id`");
		$statement->execute(array());
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		return isset($row['total']) ? $row['total'] : 0;
	}

	public function totalProduct($talla_id, $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();
		$statement = $this->db->prepare("SELECT * FROM `product_to_store` WHERE `store_id` = ? AND `talla_id` = ? AND `status` = ?");
		$statement->execute(array($store_id, $talla_id, 1));
		return $statement->rowCount();
	}

	public function totalToday($store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();
		$where_query = "`b2s`.`store_id` = {$store_id} AND `b2s`.`status` = 1";
		$from = date('Y-m-d');
		$to = date('Y-m-d');
		if (($from && ($to == false)) || ($from == $to)) {
			$day = date('d', strtotime($from));
			$month = date('m', strtotime($from));
			$year = date('Y', strtotime($from));
			$where_query .= " AND DAY(`tallas`.`created_at`) = $day";
			$where_query .= " AND MONTH(`tallas`.`created_at`) = $month";
			$where_query .= " AND YEAR(`tallas`.`created_at`) = $year";
		} else {
			$from = date('Y-m-d H:i:s', strtotime($from.' '. '00:00:00')); 
			$to = date('Y-m-d H:i:s', strtotime($to.' '. '23:59:59'));
			$where_query .= " AND tallas.created_at >= '{$from}' AND tallas.created_at <= '{$to}'";
		}
		$statement = $this->db->prepare("SELECT * FROM `tallas` LEFT JOIN `talla_to_store` b2s ON (`tallas`.`talla_id` = `b2s`.`talla_id`) WHERE $where_query");
		$statement->execute(array());
		return $statement->rowCount();
	}

	public function total($from, $to, $store_id = null) 
	{
		$store_id = $store_id ? $store_id : store_id();
		$where_query = "`b2s`.`store_id` = {$store_id} AND `b2s`.`status` = 1";
		if ($from) {
			$from = $from ? $from : date('Y-m-d');
			$to = $to ? $to : date('Y-m-d');
			if (($from && ($to == false)) || ($from == $to)) {
				$day = date('d', strtotime($from));
				$month = date('m', strtotime($from));
				$year = date('Y', strtotime($from));
				$where_query .= " AND DAY(`tallas`.`created_at`) = $day";
				$where_query .= " AND MONTH(`tallas`.`created_at`) = $month";
				$where_query .= " AND YEAR(`tallas`.`created_at`) = $year";
			} else {
				$from = date('Y-m-d H:i:s', strtotime($from.' '. '00:00:00')); 
				$to = date('Y-m-d H:i:s', strtotime($to.' '. '23:59:59'));
				$where_query .= " AND tallas.created_at >= '{$from}' AND tallas.created_at <= '{$to}'";
			}
		}
		$statement = $this->db->prepare("SELECT * FROM `tallas` LEFT JOIN `talla_to_store` b2s ON (`tallas`.`talla_id` = `b2s`.`talla_id`) WHERE $where_query");
		$statement->execute(array());
		return $statement->rowCount();
	}
}