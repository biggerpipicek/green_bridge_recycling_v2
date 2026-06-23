<?php
// MICHAEL D. PHILLIPS - UPDATED 06/10/2026
// DASHBOARD PDF EXPORT — MULTI-CURRENCY

require "../../build/auth.php";
require "../../build/functions.php";
require "../../build/fpdf.php";   // adjust path if needed

logActivity($conn, $_SESSION['user_id'], 'checking', 'dashboard', $_SESSION['user_id'], "User #{$_SESSION['user_id']} exported dashboard");

// ---------------------------------------------------------
// CHART IMAGES — decode base64 PNG from POST
// ---------------------------------------------------------
function saveChartImage($post_key) {
    if (empty($_POST[$post_key])) return null;
    $data = preg_replace('/^data:image\/png;base64,/', '', $_POST[$post_key]);
    $data = base64_decode($data);
    if (!$data) return null;
    $tmp = sys_get_temp_dir() . '/gbr_chart_' . $post_key . '_' . time() . '.png';
    file_put_contents($tmp, $data);
    return $tmp;
}

$chart_images = [
    'Activity Trend (Incoming vs Outgoing Orders)' => saveChartImage('chart_activity'),
    'Top Partners by Order Count'                  => saveChartImage('chart_partners'),
    'Revenue Over Time (EUR)'                      => saveChartImage('chart_revenue'),
    'In vs Out Ratio'                              => saveChartImage('chart_ratio'),
];

// ---------------------------------------------------------
// 1. FILTER VALIDATION
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
    $date_where    = "DATE(created_at) BETWEEN ? AND ?";
    $bind_types   .= "ss";
    $bind_params[] = $from_date;
    $bind_params[] = $to_date;
    $period_label  = "Custom: $from_date to $to_date";
} else {
    $intervals = [
        'day'      => 'INTERVAL 1 DAY',
        'week'     => 'INTERVAL 7 DAY',
        'month'    => 'INTERVAL 30 DAY',
        'semi'     => 'INTERVAL 6 MONTH',
        'annually' => 'INTERVAL 1 YEAR'
    ];
    $period_labels_map = [
        'day'      => 'Last 24 Hours',
        'week'     => 'Last 7 Days',
        'month'    => 'Last 30 Days',
        'semi'     => 'Last 6 Months',
        'annually' => 'Annual (1 Year)'
    ];
    $sql_interval = $intervals[$period] ?? 'INTERVAL 7 DAY';
    $date_where   = "created_at >= DATE_SUB(NOW(), $sql_interval)";
    $period_label = $period_labels_map[$period] ?? 'Last 7 Days';
}

$type_label_map = ['all' => 'All (In & Out)', 'in' => 'Inbound', 'out' => 'Outbound'];
$type_label     = $type_label_map[$type_filter] ?? 'All';

// ---------------------------------------------------------
// 2. EXCHANGE RATES — fetch once
// ---------------------------------------------------------
$fx_rates  = ['EUR' => 1.0];
$fx_source = 'fallback';

$fx_ctx  = stream_context_create(['http' => ['header' => 'Cache-Control: no-cache']]);
$fx_json = @file_get_contents('https://api.frankfurter.app/latest?base=EUR&symbols=USD,CZK,PLN,JPY', false, $fx_ctx);
if ($fx_json) {
    $fx_data = json_decode($fx_json, true);
    if (!empty($fx_data['rates'])) {
        foreach ($fx_data['rates'] as $code => $rate) {
            $fx_rates[$code] = (float)$rate;
        }
        $fx_source = $fx_data['date'] ?? 'live';
    }
}

function exportToEur($amount, $currency, $rates) {
    $currency = strtoupper(trim($currency ?? 'EUR'));
    if ($currency === 'EUR' || !isset($rates[$currency])) return (float)$amount;
    return (float)$amount / $rates[$currency];
}

