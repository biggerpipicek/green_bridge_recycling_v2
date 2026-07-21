<?php
    // MICHAEL D. PHILLIPS - 28/04/2026
    // GÜHRING ORDERS LIST

    require "../../build/auth.php";
    require "../../build/functions.php";

    // --- HANDLE DELETE (admin only) ---
    if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
        requireRole('admin');

        $delete_id = (int)$_GET['delete_id'];

        // Clean up dependent rows first to avoid orphaned data
        mysqli_query($conn, "DELETE FROM order_materials WHERE order_id = $delete_id");
        mysqli_query($conn, "DELETE FROM inventory_movements WHERE order_id = $delete_id");
        mysqli_query($conn, "DELETE FROM order_attachments WHERE order_id = $delete_id");
        mysqli_query($conn, "DELETE FROM order_status_history WHERE order_id = $delete_id");

        $del_stmt = mysqli_prepare($conn, "DELETE FROM orders WHERE id = ?");
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "i", $delete_id);
            if (mysqli_stmt_execute($del_stmt)) {
                logActivity($conn, $_SESSION['user_id'], 'delete', 'order', $delete_id, "Deleted order #{$delete_id}");
            }
            mysqli_stmt_close($del_stmt);
        }

        header("Location: guhring_orders.php");
        exit();
    }

    $page_title = "Gühring GBR Orders";

    $extra_css = ["../../styles/orders.css", "../../styles/guhring_orders-mobile.css"];
    
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
        case "orderno_asc":  $sort = "ORDER BY o.order_no ASC";   break;
        case "orderno_desc": $sort = "ORDER BY o.order_no DESC";  break;
        case "date_asc":     $sort = "ORDER BY o.date ASC";       break;
        case "date_desc":    $sort = "ORDER BY o.date DESC";      break;
        case "price_asc":    $sort = "ORDER BY o.price ASC";      break;
        case "price_desc":   $sort = "ORDER BY o.price DESC";     break;
        default:             $sort = "ORDER BY o.created_at DESC";      break;
    }

    // --- SEARCH ---
    $search_val = trim($_GET['search'] ?? '');
    $where_extra = "";
    $bind_types  = "";
    $bind_params = [];

    if ($search_val !== '') {
        $where_extra = "AND (o.order_no LIKE ? OR p.name LIKE ?)";
        $bind_types  = "ss";
        $like = "%" . $search_val . "%";
        $bind_params[] = $like;
        $bind_params[] = $like;
    }

    // --- PAGINATION ---
    $limit      = 15;
    $page       = (isset($_GET['page']) && is_numeric($_GET['page'])) ? max(1, (int)$_GET['page']) : 1;
    $offset     = ($page - 1) * $limit;

    // Count total (no LIMIT)
    $count_sql  = "SELECT COUNT(DISTINCT o.id) AS total
                   FROM orders o
                   JOIN partners p ON o.partner_id = p.id
                   WHERE o.type IN ('guh-in', 'guh-out') $where_extra";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    if (!empty($bind_params)) {
        mysqli_stmt_bind_param($count_stmt, $bind_types, ...$bind_params);
    }
    mysqli_stmt_execute($count_stmt);
    $total_orders = mysqli_stmt_get_result($count_stmt)->fetch_assoc()['total'] ?? 0;
    $total_pages  = ceil($total_orders / $limit);
    mysqli_stmt_close($count_stmt);

    // Main query — GROUP BY fixes the duplicate-row problem
    $sql = "SELECT o.id, o.order_no, o.track_id, o.date,
                   p.name AS partner_name,
                   MIN(oa.file_path) AS img_path,
                   o.price, o.currency, o.type,
                   o.approve_status, o.order_status
            FROM orders o
            JOIN partners p ON o.partner_id = p.id
            LEFT JOIN order_attachments oa ON o.id = oa.order_id
            WHERE o.type IN ('guh-in', 'guh-out') $where_extra
            GROUP BY o.id
            $sort
            LIMIT ? OFFSET ?";

    $final_types  = $bind_types . "ii";
    $final_params = array_merge($bind_params, [$limit, $offset]);

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $final_types, ...$final_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    logActivity($conn, $_SESSION['user_id'], 'checking', 'orders', $_SESSION['user_id'], "User #{$_SESSION['user_id']} checked guhring orders");
?>
    <div class="container-fluid">
        <!-- INCOMING/OUTGOING ORDERS NAVIGATION -->
        <ul class="nav nav-tabs container-sm">
            <li class="nav-item"><a href="/pages/system/guhring_orders.php?action=go" class="nav-link active">Gühring orders</a></li>
        </ul>

        <br>

        <!-- NAVIGATION -->
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-50 mx-auto rounded-3">
            <div class="container-fluid justify-content-between">

                <!-- SEARCH -->
                <form action="" method="get" class="d-flex">
                    <input type="hidden" name="action" value="">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search.." value="<?= htmlspecialchars($search_val, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <!-- RIGHT SIDE -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Sort</a>
                        <ul class="dropdown-menu">
                            <li><a href="/pages/system/guhring_orders.php?sort=date_asc" class="dropdown-item">Date: Old → New</a></li>
                            <li><a href="/pages/system/guhring_orders.php?sort=date_desc" class="dropdown-item">Date: New → Old</a></li>
                            <li><a href="/pages/system/guhring_orders.php?sort=price_asc" class="dropdown-item">Price: Low → High</a></li>
                            <li><a href="/pages/system/guhring_orders.php?sort=price_desc" class="dropdown-item">Price: High → Low</a></li>
                            <li><a href="/pages/system/guhring_orders.php?sort=orderno_asc" class="dropdown-item">Order No.: Low → High</a></li>
                            <li><a href="/pages/system/guhring_orders.php?sort=orderno_desc" class="dropdown-item">Order No.: High → Low</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="/pages/system/guhring_orders.php" class="nav-link">Refresh</a></li>
                    <?php if (hasRole('staff')): ?>
                    <li class="nav-item"><a href="/pages/system/add_guhring_order.php" class="nav-link">Add</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a href="/pages/system/export_guhring_orders.php" class="nav-link">Export PDF</a></li>
                    <li class="nav-item"><a href="/pages/system/export_guhring_orders_csv.php" class="nav-link">Export CSV</a></li>
                </ul>
            </div>
        </nav>
        <br>

        <!-- TABLE -->
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
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    $a_badge = $approve_type[$row['approve_status']] ?? "badge bg-secondary";
                                    $o_badge = $order_type[$row['order_status']]     ?? "badge bg-secondary";
                                    $symbol  = $order_currency[$row['currency']]     ?? $row['currency'];
                                    $date    = date("d M Y", strtotime($row['date']));

                                    $clean_id       = htmlspecialchars($row['id'],           ENT_QUOTES, 'UTF-8');
                                    $clean_order_no = htmlspecialchars($row['order_no'],     ENT_QUOTES, 'UTF-8');
                                    $clean_partner  = htmlspecialchars($row['partner_name'], ENT_QUOTES, 'UTF-8');
                                    $clean_price    = htmlspecialchars($row['price'],        ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark"><?= $clean_order_no ?></td>
                                    <td class="text-muted small"><?= $date ?></td>
                                    <td class="fw-semibold text-dark"><?= $clean_partner ?></td>
                                    <td>
                                        <?php if (!empty($row['img_path'])): ?>
                                            <a href="/<?= htmlspecialchars($row['img_path'], ENT_QUOTES, 'UTF-8') ?>"
                                               target="_blank" class="btn btn-outline-secondary btn-sm px-3">
                                                <i class="bi bi-file-earmark me-1"></i>View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No document</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?= $clean_price ?> <?= $symbol ?></td>
                                    <td><span class="<?= $o_badge ?>"><?= ucfirst($row['order_status']) ?></span></td>
                                    <td><span class="<?= $a_badge ?>"><?= ucfirst($row['approve_status']) ?></span></td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group btn-group-sm rounded-2">
                                            <a href="/pages/system/template/guhring_order.php?id=<?= $clean_id ?>" class="btn btn-outline-primary px-3"><?= hasRole('staff') ? 'Edit' : 'View' ?></a>
                                            <a href="/pages/public/track_trace.php?track_id=<?= htmlspecialchars($row['track_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                               class="btn btn-outline-secondary px-3" target="_blank">Track</a>
                                            <?php if (hasRole('admin')): ?>
                                            <a href="guhring_orders.php?delete_id=<?= $clean_id ?>" class="btn btn-outline-danger px-3"
                                               onclick="return confirm('Warning: Are you sure you want to permanently delete order \'<?= addslashes($clean_order_no) ?>\'? This action cannot be undone.');">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
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

            <!-- PAGINATION -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination pagination-sm justify-content-center gap-1">
                        <?php
                            $url_params = $_GET;
                            unset($url_params['page']);

                            $url_params['page'] = max(1, $page - 1);
                            echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '"><a class="page-link rounded-3 px-3" href="?' . http_build_query($url_params) . '">Previous</a></li>';

                            for ($i = 1; $i <= $total_pages; $i++) {
                                $url_params['page'] = $i;
                                $active = ($page === $i) ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link rounded-3 px-3" href="?' . http_build_query($url_params) . '">' . $i . '</a></li>';
                            }

                            $url_params['page'] = min($total_pages, $page + 1);
                            echo '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '"><a class="page-link rounded-3 px-3" href="?' . http_build_query($url_params) . '">Next</a></li>';
                        ?>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>

    <script src="../../js/easteregg.js"></script>

<?php
    mysqli_stmt_close($stmt);
    include "../../build/footer.php";
?>