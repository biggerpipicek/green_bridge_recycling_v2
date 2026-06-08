<?php
    // MICHAEL D. PHILLIPS - 28/04/2026
    // GÜHRING ORDERS LIST

    require "../../build/auth.php";

    $page_title = "Gühring GBR Orders";

    $extra_css = ["../../styles/orders.css"];
    
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
        "JPY" => "¥",
        "PLN" => "zł",
        "CZK" => "Kč"
    ];

    $sort_by = $_GET['sort'] ?? '';
    $sort = "";
    switch($sort_by) {
        case "orderno_asc":
            $sort = "ORDER BY order_no ASC";
            break;
        case "orderno_desc":
            $sort = "ORDER BY order_no DESC";
            break;
        case "date_asc":
            $sort = "ORDER BY date ASC";
            break;
        case "date_desc":
            $sort = "ORDER BY date DESC";
            break;
        case "price_asc":
            $sort = "ORDER BY price ASC";
            break;
        case "price_desc":
            $sort = "ORDER BY price DESC";
            break;
    }
?>
    <div class="container-fluid">
        <!-- INCOMIG/OUTGOING ORDERS NAVIGATION -->
        <ul class="nav nav-tabs container-sm">
            <!--
            <li class="nav-item"><a href="orders.php?action=incoming_orders" class="nav-link <?php echo (($_GET['action'] ?? '') === 'incoming_orders') ? 'active' : ''; ?>">Incoming orders</a></li>
            <li class="nav-item"><a href="orders.php?action=outgoing_orders" class="nav-link <?php echo (($_GET['action'] ?? '') === 'outgoing_orders') ? 'active' : ''; ?>">Outgoing orders</a></li>
            -->
            <li class="nav-item"><a href="guhring_orders.php?action=go" class="nav-link <?php echo (($_GET['action'] ?? '') === 'go') ? 'active' : ''; ?> active">Gühring orders</a></li>
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
                            <li><a href="guhring_orders.php?sort=date_asc" class="dropdown-item">Date: Old → New</a></li>
                            <li><a href="guhring_orders.php?sort=date_desc" class="dropdown-item">Date: New → Old</a></li>
                            <li><a href="guhring_orders.php?sort=price_asc" class="dropdown-item">Price: Low → High</a></li>
                            <li><a href="guhring_orders.php?sort=price_desc" class="dropdown-item">Price: High → Low</a></li>
                            <li><a href="guhring_orders.php?sort=orderno_asc" class="dropdown-item">Order No.: Low → High</a></li>
                            <li><a href="guhring_orders.php?sort=orderno_desc" class="dropdown-item">Order No.: High → Low</a></li>
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
        <div class="container">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="ps-4 py-3 text-muted small text-uppercase">Order No.</th>
                                <th class="py-3 text-muted small text-uppercase">Date</th>
                                <th class="py-3 text-muted small text-uppercase">Customer</th>
                                <th class="py-3 text-muted small text-uppercase">Documents</th>
                                <th class="py-3 text-muted small text-uppercase">Price</th>
                                <th class="py-3 text-muted small text-uppercase">Order Status</th>
                                <th class="py-3 text-muted small text-uppercase">Approve Status</th>
                                <th class="pe-4 py-3 text-muted small text-uppercase text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql = "SELECT orders.id, orders.order_no, orders.track_id, orders.date, partners.name AS partner_name, order_attachments.file_path AS img_path, orders.price, orders.currency, orders.type, orders.approve_status, orders.order_status 
                                        FROM orders 
                                        JOIN partners ON orders.partner_id = partners.id 
                                        LEFT JOIN order_attachments ON orders.id = order_attachments.order_id 
                                        WHERE orders.type IN ('guh-in', 'guh-out') $sort";

                                $result = mysqli_query($conn, $sql);

                                if(mysqli_num_rows($result) > 0):
                                    while($row = mysqli_fetch_assoc($result)):
                                        $a_stat = $row['approve_status'];
                                        $a_badge = $approve_type[$a_stat] ?? "badge bg-secondary";

                                        $o_stat = $row['order_status'];
                                        $o_badge = $order_type[$o_stat] ?? "badge bg-secondary";

                                        $currency = $row['currency'];
                                        $symbol_currency = $order_currency[$currency] ?? "XXX";
                                        $date = date("d M Y", strtotime($row['date']));

                                        $clean_id       = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                                        $clean_order_no = htmlspecialchars($row['order_no'], ENT_QUOTES, 'UTF-8');
                                        $clean_partner  = htmlspecialchars($row['partner_name'], ENT_QUOTES, 'UTF-8');
                                        $clean_price    = htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-dark"><?= $clean_order_no ?></td>
                                <td class="text-muted small"><?= $date ?></td>
                                <td class="fw-semibold text-dark"><?= $clean_partner ?></td>
                                <td>
                                    <?php if(!empty($row['img_path'])): ?>
                                        <a href="/green_bridge_recycling_v2/<?= htmlspecialchars($row['img_path'], ENT_QUOTES, 'UTF-8') ?>" 
                                        target="_blank" class="btn btn-outline-secondary btn-sm px-3">
                                            <i class="bi bi-file-earmark me-1"></i>View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">No document</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold"><?= $clean_price ?> <?= $symbol_currency ?></td>
                                <td><span class="<?= $o_badge ?>"><?= ucfirst($row['order_status']) ?></span></td>
                                <td><span class="<?= $a_badge ?>"><?= ucfirst($row['approve_status']) ?></span></td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm rounded-2">
                                        <a href="template/guhring_order.php?id=<?= $clean_id ?>" class="btn btn-outline-primary px-3">
                                            Edit
                                        </a>
                                        <a href="/green_bridge_recycling_v2/pages/public/track_trace.php?track_id=<?= htmlspecialchars($row['track_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                        class="btn btn-outline-secondary px-3" target="_blank">
                                            Track
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                                    endwhile;
                                else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    No orders found matching current filter criteria.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../js/easteregg.js"></script>

<?php
    include "../../build/footer.php";
?>