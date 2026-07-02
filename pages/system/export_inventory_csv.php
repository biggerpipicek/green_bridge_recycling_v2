<?php
// INVENTORY CSV EXPORT (matches export_inventory.php)

require "../../build/auth.php";
require "../../build/functions.php";

logActivity($conn, $_SESSION['user_id'], 'checking', 'inventory', $_SESSION['user_id'], "User #{$_SESSION['user_id']} exported inventory (CSV)");

// ---------------------------------------------------------
// FETCH ALL INVENTORY ITEMS (same query as inventory.php / PDF export)
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
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

// ---------------------------------------------------------
// BUILD CSV
// ---------------------------------------------------------
$filename = 'GBR_Inventory_Export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens accents/diacritics correctly

fputcsv($out, ['Item Code', 'Item Name', 'In Stock (kg)']);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['item_code'],
        $row['name'],
        number_format((float)$row['stock_weight'], 2),
    ]);
}

fclose($out);
exit;
