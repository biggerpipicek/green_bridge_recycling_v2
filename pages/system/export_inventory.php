<?php
// MICHAEL D. PHILLIPS - UPDATED 06/11/2026
// INVENTORY PDF EXPORT

require "../../build/auth.php";
require "../../build/functions.php";
require "../../build/fpdf.php"; // adjust path if needed

// ---------------------------------------------------------
// FETCH ALL INVENTORY ITEMS (same query as inventory.php)
// ---------------------------------------------------------
$sql = "SELECT m.item_code, m.name,
            COALESCE(SUM(CASE WHEN im.direction = 'in'  THEN im.quantity ELSE 0 END), 0)
          - COALESCE(SUM(CASE WHEN im.direction = 'out' THEN im.quantity ELSE 0 END), 0)
              AS stock_weight
        FROM materials m
        LEFT JOIN inventory_movements im ON im.material_id = m.id
        GROUP BY m.id, m.item_code, m.name
        ORDER BY m.item_code ASC";

$result = mysqli_query($conn, $sql);
$rows = [];
$total_weight = 0.0;
$in_stock_count = 0;
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
    $total_weight += (float)$r['stock_weight'];
    if ((float)$r['stock_weight'] > 0) $in_stock_count++;
}

// ---------------------------------------------------------
// BUILD PDF
// ---------------------------------------------------------
class INV_PDF extends FPDF {

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
        $this->Cell(130, 8, 'Green Bridge Recycling - Inventory Export', 0, 0, 'L');
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
}

$pdf = new INV_PDF('P', 'mm', 'A4');
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
$pdf->Cell(90, 4, 'Full inventory snapshot — all materials', 0, 0, 'L');
$pdf->SetXY(150, 24);
$pdf->Cell(50, 4, 'Exported: ' . date('Y-m-d'), 0, 0, 'R');
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(0, 0, 0);
$pdf->Ln(12);

// Stat boxes
$pdf->SectionTitle('Summary');
$pdf->Ln(1);
$stat_y = $pdf->GetY();
$pdf->StatBox(10,  $stat_y, 60, 'Total Materials',        count($rows));
$pdf->StatBox(73,  $stat_y, 60, 'Items In Stock',         $in_stock_count);
$pdf->StatBox(136, $stat_y, 64, 'Total Stock Weight',     number_format($total_weight, 2) . ' kg');
$pdf->Ln(26);

// Table
$pdf->SectionTitle('Inventory — All Materials');
$pdf->Ln(1);

$col_w = [35, 110, 45];
$headers = ['Item Code', 'Item Name', 'In Stock (kg)'];
$pdf->SetFillColor(19, 150, 15);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
foreach ($col_w as $i => $w) {
    $align = $i === 2 ? 'R' : 'L';
    $pdf->Cell($w, 7, $headers[$i], 0, 0, $align, true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 8);
$odd = true;
foreach ($rows as $row) {
    $has_stock = (float)$row['stock_weight'] > 0;
    $pdf->SetFillColor($odd ? 248 : 255, $odd ? 250 : 255, $odd ? 255 : 255);
    $pdf->SetTextColor($has_stock ? 30 : 150, $has_stock ? 30 : 150, $has_stock ? 40 : 150);
    $pdf->SetX(10);
    $pdf->Cell($col_w[0], 6, $row['item_code'],                               0, 0, 'L', true);
    $pdf->Cell($col_w[1], 6, mb_substr($row['name'], 0, 60),                  0, 0, 'L', true);
    // Stock weight: green if in stock, grey if zero
    if ($has_stock) {
        $pdf->SetTextColor(40, 167, 69);
    }
    $pdf->Cell($col_w[2], 6, number_format((float)$row['stock_weight'], 2) . ' kg', 0, 0, 'R', true);
    $pdf->Ln();
    $odd = !$odd;
    $pdf->SetTextColor(30, 30, 40);
}

// Totals row
$pdf->SetFillColor(230, 236, 250);
$pdf->SetTextColor(19, 150, 15);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell($col_w[0] + $col_w[1], 7, 'TOTAL STOCK WEIGHT', 0, 0, 'L', true);
$pdf->Cell($col_w[2],              7, number_format($total_weight, 2) . ' kg', 0, 0, 'R', true);
$pdf->Ln();

$filename = 'GBR_Inventory_Export_' . date('Ymd_His') . '.pdf';
$pdf->Output('D', $filename);
exit;