// Format: "CZK 22,037.00 -> EUR 882.15" (ASCII arrow for FPDF Latin-1)
function exportFmtPrice($amount, $currency, $rates) {
    $currency = strtoupper(trim($currency ?? 'EUR'));
    $orig = $currency . ' ' . number_format((float)$amount, 2);
    if ($currency === 'EUR') return $orig;
    $eur = exportToEur($amount, $currency, $rates);
    return $orig . ' -> EUR ' . number_format($eur, 2);
}

// ---------------------------------------------------------
// 3. FETCH SUMMARY STATS
// ---------------------------------------------------------
function exportFetchSingle($conn, $sql, $types = "", $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['count' => 0, 'total' => 0];
    if (!empty($params)) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res) ?? ['count' => 0, 'total' => 0];
    mysqli_stmt_close($stmt);
    return $data;
}

$total_res   = exportFetchSingle($conn, "SELECT COUNT(*) as count FROM orders");
$pending_res = exportFetchSingle($conn, "SELECT COUNT(*) as count FROM orders WHERE approve_status = 'not approved'");

// Filtered count + EUR total
$filtered_stmt = mysqli_prepare($conn, "SELECT price, currency FROM orders WHERE $type_where AND $date_where");
$filtered_count = 0; $filtered_eur = 0.0;
if ($filtered_stmt) {
    if (!empty($bind_params)) mysqli_stmt_bind_param($filtered_stmt, $bind_types, ...$bind_params);
    mysqli_stmt_execute($filtered_stmt);
    $filtered_res = mysqli_stmt_get_result($filtered_stmt);
    while ($r = mysqli_fetch_assoc($filtered_res)) {
        $filtered_eur += exportToEur($r['price'], $r['currency'] ?? 'EUR', $fx_rates);
        $filtered_count++;
    }
    mysqli_stmt_close($filtered_stmt);
}

