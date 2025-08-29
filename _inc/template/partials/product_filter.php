  <!--Boton filtro por categoria-->
<div class="btn-group"> 
  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
      <span class="sr-only">Toggle Dropdown</span>
      <span class="fa fa-fw fa-filter"></span>
      <?php if (isset($request->get['category_id'])) : ?>
        <?php echo get_the_category($request->get['category_id'], 'category_name'); ?> (<?php echo get_total_category_item($request->get['category_id']); ?>)
      <?php else: ?>
        <?php echo trans('label_category'); ?>
      <?php endif; ?>
      <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" role="menu">
    <li>
      <a href="product.php">
      <span>
          <?php echo trans('label_all_product'); ?>
        </span>
      </a>
    </li>
    <?php
    $statement = db()->prepare("SELECT DISTINCT(`category_id`) FROM `products` LEFT JOIN `product_to_store` p2s ON (`products`.`p_id` = `p2s`.`product_id`) WHERE `p2s`.`store_id` = ?");
    $statement->execute(array(store_id()));
    $category = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($category as $category) :
      $category_id = $category['category_id'];
      $statement1 = db()->prepare("SELECT * FROM `categorys` WHERE `category_id` = ?");
      $statement1->execute(array($category_id));
      $category = $statement1->fetch(PDO::FETCH_ASSOC);
      if ($category) : ?>
        <li class="category_name<?php echo isset($request->get['category_id']) && $request->get['category_id'] == $category_id ? ' active' : null; ?>">
            <a href="product.php?category_id=<?php echo $category_id; ?>">
              <span><?php echo $category['category_name']; ?> (<?php echo get_total_category_item($category_id); ?>)</span>
            </a>
        </li>
    <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</div>