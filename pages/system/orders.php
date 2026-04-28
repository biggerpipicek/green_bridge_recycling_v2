<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // ORDERS - SHOWING

    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "GBR Orders";
    include "../../build/header.php";

    $action = $_GET['action'] ?? '';

    $approve_type = [
        "approved" => "badge bg-success",
        "not approved" => "badge bg-danger"
    ];

    $order_type = [
        "created" => "badge bg-danger",
        "received" => "badge bg-warning",
        "in process" => "badge bg-info",
        "completed" => "badge bg-success",
        "cancelled" => "badge bg-danger"
    ];

    $order_currency = [
        "EUR" => "€",
        "USD" => "$",
        "YEN" => "¥",
        "ZL" => "zł",
        "CZK" => "Kč"
    ];
    ?>
    
    <div class="container-fluid">
        <!-- INCOMIG/OUTGOING ORDERS NAVIGATION -->
        <ul class="nav nav-tabs container-sm">
            <li class="nav-item"><a href="?action=incoming_orders" class="nav-link <?php echo (($_GET['action'] ?? '') === 'incoming_orders') ? 'active' : ''; ?>">Incoming orders</a></li>
            <li class="nav-item"><a href="?action=outgoing_orders" class="nav-link <?php echo (($_GET['action'] ?? '') === 'outgoing_orders') ? 'active' : ''; ?>">Outgoing orders</a></li>
            <li class="nav-item"><a href="guhring_orders.php?action=go" class="nav-link <?php echo (($_GET['action'] ?? '') === 'go') ? 'active' : ''; ?>">Gühring orders</a></li>
        </ul>

        <br>

        <!-- NAVIGATION -->
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-50 mx-auto rounded-3">
            <div class="container-fluid justify-content-between">

                <!-- SEARCH -->
                <form action="" method="get" class="d-flex">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <!-- RIGHT SIDE -->
                <ul class="navbar-nav">

                    <!-- SORT DROPDOWN -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Sort</a>
                        <ul class="dropdown-menu">
                            <li><a href="?action=<?php echo $action; ?>&sort=date_asc" class="dropdown-item">Date: New → Old</a></li>
                            <li><a href="?action=<?php echo $action; ?>&sort=date_desc" class="dropdown-item">Date: Old → New</a></li>
                            <li><a href="?action=<?php echo $action; ?>&sort=price_asc" class="dropdown-item">Price: Low → High</a></li>
                            <li><a href="?action=<?php echo $action; ?>&sort=price_desc" class="dropdown-item">Price: High → Low</a></li>
                        </ul>
                    </li>

                    <!-- ACTIONS -->
                    <li class="nav-item">
                        <a href="orders.php?action=<?php echo $action; ?>" class="nav-link">Refresh</a>
                    </li>
                    <li class="nav-item">
                        <a href="new_orders.php?action=<?php echo $action; ?>" class="nav-link">Add</a>
                    </li>
                    <li class="nav-item">
                        <a href="" class="nav-link">Export</a>
                    </li>

                </ul>
            </div>
        </nav>
        <br>
        <!-- TABLE WITH ORDER LIST -->
        <div class="container-sm pt-4">

            <?php
                if($action == "incoming_orders"):
            ?>
            <!-- INCOMING ORDERS -->
            <h1><i>Incoming Orders</i></h1>
            <table class="table align-middle text-center">
                <thead>
                    <th>Order No.</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Documents</th>
                    <th>Price</th>
                    <th>Order Status</th>
                    <th>Approve Status</th>
                    <th>Check Order</th>
                </thead>
                <tbody>
                    <!--<tr>
                        <td>GBR-IN-00001</td>
                        <td>17/04/2026</td>
                        <td>Schredder</td>
                        <td>GBR-IN-00001_document.pdf</td>
                        <td>10000 €</td>
                        <td><div class="badge bg-success">Completed</div></td>
                        <td><div class="badge bg-primary">Approved</div></td>
                        <td><a href="" type="button" class="btn btn-primary">Check order</a></td>
                    </tr>
                    <tr>
                        <td>GBR-IN-00002</td>
                        <td>17/04/2026</td>
                        <td>RecoMetall</td>
                        <td>GBR-IN-00002_document.pdf</td>
                        <td>12500 €</td>
                        <td><div class="badge bg-danger">Received</div></td>
                        <td><div class="badge bg-primary">Approved</div></td>
                        <td><a href="" type="button" class="btn btn-primary">Check order</a></td>
                    </tr>
                    <tr>
                        <td>GBR-IN-00003</td>
                        <td>20/04/2026</td>
                        <td>Comet A Trading</td>
                        <td>GBR-IN-00003_document.pdf</td>
                        <td>8500 €</td>
                        <td><div class="badge bg-warning">In process</div></td>
                        <td><div class="badge bg-danger">Not Approved</div></td>
                        <td><a href="" type="button" class="btn btn-primary">Check order</a></td>
                    </tr>
                    <tr>
                        <td>GBR-IN-00004</td>
                        <td>20/04/2026</td>
                        <td>Guhring Sweden</td>
                        <td>GBR-IN-00004_document.pdf</td>
                        <td>125000 CZK</td>
                        <td><div class="badge bg-warning">In process</div></td>
                        <td><div class="badge bg-danger">Not Approved</div></td>
                        <td><a href="" type="button" class="btn btn-primary">Check order</a></td>
                    </tr>-->
                    <?php
                        //$sql = "SELECT orders.id, orders.order_no, orders.date, partners.name AS partner_name, order_attachments.file_path AS img_path, orders.price, orders.currency, orders.type, orders.approve_status, orders.order_status FROM orders JOIN partners ON orders.partner_id = partners.id JOIN order_attachments ON orders.id = order_attachments.order_id";
                        $sql = "SELECT orders.id, orders.order_no, orders.date, partners.name AS partner_name, order_attachments.file_path AS img_path, orders.price, orders.currency, orders.type, orders.approve_status, orders.order_status FROM orders JOIN partners ON orders.partner_id = partners.id LEFT JOIN order_attachments ON orders.id = order_attachments.order_id";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                if($row['type'] === "in") {
                                    $a_stat = $row['approve_status'];
                                    $a_badge = $approve_type[$a_stat] ?? "badge bg-secondary";

                                    $o_stat = $row['order_status'];
                                    $o_badge = $order_type[$o_stat] ?? "badge bg-secondary";

                                    $currency = $row['currency'];
                                    $symbol_currency = $order_currency[$currency] ?? "XXX";
                                    $date = date("m/d/Y", strtotime($row['date']));
                                    echo "<tr><td>".$row['order_no']."</td><td>".$date."</td><td>".$row['partner_name']."</td><td><a href='/green_bridge_recycling_v2/".$row['img_path']."' target='_blank'>Document</a></td><td>".$row['price']." ".$symbol_currency."</td><td><span class='{$o_badge}'>".ucfirst($row['order_status'])."</span></td><td><span class='{$a_badge}'>".ucfirst($row['approve_status'])."</span></td><td><a href='template/order.php?id=".$row['id']."' class='btn btn-outline-primary'>Check</a></td></tr>";
                                }
                            }
                        }
                    ?>
                </tbody>
            </table>
            <?php elseif($action == "outgoing_orders"): ?>
            <h1><i>Outgoing Orders</i></h1>
            <!-- OUTGOING ORDERS -->
            <table class="table align-middle text-center">
                <thead>
                    <th>Order No.</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Documents</th>
                    <th>Price</th>
                    <th>Approve Status</th>
                    <th>Check Order</th>
                </thead>
                <tbody>
                    <!--<tr>
                        <td>GBR-OUT-00001</td>
                        <td>17/04/2026</td>
                        <td>RecoMetall</td>
                        <td>GBR-OUT-00001_document.pdf</td>
                        <td>10000 €</td>
                        <td><div class="badge bg-primary">Approved</div></td>
                        <td><a href="" type="button" class="btn btn-primary">Check order</a></td>
                    </tr>
                    <tr>
                        <td>GBR-OUT-00002</td>
                        <td>17/04/2026</td>
                        <td>Metallio SP</td>
                        <td>GBR-OUT-00002_document.pdf</td>
                        <td>12500 €</td>
                        <td><div class="badge bg-primary">Approved</div></td>
                        <td><a href="" type="button" class="btn btn-primary">Check order</a></td>
                    </tr>
                    <tr>
                        <td>GBR-OUT-00003</td>
                        <td>20/04/2026</td>
                        <td>Guhring Germany</td>
                        <td>GBR-OUT-00003_document.pdf</td>
                        <td>8500 €</td>
                        <td><div class="badge bg-danger">Not Approved</div></td>
                        <td><a href="" type="button" class="btn btn-primary">Check order</a></td>
                    </tr>
                    <tr>
                        <td>GBR-OUT-00004</td>
                        <td>20/04/2026</td>
                        <td>Schrott & Bobs GmbH</td>
                        <td>GBR-OUT-00004_document.pdf</td>
                        <td>125000 CZK</td>
                        <td><div class="badge bg-danger">Not Approved</div></td>
                        <td><a href="" type="button" class="btn btn-primary">Check order</a></td>
                    </tr>-->
                    <?php
                        $sql = "SELECT orders.id, orders.order_no, orders.date, partners.name AS partner_name, order_attachments.file_path AS img_path, orders.price, orders.currency, orders.type, orders.approve_status, orders.order_status FROM orders JOIN partners ON orders.partner_id = partners.id LEFT JOIN order_attachments ON orders.id = order_attachments.order_id";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                if($row['type'] === "out") {
                                    $stat = $row['approve_status'];
                                    $badge = $approve_type[$stat] ?? "badge bg-secondary";
                                    $date = date("m/d/Y", strtotime($row['date']));
                                    echo "<tr><td>".$row['order_no']."</td><td>".$date."</td><td>".$row['partner_name']."</td><td><a href='/green_bridge_recycling_v2/".$row['img_path']."' target='_blank'>Document</a></td><td>".$row['price']." ".$row['currency']."</td><td><span class='{$badge}'>".ucfirst($stat)."</span></td><td><a href='template/order.php?id=".$row['id']."' class='btn btn-outline-primary'>Check</a></td></tr>";
                                }
                            }
                        }
                    ?>
                </tbody>
            </table>
            <?php endif; ?>
            <?php if(!isset($_GET['action'])): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>    
                        Please select either Incoming orders or Outgoing orders to view the list.
                </div>
                <?php endif; ?>
        </div>
    </div>

    <!-- LAST ADDED ORDER -->

    <?php
        include "../../build/footer.php";
    ?>