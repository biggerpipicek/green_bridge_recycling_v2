<?php
// MICHAEL D. PHILLIPS - UPDATED 06/10/2026
// DASHBOARD PAGE — CHARTS EXPANDED + PDF EXPORT

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. INCLUDES & CONFIG
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
$bind_types  = "";
$bind_params = [];

// Determine Date Range SQL
if (!empty($from_date) && !empty($to_date)) {
    $date_where   = "DATE(created_at) BETWEEN ? AND ?";
    $bind_types  .= "ss";
    $bind_params[] = $from_date;
    $bind_params[] = $to_date;
} else {
    $intervals = [
        'day'      => 'INTERVAL 1 DAY',
        'week'     => 'INTERVAL 7 DAY',
        'month'    => 'INTERVAL 30 DAY',
        'semi'     => 'INTERVAL 6 MONTH',
        'annually' => 'INTERVAL 1 YEAR'
    ];
    $sql_interval = $intervals[$period] ?? 'INTERVAL 7 DAY';
    $date_where   = "created_at >= DATE_SUB(NOW(), $sql_interval)";
}

// ---------------------------------------------------------
// 4. DATA FETCHING
// ---------------------------------------------------------

// A. Filtered Stats
$filtered_stats_sql = "
    SELECT COUNT(*) as count, SUM(price) as total
    FROM orders
    WHERE $type_where AND $date_where
";
$filtered_stats = fetchSingleSecure($conn, $filtered_stats_sql, $bind_types, $bind_params);

// B. Global Stats
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
$percentage    = ($prev_month > 0) ? (($current_month - $prev_month) / $prev_month) * 100 : 0;

// ---------------------------------------------------------
// 5. CHART DATA: Activity Trend
// ---------------------------------------------------------
$chart_sql = "
    SELECT DATE(created_at) as date,
           SUM(CASE WHEN type IN ('out', 'guh-out') THEN 1 ELSE 0 END) as out_count,
           SUM(CASE WHEN type IN ('in', 'guh-in') THEN 1 ELSE 0 END) as in_count
    FROM orders
    WHERE $date_where
    GROUP BY DATE(created_at)
    ORDER BY date ASC";

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
if (empty($out_series)) {
    $out_series[] = [date("Y-m-d"), 0];
    $in_series[]  = [date("Y-m-d"), 0];
}
if ($chart_stmt) mysqli_stmt_close($chart_stmt);

$out_labels = []; $out_data = []; $in_data = [];
foreach (array_map(null, $out_series, $in_series) as [$o, $i]) {
    $out_labels[] = $o[0];
    $out_data[]   = $o[1];
    $in_data[]    = $i[1];
}
$labels_json = json_encode($out_labels);
$out_json    = json_encode($out_data);
$in_json     = json_encode($in_data);

// ---------------------------------------------------------
// 6. CHART DATA: Top Partners by Order Count (filtered period)
// ---------------------------------------------------------
$partners_sql = "
    SELECT p.name AS partner_name, COUNT(o.id) AS order_count
    FROM orders o
    LEFT JOIN partners p ON o.partner_id = p.id
    WHERE $type_where AND " . str_replace('created_at', 'o.created_at', $date_where) . "
    GROUP BY o.partner_id, p.name
    ORDER BY order_count DESC
    LIMIT 8";

$partners_stmt = mysqli_prepare($conn, $partners_sql);
$partner_labels = []; $partner_data = [];
if ($partners_stmt) {
    if (!empty($bind_params)) {
        mysqli_stmt_bind_param($partners_stmt, $bind_types, ...$bind_params);
    }
    mysqli_stmt_execute($partners_stmt);
    $partners_result = mysqli_stmt_get_result($partners_stmt);
    while ($row = mysqli_fetch_assoc($partners_result)) {
        $partner_labels[] = $row['partner_name'] ?? 'Unknown';
        $partner_data[]   = (int)$row['order_count'];
    }
    mysqli_stmt_close($partners_stmt);
}
$partner_labels_json = json_encode($partner_labels);
$partner_data_json   = json_encode($partner_data);

