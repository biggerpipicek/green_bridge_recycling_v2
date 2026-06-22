<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // ORDERS - SHOWING (Refactored for efficiency)

    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "GBR Orders";
    $extra_css = ["../../styles/orders.css"];

    include "../../build/header.php";

    // 1. ACCESS CONTROL
    // Using an array makes it easy to add/remove people later.
    $allowed_users = ["admin", "adreas", "mirka", "info", "test_user"];
    $current_user = $_SESSION['user'] ?? null;

    if (in_array($current_user, $allowed_users)):

        $action = $_GET['action'] ?? '';
        
        // 2. CONFIGURATION MAPS
        $approve_type = [
            "approved" => "badge bg-success",
            "not approved" => "badge bg-danger"
        ];

        $order_type_badges = [
            "created" => "badge bg-danger",
            "received" => "badge bg-warning",
            "in process" => "badge bg-info",
            "completed" => "badge bg-success",
            "cancelled" => "badge bg-danger"
        ];

        $order_currency = [
            "EUR" => "€", "USD" => "$", "YEN" => "¥", "ZL" => "zł", "CZK" => "Kč"
        ];

        // 3. SORTING LOGIC
        $sort_by = $_GET['sort'] ?? '';
        $sort_map = [
            "orderno_asc"  => "ORDER BY order_no ASC",
            "orderno_desc" => "ORDER BY order_no DESC",
            "date_asc"     => "ORDER BY date ASC",
            "date_desc"    => "ORDER BY date DESC",
            "price_asc"    => "ORDER BY price ASC",
            "price_desc"   => "ORDER BY price DESC",
        ];
        $sort_sql = $sort_map[$sort_by] ?? "ORDER BY date DESC";

    ?>
    
    <div class="container-fluid">
        <ul class="nav nav-tabs container-sm">
            <li class="nav-item"><a href="?action=incoming_orders" class="nav-link <?= ($action === 'incoming_orders') ? 'active' : ''; ?>">Incoming orders</a></li>
            <li class="nav-item"><a href="?action=outgoing_orders" class="nav-link <?= ($action === 'outgoing_orders') ? 'active' : ''; ?>">Outgoing orders</a></li>
            <li class="nav-item"><a href="guhring_orders.php?action=go" class="nav-link <?= ($action === 'go') ? 'active' : ''; ?>">Gühring orders</a></li>
        </ul>

        <br>

        <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-50 mx-auto rounded-3">
            <div class="container-fluid justify-content-between">
                <form action="" method="get" class="d-flex">
                    <input type="hidden" name="action" value="<?= htmlspecialchars($action); ?>">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Sort</a>
                        <ul class="dropdown-menu">
                            <li><a href="?action=<?= $action; ?>&sort=date_asc" class="dropdown-item">Date: Old → New</a></li>
                            <li><a href="?action=<?= $action; ?>&sort=date_desc" class="dropdown-item">Date: New → Old</a></li>
                            <li><a href="?action=<?= $action; ?>&sort=price_asc" class="dropdown-item">Price: Low → High</a></li>
                            <li><a href="?action=<?= $action; ?>&sort=price_desc" class="dropdown-item">Price: High → Low</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="orders.php?action=<?= $action; ?>" class="nav-link">Refresh</a></li>
                    <li class="nav-item"><a href="new_orders.php?action=<?= $action; ?>" class="nav-link">Add</a></li>
                </ul>
            </div>
        </nav>

        <div class="container-sm pt-4">
            <?php if ($action === "incoming_orders" || $action === "outgoing_orders"): 
                $type_filter = ($action === "incoming_orders") ? "in" : "out";
            ?>
                <h1><i><?= ucwords(str_replace('_', ' ', $action)) ?></i></h1>
                <table class="table align-middle text-center">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Date</th>
                            <th>Customer/Partner</th>
                            <th>Documents</th>
                            <th>Price</th>
                            <?php if($type_filter === 'in'): ?><th>Order Status</th><?php endif; ?>
                            <th>Approve Status</th>
                            <th>Check Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Fetching data - One query to rule them all
                            $sql = "SELECT o.*, p.name AS partner_name, oa.file_path AS img_path 
                                    FROM orders o 
                                    JOIN partners p ON o.partner_id = p.id 
                                    LEFT JOIN order_attachments oa ON o.id = oa.order_id 
                                    WHERE o.type = '$type_filter' 
                                    $sort_sql";
                                    
                            $result = mysqli_query($conn, $sql);

                            while($row = mysqli_fetch_assoc($result)):
                                $a_badge = $approve_type[$row['approve_status']] ?? "badge bg-secondary";
                                $o_badge = $order_type_badges[$row['order_status']] ?? "badge bg-secondary";
                                $symbol = $order_currency[$row['currency']] ?? $row['currency'];
                                $date = date("m/d/Y", strtotime($row['date']));

                                $doc_link = !empty($row['img_path']) 
                                    ? "<a href='{$row['img_path']}' target='_blank'>Document</a>" 
                                    : "<span class='text-muted'>No document</span>";
                        ?>
                            <tr>
                                <td><?= $row['order_no'] ?></td>
                                <td><?= $date ?></td>
                                <td><?= $row['partner_name'] ?></td>
                                <td><?= $doc_link ?></td>
                                <td><?= $row['price'] ?> <?= $symbol ?></td>
                                <?php if($type_filter === 'in'): ?>
                                    <td><span class="<?= $o_badge ?>"><?= ucfirst($row['order_status']) ?></span></td>
                                <?php endif; ?>
                                <td><span class="<?= $a_badge ?>"><?= ucfirst($row['approve_status']) ?></span></td>
                                <td><a href="template/order.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary">Check</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>    
                    Please select either Incoming orders or Outgoing orders to view the list.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../js/easteregg.js"></script>

    <?php
    else:
        // Optional: Redirect if not allowed
        echo "<div class='container mt-5'><div class='alert alert-danger'>Access Denied.</div></div>";
    endif;

    include "../../build/footer.php";
?>