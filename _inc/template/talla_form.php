<h4 class="sub-title">
  <?php echo trans('text_delete_title'); ?>
</h4>
<form class="form-horizontal" id="talla-del-form" action="talla.php" method="post">
  <input type="hidden" id="action_type" name="action_type" value="DELETE">
  <input type="hidden" id="talla_id" name="talla_id" value="<?php echo $talla['talla_id']; ?>">
  <h4 class="box-title text-center">
    <?php echo trans('text_delete_instruction'); ?>
  </h4>
  <div class="box-body">
    <div class="form-group">
      <label for="insert_to" class="col-sm-4 control-label">
        <?php echo trans('label_insert_content_into'); ?>
       </label>
      <div class="col-sm-6">
        <div class="radio">
          <input type="radio" id="insert_to" value="insert_to" name="delete_action" checked="checked">
          <select name="new_talla_id" class="form-control">
              <option value="">
                <?php echo trans('text_select'); ?>
               </option>
            <?php foreach (get_tallas() as $the_talla) : ?>
              <?php if($the_talla['talla_id'] == $talla['talla_id']) continue ?>
              <option value="<?php echo $the_talla['talla_id']; ?>">
                <?php echo $the_talla['talla_name']; ?>
               </option>
            <?php endforeach; ?>
          </select> 
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label"></label>
      <div class="col-sm-6">
        <button id="talla-delete" data-form="#talla-del-form" data-datatable="#talla-talla-list" class="btn btn-danger" name="btn_edit_talla" data-loading-text="Deleting...">
          <span class="fa fa-fw fa-trash"></span>
          <?php echo trans('button_delete'); ?>
        </button>
      </div>
    </div>
  </div>
</form>