// Monthly revenue in EUR
$monthly_stmt = mysqli_prepare($conn, "SELECT price, currency FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$monthly_eur = 0.0;
if ($monthly_stmt) {
    mysqli_stmt_execute($monthly_stmt);
    $monthly_res = mysqli_stmt_get_result($monthly_stmt);
    while ($r = mysqli_fetch_assoc($monthly_res)) {
        $monthly_eur += exportToEur($r['price'], $r['currency'] ?? 'EUR', $fx_rates);
    }
    mysqli_stmt_close($monthly_stmt);
}

// ---------------------------------------------------------
// 4. FETCH FILTERED ORDERS TABLE
// ---------------------------------------------------------
$orders_sql = "
    SELECT o.order_no, p.name AS partner_name, o.type,
           o.approve_status, o.order_status,
           o.price, o.currency, DATE(o.created_at) AS order_date
    FROM orders o
    LEFT JOIN partners p ON o.partner_id = p.id
    WHERE $type_where AND " . str_replace('created_at', 'o.created_at', $date_where) . "
    ORDER BY o.created_at DESC
    LIMIT 200
";
$orders_stmt = mysqli_prepare($conn, $orders_sql);
$orders_rows = [];
if ($orders_stmt) {
    if (!empty($bind_params)) mysqli_stmt_bind_param($orders_stmt, $bind_types, ...$bind_params);
    mysqli_stmt_execute($orders_stmt);
    $orders_result = mysqli_stmt_get_result($orders_stmt);
    while ($r = mysqli_fetch_assoc($orders_result)) {
        $orders_rows[] = $r;
    }
    mysqli_stmt_close($orders_stmt);
}

// ---------------------------------------------------------
// 5. FETCH DAILY ACTIVITY (chart data)
// ---------------------------------------------------------
$chart_sql = "
    SELECT DATE(created_at) as date,
           SUM(CASE WHEN type IN ('out','guh-out') THEN 1 ELSE 0 END) as out_count,
           SUM(CASE WHEN type IN ('in','guh-in')  THEN 1 ELSE 0 END) as in_count
    FROM orders
    WHERE $date_where
    GROUP BY DATE(created_at)
    ORDER BY date ASC
    LIMIT 50
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

// Daily revenue in EUR (fetched per-order, summed by day)
$rev_sql = "SELECT DATE(created_at) as date, price, currency FROM orders WHERE $date_where ORDER BY created_at ASC";
$rev_stmt = mysqli_prepare($conn, $rev_sql);
$rev_by_day = [];
if ($rev_stmt) {
    if (!empty($bind_params)) mysqli_stmt_bind_param($rev_stmt, $bind_types, ...$bind_params);
    mysqli_stmt_execute($rev_stmt);
    $rev_res = mysqli_stmt_get_result($rev_stmt);
    while ($r = mysqli_fetch_assoc($rev_res)) {
        $d = $r['date'];
        if (!isset($rev_by_day[$d])) $rev_by_day[$d] = 0.0;
        $rev_by_day[$d] += exportToEur($r['price'], $r['currency'] ?? 'EUR', $fx_rates);
    }
    mysqli_stmt_close($rev_stmt);
}

// Merge daily revenue into chart_rows
foreach ($chart_rows as &$cr) {
    $cr['day_revenue_eur'] = $rev_by_day[$cr['date']] ?? 0.0;
}
unset($cr);

// ---------------------------------------------------------
// 6. FETCH TOP PARTNERS
// ---------------------------------------------------------
$partners_sql = "
    SELECT p.name AS partner_name, COUNT(o.id) AS order_count,
           SUM(o.price) AS total_value, o.currency
    FROM orders o
    LEFT JOIN partners p ON o.partner_id = p.id
    WHERE $type_where AND " . str_replace('created_at', 'o.created_at', $date_where) . "
    GROUP BY o.partner_id, p.name, o.currency
    ORDER BY order_count DESC
    LIMIT 8
";
$partners_stmt = mysqli_prepare($conn, $partners_sql);
$partner_rows  = [];
if ($partners_stmt) {
    if (!empty($bind_params)) mysqli_stmt_bind_param($partners_stmt, $bind_types, ...$bind_params);
    mysqli_stmt_execute($partners_stmt);
    $partners_result = mysqli_stmt_get_result($partners_stmt);
    while ($r = mysqli_fetch_assoc($partners_result)) {
        $r['total_value_eur'] = exportToEur($r['total_value'], $r['currency'] ?? 'EUR', $fx_rates);
        $partner_rows[] = $r;
    }
    mysqli_stmt_close($partners_stmt);
}

// ---------------------------------------------------------
// 7. BUILD PDF
// ---------------------------------------------------------
class GBR_PDF extends FPDF {

    function u($str) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str ?? '');
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, $this->u((string)$txt), $border, $ln, $align, $fill, $link);
    }

    function Header() {
        $this->SetFillColor(19, 150, 15);
        $this->Rect(0, 0, 210, 18, 'F');
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 5);
        $this->Cell(130, 8, 'Green Bridge Recycling - Dashboard Export', 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->SetXY(140, 5);
        $this->Cell(60, 8, 'Generated: ' . date('d M Y, H:i'), 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(22);
    }

    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'GBR System  |  Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function SectionTitle($title) {
        $this->SetFillColor(240, 243, 250);
        $this->SetTextColor(19, 150, 15);
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(10);
        $this->Cell(190, 7, $title, 0, 1, 'L', true);
        $this->Ln(2);
        $this->SetTextColor(0, 0, 0);
    }

    function StatBox($x, $y, $w, $label, $value, $sub = '') {
        $this->SetFillColor(250, 250, 252);
        $this->SetDrawColor(220, 220, 230);
        $this->RoundedRect($x, $y, $w, 22, 2, 'DF');
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(120, 120, 130);
        $this->SetXY($x + 3, $y + 2);
        $this->Cell($w - 6, 5, $label, 0, 1, 'L');
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(30, 30, 40);
        $this->SetXY($x + 3, $y + 7);
        $this->Cell($w - 6, 8, $value, 0, 1, 'L');
        if ($sub) {
            $this->SetFont('Arial', '', 7);
            $this->SetTextColor(100, 100, 110);
            $this->SetXY($x + 3, $y + 15);
            $this->Cell($w - 6, 5, $sub, 0, 1, 'L');
        }
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
    }

    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k; $hp = $this->h;
        if ($style == 'F') $op = 'f';
        elseif ($style == 'FD' || $style == 'DF') $op = 'B';
        else $op = 'S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x+$r)*$k, ($hp-$y)*$k));
        $xc = $x+$w-$r; $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-$y)*$k));
        $this->_Arc($xc+$r*$MyArc,$yc-$r,$xc+$r,$yc-$r*$MyArc,$xc+$r,$yc);
        $xc = $x+$w-$r; $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-$yc)*$k));
        $this->_Arc($xc+$r,$yc+$r*$MyArc,$xc+$r*$MyArc,$yc+$r,$xc,$yc+$r);
        $xc = $x+$r; $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-($y+$h))*$k));
        $this->_Arc($xc-$r*$MyArc,$yc+$r,$xc-$r,$yc+$r*$MyArc,$xc-$r,$yc);
        $xc = $x+$r; $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', ($x)*$k, ($hp-$yc)*$k));
        $this->_Arc($xc-$r,$yc-$r*$MyArc,$xc-$r*$MyArc,$yc-$r,$xc,$yc-$r);
        $this->_out($op);
    }
    function _Arc($x1,$y1,$x2,$y2,$x3,$y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1*$this->k,($h-$y1)*$this->k,$x2*$this->k,($h-$y2)*$this->k,$x3*$this->k,($h-$y3)*$this->k));
    }
}

