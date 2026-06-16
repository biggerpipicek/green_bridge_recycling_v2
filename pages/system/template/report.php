<?php

    // MICHAEL D. PHILLIPS - 16.06.2026
    // REPORT PAGE

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // --- 1. FETCH MAIN ORDER DATA ---
    $sql = "SELECT orders.*, partners.name AS partner_name FROM orders JOIN partners ON orders.partner_id = partners.id WHERE orders.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $order_data = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$order_data) { die("Order not found."); }

    $page_title = "GBR REPORT ORDER #" . $id;

    logActivity($conn, $_SESSION['user_id'], 'checking', 'ticket', $id, "User #{$_SESSION['user_id']} clicked on Write report for order {$id}");

    include "../../../build/header.php";
?>
    <div class="container-fluid">
        <div class="container-sm" id="printable-report">

            <div class="d-flex justify-content-between align-items-center">
                <img src="/green_bridge_recycling_v2/imgs/internal/img_one.png" alt="Green Bridge Recycling" style="height: 80px;">
                <img src="/green_bridge_recycling_v2/imgs/internal/img_two.png" alt="Gühring Carbide" style="height: 40px;">
            </div>
            <hr>

            <h1>Sortierbericht Gühring Tool Circle</h1>
            <?php if($id != 0): ?>
                <h2>Gühring order #<?= $id; ?> -  <?php echo $order_data['partner_name']; ?></h2>
                <label for="kunde" class="form-label"><strong>Kunde:</strong><input type="text" name="" id="" class="form-control" required style="width: 500px;" value="<?php echo $order_data['partner_name']; ?>"></label>
                <br>
                <br>
                <label for="brutto_weight" class="form-label"><strong>Brutto Weight:</strong><input type="number" name="" id="" class="form-control" required style="width: 500px;" value="<?php echo $order_data['brutto_w']; ?>"></label>
                <br>
                <br>
                <label for="interne_vorgangsnummer" class="form-label"><strong>Interne Vorgangsnummer:</strong><input type="number" name="" id="" class="form-control" style="width: 500px;"></label>
                <br>
                <br>
                <label for="datum"><strong>Datum:</strong><input type="date" name="" id="" class="form-control" style="width: 225px;" value="<?php echo $order_data['date']; ?>"></label>
                <br>
                <br>
                <label for="m_schaftwerkzeuge"><strong>Menge Schaftwerkzeuge:</strong><input type="number" class="form-control" style="width: 500px;"></input></label>
                <br>
                <br>
                <label for="m_wsp_und_sontiges_hm"><strong>Menge WSP und sontiges HM:</strong><input type="number" class="form-control" style="width: 500px;"></input></label>
                <br>
                <br>
                <label for="m_reststoffe"><strong>Menge Reststoffe (HSS etc.):</strong><input type="number" class="form-control" style="width: 500px;"></input></label>
                <br>
                <br>
                <label for="g_gebinde"><strong>Gewicht Gebinde (Fass, Palette etc.):</strong><input type="number" class="form-control" style="width: 500px;"></input></label>
                <br>
                <br>
                <br>
                <div class="container">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light border-bottom">
                                    <tr>
                                        <th class="ps-4 py-3 text-muted small text-uppercase">Nummer</th>
                                        <th class="py-3 text-muted small text-uppercase text-center">Beschreibung</th>
                                        <th class="pe-4 py-3 text-muted small text-uppercase text-end">Menge (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>400199354</strong></td>
                                        <td>Schaftwerkzeuge Bohrer, Fräser etc.</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>400199360</strong></td>
                                        <td>WSP, anderer HM Schrott</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>400200010</strong></td>
                                        <td>Reststoffe HSS, Metalle, Abfälle</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>    
                <br>
                <br>
                <br>
                <br>
                <div class="col-12 mt-4">
                    <button type="button" class="btn btn-primary w-100 py-2" onclick="printReport()">
                        Print report
                    </button>
                </div>
            <?php else: ?>
                <p colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-folder-x display-6 d-block mb-2 text-secondary opacity-50"></i>
                    No orders found matching current filter criteria.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function printReport() {
            const printWindow = window.open('', '_blank');
            const reportContent = document.getElementById('printable-report').innerHTML;

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Sortierbericht - Order #<?= $id; ?></title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            @page {
                                size: A4 portrait;
                                margin: 10mm;
                            }
                            body {
                                padding: 10px;
                                font-size: 12px;
                            }
                            button { display: none !important; }
                            h1 { font-size: 18px; }
                            h2 { font-size: 15px; }
                            .form-control { font-size: 12px; padding: 2px 6px; }
                            .table { font-size: 11px; }
                        </style>
                    </head>
                    <body>
                        ${reportContent}
                    </body>
                </html>
            `);

            printWindow.document.close();

            const link = printWindow.document.querySelector('link');
            link.onload = function() {
                printWindow.focus();
                printWindow.print();
            };
        }
    </script>
<?php

    include "../../../build/footer.php";

?>