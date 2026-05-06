<?php
// MICHAEL D. PHILLIPS - 04/05/2026
// DASHBOARD PAGE

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---------------------------------------------------------
// 1. INCLUDES & CONFIG (Always first to establish $conn)
// ---------------------------------------------------------
require "../../build/auth.php";      
require "../../build/functions.php"; 
include "../../chartphp/lib/inc/chartphp_dist.php";

// ---------------------------------------------------------
// 2. HELPER FUNCTION (Defined once, before any usage)
// ---------------------------------------------------------
if (!function_exists('fetchSingle')) {
    function fetchSingle($conn, $sql) {
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            return ['count' => 0, 'total' => 0];
        }
        return mysqli_fetch_assoc($res);
    }
}

// ---------------------------------------------------------
// 3. FILTER LOGIC
// ---------------------------------------------------------
$period = $_GET['period'] ?? 'month';
$type_filter = $_GET['type'] ?? 'all'; 

$intervals = [
    'day'      => 'INTERVAL 1 DAY',
    'week'     => 'INTERVAL 7 DAY',
    'month'    => 'INTERVAL 30 DAY',
    'semi'     => 'INTERVAL 6 MONTH',
    'annually' => 'INTERVAL 1 YEAR'
];
$sql_interval = $intervals[$period] ?? 'INTERVAL 30 DAY';

$type_where = "1=1"; 
if ($type_filter === 'out') {
    $type_where = "type IN ('out', 'guh-out')";
} elseif ($type_filter === 'in') {
    $type_where = "type IN ('in', 'guh-in')";
}

// ---------------------------------------------------------
// 4. DATA FETCHING (Now $conn is guaranteed to exist)
// ---------------------------------------------------------

// A. Filtered Stats (Main big numbers based on dropdowns)
$filtered_res = fetchSingle($conn, "
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE $type_where 
    AND created_at >= DATE_SUB(NOW(), $sql_interval)
");

$filtered_value = fetchSingle($conn, "
    SELECT SUM(price) as total 
    FROM orders 
    WHERE $type_where 
    AND created_at >= DATE_SUB(NOW(), $sql_interval)
");

// B. Global Dashboard Stats (Top row cards)
$total_res = fetchSingle($conn, "SELECT COUNT(*) as count FROM orders");

$month_res = fetchSingle($conn, "
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE type IN ('out', 'guh-out')
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");

$pending_res = fetchSingle($conn, "
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE approve_status = 'not approved'
");

$value_res = fetchSingle($conn, "
    SELECT SUM(price) as total
    FROM orders
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");

// C. Month Comparison for Percentage
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

// ---------------------------------------------------------
// 5. CHART DATA (Orders Trend)
// ---------------------------------------------------------
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

if (empty($out_series)) {
    $out_series[] = [date("Y-m-d"), 0];
    $in_series[] = [date("Y-m-d"), 0];
}

// Chart Initialization
$p = new chartphp();
$p->chart_type = "area";
$p->data = array($out_series, $in_series);
$p->series_color = array("#ffc107", "#0548ad");
$p->options["series"] = array(array("label" => "Outgoing"), array("label" => "Incoming"));
$p->options["legend"] = array("show" => true, "location" => "ne", "placement" => "insideGrid");
$p->options["highlighter"] = array("show" => true, "formatString" => "%s: %d orders");
$p->options["axes"] = array("yaxis" => array("min" => 0, "tickOptions" => array("formatString" => "%d")));
$p->title = "Activity Trend (Last 14 Days)";
$out = $p->render('c1');

// ---------------------------------------------------------
// 6. RECENT ORDERS
// ---------------------------------------------------------
$recent_sql = "
    SELECT o.order_no, p.name AS partner_name, o.type, o.approve_status, o.order_status 
    FROM orders o
    JOIN partners p ON o.partner_id = p.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
";
$recent_result = mysqli_query($conn, $recent_sql);

// ---------------------------------------------------------
// 7. PAGE ASSETS
// ---------------------------------------------------------
$page_title = "GBR Dashboard";
$extra_css = ["../../chartphp/lib/js/chartphp.css", "../../styles/dashboard.css"];
$extra_js = [
    "../../chartphp/lib/js/jquery.min.js",
    "../../chartphp/lib/js/chartphp.js",
    "../../chartphp/lib/js/plugins/jqplot.dateAxisRenderer.js",
    "../../chartphp/lib/js/plugins/jqplot.highlighter.js",
    "../../chartphp/lib/js/plugins/jqplot.cursor.js",
    "../../chartphp/lib/js/plugins/jqplot.enhancedLegendRenderer.js",
    "../../js/dashboard.js"
];

include "../../build/header.php";
?>

<div class="container-fluid px-4 py-4">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="small text-muted d-block">Time Period</label>
                    <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="day" <?= $period == 'day' ? 'selected' : '' ?>>Last 24 Hours</option>
                        <option value="week" <?= $period == 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                        <option value="semi" <?= $period == 'semi' ? 'selected' : '' ?>>Last 6 Months</option>
                        <option value="annually" <?= $period == 'annually' ? 'selected' : '' ?>>Annual (1 Year)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted d-block">Transaction Type</label>
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" <?= $type_filter == 'all' ? 'selected' : '' ?>>All (In & Out)</option>
                        <option value="in" <?= $type_filter == 'in' ? 'selected' : '' ?>>Bought (Inbound)</option>
                        <option value="out" <?= $type_filter == 'out' ? 'selected' : '' ?>>Sold (Outbound)</option>
                    </select>
                </div>
                <div class="col-md-6 text-end align-self-end">
                    <a href="dashboard.php" class="btn btn-link btn-sm text-decoration-none">Reset Filters</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Total Orders</small>
                <h4 class="fw-bold"><?= number_format($total_res['count'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Outgoing (30d)</small>
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
                <h4 class="fw-bold">€<?= number_format($value_res['total'] ?? 0, 2) ?></h4>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold mb-3">Orders Overview</h6>
                <?= $out ?>
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
                $approve_classes = ["approved" => "badge bg-success", "not approved" => "badge bg-danger"];
                $order_classes = ["created" => "badge bg-danger", "received" => "badge bg-warning", "in process" => "badge bg-info", "completed" => "badge bg-success", "cancelled" => "badge bg-danger"];
                ?>
                <table class="table table-sm">
                    <thead>
                        <tr><th>Order</th><th>Customer</th><th>Approval</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['order_no']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($row['partner_name'])) ?></td>
                                <td><span class="<?= $approve_classes[$row['approve_status']] ?? 'badge bg-secondary' ?>"><?= ucfirst(htmlspecialchars($row['approve_status'])) ?></span></td>
                                <td><span class="<?= $order_classes[$row['order_status']] ?? 'badge bg-secondary' ?>"><?= ucfirst(htmlspecialchars($row['order_status'])) ?></span></td>
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