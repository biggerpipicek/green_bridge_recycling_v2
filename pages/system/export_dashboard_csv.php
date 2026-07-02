<?php
// DASHBOARD CSV EXPORT — respects the same filters as export_dashboard.php (PDF)
// Revenue figures are intentionally left out, matching the dashboard's current revenue-hidden state.

require "../../build/auth.php";
require "../../build/functions.php";

logActivity($conn, $_SESSION['user_id'], 'checking', 'dashboard', $_SESSION['user_id'], "User #{$_SESSION['user_id']} exported dashboard (CSV)");

// ---------------------------------------------------------
// 1. FILTER VALIDATION (same logic as export_dashboard.php)
// ---------------------------------------------------------
$allowed_periods = ['day', 'week', 'month', 'semi', 'annually'];
$allowed_types   = ['all', 'in', 'out'];

$period      = in_array($_GET['period'] ?? '', $allowed_periods) ? $_GET['period'] : 'week';
$type_filter = in_array($_GET['type'] ?? '', $allowed_types)    ? $_GET['type']   : 'all';
$from_date   = $_GET['from'] ?? '';
$to_date     = $_GET['to'] ?? '';

$type_where = "1=1";
if ($type_filter === 'out') {
    $type_where = "type IN ('out', 'guh-out')";
} elseif ($type_filter === 'in') {
    $type_where = "type IN ('in', 'guh-in')";
}

$bind_types  = "";
$bind_params = [];

if (!empty($from_date) && !empty($to_date)) {
    $date_where = "DATE(created_at) BETWEEN ? AND ?";
    $bind_types   .= "ss";
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
// 2. DAILY ACTIVITY
// ---------------------------------------------------------
$chart_sql = "
    SELECT DATE(created_at) as date,
           SUM(CASE WHEN type IN ('out','guh-out') THEN 1 ELSE 0 END) as out_count,
           SUM(CASE WHEN type IN ('in','guh-in')  THEN 1 ELSE 0 END) as in_count
    FROM orders
    WHERE $date_where
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$chart_stmt = mysqli_prepare($conn, $chart_sql);
$chart_rows = [];
if ($chart_stmt) {
    if (!empty($bind_params)) mysqli_stmt_bind_param($chart_stmt, $bind_types, ...$bind_params);
    mysqli_stmt_execute($chart_stmt);
    $chart_result = mysqli_stmt_get_result($chart_stmt);
    while ($r = mysqli_fetch_assoc($chart_result)) {
        $chart_rows[] = $r;
    }
    mysqli_stmt_close($chart_stmt);
}

// ---------------------------------------------------------
// 3. TOP PARTNERS
// ---------------------------------------------------------
$partners_sql = "
    SELECT p.name AS partner_name, COUNT(o.id) AS order_count
    FROM orders o
    LEFT JOIN partners p ON o.partner_id = p.id
    WHERE $type_where AND " . str_replace('created_at', 'o.created_at', $date_where) . "
    GROUP BY o.partner_id, p.name
    ORDER BY order_count DESC
";
$partners_stmt = mysqli_prepare($conn, $partners_sql);
$partner_rows  = [];
if ($partners_stmt) {
    if (!empty($bind_params)) mysqli_stmt_bind_param($partners_stmt, $bind_types, ...$bind_params);
    mysqli_stmt_execute($partners_stmt);
    $partners_result = mysqli_stmt_get_result($partners_stmt);
    while ($r = mysqli_fetch_assoc($partners_result)) {
        $partner_rows[] = $r;
    }
    mysqli_stmt_close($partners_stmt);
}

// ---------------------------------------------------------
// 4. BUILD CSV — two sections in one file, separated by a blank line
// ---------------------------------------------------------
$filename = 'GBR_Dashboard_Export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens accents/diacritics correctly

fputcsv($out, ['Daily Activity']);
fputcsv($out, ['Date', 'Outgoing', 'Incoming', 'Total']);
$total_out = 0; $total_in = 0;
foreach ($chart_rows as $row) {
    $day_total = (int)$row['out_count'] + (int)$row['in_count'];
    fputcsv($out, [$row['date'], $row['out_count'], $row['in_count'], $day_total]);
    $total_out += (int)$row['out_count'];
    $total_in  += (int)$row['in_count'];
}
fputcsv($out, ['TOTAL', $total_out, $total_in, $total_out + $total_in]);

fputcsv($out, []); // blank line between sections

fputcsv($out, ['Top Partners']);
fputcsv($out, ['Partner Name', 'Orders']);
foreach ($partner_rows as $row) {
    fputcsv($out, [$row['partner_name'] ?? 'Unknown', $row['order_count']]);
}

fclose($out);
exit;
