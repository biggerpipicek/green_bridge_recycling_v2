<?php
// GÜHRING ORDERS CSV EXPORT (full export, ignores filters — matches export_guhring_orders.php)

require "../../build/auth.php";
require "../../build/functions.php";

logActivity($conn, $_SESSION['user_id'], 'checking', 'orders', $_SESSION['user_id'], "User #{$_SESSION['user_id']} exported guhring orders (CSV)");

// ---------------------------------------------------------
// FETCH ALL GÜHRING ORDERS (same query as the PDF export)
// ---------------------------------------------------------
$sql = "SELECT o.id, o.order_no, o.date, p.name AS partner_name,
               o.price, o.currency, o.netto_w, o.brutto_w,
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
// FETCH ORDER MATERIALS FOR ALL ORDERS IN ONE QUERY
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
// BUILD CSV
// ---------------------------------------------------------
$filename = 'GBR_Guhring_Orders_Export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens accents/diacritics correctly

fputcsv($out, ['Order No.', 'Date', 'Customer', 'Price', 'Currency', 'Netto (kg)', 'Brutto (kg)', 'Order Status', 'Approve Status', 'Materials']);

foreach ($orders_rows as $row) {
    $items = $materials_by_order[$row['id']] ?? [];
    $items_str = empty($items)
        ? ''
        : implode('; ', array_map(
            fn($i) => $i['item_code'] . ' - ' . $i['name'] . ' x ' . number_format((float)$i['quantity'], 2) . 'kg',
            $items
        ));

    fputcsv($out, [
        $row['order_no'],
        $row['date'],
        $row['partner_name'],
        number_format((float)$row['price'], 2),
        strtoupper($row['currency'] ?? 'EUR'),
        number_format((float)$row['netto_w'], 2),
        number_format((float)$row['brutto_w'], 2),
        ucfirst($row['order_status']),
        ucfirst($row['approve_status']),
        $items_str,
    ]);
}

fclose($out);
exit;
