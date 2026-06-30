<?php
    // MICHAEL D. PHILLIPS - UPDATED SECURITY & FUNCTIONALITY
    // CLIENT LIST - VIEW, SEARCH, SORT, REMOVE

    require "../../build/auth.php";
    require "../../build/functions.php";

    // --- 1. HANDLE ACTION TERMINATIONS (SAFE INLINE DELETION) ---
    if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
        requireRole('admin'); // only admins can delete partners

        $delete_id = (int)$_GET['delete_id'];
        $delete_stmt = mysqli_prepare($conn, "DELETE FROM partners WHERE id = ?");
        if ($delete_stmt) {
            mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);
            if (mysqli_stmt_execute($delete_stmt)) {
                logActivity($conn, $_SESSION['user_id'], 'delete', 'client', $delete_id, "Deleted client record index #{$delete_id}");
            }
            mysqli_stmt_close($delete_stmt);
        }
        // Redirect cleanly to avoid continuous deletion flags on reload
        header("Location: clients.php");
        exit();
    }

    $extra_css = ["../../styles/clients-mobile.css"];

    $page_title = "GBR Clients";
    include "../../build/header.php";

    $client_type = [
        "supplier" => "badge bg-info text-dark",
        "customer" => "badge bg-warning text-dark"
    ];

    // --- 2. COMPOSE SEARCH & SORT CONDITIONALS DYNAMICALLY ---
    $where_clauses = ["1=1"]; 
    $bind_types = "";
    $bind_params = [];

    // Apply Search Logic Safely
    $search_val = trim($_GET['search'] ?? '');
    if ($search_val !== '') {
        $where_clauses[] = "(name LIKE ? OR contact_info LIKE ?)";
        $bind_types .= "ss";
        $like_val = "%" . $search_val . "%";
        $bind_params[] = $like_val;
        $bind_params[] = $like_val;
    }

    // Explicit Sort Mapping Whitelist (Mitigates raw input manipulation)
    $sort_input = $_GET['sort'] ?? 'name_asc';
    $sort_map = [
        'name_asc'  => 'name ASC',
        'name_desc' => 'name DESC',
        'type_asc'  => 'type ASC',
        'type_desc' => 'type DESC'
    ];
    $order_sql = $sort_map[$sort_input] ?? 'name_ASC';

    $where_sql = implode(' AND ', $where_clauses);

    // --- 3. COUNT DATA ENTRIES FOR ACCURATE PAGINATION ---
    $count_sql = "SELECT COUNT(*) as total FROM partners WHERE $where_sql";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    if (!empty($bind_params)) {
        mysqli_stmt_bind_param($count_stmt, $bind_types, ...$bind_params);
    }
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_clients = mysqli_fetch_assoc($count_result)['total'] ?? 0;
    mysqli_stmt_close($count_stmt);

    // Setup Window Limits
    $client_limit = 10; // Bumped up slightly for improved desktop balance
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $client_limit;
    $total_pages = ceil($total_clients / $client_limit);

    // --- 4. EXECUTE FINAL RECORD QUERY ---
    $sql = "SELECT id, name, type, contact_info FROM partners WHERE $where_sql ORDER BY $order_sql LIMIT ?, ?";
    $final_bind_types = $bind_types . "ii";
    $final_bind_params = array_merge($bind_params, [$offset, $client_limit]);

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $final_bind_types, ...$final_bind_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    logActivity($conn, $_SESSION['user_id'], 'checking', 'partners', $_SESSION['user_id'], "User #{$_SESSION['user_id']} checked partners");
?>

<div class="container-fluid py-4">
    <div class="container-sm xl-container">
        
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-50 mx-auto rounded-3">
            <div class="container-fluid justify-content-between">

                <form action="" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search.." value="<?= htmlspecialchars($search_val, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if(!empty($_GET['sort'])): ?>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <ul class="navbar-nav">

                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Sort Catalog</a>
                        <ul class="dropdown-menu">
                            <?php 
                                $base_query = $_GET; 
                                unset($base_query['page']); 
                                $sorts = [
                                    'name_asc'  => 'Name: A → Z',
                                    'name_desc' => 'Name: Z → A',
                                    'type_asc'  => 'Type: Customer First',
                                    'type_desc' => 'Type: Supplier First'
                                ];
                                foreach($sorts as $key => $label) {
                                    $base_query['sort'] = $key;
                                    $active_item = ($sort_input === $key) ? 'active fw-bold' : '';
                                    echo "<li><a href='?" . http_build_query($base_query) . "' class='dropdown-item {$active_item}'>{$label}</a></li>";
                                }
                            ?>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="clients.php" class="nav-link">Reset</a>
                    </li>

                    <li class="nav-item">
                        <a href="add_client.php" class="nav-link">Add Partner</a>
                    </li>

                </ul>
            </div>
        </nav>

        <br>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-muted small text-uppercase">Client Identity Profile</th>
                            <th class="py-3 text-muted small text-uppercase" style="width: 150px;">Classification</th>
                            <th class="py-3 text-muted small text-uppercase">Contact Info Mapping</th>
                            <th class="pe-4 py-3 text-muted small text-uppercase text-end" style="width: 180px;">Action Directory</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): 
                                $type = $row['type'];
                                $badge_class = $client_type[$type] ?? "badge bg-secondary text-white";
                                
                                // Neutralizing Output Streams Explicitly (Mitigates Stored XSS Attacks)
                                $clean_id      = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                                $clean_name    = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                                $clean_contact = htmlspecialchars($row['contact_info'], ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-dark"><?= $clean_name ?></td>
                                <td>
                                    <span class="<?= $badge_class ?> text-uppercase px-2.5 py-1.5 fw-bold" style="font-size: 0.72rem;">
                                        <?= ucfirst($type) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><i class="bi bi-envelope me-1.5"></i><?= $clean_contact ?></td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm rounded-2">
                                        <a href="template/client.php?id=<?= $clean_id ?>" class="btn btn-outline-primary px-3">
                                            Edit
                                        </a>
                                        <?php if (hasRole('admin')): ?>
                                        <a href="clients.php?delete_id=<?= $clean_id ?>" class="btn btn-outline-danger px-3" onclick="return confirm('Warning: Are you absolutely certain you want to permanently delete client profile \'<?= addslashes($clean_name) ?>\'? This action cannot be undone.');">
                                            Delete
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    No active system partner accounts match current filter criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination pagination-sm justify-content-center gap-1">
                    <?php 
                        $url_params = $_GET;
                        
                        // Handle Previous Button Route String
                        $url_params['page'] = max(1, $page - 1);
                        echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '"><a class="page-link rounded-3 px-3" href="?' . http_build_query($url_params) . '">Previous</a></li>';

                        // Individual Page Iterations maintaining Search and Filter persistence
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $url_params['page'] = $i;
                            $active_item = ($page === $i) ? 'active' : '';
                            echo '<li class="page-item ' . $active_item . '"><a class="page-link rounded-3 px-2.5" href="?' . http_build_query($url_params) . '">' . $i . '</a></li>';
                        }

                        // Next Route Processing String
                        $url_params['page'] = min($total_pages, $page + 1);
                        echo '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '"><a class="page-link rounded-3 px-3" href="?' . http_build_query($url_params) . '">Next</a></li>';
                    ?>
                </ul>
            </nav>
        <?php endif; ?>

    </div>
</div>

<?php 
    mysqli_stmt_close($stmt);
    include "../../build/footer.php";
?>