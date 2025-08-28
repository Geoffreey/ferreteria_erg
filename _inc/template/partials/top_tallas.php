<?php 
if (top_tallas(from(), to(), 5)) {
  foreach (top_tallas(from(), to(), 5) as $row) {
    $top_tallas['name'][] = limit_char(get_the_talla($row['talla_id'], 'talla_name'),15);
    $top_tallas['quantity'][] = currency_format($row['quantity']);
  } 
} else {
  $top_tallas['name'] = array();
  $top_tallas['quantity'] = array();
}
?>

<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">
      <?php echo trans('text_top_tallas'); ?>
    </h3>
  </div>
  <div class="box-body">
    <canvas id="topTallas" height="250"></canvas>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
  var topTallas = <?php echo json_encode(array_values($top_tallas['name'])); ?>;
  var topTallasQuantity = <?php echo json_encode(array_values($top_tallas['quantity'])); ?>;
  var ctx = document.getElementById("topTallas");
  var myPieChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: topTallas,
        datasets: [
            {
              label: "Top",
              backgroundColor: ["#e6194B", "#f58231", "#ffe119", "#3cb44b", "#4363d8", "#f032e6", "#42d4f4", "#9A6324", "#469990", "#fabebe"],
              data: topTallasQuantity
            },
        ],
      },
      options: {
          responsive: true,
          tooltips: {
              mode: 'index',
              intersect: true
          },
          hover: {
              mode: 'nearest',
              intersect: true
          }
      }
  });
});
</script>