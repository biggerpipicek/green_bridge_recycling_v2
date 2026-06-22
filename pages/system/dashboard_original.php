<?php
// MICHAEL D. PHILLIPS - UPDATED 05/25/2026
// DASHBOARD PAGE (DYNAMIC CHART UPDATE & SECURITY FIX)

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. INCLUDES & CONFIG
require "../../build/fpdf.php";
require "../../build/auth.php";      
require "../../build/functions.php"; 
include "../../chartphp/lib/inc/chartphp_dist.php";

// 2. SECURE HELPER FUNCTION FOR DYNAMIC QUERIES
if (!function_exists('fetchSingleSecure')) {
    function fetchSingleSecure($conn, $sql, $types = "", $params = []) {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) return ['count' => 0, 'total' => 0];
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (!$res) return ['count' => 0, 'total' => 0];
        
        $data = mysqli_fetch_assoc($res) ?? ['count' => 0, 'total' => 0];
        mysqli_stmt_close($stmt);
        return $data;
    }
}

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
    "../../js/dashboard.js",
    "https://cdn.jsdelivr.net/npm/chart.js"
];

// 3. FILTER LOGIC & VALIDATION
// Changed default from 'month' to 'week' (7 days) as requested
$allowed_periods = ['day', 'week', 'month', 'semi', 'annually'];
$allowed_types   = ['all', 'in', 'out'];

$period      = in_array($_GET['period'] ?? '', $allowed_periods) ? $_GET['period'] : 'week';
$type_filter = in_array($_GET['type'] ?? '', $allowed_types)    ? $_GET['type']   : 'all';
$from_date   = $_GET['from'] ?? '';
$to_date     = $_GET['to'] ?? '';

// Determine Transaction Type SQL
$type_where = "1=1"; 
if ($type_filter === 'out') {
    $type_where = "type IN ('out', 'guh-out')";
} elseif ($type_filter === 'in') {
    $type_where = "type IN ('in', 'guh-in')";
}

// Prepare arrays for secure parameter binding
$bind_types = "";
$bind_params = [];

// Determine Date Range SQL
if (!empty($from_date) && !empty($to_date)) {
    // Custom Date Range using safe placeholders
    $date_where = "DATE(created_at) BETWEEN ? AND ?";
    $bind_types .= "ss";
    $bind_params[] = $from_date;
    $bind_params[] = $to_date;
} else {
    // Fallback: Preset Intervals
    $intervals = [
        'day'      => 'INTERVAL 1 DAY',
        'week'     => 'INTERVAL 7 DAY',
        'month'    => 'INTERVAL 30 DAY',
        'semi'     => 'INTERVAL 6 MONTH',
        'annually' => 'INTERVAL 1 YEAR'
    ];
    $sql_interval = $intervals[$period] ?? 'INTERVAL 7 DAY';
    $date_where = "created_at >= DATE_SUB(NOW(), $sql_interval)";
}

// ---------------------------------------------------------
// 4. DATA FETCHING
// ---------------------------------------------------------

// A. Filtered Stats (Main big numbers)
$filtered_stats_sql = "
    SELECT COUNT(*) as count, SUM(price) as total 
    FROM orders 
    WHERE $type_where AND $date_where
";
$filtered_stats = fetchSingleSecure($conn, $filtered_stats_sql, $bind_types, $bind_params);

