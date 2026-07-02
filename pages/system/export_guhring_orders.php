<?php
// MICHAEL D. PHILLIPS - UPDATED 06/11/2026
// GÜHRING ORDERS PDF EXPORT (full export, ignores filters)

require "../../build/auth.php";
require "../../build/functions.php";
require "../../build/fpdf.php";
require_once "../../build/mailer.php";

logActivity($conn, $_SESSION['user_id'], 'checking', 'orders', $_SESSION['user_id'], "User #{$_SESSION['user_id']} exported guhring orders");

$order_currency = [
    "EUR" => "EUR",
    "USD" => "USD",
    "JPY" => "JPY",
    "PLN" => "PLN",
    "CZK" => "CZK"
];

// ---------------------------------------------------------
// FETCH ALL GÜHRING ORDERS
// ---------------------------------------------------------
$sql = "SELECT o.id, o.order_no, o.date, p.name AS partner_name,
               o.netto_w, o.brutto_w,
               o.order_status, o.approve_status
        FROM orders o
        JOIN partners p ON o.partner_id = p.id
        WHERE o.type IN ('guh-in', 'guh-out')
        ORDER BY o.date DESC";

$result = mysqli_query($conn, $sql);
$orders_rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $orders_rows[] = $r;
}

// ---------------------------------------------------------
// FETCH ORDER MATERIALS (items) FOR ALL ORDERS IN ONE QUERY
// ---------------------------------------------------------
$materials_by_order = [];
if (!empty($orders_rows)) {
    $ids = array_map(fn($r) => (int)$r['id'], $orders_rows);
    $ids_list = implode(',', $ids);

    $mat_sql = "SELECT om.order_id, m.item_code, m.name, om.quantity
                FROM order_materials om
                JOIN materials m ON om.material_id = m.id
                WHERE om.order_id IN ($ids_list)
                ORDER BY om.order_id ASC, m.item_code ASC";
    $mat_result = mysqli_query($conn, $mat_sql);
    while ($r = mysqli_fetch_assoc($mat_result)) {
        $materials_by_order[$r['order_id']][] = $r;
    }
}

// ---------------------------------------------------------
// BUILD PDF
// ---------------------------------------------------------
class GUH_PDF extends FPDF {

    function u($str) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str ?? '');
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, $this->u((string)$txt), $border, $ln, $align, $fill, $link);
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) {
        parent::MultiCell($w, $h, $this->u((string)$txt), $border, $align, $fill);
    }

    function Header() {
        $this->SetFillColor(19, 150, 15);
        $this->Rect(0, 0, 210, 18, 'F');
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 5);
        $this->Cell(140, 8, 'Green Bridge Recycling - Guhring Orders Export', 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->SetXY(150, 5);
        $this->Cell(50, 8, 'Generated: ' . date('d M Y, H:i'), 0, 0, 'R');
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

    function StatBox($x, $y, $w, $label, $value) {
        $this->SetFillColor(250, 250, 252);
        $this->SetDrawColor(220, 220, 230);
        $this->RoundedRect($x, $y, $w, 20, 2, 'DF');
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(120, 120, 130);
        $this->SetXY($x + 3, $y + 2);
        $this->Cell($w - 6, 5, $label, 0, 1, 'L');
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(30, 30, 40);
        $this->SetXY($x + 3, $y + 8);
        $this->Cell($w - 6, 8, $value, 0, 1, 'L');
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

    // Reusable order block header row
    function OrderRowHeader() {
        $this->SetFillColor(19, 150, 15);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 7);
        $this->SetX(10);
        $col_w = [34, 20, 34, 26, 26, 26];
        $headers = ['Order No.', 'Date', 'Customer', 'Order Status', 'Approve', 'Netto/Brutto (kg)'];
        foreach ($col_w as $i => $w) {
            $align = ($i === 2) ? 'L' : 'C';
            $this->Cell($w, 7, $headers[$i], 0, 0, $align, true);
        }
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
        return $col_w;
    }
}

// Compute totals
$total_netto = 0.0; $total_brutto = 0.0;
$status_counts = [];
foreach ($orders_rows as $r) {
    $total_netto  += (float)$r['netto_w'];
    $total_brutto += (float)$r['brutto_w'];
    $st = $r['order_status'] ?? 'unknown';
    $status_counts[$st] = ($status_counts[$st] ?? 0) + 1;
}

$pdf = new GUH_PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Info strip
$pdf->SetFillColor(255, 248, 220);
$pdf->SetDrawColor(255, 193, 7);
$pdf->Rect(10, 22, 190, 8, 'DF');
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(80, 60, 0);
$pdf->SetXY(13, 24);
$pdf->Cell(120, 4, 'Full Guhring order export - all orders, all statuses', 0, 0, 'L');
$pdf->SetXY(150, 24);
$pdf->Cell(50, 4, 'Exported: ' . date('Y-m-d'), 0, 0, 'R');
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(0, 0, 0);
$pdf->Ln(12);

// Stat boxes
$pdf->SectionTitle('Summary');
$pdf->Ln(1);
$stat_y = $pdf->GetY();
$pdf->StatBox(10,  $stat_y, 46, 'Total Orders',       count($orders_rows));
$pdf->StatBox(58,  $stat_y, 46, 'Total Netto (kg)',   number_format($total_netto, 2));
$pdf->StatBox(106, $stat_y, 46, 'Total Brutto (kg)',  number_format($total_brutto, 2));

