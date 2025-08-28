<?php 
ob_start();
session_start();
include ("../_init.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check, if your logged in or not
// If user is not logged in then return an alert message
if (!is_loggedin()) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => trans('error_login')));
  exit();
}

// Comprobar, si el usuario tiene permiso de lectura o no
// If user have not reading permission return an alert message
if (user_group_id() != 1 && !has_permission('access', 'read_talla')) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => trans('error_read_permission')));
  exit();
}

// LOAD SUPPLIER MODEL
$talla_model = registry()->get('loader')->model('talla');

// Validar datos de publicación
function validate_request_data($request) 
{
  // Vaidar nombre de la talla
  if(!validateString($request->post['talla_name'])) {
    throw new Exception(trans('error_talla_name'));
  }

  // Validar nombre de codigo de talla
  if(!validateString($request->post['code_name'])) {
    throw new Exception(trans('error_code_name'));
  }

  // Validar talla slug
  if(!validateString($request->post['code_name'])) {
    throw new Exception(trans('error_code_name'));
  }

  // Validar tienda
  if (!isset($request->post['talla_store']) || empty($request->post['talla_store'])) {
    throw new Exception(trans('error_store'));
  }

  // Validar estado
  if (!is_numeric($request->post['status'])) {
    throw new Exception(trans('error_status'));
  }

  // Validar orden de clasificación
  if (!is_numeric($request->post['sort_order'])) {
    throw new Exception(trans('error_sort_order'));
  }
}

// Check, if already exist or not
function validate_existance($request, $id = 0)
{
  

  // Verifique si existe o no la talla
  $statement = db()->prepare("SELECT * FROM `tallas` WHERE (`talla_name` = ? OR `code_name` = ?) AND `talla_id` != ?");
  $statement->execute(array($request->post['talla_name'], $request->post['code_name'], $id));
  if ($statement->rowCount() > 0) {
    throw new Exception(trans('error_talla_exist'));
  }
}

// Crear talla
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE')
{
  try {

    // Verifique el permiso de creación
    if (user_group_id() != 1 && !has_permission('access', 'create_talla')) {
      throw new Exception(trans('error_create_permission'));
    }

    // Validar datos de publicación
    validate_request_data($request);

    // Validate existance
    validate_existance($request);
    
    $statement = db()->prepare("SELECT * FROM `tallas` WHERE (`code_name` = ? OR `talla_name` = ?)");
    $statement->execute(array($request->post['code_name'], $request->post['talla_name']));
    $total = $statement->rowCount();
    if ($total>0) {
      throw new Exception(trans('error_talla_exist'));
    }

    $Hooks->do_action('Before_Create_Talla', $request);

    // Insert talla into database
    $talla_id = $talla_model->addTalla($request->post);

    // get talla info
    $talla = $talla_model->getTalla($talla_id);

    $Hooks->do_action('After_Create_Talla', $talla);

    // SET OUTPUT CONTENT TYPE
    header('Content-Type: application/json');
    echo json_encode(array('msg' => trans('text_success'), 'id' => $talla_id, 'talla' => $talla));
    exit();

  } catch (Exception $e) { 
    
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();

  }
} 