// B. Global Dashboard Stats (Fixed top row)
$total_res   = fetchSingleSecure($conn, "SELECT COUNT(*) as count FROM orders");
$pending_res = fetchSingleSecure($conn, "SELECT COUNT(*) as count FROM orders WHERE approve_status = 'not approved'");
$value_res   = fetchSingleSecure($conn, "
    SELECT SUM(price) as total FROM orders 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");

// C. Month Comparison
$month_res     = fetchSingleSecure($conn, "SELECT COUNT(*) as count FROM orders WHERE type IN ('out', 'guh-out') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$current_month = $month_res['count'];
$prev_res      = fetchSingleSecure($conn, "SELECT COUNT(*) as count FROM orders WHERE type IN ('out', 'guh-out') AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)");
$prev_month    = $prev_res['count'];

$percentage = ($prev_month > 0) ? (($current_month - $prev_month) / $prev_month) * 100 : 0;

// ---------------------------------------------------------
// 5. CHART DATA (Now updates dynamically based on timeline filters)
// ---------------------------------------------------------
$chart_sql = "
    SELECT DATE(created_at) as date,
           SUM(CASE WHEN type IN ('out', 'guh-out') THEN 1 ELSE 0 END) as out_count,
           SUM(CASE WHEN type IN ('in', 'guh-in') THEN 1 ELSE 0 END) as in_count
    FROM orders
    WHERE $date_where
    GROUP BY DATE(created_at) 
    ORDER BY date ASC";

// Execute chart query safely using prepared statements
$chart_stmt = mysqli_prepare($conn, $chart_sql);
if ($chart_stmt) {
    if (!empty($bind_params)) {
        mysqli_stmt_bind_param($chart_stmt, $bind_types, ...$bind_params);
    }
    mysqli_stmt_execute($chart_stmt);
    $chart_result = mysqli_stmt_get_result($chart_stmt);
} else {
    $chart_result = false;
}

$out_series = []; $in_series = [];
if ($chart_result) {
    while ($row = mysqli_fetch_assoc($chart_result)) {
        $out_series[] = [$row['date'], (int)$row['out_count']];
        $in_series[]  = [$row['date'], (int)$row['in_count']];
    }
}

// Fallback if data array is empty
if (empty($out_series)) { 
    $out_series[] = [date("Y-m-d"), 0]; 
    $in_series[]  = [date("Y-m-d"), 0]; 
}

if ($chart_stmt) {
    mysqli_stmt_close($chart_stmt);
}

$out_labels = [];
$out_data = [];
$in_data = [];

foreach (array_map(null, $out_series, $in_series) as [$o, $i]) {
    $out_labels[] = $o[0];
    $out_data[]   = $o[1];
    $in_data[]    = $i[1];
}

$labels_json   = json_encode($out_labels);
$out_json      = json_encode($out_data);
$in_json       = json_encode($in_data);

// 6. RECENT ORDERS
$recent_sql = "
    SELECT o.id, o.order_no, p.name AS partner_name, o.type, o.approve_status, o.order_status 
    FROM orders o 
    LEFT JOIN partners p ON o.partner_id = p.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
";
$recent_result = mysqli_query($conn, $recent_sql);

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
                    <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from_date) ?>">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to_date) ?>">
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
                <canvas id="activityChart" height="300"></canvas>
                    <script>
                    new Chart(document.getElementById('activityChart'), {
                        type: 'line',
                        data: {
                            labels: <?= $labels_json ?>,
                            datasets: [
                                {
                                    label: 'Outgoing',
                                    data: <?= $out_json ?>,
                                    borderColor: '#ffc107',
                                    backgroundColor: 'rgba(255,193,7,0.15)',
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Incoming',
                                    data: <?= $in_json ?>,
                                    borderColor: '#0548ad',
                                    backgroundColor: 'rgba(5,72,173,0.15)',
                                    fill: true,
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true, ticks: { stepSize: 1 } }
                            },
                            plugins: {
                                legend: { position: 'top' }
                            }
                        }
                    });
                    </script>
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
                                        <td>
                                            <a href="pages/system/template/guhring_order.php?id=<?= $row['id'] ?>" 
                                            class="text-decoration-none fw-bold">
                                                <?= htmlspecialchars($row['order_no'] ?? 'N/A') ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($row['partner_name'] ?? 'Unknown Partner') ?></td>
                                        <td>
                                            <?php 
                                                $status = $row['order_status'] ?? 'created';
                                                $badge_class = match($status) {
                                                    'completed' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                    'in process' => 'bg-warning text-dark',
                                                    'received'  => 'bg-info text-dark',
                                                    default     => 'bg-secondary'
                                                };
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= ucfirst(htmlspecialchars($status)) ?></span>
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