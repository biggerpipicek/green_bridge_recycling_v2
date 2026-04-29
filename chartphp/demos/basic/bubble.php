<?php
/**
 * Charts 4 PHP
 *
 * @author Shani <support@chartphp.com> - http://www.chartphp.com
 * @version 1.2.0
 * @license: see license.txt included in package
 */
 
include("../../lib/inc/chartphp_dist.php");

$p = new chartphp();

$p->data = array(array(array(11, 123, 1236, "Acura"), array(45, 92, 1067, "Alfa Romeo"), array(24, 104, 1176, "AM General"), array(50, 23, 610, "Aston Martin Lagonda"), array(18, 17, 539, "Audi"), array(7, 89, 864, "BMW"), array(2, 13, 1026, "Bugatti")));
$p->chart_type = "bubble";

// Common Options
$p->title = "Bubble Chart";
$p->xlabel = "My X Axis";
$p->ylabel = "My Y Axis";

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