// Update talla
if($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATE')
{
  try {

    // Comprobar permiso de actualización
    if (user_group_id() != 1 && !has_permission('access', 'update_talla')) {
      throw new Exception(trans('error_update_permission'));
    }

    // Validar identificación del producto
    if (empty($request->post['talla_id'])) {
      throw new Exception(trans('error_talla_id'));
    }

    $id = $request->post['btalla_id'];

    // Validar datos de publicación
    validate_request_data($request);

    // Validate existance
    validate_existance($request, $id);

    $Hooks->do_action('Before_Update_Talla', $request);

    // Edit talla
    $talla = $talla_model->editTalla($id, $request->post);

    $Hooks->do_action('After_Update_Talla', $talla);

    header('Content-Type: application/json');
    echo json_encode(array('msg' => trans('text_update_success'), 'id' => $id));
    exit();
    
  } catch(Exception $e) { 

    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
} 


// Delete talla
if($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'DELETE') 
{
  try {

    // Comprobar permiso de eliminación
    if (user_group_id() != 1 && !has_permission('access', 'delete_talla')) {
      throw new Exception(trans('error_delete_permission'));
    }

    // Validate talla id
    if (empty($request->post['talla_id'])) {
      throw new Exception(trans('error_talla_id'));
    }

    $id = $request->post['talla_id'];
    $new_talla_id = $request->post['new_talla_id'];

    // Validate delete action
    if (empty($request->post['delete_action'])) {
      throw new Exception(trans('error_delete_action'));
    }

    if ($request->post['delete_action'] == 'insert_to' && empty($new_talla_id)) {
      throw new Exception(trans('error_talla_name'));
    }

    $Hooks->do_action('Before_Delete_talla', $request);

    $belongs_stores = $talla_model->getBelongsStore($id);
    foreach ($belongs_stores as $the_store) {

      // Check if relationship exist or not
      $statement = db()->prepare("SELECT * FROM `talla_to_store` WHERE `talla_id` = ? AND `store_id` = ?");
      $statement->execute(array($new_talla_id, $the_store['store_id']));
      if ($statement->rowCount() > 0) continue;

      // Create relationship
      $statement = db()->prepare("INSERT INTO `talla_to_store` SET `talla_id` = ?, `store_id` = ?");
      $statement->execute(array($new_talla_id, $the_store['store_id']));
    }

    if ($request->post['delete_action'] == 'insert_to') 
    {
      $statement = db()->prepare("UPDATE `holding_item` SET `talla_id` = ? WHERE `talla_id` = ?");
      $statement->execute(array($new_talla_id, $id));

      $statement = db()->prepare("UPDATE `quotation_item` SET `talla_id` = ? WHERE `talla_id` = ?");
      $statement->execute(array($new_talla_id, $id));

      $statement = db()->prepare("UPDATE `product_to_store` SET `talla_id` = ? WHERE `talla_id` = ?");
      $statement->execute(array($new_talla_id, $id));

      $statement = db()->prepare("UPDATE `selling_item` SET `talla_id` = ? WHERE `talla_id` = ?");
      $statement->execute(array($new_talla_id, $id));
    } 

    // Delete talla
    $talla = $talla_model->deleteTalla($id);

    $Hooks->do_action('After_Delete_Talla', $talla);
    
    header('Content-Type: application/json');
    echo json_encode(array('msg' => trans('text_delete_success')));
    exit();

  } catch (Exception $e) { 

    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

// talla create form
if (isset($request->get['action_type']) && $request->get['action_type'] == 'CREATE') 
{
  $Hooks->do_action('Before_Talla_Create_Form');
  include 'template/talla_create_form.php';
  $Hooks->do_action('After_Talla_Create_Form');
  exit();
}

// Talla edit form
if (isset($request->get['talla_id']) && isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') {
    
  // Fetch talla info
  $talla = $talla_model->getTalla($request->get['talla_id']);
  $Hooks->do_action('Before_Talla_Edit_Form', $talla);
  include 'template/talla_form.php';
  $Hooks->do_action('After_Talla_Edit_Form', $talla);
  exit();
}

// talla delete form
if (isset($request->get['talla_id']) && isset($request->get['action_type']) && $request->get['action_type'] == 'DELETE') {

  // Fetch talla info
  $talla = $talla_model->getTalla($request->get['talla_id']);
  $Hooks->do_action('Before_Talla_Delete_Form');
  include 'template/talla_form.php';
  $Hooks->do_action('Before_Talla_Delete_Form');
  exit();
}

/**
 *===================
 * INICIO DE TABLA DE DATOS
 *===================
 */
$Hooks->do_action('Before_Showing_Talla_List');

$where_query = 'b2s.store_id = ' . store_id();
 
// tabla de base de datos a utilizar
$table = "(SELECT tallas.*, b2s.status, b2s.sort_order FROM tallas 
  LEFT JOIN talla_to_store b2s ON (tallas.talla_id = b2s.talla_id) 
  WHERE 1=1 GROUP BY tallas.talla_id
) as tallas";
 
// Llave principal de la tabla
$primaryKey = 'talla_id';

$columns = array(
  array(
      'db' => 'talla_id',
      'dt' => 'DT_RowId',
      'formatter' => function( $d, $row ) {
          return 'row_'.$d;
      }
  ),
  array( 'db' => 'talla_id', 'dt' => 'talla_id' ),
  array( 
    'db' => 'talla_name',   
    'dt' => 'talla_name' ,
    'formatter' => function($d, $row) {
        return ucfirst($row['talla_name']);
    }
  ),
  array( 'db' => 'code_name',   'dt' => 'code_name' ),
  array( 
    'db' => 'talla_id',   
    'dt' => 'total_product' ,
    'formatter' => function($d, $row) {
      return total_product_of_talla($row['talla_id']);
    }
  ),
  array( 
    'db' => 'status',   
    'dt' => 'status',
    'formatter' => function($d, $row) {
      return $row['status'] 
        ? '<span class="label label-success">'.trans('text_active').'</span>' 
        : '<span class="label label-warning">' .trans('text_inactive').'</span>';
    }
  ),
  array( 
    'db' => 'talla_id',   
    'dt' => 'btn_view' ,
    'formatter' => function($d, $row) {
        return '<a id="view-talla" class="btn btn-sm btn-block btn-info" href="talla_profile.php?talla_id='.$row['talla_id'].'" title="'.trans('button_view_profile').'"><i class="fa fa-fw fa-user"></i></a>';
    }
  ),
  array( 
    'db' => 'talla_id',   
    'dt' => 'btn_edit' ,
    'formatter' => function($d, $row) {
      if (DEMO && $row['talla_id'] == 1) {          
        return'<button class="btn btn-sm btn-block btn-default" type="button" disabled><i class="fa fa-pencil"></i></button>';
      }
      return '<button id="edit-talla" class="btn btn-sm btn-block btn-primary" type="button" title="'.trans('button_edit').'"><i class="fa fa-fw fa-pencil"></i></button>';
    }
  ),
  array( 
    'db' => 'talla_id',   
    'dt' => 'btn_delete' ,
    'formatter' => function($d, $row) {
      if (DEMO && $row['talla_id'] == 1) {          
        return'<button class="btn btn-sm btn-block btn-default" type="button" disabled><i class="fa fa-trash"></i></button>';
      }
      return '<button id="delete-talla" class="btn btn-sm btn-block btn-danger" type="button" title="'.trans('button_delete').'"><i class="fa fa-fw fa-trash"></i></button>';
    }
  )
);



echo json_encode(
  SSP::simple($request->get, $sql_details, $table, $primaryKey, $columns)
  
);

$Hooks->do_action('After_Showing_Talla_List');

/**
 *===================
 * FIN TABLA DE DATOS
 *===================
 */