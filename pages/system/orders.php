<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // ORDERS - SHOWING

    $page_title = "GBR Orders";
    include "../../build/header.php";
    ?>

    <!-- NAVIGATION -->
    <div class="container-fluid">
    <!-- TABLE WITH ORDER LIST -->
        <div class="container-sm pt-4">
            <table class="table align-middle">
                <thead>
                    <th>Order No.</th>
                    <th>Creation Date</th>
                    <th>Customer</th>
                    <th>Documents</th>
                    <th>Price</th>
                    <th>Order Status</th>
                    <th>Approve Status</th>
                    <th>Check Order</th>
                </thead>
                <tbody>
                    <tr>
                        <td>GBR-IN-00001</td>
                        <td>17/04/2026</td>
                        <td>Schredder</td>
                        <td>GBR-IN-00001_document.pdf</td>
                        <td>10000 €</td>
                        <td><div class="badge bg-success">Completed</div></td>
                        <td><div class="badge bg-primary">Approved</div></td>
                        <td><button type="button" class="btn btn-primary">Check order</button></td>
                    </tr>
                    <tr>
                        <td>GBR-IN-00002</td>
                        <td>17/04/2026</td>
                        <td>RecoMetall</td>
                        <td>GBR-IN-00002_document.pdf</td>
                        <td>12500 €</td>
                        <td><div class="badge bg-danger">Received</div></td>
                        <td><div class="badge bg-primary">Approved</div></td>
                        <td><button type="button" class="btn btn-primary">Check order</button></td>
                    </tr>
                    <tr>
                        <td>GBR-IN-00003</td>
                        <td>20/04/2026</td>
                        <td>Comet A Trading</td>
                        <td>GBR-IN-00003_document.pdf</td>
                        <td>8500 €</td>
                        <td><div class="badge bg-warning">In process</div></td>
                        <td><div class="badge bg-danger">Not Approved</div></td>
                        <td><button type="button" class="btn btn-primary">Check order</button></td>
                    </tr>
                    <tr>
                        <td>GBR-IN-00004</td>
                        <td>20/04/2026</td>
                        <td>Guhring Sweden</td>
                        <td>GBR-IN-00004_document.pdf</td>
                        <td>125000 CZK</td>
                        <td><div class="badge bg-warning">In process</div></td>
                        <td><div class="badge bg-danger">Not Approved</div></td>
                        <td><button type="button" class="btn btn-primary">Check order</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- LAST ADDED ORDER -->

    <?php
        include "../../build/footer.php";
    ?>