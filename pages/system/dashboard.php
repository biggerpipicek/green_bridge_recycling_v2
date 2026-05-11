<?php
// MICHAEL D. PHILLIPS - UPDATED 05/11/2026
// DASHBOARD PAGE

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. INCLUDES & CONFIG
require "../../build/auth.php";      
require "../../build/functions.php"; 
include "../../chartphp/lib/inc/chartphp_dist.php";

// 2. HELPER FUNCTION
if (!function_exists('fetchSingle')) {
    function fetchSingle($conn, $sql) {
        $res = mysqli_query($conn, $sql);
        if (!$res) return ['count' => 0, 'total' => 0];
        return mysqli_fetch_assoc($res) ?? ['count' => 0, 'total' => 0];
    }
}

// 3. FILTER LOGIC
$period      = $_GET['period'] ?? 'month';
$type_filter = $_GET['type'] ?? 'all'; 
$from_date   = $_GET['from'] ?? '';
$to_date     = $_GET['to'] ?? '';

// Determine Transaction Type SQL
$type_where = "1=1"; 
if ($type_filter === 'out') {
    $type_where = "type IN ('out', 'guh-out')";
} elseif ($type_filter === 'in') {
    $type_where = "type IN ('in', 'guh-in')";
}

// Determine Date Range SQL
if (!empty($from_date) && !empty($to_date)) {
    // Priority: Custom Date Range
    $date_where = "DATE(created_at) BETWEEN '$from_date' AND '$to_date'";
} else {
    // Fallback: Preset Intervals
    $intervals = [
        'day'      => 'INTERVAL 1 DAY',
        'week'     => 'INTERVAL 7 DAY',
        'month'    => 'INTERVAL 30 DAY',
        'semi'     => 'INTERVAL 6 MONTH',
        'annually' => 'INTERVAL 1 YEAR'
    ];
    $sql_interval = $intervals[$period] ?? 'INTERVAL 30 DAY';
    $date_where = "created_at >= DATE_SUB(NOW(), $sql_interval)";
}

// ---------------------------------------------------------
// 4. DATA FETCHING
// ---------------------------------------------------------

// A. Filtered Stats (Main big numbers)
$filtered_stats = fetchSingle($conn, "
    SELECT COUNT(*) as count, SUM(price) as total 
    FROM orders 
    WHERE $type_where AND $date_where
");

// B. Global Dashboard Stats (Fixed top row)
$total_res   = fetchSingle($conn, "SELECT COUNT(*) as count FROM orders");
$pending_res = fetchSingle($conn, "SELECT COUNT(*) as count FROM orders WHERE approve_status = 'not approved'");
$value_res   = fetchSingle($conn, "
    SELECT SUM(price) as total FROM orders 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");

// C. Month Comparison
$month_res     = fetchSingle($conn, "SELECT COUNT(*) as count FROM orders WHERE type IN ('out', 'guh-out') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$current_month = $month_res['count'];
$prev_res      = fetchSingle($conn, "SELECT COUNT(*) as count FROM orders WHERE type IN ('out', 'guh-out') AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)");
$prev_month    = $prev_res['count'];

$percentage = ($prev_month > 0) ? (($current_month - $prev_month) / $prev_month) * 100 : 0;

// ---------------------------------------------------------
// 5. CHART DATA (Stays as 14-day trend)
// ---------------------------------------------------------
$chart_sql = "
    SELECT DATE(created_at) as date,
           SUM(CASE WHEN type IN ('out', 'guh-out') THEN 1 ELSE 0 END) as out_count,
           SUM(CASE WHEN type IN ('in', 'guh-in') THEN 1 ELSE 0 END) as in_count
    FROM orders
    WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 14 DAY)
    GROUP BY DATE(created_at) ORDER BY date ASC";

$chart_result = mysqli_query($conn, $chart_sql);
$out_series = []; $in_series = [];
while ($row = mysqli_fetch_assoc($chart_result)) {
    $out_series[] = [$row['date'], (int)$row['out_count']];
    $in_series[]  = [$row['date'], (int)$row['in_count']];
}
if (empty($out_series)) { $out_series[] = [date("Y-m-d"), 0]; $in_series[] = [date("Y-m-d"), 0]; }

$p = new chartphp();
$p->chart_type = "area";
$p->data = [$out_series, $in_series];
$p->series_color = ["#ffc107", "#0548ad"];
$p->options["series"] = [["label" => "Outgoing"], ["label" => "Incoming"]];
$p->options["legend"] = ["show" => true, "location" => "ne"];
$out = $p->render('c1');

// 6. RECENT ORDERS
$recent_sql = "
    SELECT o.order_no, p.name AS partner_name, o.type, o.approve_status, o.order_status 
    FROM orders o 
    LEFT JOIN partners p ON o.partner_id = p.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
";

$recent_result = mysqli_query($conn, $recent_sql);

$page_title = "GBR Dashboard";
include "../../build/header.php";
?>

<div class="container-fluid px-4 py-4">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Quick Period</label>
                    <select name="period" class="form-select form-select-sm">
                        <option value="day" <?= $period == 'day' ? 'selected' : '' ?>>Last 24 Hours</option>
                        <option value="week" <?= $period == 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                        <option value="semi" <?= $period == 'semi' ? 'selected' : '' ?>>Last 6 Months</option>
                        <option value="annually" <?= $period == 'annually' ? 'selected' : '' ?>>Annual (1 Year)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="<?= $from_date ?>">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="<?= $to_date ?>">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="all" <?= $type_filter == 'all' ? 'selected' : '' ?>>All (In & Out)</option>
                        <option value="in" <?= $type_filter == 'in' ? 'selected' : '' ?>>Inbound</option>
                        <option value="out" <?= $type_filter == 'out' ? 'selected' : '' ?>>Outbound</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <a href="dashboard.php" class="btn btn-link btn-sm text-decoration-none">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Total Orders (System)</small>
                <h4 class="fw-bold"><?= number_format($total_res['count']) ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Filtered Count</small>
                <h4 class="fw-bold"><?= number_format($filtered_stats['count']) ?></h4>
                <small class="<?= $percentage >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= ($percentage >= 0 ? '↑ ' : '↓ ') . round(abs($percentage), 1) ?>% vs prev. month
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Pending Action</small>
                <h4 class="fw-bold text-danger"><?= $pending_res['count'] ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Monthly Revenue</small>
                <h4 class="fw-bold">€<?= number_format($value_res['total'] ?? 0, 2) ?></h4>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold mb-3">Activity Trend</h6>
                <?php echo $out; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <h6 class="fw-bold mb-3">Recent Orders</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr class="text-muted small"><th>Order #</th><th>Partner</th><th>Status</th></tr>
                        </thead>
                        <tbody class="small">
                            <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['order_no'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['partner_name'] ?? 'Unknown Partner') ?></td>
                                    <td>
                                        <?php 
                                            $status = $row['approve_status'] ?? 'pending';
                                            $badge_class = ($status == 'approved') ? 'bg-success' : 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No recent orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../../build/footer.php"; ?>