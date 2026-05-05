<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// -----------------------------
// INCLUDES & CONFIG
// -----------------------------
require "../../build/auth.php";
require "../../build/functions.php";
include "../../chartphp/lib/inc/chartphp_dist.php";

$page_title = "GBR Dashboard";
$extra_css = [
    "../../chartphp/lib/js/chartphp.css",
    "../../styles/dashboard.css"
];
$extra_js = [
    "../../chartphp/lib/js/jquery.min.js",
    "../../chartphp/lib/js/chartphp.js",
    "../../chartphp/lib/js/plugins/jqplot.dateAxisRenderer.js",
    "../../chartphp/lib/js/plugins/jqplot.highlighter.js",
    "../../chartphp/lib/js/plugins/jqplot.cursor.js",
    "../../chartphp/lib/js/plugins/jqplot.enhancedLegendRenderer.js",
    "../../js/dashboard.js"
];

// -----------------------------
// HELPER FUNCTION
// -----------------------------
function fetchSingle($conn, $sql) {
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        return ['count' => 0, 'total' => 0];
    }
    return mysqli_fetch_assoc($res);
}

// -----------------------------
// DATA FETCHING
// -----------------------------

// 1. Total Orders
$total_res = fetchSingle($conn, "SELECT COUNT(*) as count FROM orders");