// Status breakdown as one box (small text list)
$pdf->SetXY(154, $stat_y);
$pdf->SetFillColor(250, 250, 252);
$pdf->SetDrawColor(220, 220, 230);
$pdf->RoundedRect(154, $stat_y, 46, 20, 2, 'DF');
$pdf->SetFont('Arial', '', 6.5);
$pdf->SetTextColor(120, 120, 130);
$pdf->SetXY(157, $stat_y + 2);
$pdf->Cell(40, 4, 'By Status', 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->SetTextColor(30, 30, 40);
$y_cursor = $stat_y + 6;
foreach ($status_counts as $status => $cnt) {
    $pdf->SetXY(157, $y_cursor);
    $pdf->Cell(40, 4, ucfirst($status) . ': ' . $cnt, 0, 1, 'L');
    $y_cursor += 4;
    if ($y_cursor > $stat_y + 18) break; // don't overflow box
}
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetY($stat_y + 24);

// Orders section
$pdf->SectionTitle('Orders - Full List (' . count($orders_rows) . ' total)');
$pdf->Ln(1);

$col_w = [34, 20, 34, 24, 26, 26, 26];

$pdf->OrderRowHeader();
$pdf->SetFont('Arial', '', 7);

foreach ($orders_rows as $row) {
    // Estimate height needed: header row (6) + 1 line per material (or "no items" line)
    $items = $materials_by_order[$row['id']] ?? [];
    $item_lines = max(count($items), 1);
    $block_height = 6 + ($item_lines * 4.5) + 2; // header + items + spacing

    // Page break check
    if ($pdf->GetY() + $block_height > 280) {
        $pdf->AddPage();
        $pdf->OrderRowHeader();
        $pdf->SetFont('Arial', '', 7);
    }

    // Order header row
    $cur     = strtoupper($row['currency'] ?? 'EUR');
    $price_str = $cur . ' ' . number_format((float)$row['price'], 2);
    $weight_str = number_format((float)$row['netto_w'], 1) . ' / ' . number_format((float)$row['brutto_w'], 1);
    $date_str = date("d.m.Y", strtotime($row['date']));

    $pdf->SetFillColor(235, 240, 250);
    $pdf->SetTextColor(20, 20, 30);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetX(10);
    $pdf->Cell($col_w[0], 6, $row['order_no'],                       1, 0, 'C', true);
    $pdf->Cell($col_w[1], 6, $date_str,                              1, 0, 'C', true);
    $pdf->Cell($col_w[2], 6, mb_substr($row['partner_name'], 0, 28), 1, 0, 'L', true);
    $pdf->Cell($col_w[3], 6, $price_str,                             1, 0, 'C', true);
    $pdf->Cell($col_w[4], 6, ucfirst($row['order_status']),          1, 0, 'C', true);
    $pdf->Cell($col_w[5], 6, ucfirst($row['approve_status']),        1, 0, 'C', true);
    $pdf->Cell($col_w[6], 6, $weight_str,                            1, 0, 'C', true);
    $pdf->Ln();

    // Items sub-rows
    $pdf->SetFont('Arial', '', 6.5);
    $pdf->SetTextColor(80, 80, 90);
    if (!empty($items)) {
        foreach ($items as $item) {
            $pdf->SetFillColor(252, 252, 253);
            $pdf->SetX(10);
            $item_line = '   ' . $item['item_code'] . ' - ' . $item['name'] . '   x ' . number_format((float)$item['quantity'], 2) . ' kg';
            $pdf->Cell(array_sum($col_w), 4.5, $item_line, 'LRB', 0, 'L', true);
            $pdf->Ln();
        }
    } else {
        $pdf->SetFillColor(252, 252, 253);
        $pdf->SetX(10);
        $pdf->Cell(array_sum($col_w), 4.5, '   No items listed for this order', 'LRB', 0, 'L', true);
        $pdf->Ln();
    }
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(1.5);
}

if (empty($orders_rows)) {
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX(10);
    $pdf->Cell(190, 6, 'No Guhring orders found.', 0, 1, 'C');
}

$filename = 'GBR_Guhring_Orders_Export_' . date('Ymd_His') . '.pdf';

$tmp_dir  = __DIR__ . '/../../uploads/tmp/';
if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0755, true);
$tmp_path = $tmp_dir . $filename;

$pdf->Output('F', $tmp_path);

$user_email = $_SESSION['email'] ?? '';
$username   = $_SESSION['user']  ?? "User #{$_SESSION['user_id']}";

if (!empty($user_email)) {
    if (file_exists($tmp_path) && filesize($tmp_path) > 0) {
        mailExportReady($conn, $tmp_path, $filename, 'Gühring Orders', $user_email, $username);
    } else {
        error_log("GBR Export: tmp PDF not created or empty at {$tmp_path}");
    }
} else {
    error_log("GBR Export: no email in session, export email skipped for user_id " . ($_SESSION['user_id'] ?? 'unknown'));
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmp_path));
readfile($tmp_path);
@unlink($tmp_path);
exit;