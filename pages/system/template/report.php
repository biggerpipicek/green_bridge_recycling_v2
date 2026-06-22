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
    <style id="gbr-report-style">
        #printable-report {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            padding: 30px 40px;
        }
        #printable-report h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .field-row {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            margin-bottom: 10px;
        }
        .field-row label {
            font-weight: 400;
            min-width: 230px;
            flex-shrink: 0;
            margin-bottom: 0;
        }
        .field-row .menge-number {
            font-weight: 700;
            min-width: 95px;
            flex-shrink: 0;
        }
        .field-input {
            display: inline-block;
            border: none;
            border-bottom: 1px solid #999;
            background: #f3f1ee;
            padding: 2px 8px;
            font-size: 14px;
            border-radius: 0;
            flex-shrink: 0;
        }
        .field-input:focus {
            box-shadow: none;
            border-bottom: 1px solid #555;
            background: #ece9e4;
        }
        .kg-suffix {
            margin-left: 6px;
            flex-shrink: 0;
        }
        .gbr-table {
            border: 1px solid #ccc;
            margin-top: 25px;
        }
        .gbr-table th {
            background: #f3f1ee;
            font-weight: 600;
            border-bottom: 1px solid #ccc;
        }
        .gbr-table td,
        .gbr-table th {
            border-right: 1px solid #e2e2e2;
            padding: 8px 12px;
            vertical-align: middle;
        }
        .gbr-table td:last-child,
        .gbr-table th:last-child {
            border-right: none;
        }
        .gbr-table .total-row {
            background: #f3f1ee;
            font-weight: 700;
        }
        .menge-kg-cell {
            text-align: right;
            width: 140px;
        }
        hr.top-rule {
            border-top: 1px solid #333;
            margin: 25px 0 25px 0;
        }
    </style>

    <div class="container-fluid">
        <div class="container-sm" id="printable-report">

            <div class="d-flex justify-content-between align-items-center">
                <img src="https://www.gbrguh.eu/imgs/internal/img_one.png" alt="Green Bridge Recycling" style="height: 80px;">
                <img src="https://www.gbrguh.eu/imgs/internal/img_two.png" alt="Gühring Carbide" style="height: 40px;">
            </div>
            <hr class="top-rule">

            <?php if($id != 0): ?>
                <h1>Sortierbericht Gühring Tool Circle</h1>
                <h2 class="mb-4">Gühring order #<?= $id; ?> - <?php echo $order_data['partner_name']; ?></h2>

                <div class="field-row">
                    <label for="kunde">Kunde:</label>
                    <input type="text" id="kunde" class="form-control field-input" style="width: 400px;" value="<?php echo $order_data['partner_name']; ?>">
                </div>

                <div class="field-row">
                    <label for="interne_vorgangsnummer">Interne Vorgangsnummer:</label>
                    <input type="number" id="interne_vorgangsnummer" class="form-control field-input" style="width: 220px;">
                </div>

                <div class="field-row">
                    <label for="datum">Datum:</label>
                    <input type="date" id="datum" class="form-control field-input" style="width: 220px;" value="<?php echo $order_data['date']; ?>">
                </div>

                <br>

                <div class="field-row">
                    <label for="m_schaftwerkzeuge">Menge Schaftwerkzeuge</label>
                    <span class="menge-number">400199354:</span>
                    <input type="number" id="m_schaftwerkzeuge" name="m_schaftwerkzeuge" class="form-control field-input menge-input" data-row="schaft" style="width: 120px;">
                    <span class="kg-suffix">kg</span>
                </div>

                <div class="field-row">
                    <label for="m_wsp_und_sontiges_hm">Menge WSP und sonstiges HM</label>
                    <span class="menge-number">400199360:</span>
                    <input type="number" id="m_wsp_und_sontiges_hm" name="m_wsp_und_sontiges_hm" class="form-control field-input menge-input" data-row="wsp" style="width: 120px;">
                    <span class="kg-suffix">kg</span>
                </div>

                <div class="field-row">
                    <label for="m_reststoffe">Menge Reststoffe (HSS etc.)</label>
                    <span class="menge-number">400200010:</span>
                    <input type="number" id="m_reststoffe" name="m_reststoffe" class="form-control field-input menge-input" data-row="rest" style="width: 120px;">
                    <span class="kg-suffix">kg</span>
                </div>

                <div class="field-row">
                    <label for="g_gebinde">Gewicht Gebinde (Fass, Palette etc.)</label>
                    <input type="number" id="g_gebinde" name="g_gebinde" class="form-control field-input" style="width: 120px;">
                    <span class="kg-suffix">kg</span>
                </div>

                <div class="container px-0">
                    <div class="table-responsive">
                        <table class="table gbr-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Nummer</th>
                                    <th>Beschreibung</th>
                                    <th class="menge-kg-cell">Menge (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>400199354</strong></td>
                                    <td>Schaftwerkzeuge Bohrer, Fräser etc.</td>
                                    <td class="menge-kg-cell" id="row_schaft">0</td>
                                </tr>
                                <tr>
                                    <td><strong>400199360</strong></td>
                                    <td>WSP, anderer HM Schrott</td>
                                    <td class="menge-kg-cell" id="row_wsp">0</td>
                                </tr>
                                <tr>
                                    <td><strong>400200010</strong></td>
                                    <td>Reststoffe HSS, Metalle, Abfälle</td>
                                    <td class="menge-kg-cell" id="row_rest">0</td>
                                </tr>
                                <tr class="total-row">
                                    <td colspan="2">Total</td>
                                    <td class="menge-kg-cell" id="row_total">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12 mt-4 d-print-none">
                    <button type="button" class="btn btn-primary w-100 py-2" onclick="printReport()">
                        Print report
                    </button>
                </div>
            <?php else: ?>
                <p class="text-center py-5 text-muted">
                    <i class="bi bi-folder-x display-6 d-block mb-2 text-secondary opacity-50"></i>
                    No orders found matching current filter criteria.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Live-sync the three Menge inputs into the table rows and recompute Total
        function updateMengeTable() {
            const rows = ['schaft', 'wsp', 'rest'];
            let total = 0;

            rows.forEach(key => {
                const input = document.querySelector(`.menge-input[data-row="${key}"]`);
                const cell = document.getElementById('row_' + key);
                const value = input && input.value !== '' ? parseFloat(input.value) : 0;
                cell.textContent = value.toLocaleString('de-DE', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                total += value;
            });

            document.getElementById('row_total').textContent = total.toLocaleString('de-DE', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        document.querySelectorAll('.menge-input').forEach(input => {
            input.addEventListener('input', updateMengeTable);
        });

        // Run once on load in case of pre-filled values
        updateMengeTable();

        function printReport() {
            const printWindow = window.open('', '_blank');
            const reportContent = document.getElementById('printable-report').outerHTML;
            const reportStyles = document.getElementById('gbr-report-style').outerHTML;

            // Snapshot the current input/select values into the cloned markup,
            // since outerHTML alone won't capture live form field values.
            const parser = new DOMParser();
            const doc = parser.parseFromString(`<div>${reportContent}</div>`, 'text/html');
            document.querySelectorAll('#printable-report input').forEach((input, i) => {
                const clone = doc.querySelectorAll('input')[i];
                if (clone) {
                    if (input.type === 'date' || input.type === 'number' || input.type === 'text') {
                        clone.setAttribute('value', input.value);
                    }
                }
            });
            const syncedContent = doc.body.innerHTML;

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Sortierbericht - Order #<?= $id; ?></title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        ${reportStyles}
                        <style>
                            @page {
                                size: A4 portrait;
                                margin: 10mm;
                            }
                            body {
                                padding: 10px;
                                font-size: 12px;
                                font-family: Arial, Helvetica, sans-serif;
                            }
                            button { display: none !important; }
                            h1 { font-size: 18px; }
                            h2 { font-size: 15px; }
                            .field-input { font-size: 12px; padding: 2px 6px; }
                            .gbr-table { font-size: 11px; }
                        </style>
                    </head>
                    <body>
                        ${syncedContent}
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