<?php
    // MICHAEL D. PHILLIPS - 28/04/2026
    // GÜHRING ORDERS LIST

    require "../../build/auth.php";

    $page_title = "Gühring GBR Orders";
    include "../../build/header.php";

    $action = $_GET['action'] ?? '';
?>
    <div class="container-fluid">
        <!-- INCOMIG/OUTGOING ORDERS NAVIGATION -->
        <ul class="nav nav-tabs container-sm">
            <li class="nav-item"><a href="orders.php?action=incoming_orders" class="nav-link <?php echo (($_GET['action'] ?? '') === 'incoming_orders') ? 'active' : ''; ?>">Incoming orders</a></li>
            <li class="nav-item"><a href="orders.php?action=outgoing_orders" class="nav-link <?php echo (($_GET['action'] ?? '') === 'outgoing_orders') ? 'active' : ''; ?>">Outgoing orders</a></li>
            <li class="nav-item"><a href="guhring_orders.php?action=go" class="nav-link <?php echo (($_GET['action'] ?? '') === 'go') ? 'active' : ''; ?>">Gühring orders</a></li>
        </ul>

        <br>

        <!-- NAVIGATION -->
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-50 mx-auto rounded-3">
            <div class="container-fluid justify-content-between">

                <!-- SEARCH -->
                <form action="" method="get" class="d-flex">
                    <input type="hidden" name="action" value="">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <!-- RIGHT SIDE -->
                <ul class="navbar-nav">

                    <!-- SORT DROPDOWN -->
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Sort</a>
                        <ul class="dropdown-menu">
                            <li><a href="guhring_orders.php?action=&sort=date_asc" class="dropdown-item">Date: New → Old</a></li>
                            <li><a href="guhring_orders.php?action=&sort=date_desc" class="dropdown-item">Date: Old → New</a></li>
                            <li><a href="guhring_orders.php?action=&sort=price_asc" class="dropdown-item">Price: Low → High</a></li>
                            <li><a href="guhring_orders.php?action=&sort=price_desc" class="dropdown-item">Price: High → Low</a></li>
                        </ul>
                    </li>

                    <!-- ACTIONS -->
                    <li class="nav-item">
                        <a href="guhring_orders.php" class="nav-link">Refresh</a>
                    </li>
                    <li class="nav-item">
                        <a href="add_guhring_order.php?action=" class="nav-link">Add</a>
                    </li>
                    <li class="nav-item">
                        <a href="" class="nav-link">Export</a>
                    </li>

                </ul>
            </div>
        </nav>
        <br>
        <!-- TABLE WITH GUHRING ORDERS -->
        <div class="container-sm pt-4">
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
                    <?php
                        //$sql = "SELECT orders.id, orders.order_no, orders.date, partners.name AS partner_name, order_attachments.file_path AS img_path, orders.price, orders.currency, orders.type, orders.approve_status, orders.order_status FROM orders JOIN partners ON orders.partner_id = partners.id JOIN order_attachments ON orders.id = order_attachments.order_id";
                        $sql = "SELECT orders.id, orders.order_no, orders.date, partners.name AS partner_name, order_attachments.file_path AS img_path, orders.price, orders.currency, orders.type, orders.approve_status, orders.order_status FROM orders JOIN partners ON orders.partner_id = partners.id LEFT JOIN order_attachments ON orders.id = order_attachments.order_id WHERE orders.type IN ('guh-in', 'guh-out')";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                if($row['type'] === "guh-in" || $row['type'] === "guh-out") {
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
                        } else {
                            echo "<tr><td colspan='8'>No orders found..</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

<?php
    include "../../build/footer.php";
?>