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

$p->data_sql = "select strftime('%Y-%m',o.orderdate) as Year, sum(d.quantity) as Sales
					from `order details` d, orders o
					where o.orderid = d.orderid
					group by strftime('%Y-%m',o.orderdate) limit 10";
					
// Line Data
$p->chart_type = "line";

// Common Options
$p->title = "Sales Summary";
$p->xlabel = "Month";
$p->ylabel = "Sales";

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