// 2. Outgoing this month (Changed 'status' to 'type' to match your DB)
$month_res = fetchSingle($conn, "
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE type IN ('out', 'guh-out')
    AND MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");

// Incoming this month (NEW)
$incoming_res = fetchSingle($conn, "
    SELECT COUNT(*) as count FROM orders 
    WHERE type IN ('in', 'guh-in')
    AND MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");

// 3. Pending
$pending_res = fetchSingle($conn, "
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE approve_status = 'not approved'
");

// 4. Value this month (Removed GROUP BY for single dashboard total)
$value_res = fetchSingle($conn, "
    SELECT SUM(price) as total
    FROM orders
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");

// -----------------------------
// MONTH COMPARISON (for %)
// -----------------------------
$current_month = $month_res['count'] ?? 0;

$prev_res = fetchSingle($conn, "
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE type IN ('out', 'guh-out')
    AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
    AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
");

$previous_month = $prev_res['count'] ?? 0;

$percentage = 0;
if ($previous_month > 0) {
    $percentage = (($current_month - $previous_month) / $previous_month) * 100;
}

// -----------------------------
// CHART DATA (Orders Trend)
// -----------------------------
$chart_sql = "
    SELECT 
        DATE(created_at) as date,
        SUM(CASE WHEN type IN ('out', 'guh-out') THEN 1 ELSE 0 END) as out_count,
        SUM(CASE WHEN type IN ('in', 'guh-in') THEN 1 ELSE 0 END) as in_count
    FROM orders
    WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 14 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";

$chart_result = mysqli_query($conn, $chart_sql);
$out_series = [];
$in_series = [];

if ($chart_result) {
    while ($row = mysqli_fetch_assoc($chart_result)) {
        $out_series[] = array($row['date'], (int)$row['out_count']);
        $in_series[] = array($row['date'], (int)$row['in_count']);
    }
}

// Fallback
if (empty($out_series)) {
    $out_series[] = [date("Y-m-d"), 0];
    $in_series[] = [date("Y-m-d"), 0];
}

// CHART INIT
$p = new chartphp();
$p->chart_type = "area";
$p->data = array($out_series, $in_series);
$p->series_color = array("#ffc107", "#0548ad"); // Add this line

// Explicitly set series labels
$p->options["series"] = array(
    array("label" => "Outgoing"),
    array("label" => "Incoming")
);

$p->options["legend"] = array(
    "show"      => true,
    "location"  => "ne",
    "placement" => "insideGrid"
);

$p->options["highlighter"] = array(
    "show"              => true,
    "showMarker"        => true,
    "tooltipLocation"   => "n",
    "useAxesFormatters" => true,
    "formatString"      => "%s: %d orders"
);

$p->options["cursor"] = array("show" => true);

// fillToZero stops curves dipping below 0, smooth false prevents bezier weirdness
$p->options["seriesDefaults"] = array(
    "fill"        => true,
    "fillToZero"  => true,
    "rendererOptions" => array("smooth" => false)
);

// Lock Y axis to never go below 0
$p->options["axes"] = array(
    "yaxis" => array(
        "min"  => 0,
        "pad"  => 0,
        "tickOptions" => array("formatString" => "%d")
    )
);

$p->title = "Activity Trend (Last 14 Days)";
$out = $p->render('c1');

// -----------------------------
// RECENT ORDERS (Select correct columns)
// -----------------------------
$recent_sql = "
    SELECT 
        o.order_no, 
        p.name AS partner_name, 
        o.type, 
        o.approve_status,
        o.order_status 
    FROM orders o
    JOIN partners p ON o.partner_id = p.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
";
$recent_result = mysqli_query($conn, $recent_sql);

include "../../build/header.php";
?>

<div class="container-fluid px-4 py-4">
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Total Orders</small>
                <h4 class="fw-bold"><?= number_format($total_res['count'] ?? 0) ?></h4>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Outgoing Orders</small>
                <h4 class="fw-bold"><?= $current_month ?></h4>
                <small class="<?= $percentage >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= ($percentage >= 0 ? '+' : '') . round($percentage, 1) ?>%
                </small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Pending</small>
                <h4 class="fw-bold text-danger"><?= $pending_res['count'] ?? 0 ?></h4>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Value (Month)</small>
                <h4 class="fw-bold">
                    €<?= number_format($value_res['total'] ?? 0, 2) ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold mb-3">Orders Overview</h6>
                <?= $out ?>

                <!-- Custom legend -->
                <div class="d-flex gap-3 justify-content-center mt-2">
                    <span class="d-flex align-items-center gap-1">
                        <span style="display:inline-block; width:14px; height:14px; background:#ffc107; border-radius:3px;"></span>
                        <small class="text-muted">Outgoing</small>
                    </span>
                    <span class="d-flex align-items-center gap-1">
                        <span style="display:inline-block; width:14px; height:14px; background:#0548ad; border-radius:3px;"></span>
                        <small class="text-muted">Incoming</small>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold mb-3">Recent Orders</h6>

                <?php
                    // Define your mappings at the top
                    $approve_classes = [
                        "approved" => "badge bg-success",
                        "not approved" => "badge bg-danger"
                    ];

                    $order_classes = [
                        "created" => "badge bg-danger",
                        "received" => "badge bg-warning",
                        "in process" => "badge bg-info",
                        "completed" => "badge bg-success",
                        "cancelled" => "badge bg-danger"
                    ];
                    ?>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Approval</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['order_no']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($row['partner_name'])) ?></td>
                                
                                <td>
                                    <span class="<?= $approve_classes[$row['approve_status']] ?? 'badge bg-secondary' ?>">
                                        <?= ucfirst(htmlspecialchars($row['approve_status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="<?= $order_classes[$row['order_status']] ?? 'badge bg-secondary' ?>">
                                        <?= ucfirst(htmlspecialchars($row['order_status'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-muted text-center">No orders yet</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
                <h6 class="fw-bold">Quick Actions</h6>
                <a href="orders.php?action=outgoing_orders" class="btn btn-outline-primary btn-sm w-100 mb-2">+ Outgoing</a>
                <a href="orders.php?action=incoming_orders" class="btn btn-outline-primary btn-sm w-100 mb-2">+ Incoming</a>
                <a href="guhring_orders.php?action=go" class="btn btn-outline-primary btn-sm w-100 mb-2">+ Gühring</a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold">System</h6>
                <small class="text-muted">No alerts</small>
            </div>
        </div>

    </div>
</div>

<?php include "../../build/footer.php"; ?>