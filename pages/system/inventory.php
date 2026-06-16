<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // INVENTORY SYSTEM

    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "GBR Inventory";
    include "../../build/header.php";

    $sort_by = $_GET['sort'] ?? '';
    $sort = "";
    switch($sort_by) {
        case "az":
            $sort = "ORDER BY m.name ASC";
            break;
        case "za":
            $sort = "ORDER BY m.name DESC";
            break;
        case "weight_asc":
            $sort = "ORDER BY stock_weight ASC";
            break;
        case "weight_desc":
            $sort = "ORDER BY stock_weight DESC";
            break;
        case "code_asc":
            $sort = "ORDER BY m.item_code ASC";
            break;
        case "code_desc":
            $sort = "ORDER BY m.item_code DESC";
            break;
    }

    $search_term = $_GET['search'] ?? '';
    $where_clause = "";

    if (!empty($search_term)) {
        // Sanitize the input to prevent SQL Injection
        $safe_search = mysqli_real_escape_string($conn, $search_term);
        
        // We search across name OR item code
        $where_clause = "WHERE (m.name LIKE '%$safe_search%' OR m.item_code LIKE '%$safe_search%')";
    }

    logActivity($conn, $_SESSION['user_id'], 'checking', 'inventory', $_SESSION['user_id'], "User #{$_SESSION['user_id']} checked inventory");
?>

    <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-50 mx-auto rounded-3">
        <div class="container-fluid justify-content-between">

            <form action="" method="get" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search..">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <ul class="navbar-nav">

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Sort</a>
                    <ul class="dropdown-menu">
                        <li><a href="?sort=az" class="dropdown-item">A → Z</a></li>
                        <li><a href="?sort=za" class="dropdown-item">Z → A</a></li>
                        <li><a href="?sort=weight_asc" class="dropdown-item">Weight: Low → High</a></li>
                        <li><a href="?sort=weight_desc" class="dropdown-item">Weight: High → Low</a></li>
                        <li><a href="?sort=code_asc" class="dropdown-item">Item Code: Low → High</a></li>
                        <li><a href="?sort=code_desc" class="dropdown-item">Item Code: High → Low</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="" class="nav-link">Refresh</a>
                </li>
                <li class="nav-item">
                    <a href="add_inventory.php" class="nav-link">Add</a>
                </li>
                <li class="nav-item">
                    <a href="export_inventory.php" class="nav-link">Export</a>
                </li>

            </ul>
        </div>
    </nav>
    <br>
        <div class="container">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="ps-4 py-3 text-muted small text-uppercase">Item Code</th>
                                <th class="py-3 text-muted small text-uppercase">Item Name</th>
                                <th class="pe-4 py-3 text-muted small text-uppercase text-end">In Stock Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql = "SELECT m.item_code, m.name,
                                            COALESCE(SUM(CASE WHEN im.direction = 'in'  THEN im.quantity ELSE 0 END), 0)
                                        - COALESCE(SUM(CASE WHEN im.direction = 'out' THEN im.quantity ELSE 0 END), 0)
                                            AS stock_weight
                                        FROM materials m
                                        LEFT JOIN inventory_movements im ON im.material_id = m.id
                                        $where_clause
                                        GROUP BY m.id, m.item_code, m.name
                                        $sort";

                                $result = mysqli_query($conn, $sql);

                                if(mysqli_num_rows($result) > 0):
                                    while($row = mysqli_fetch_assoc($result)):
                                        $weight = number_format($row['stock_weight'], 2) . " kg";
                                        $has_stock = $row['stock_weight'] > 0;
                                        $clean_code = htmlspecialchars($row['item_code'], ENT_QUOTES, 'UTF-8');
                                        $clean_name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-dark"><?= $clean_code ?></td>
                                <td class="<?= $has_stock ? 'fw-semibold text-dark' : 'text-muted' ?>"><?= $clean_name ?></td>
                                <td class="pe-4 text-end">
                                    <span class="badge <?= $has_stock ? 'bg-success' : 'bg-secondary' ?> px-3 py-2">
                                        <?= $weight ?>
                                    </span>
                                </td>
                            </tr>
                            <?php
                                    endwhile;
                                else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    No inventory items match current filter criteria.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<?php include "../../build/footer.php"; ?>