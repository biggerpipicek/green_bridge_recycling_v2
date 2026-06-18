<?php
    // MICHAEL D. PHILLIPS - UPDATED 05/25/2026
    // GBR ACTIVITY LOG (SECURE, PARAMETERIZED & PAGINATED)

    require "build/auth.php";
    require "build/functions.php";

    $page_title = "GBR Activity Log";
    include "build/header.php";

    // --- 1. PREPARE THE DROPDOWN DATA ---
    $action_query = "SELECT DISTINCT `action` FROM activity_log ORDER BY `action` ASC";
    $action_result = mysqli_query($conn, $action_query);

    // --- 2. PAGINATION & FILTER PREPARATION ---
    $where_clauses = ["user_id = ?"];
    $bind_types = "i";
    $bind_params = [$_SESSION['user_id']];

    // Handle Input Query Parameters safely
    if (!empty($_GET['search'])) {
        $where_clauses[] = "description LIKE ?";
        $bind_types .= "s";
        $bind_params[] = "%" . $_GET['search'] . "%";
    }

    if (!empty($_GET['action_filter'])) {
        $where_clauses[] = "`action` = ?";
        $bind_types .= "s";
        $bind_params[] = $_GET['action_filter'];
    }

    if (!empty($_GET['start_date'])) {
        $where_clauses[] = "created_at >= ?";
        $bind_types .= "s";
        $bind_params[] = $_GET['start_date'] . " 00:00:00";
    }

    if (!empty($_GET['end_date'])) {
        $where_clauses[] = "created_at <= ?";
        $bind_types .= "s";
        $bind_params[] = $_GET['end_date'] . " 23:59:59";
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Calculate Total Records for Pagination
    $count_sql = "SELECT COUNT(*) as total FROM activity_log WHERE $where_sql";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    if (!empty($bind_params)) {
        mysqli_stmt_bind_param($count_stmt, $bind_types, ...$bind_params);
    }
    mysqli_stmt_execute($count_stmt);
    $count_res = mysqli_stmt_get_result($count_stmt);
    $total_records = mysqli_fetch_assoc($count_res)['total'] ?? 0;
    mysqli_stmt_close($count_stmt);

    // Pagination Variables
    $limit = 25; // Rows displayed per page
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;
    $total_pages = ceil($total_records / $limit);

    // --- 3. FETCH THE CONSTRAINED RECORDSET ---
    $sql = "SELECT * FROM activity_log WHERE $where_sql ORDER BY created_at DESC LIMIT ?, ?";
    $final_bind_types = $bind_types . "ii";
    $final_bind_params = array_merge($bind_params, [$offset, $limit]);

    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($final_bind_params)) {
        mysqli_stmt_bind_param($stmt, $final_bind_types, ...$final_bind_params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $img_map = [
        "track_and_trace" => "imgs/pointer.png",
        "password"        => "imgs/padlock.png",
        "order"           => "imgs/package.png",
        "create"          => "imgs/package.png",
        "client"          => "imgs/person.png",
        "login"           => "imgs/person.png",
        "activity_check"  => "imgs/person.png",
        "login_failed"    => "imgs/person.png",
        "logout"          => "imgs/person.png",
        "update"          => "imgs/package.png",
        "checking"        => "imgs/search.png"
    ];

    // Log page access smoothly
    logActivity($conn, $_SESSION['user_id'], 'activity_check', 'user', $_SESSION['user_id'], "User #{$_SESSION['user_id']} checked all his activities");
?>

<div class="container-fluid py-4">
    <div class="container-sm">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="fw-bold mb-0">All activity</h2>
                <p class="text-muted mb-0">A complete log of your recent system activity.</p>
            </div>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3 d-print-none">
                <i class="bi bi-printer"></i> Print/Export
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4 d-print-none">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search activities..." value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <select name="action_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">All actions</option>
                            <?php 
                                while($action_row = mysqli_fetch_assoc($action_result)) {
                                    $val = $action_row['action'];
                                    $friendly_val = ucfirst(str_replace('_', ' ', $val));
                                    $selected = (isset($_GET['action_filter']) && $_GET['action_filter'] === $val) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($friendly_val, ENT_QUOTES, 'UTF-8') . "</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($_GET['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <span class="input-group-text bg-transparent border-0">→</span>
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($_GET['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="col-md-2 text-end">
                        <a href="activity.php" class="btn btn-link text-decoration-none text-dark me-2">Clear</a>
                        <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;"></th>
                            <th class="text-muted fw-normal small">Date & Time</th>
                            <th class="text-muted fw-normal small">Action</th>
                            <th class="text-muted fw-normal small">Details</th>
                            <th class="text-muted fw-normal small text-end pe-4">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): 
                                $icon = $img_map[$row['action']] ?? 'imgs/default.png';
                                $formatted_date = date("M d, Y h:i A", strtotime($row['created_at']));
                                
                                // Secure output elements explicitly to prevent Stored Cross-Site Scripting injections
                                $clean_action = htmlspecialchars(str_replace('_', ' ', $row['action']), ENT_QUOTES, 'UTF-8');
                                $clean_desc   = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                                $clean_entity = htmlspecialchars($row['entity_id'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>" style="width: 24px; height: 24px;" alt="icon">
                                </td>
                                <td class="small text-nowrap"><?= $formatted_date ?></td>
                                <td><span class="fw-semibold text-capitalize"><?= $clean_action ?></span></td>
                                <td class="text-muted"><?= $clean_desc ?></td>
                                <td class="text-end pe-4 text-muted text-nowrap">User #<?= $clean_entity ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No activity found matching your filters.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="mt-4 d-print-none">
                <ul class="pagination pagination-sm justify-content-center">
                    <?php 
                        // Preserve existing filter parameters while navigating pages
                        $url_params = $_GET;
                        
                        // Previous Page Item Link
                        $url_params['page'] = max(1, $page - 1);
                        $prev_url = "?" . http_build_query($url_params);
                        echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '"><a class="page-link" href="' . $prev_url . '">Previous</a></li>';

                        // Individual Numbered Page Links
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $url_params['page'] = $i;
                            $target_url = "?" . http_build_query($url_params);
                            $active_class = ($page === $i) ? 'active' : '';
                            echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="' . $target_url . '">' . $i . '</a></li>';
                        }

                        // Next Page Item Link
                        $url_params['page'] = min($total_pages, $page + 1);
                        $next_url = "?" . http_build_query($url_params);
                        echo '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '"><a class="page-link" href="' . $next_url . '">Next</a></li>';
                    ?>
                </ul>
            </nav>
        <?php endif; ?>

    </div>
</div>

<?php 
    mysqli_stmt_close($stmt);
    include "build/footer.php"; 
?>