// ---------------------------------------------------------
// 7. CHART DATA: Revenue Over Time (filtered period)
// ---------------------------------------------------------
$revenue_sql = "
    SELECT DATE(o.created_at) as date, SUM(o.price) as revenue
    FROM orders o
    WHERE $type_where AND $date_where
    GROUP BY DATE(o.created_at)
    ORDER BY date ASC";

$revenue_stmt = mysqli_prepare($conn, $revenue_sql);
$revenue_labels = []; $revenue_data = [];
if ($revenue_stmt) {
    if (!empty($bind_params)) {
        mysqli_stmt_bind_param($revenue_stmt, $bind_types, ...$bind_params);
    }
    mysqli_stmt_execute($revenue_stmt);
    $revenue_result = mysqli_stmt_get_result($revenue_stmt);
    while ($row = mysqli_fetch_assoc($revenue_result)) {
        $revenue_labels[] = $row['date'];
        $revenue_data[]   = round((float)$row['revenue'], 2);
    }
    mysqli_stmt_close($revenue_stmt);
}
if (empty($revenue_labels)) {
    $revenue_labels[] = date("Y-m-d");
    $revenue_data[]   = 0;
}
$revenue_labels_json = json_encode($revenue_labels);
$revenue_data_json   = json_encode($revenue_data);

// ---------------------------------------------------------
// 8. CHART DATA: In vs Out Ratio (doughnut, filtered period)
// ---------------------------------------------------------
$ratio_in_res  = fetchSingleSecure($conn,
    "SELECT COUNT(*) as count FROM orders WHERE type IN ('in','guh-in') AND $date_where",
    $bind_types, $bind_params
);
$ratio_out_res = fetchSingleSecure($conn,
    "SELECT COUNT(*) as count FROM orders WHERE type IN ('out','guh-out') AND $date_where",
    $bind_types, $bind_params
);
$ratio_in  = (int)$ratio_in_res['count'];
$ratio_out = (int)$ratio_out_res['count'];

// ---------------------------------------------------------
// 9. RECENT ORDERS
// ---------------------------------------------------------
$recent_sql = "
    SELECT o.id, o.order_no, p.name AS partner_name, o.type, o.approve_status, o.order_status
    FROM orders o
    LEFT JOIN partners p ON o.partner_id = p.id
    ORDER BY o.created_at DESC
    LIMIT 5
";
$recent_result = mysqli_query($conn, $recent_sql);

// Build export query string (pass all current filters through)
$export_params = http_build_query(array_filter([
    'period' => $period,
    'type'   => $type_filter,
    'from'   => $from_date,
    'to'     => $to_date,
]));

include "../../build/header.php";
?>