$pdf = new GBR_PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetMargins(10, 10, 10);

// ---------------------------------------------------------
// PAGE 1: Summary + FX rates + Top Partners
// ---------------------------------------------------------
$pdf->AddPage();

// Filter strip
$pdf->SetFillColor(255, 248, 220);
$pdf->SetDrawColor(255, 193, 7);
$pdf->Rect(10, 22, 190, 8, 'DF');
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(80, 60, 0);
$pdf->SetXY(13, 24);
$pdf->Cell(60, 4, 'Period: ' . $period_label, 0, 0, 'L');
$pdf->SetXY(80, 24);
$pdf->Cell(60, 4, 'Type Filter: ' . $type_label, 0, 0, 'L');
$pdf->SetXY(150, 24);
$pdf->Cell(50, 4, 'Exported: ' . date('Y-m-d'), 0, 0, 'R');
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(0, 0, 0);
$pdf->Ln(12);

// FX rates strip
$pdf->SetFillColor(245, 255, 245);
$pdf->SetDrawColor(40, 167, 69);
$pdf->Rect(10, $pdf->GetY(), 190, 7, 'DF');
$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(20, 80, 30);
$pdf->SetX(13);
$fx_str = 'FX Rates (-> EUR, ' . $fx_source . '):  ';
foreach ($fx_rates as $code => $rate) {
    if ($code !== 'EUR') $fx_str .= "1 EUR = $rate $code   ";
}
$pdf->Cell(186, 7, $fx_str, 0, 1, 'L');
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(0, 0, 0);
$pdf->Ln(3);

// Stat boxes
$pdf->SectionTitle('Summary');
$pdf->Ln(1);
$stat_y = $pdf->GetY();
$pdf->StatBox(10,  $stat_y, 44, 'Total Orders (System)',  number_format($total_res['count']));
$pdf->StatBox(57,  $stat_y, 44, 'Filtered Count',         number_format($filtered_count));
$pdf->StatBox(104, $stat_y, 44, 'Pending Action',         $pending_res['count']);
$pdf->StatBox(151, $stat_y, 49, 'Monthly Revenue (EUR)',  'EUR ' . number_format($monthly_eur, 2),
              'Filtered: EUR ' . number_format($filtered_eur, 2));
$pdf->Ln(28);

// Top Partners table
$pdf->SectionTitle('Top Partners - ' . $period_label . ' (' . $type_label . ')');
$pdf->Ln(1);

