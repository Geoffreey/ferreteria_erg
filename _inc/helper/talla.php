<?php
function get_talla_id_by_code($id) 
{
	
	$model = registry()->get('loader')->model('talla');
	return $model->getTallaIdByCode($id);
}

function get_tallas($data = array()) 
{
	$model = registry()->get('loader')->model('talla');
	return $model->getTallas($data); // ✅ llama al método que sí acepta array de filtros
}

function get_the_talla($id, $field = null) 
{
	
	$model = registry()->get('loader')->model('talla');
	$talla = $model->getTalla($id);
	if ($field && isset($talla[$field])) {
		return $talla[$field];
	} elseif ($field) {
		return;
	}
	return $talla;
}

function talla_selling_price($talla_id, $from, $to)
{
	
	$talla_model = registry()->get('loader')->model('talla');
	return $talla_model->getSellingPrice($talla_id, $from, $to);
}

function talla_purchase_price($talla_id, $from, $to)
{
	
	$talla_model = registry()->get('loader')->model('talla');
	return $talla_model->getpurchasePrice($talla_id, $from, $to);
}

function total_talla_today($store_id = null)
{
	
	$talla_model = registry()->get('loader')->model('talla');
	return $talla_model->totalToday($store_id);
}

function total_talla($from = null, $to = null, $store_id = null)
{
	
	$talla_model = registry()->get('loader')->model('talla');
	return $talla_model->total($from, $to, $store_id);
}

function total_product_of_talla($talla_id)
{
	
	
	$talla_model = registry()->get('loader')->model('talla');
	return $talla_model->totalProduct($talla_id);

}