<div class="container-fluid px-4 py-4">

    <!-- FILTER BAR -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Quick Period</label>
                    <select name="period" class="form-select form-select-sm">
                        <option value="day"      <?= $period == 'day'      ? 'selected' : '' ?>>Last 24 Hours</option>
                        <option value="week"     <?= $period == 'week'     ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="month"    <?= $period == 'month'    ? 'selected' : '' ?>>Last 30 Days</option>
                        <option value="semi"     <?= $period == 'semi'     ? 'selected' : '' ?>>Last 6 Months</option>
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
                        <option value="in"  <?= $type_filter == 'in'  ? 'selected' : '' ?>>Inbound</option>
                        <option value="out" <?= $type_filter == 'out' ? 'selected' : '' ?>>Outbound</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2 justify-content-end align-items-end">
                    <a href="dashboard.php" class="btn btn-link btn-sm text-decoration-none">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Apply</button>
                    <a href="export_dashboard.php?<?= $export_params ?>"
                       class="btn btn-outline-secondary btn-sm px-3"
                       title="Export current view as PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
                             class="bi bi-file-earmark-pdf me-1" viewBox="0 0 16 16">
                            <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                            <path d="M4.603 12.087a.8.8 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.27.27 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 4.8a6.772 6.772 0 0 0 .18-.704c.038-.228.043-.4.016-.54-.022-.112-.065-.155-.09-.164a.243.243 0 0 0-.115.013c-.137.05-.2.187-.23.33-.04.167-.035.37.01.614.046.243.11.494.19.705l.04-.25z"/>
                        </svg>Export PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- STAT CARDS -->
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

    <!-- ROW 1: Activity Trend + Recent Orders -->
    <div class="row g-4 mb-4">
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
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                        plugins: { legend: { position: 'top' } }
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
                                            <a href="/green_bridge_recycling_v2/pages/system/template/guhring_order.php?id=<?= $row['id'] ?>"
                                               class="text-decoration-none fw-bold">
                                                <?= htmlspecialchars($row['order_no'] ?? 'N/A') ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($row['partner_name'] ?? 'Unknown Partner') ?></td>
                                        <td>
                                            <?php
                                                $status = $row['order_status'] ?? 'created';
                                                $badge_class = match($status) {
                                                    'completed'  => 'bg-success',
                                                    'cancelled'  => 'bg-danger',
                                                    'in process' => 'bg-warning text-dark',
                                                    'received'   => 'bg-info text-dark',
                                                    default      => 'bg-secondary'
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

    <!-- ROW 2: Top Partners | Revenue Over Time | In vs Out Ratio -->
    <div class="row g-4">
        <!-- Top Partners Bar Chart -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <h6 class="fw-bold mb-3">Top Partners by Order Count</h6>
                <?php if (empty($partner_labels)): ?>
                    <p class="text-muted small text-center py-4">No partner data for this period.</p>
                <?php else: ?>
                    <canvas id="partnersChart" height="260"></canvas>
                    <script>
                    new Chart(document.getElementById('partnersChart'), {
                        type: 'bar',
                        data: {
                            labels: <?= $partner_labels_json ?>,
                            datasets: [{
                                label: 'Orders',
                                data: <?= $partner_data_json ?>,
                                backgroundColor: [
                                    'rgba(5,72,173,0.7)',   'rgba(255,193,7,0.7)',
                                    'rgba(40,167,69,0.7)',  'rgba(220,53,69,0.7)',
                                    'rgba(23,162,184,0.7)', 'rgba(111,66,193,0.7)',
                                    'rgba(253,126,20,0.7)', 'rgba(32,201,151,0.7)'
                                ],
                                borderRadius: 5,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            scales: {
                                x: { beginAtZero: true, ticks: { stepSize: 1 } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });
                    </script>
                <?php endif; ?>
            </div>
        </div>

        <!-- Revenue Over Time Line Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <h6 class="fw-bold mb-3">Revenue Over Time</h6>
                <canvas id="revenueChart" height="260"></canvas>
                <script>
                new Chart(document.getElementById('revenueChart'), {
                    type: 'line',
                    data: {
                        labels: <?= $revenue_labels_json ?>,
                        datasets: [{
                            label: 'Revenue (€)',
                            data: <?= $revenue_data_json ?>,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40,167,69,0.12)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: val => '€' + val.toLocaleString()
                                }
                            }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
                </script>
            </div>
        </div>

        <!-- In vs Out Ratio Doughnut -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 d-flex flex-column">
                <h6 class="fw-bold mb-3">In vs Out Ratio</h6>
                <?php if ($ratio_in === 0 && $ratio_out === 0): ?>
                    <p class="text-muted small text-center py-4">No orders in this period.</p>
                <?php else: ?>
                    <div class="d-flex justify-content-center align-items-center flex-grow-1">
                        <canvas id="ratioChart" style="max-height:220px;"></canvas>
                    </div>
                    <div class="d-flex justify-content-center gap-3 mt-2 small text-muted">
                        <span><span class="badge bg-primary me-1">&nbsp;</span>In: <?= $ratio_in ?></span>
                        <span><span class="badge bg-warning text-dark me-1">&nbsp;</span>Out: <?= $ratio_out ?></span>
                    </div>
                    <script>
                    new Chart(document.getElementById('ratioChart'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Inbound', 'Outbound'],
                            datasets: [{
                                data: [<?= $ratio_in ?>, <?= $ratio_out ?>],
                                backgroundColor: ['rgba(5,72,173,0.8)', 'rgba(255,193,7,0.85)'],
                                borderWidth: 2,
                                borderColor: ['#0548ad', '#ffc107']
                            }]
                        },
                        options: {
                            responsive: true,
                            cutout: '65%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => {
                                            const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                            const pct   = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php include "../../build/footer.php"; ?>