$col_w = [75, 30, 45, 45];
$headers = ['Partner Name', 'Orders', 'Original Value', 'EUR Value'];
$pdf->SetFillColor(19, 150, 15);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
foreach ($col_w as $i => $w) {
    $pdf->Cell($w, 7, $headers[$i], 0, 0, $i === 0 ? 'L' : 'C', true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 8);
$odd = true;
foreach ($partner_rows as $row) {
    $pdf->SetFillColor($odd ? 248 : 255, $odd ? 250 : 255, $odd ? 255 : 255);
    $pdf->SetTextColor(30, 30, 40);
    $pdf->SetX(10);
    $cur = strtoupper($row['currency'] ?? 'EUR');
    $pdf->Cell($col_w[0], 6, mb_substr($row['partner_name'] ?? 'Unknown', 0, 40), 0, 0, 'L', true);
    $pdf->Cell($col_w[1], 6, $row['order_count'],                                  0, 0, 'C', true);
    $pdf->Cell($col_w[2], 6, $cur . ' ' . number_format((float)$row['total_value'], 2), 0, 0, 'R', true);
    $pdf->Cell($col_w[3], 6, 'EUR ' . number_format((float)$row['total_value_eur'], 2), 0, 0, 'R', true);
    $pdf->Ln();
    $odd = !$odd;
}
if (empty($partner_rows)) {
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetX(10);
    $pdf->Cell(195, 6, 'No partner data for this period.', 0, 1, 'C');
}
$pdf->SetTextColor(0, 0, 0);

// ---------------------------------------------------------
// PAGE 2: Daily Activity
// ---------------------------------------------------------
$pdf->AddPage();
$pdf->SectionTitle('Daily Activity - ' . $period_label);
$pdf->Ln(1);

$col_w2 = [38, 30, 30, 32, 65];
$headers2 = ['Date', 'Outgoing', 'Incoming', 'Total', 'Daily Revenue (EUR)'];
$pdf->SetFillColor(19, 150, 15);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
foreach ($col_w2 as $i => $w) {
    $pdf->Cell($w, 7, $headers2[$i], 0, 0, $i === 0 ? 'L' : 'C', true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 8);
$odd = true;
$total_out = 0; $total_in = 0; $total_rev_eur = 0.0;
foreach ($chart_rows as $row) {
    $pdf->SetFillColor($odd ? 248 : 255, $odd ? 250 : 255, $odd ? 255 : 255);
    $pdf->SetTextColor(30, 30, 40);
    $pdf->SetX(10);
    $day_total  = (int)$row['out_count'] + (int)$row['in_count'];
    $day_rev    = $row['day_revenue_eur'] ?? 0.0;
    $pdf->Cell($col_w2[0], 6, $row['date'],                              0, 0, 'L', true);
    $pdf->Cell($col_w2[1], 6, $row['out_count'],                         0, 0, 'C', true);
    $pdf->Cell($col_w2[2], 6, $row['in_count'],                          0, 0, 'C', true);
    $pdf->Cell($col_w2[3], 6, $day_total,                                0, 0, 'C', true);
    $pdf->Cell($col_w2[4], 6, 'EUR ' . number_format($day_rev, 2),       0, 0, 'R', true);
    $pdf->Ln();
    $odd = !$odd;
    $total_out    += (int)$row['out_count'];
    $total_in     += (int)$row['in_count'];
    $total_rev_eur += $day_rev;
}

// Totals row
$pdf->SetFillColor(232, 250, 230);
$pdf->SetTextColor(19, 150, 15);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell($col_w2[0], 7, 'TOTAL',                                        0, 0, 'L', true);
$pdf->Cell($col_w2[1], 7, $total_out,                                     0, 0, 'C', true);
$pdf->Cell($col_w2[2], 7, $total_in,                                      0, 0, 'C', true);
$pdf->Cell($col_w2[3], 7, $total_out + $total_in,                         0, 0, 'C', true);
$pdf->Cell($col_w2[4], 7, 'EUR ' . number_format($total_rev_eur, 2),      0, 0, 'R', true);
$pdf->Ln();

if (empty($chart_rows)) {
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX(10);
    $pdf->Cell(195, 6, 'No activity data for this period.', 0, 1, 'C');
}
$pdf->SetTextColor(0, 0, 0);

// ---------------------------------------------------------
// PAGE 3+: Filtered Orders
// ---------------------------------------------------------
$pdf->AddPage();
$pdf->SectionTitle('Filtered Orders - ' . $period_label . ' (' . $type_label . ')');
if (count($orders_rows) === 200) {
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetTextColor(150, 100, 0);
    $pdf->SetX(10);
    $pdf->Cell(190, 4, 'Note: results capped at 200 rows. Use a narrower date range for a full export.', 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
}
$pdf->Ln(1);

$col_w3 = [25, 45, 18, 24, 22, 60];
$headers3 = ['Order #', 'Partner', 'Type', 'Approval', 'Status', 'Price (orig -> EUR)'];
$pdf->SetFillColor(19, 150, 15);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetX(10);
foreach ($col_w3 as $i => $w) {
    $pdf->Cell($w, 7, $headers3[$i], 0, 0, $i >= 2 ? 'C' : 'L', true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 7);
$odd = true;
foreach ($orders_rows as $row) {
    $pdf->SetFillColor($odd ? 248 : 255, $odd ? 250 : 255, $odd ? 255 : 255);
    $pdf->SetTextColor(30, 30, 40);
    $pdf->SetX(10);
    $cur     = strtoupper($row['currency'] ?? 'EUR');
    $amt     = (float)$row['price'];
    $eur_amt = exportToEur($amt, $cur, $fx_rates);
    $price_str = $cur === 'EUR'
        ? 'EUR ' . number_format($amt, 2)
        : $cur . ' ' . number_format($amt, 2) . ' -> EUR ' . number_format($eur_amt, 2);

    $pdf->Cell($col_w3[0], 5.5, mb_substr($row['order_no'] ?? 'N/A', 0, 14),         0, 0, 'L', true);
    $pdf->Cell($col_w3[1], 5.5, mb_substr($row['partner_name'] ?? 'Unknown', 0, 26), 0, 0, 'L', true);
    $pdf->Cell($col_w3[2], 5.5, strtoupper($row['type'] ?? ''),                      0, 0, 'C', true);
    $pdf->Cell($col_w3[3], 5.5, ucfirst($row['approve_status'] ?? ''),               0, 0, 'C', true);
    $pdf->Cell($col_w3[4], 5.5, ucfirst($row['order_status'] ?? ''),                 0, 0, 'C', true);
    $pdf->Cell($col_w3[5], 5.5, $price_str,                                          0, 0, 'R', true);
    $pdf->Ln();
    $odd = !$odd;
}
if (empty($orders_rows)) {
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX(10);
    $pdf->Cell(194, 6, 'No orders found for this filter combination.', 0, 1, 'C');
}

// ---------------------------------------------------------
// PAGE 4: Charts
// ---------------------------------------------------------
$has_charts = array_filter($chart_images);
if (!empty($has_charts)) {
    $pdf->AddPage();
    $pdf->SectionTitle('Dashboard Charts - ' . $period_label);
    $pdf->Ln(2);

    $chart_y = $pdf->GetY();
    $col = 0;

    foreach ($chart_images as $label => $path) {
        if (!$path || !file_exists($path)) continue;

        $x = ($col === 0) ? 10 : 108;
        $w = 92;
        $h = 58;

        if ($col === 0 && ($chart_y + $h + 12) > 272) {
            $pdf->AddPage();
            $chart_y = $pdf->GetY();
        }

        // Label
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(60, 60, 70);
        $pdf->SetXY($x, $chart_y);
        $pdf->Cell($w, 5, $label, 0, 0, 'L');

        // Image
        $pdf->Image($path, $x, $chart_y + 6, $w, $h, 'PNG');

        // Border
        $pdf->SetDrawColor(220, 220, 230);
        $pdf->Rect($x, $chart_y + 6, $w, $h);
        $pdf->SetDrawColor(0, 0, 0);

        if ($col === 0) {
            $col = 1;
        } else {
            $col = 0;
            $chart_y += $h + 14;
        }

        @unlink($path);
    }
}

// ---------------------------------------------------------
// OUTPUT
// ---------------------------------------------------------
$filename = 'GBR_Dashboard_Export_' . date('Ymd_His') . '.pdf';
$pdf->Output('D', $filename);
exit;