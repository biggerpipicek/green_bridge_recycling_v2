<?php
/**
 * Charts 4 PHP
 *
 * @author Shani <support@chartphp.com> - http://www.chartphp.com
 * @version 1.2.0
 * @license: see license.txt included in package
 */
 
include("../../config.php");
include("../../lib/inc/chartphp_dist.php");

$p = new chartphp();

$p->data_sql = "select c.categoryname, sum(a.quantity) as Sales
				from products b, `order details` a, categories c
				where a.productid = b.productid and c.categoryid = b.categoryid
				group by c.categoryid
				order by c.categoryid";
					
// Line Data
$p->chart_type = "bar";

// Common Options
$p->title = "Category Sales";
$p->xlabel = "Category";
$p->ylabel = "Sales";

$color = array("#1AAF5D","#F2C500","#F45B00","#8E0000","#0E948C");
$p->color = $color[rand(0,4)];

$out = $p->render('c1');
?>
<!DOCTYPE html>
<html>
	<head>
		<script src="../../lib/js/jquery.min.js"></script>
		<script src="../../lib/js/chartphp.js"></script>
		<link rel="stylesheet" href="../../lib/js/chartphp.css">
	</head>

	<body>
		<div style="width:40%; min-width:450px;">
			<?php echo $out; ?>
		</div>
	</body>
</html>


