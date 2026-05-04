<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "OK - PHP funguje";


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
    "../../chartphp/lib/js/chartphp.js"
];

// -----------------------------
// HELPER FUNCTION
// -----------------------------
function fetchSingle($conn, $sql) {
    $res = mysqli_query($conn, $sql);
    return $res ? mysqli_fetch_assoc($res) : ['count' => 0];
}

// -----------------------------
// DATA FETCHING
// -----------------------------

// Total Orders
$total_res = fetchSingle($conn, "SELECT COUNT(*) as count FROM orders");

// Outgoing this month
$month_res = fetchSingle($conn, "
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE status = 'outgoing'
    AND MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");

// Pending
$pending_res = fetchSingle($conn, "
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE status = 'not_approved'
");

// Value this month
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
    WHERE status = 'outgoing'
    AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
    AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
");

$previous_month = $prev_res['count'] ?? 0;

$percentage = 0;
if ($previous_month > 0) {
    $percentage = (($current_month - $previous_month) / $previous_month) * 100;
}

// -----------------------------
// CHART DATA (REAL DATA)
// -----------------------------

$chart_sql = "
    SELECT DATE(created_at) as date, COUNT(*) as total
    FROM orders
    WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 14 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";

$chart_result = mysqli_query($conn, $chart_sql);

$chart_data = [];
if ($chart_result) {
    while ($row = mysqli_fetch_assoc($chart_result)) {
        $chart_data[] = array($row['date'], (int)$row['total']);
    }
}

// fallback if empty
if (empty($chart_data)) {
    $chart_data[] = array(date("Y-m-d"), 0);
}

// -----------------------------
// CHART INIT
// -----------------------------
$p = new chartphp();
$p->chart_type = "area";
$p->data = array($chart_data);
$p->title = "Orders Trend (Last 14 Days)";
$out = $p->render('c1');

// -----------------------------
// RECENT ORDERS
// -----------------------------
$recent_sql = "
    SELECT order_number, customer_name, status 
    FROM orders 
    ORDER BY created_at DESC 
    LIMIT 5
";

$recent_result = mysqli_query($conn, $recent_sql);

include "../../build/header.php";
?>

<div class="container-fluid px-4 py-4">
    <div class="row g-3 mb-4">

        <!-- TOTAL -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Total Orders</small>
                <h4 class="fw-bold"><?= number_format($total_res['count'] ?? 0) ?></h4>
            </div>
        </div>

        <!-- OUTGOING -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Outgoing Orders</small>
                <h4 class="fw-bold"><?= $current_month ?></h4>
                <small class="<?= $percentage >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= round($percentage, 1) ?>%
                </small>
            </div>
        </div>

        <!-- PENDING -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Pending</small>
                <h4 class="fw-bold text-danger"><?= $pending_res['count'] ?? 0 ?></h4>
            </div>
        </div>

        <!-- VALUE -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Value</small>
                <h4 class="fw-bold">
                    €<?= number_format($value_res['total'] ?? 0, 2) ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- CHART -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold mb-3">Orders Overview</h6>
                <?= $out ?>
            </div>
        </div>

        <!-- RECENT -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold mb-3">Recent Orders</h6>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['order_number']) ?></td>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] == 'outgoing' ? 'success' : ($row['status'] == 'not_approved' ? 'danger' : 'secondary') ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-muted">No orders yet</td></tr>
                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
                <h6 class="fw-bold">Quick Actions</h6>
                <button class="btn btn-outline-primary btn-sm w-100 mb-2">+ Outgoing</button>
                <button class="btn btn-outline-primary btn-sm w-100 mb-2">+ Incoming</button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold">System</h6>
                <small class="text-muted">No alerts</small>
            </div>
        </div>

    </div>
</div>

<?php include "../../build/footer.php"; ?>