<?php
    // MICAHEL D. PHILLIPS - 30/04/2026
    // ACTIVITY PAGE
    require "build/auth.php";
    require "build/functions.php";

    $page_title = "GBR Home";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. Handle Filters (Basic implementation)
    // Start with the basic user filter
    $where_clauses = ["entity_id = {$_SESSION['user_id']}"];

    // Filter by Search Text
    if (!empty($_GET['search'])) {
        $search = mysqli_real_escape_string($conn, $_GET['search']);
        $where_clauses[] = "description LIKE '%$search%'";
    }

    // NEW: Filter by Action Dropdown
    if (!empty($_GET['action_filter'])) {
        $act_filter = mysqli_real_escape_string($conn, $_GET['action_filter']);
        $where_clauses[] = "`action` = '$act_filter'";
    }

    // Combine and run the main query
    $where_sql = implode(' AND ', $where_clauses);
    $sql = "SELECT * FROM activity_log WHERE $where_sql ORDER BY created_at DESC";

    $result = mysqli_query($conn, $sql);

    $img_map = [
        "track_and_trace" => "imgs/pointer.png",
        "password" => "imgs/padlock.png",
        "order" => "imgs/package.png",
        "create" => "imgs/package.png",
        "client" => "imgs/person.png",
        "login" => "imgs/person.png",
        "activity_check" => "imgs/person.png",
        "login_failed" => "imgs/person.png",
        "logout" => "imgs/person.png",
        "update" => "imgs/package.png"
    ];

    include "build/header.php";

    logActivity($conn, $_SESSION['user_id'], 'activity_check', 'user', $_SESSION['user_id'], "User #{$_SESSION['user_id']} checked all his activities");

?>

    <!-- DYNAMICALLY DISPLAY ALL ACTIVITIES USER DID -->
    <div class="container-fluid py-4">
        <div class="container-sm">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div>
                    <h2 class="fw-bold mb-0">All activity</h2>
                    <p class="text-muted">A complete log of your recent system activity.</p>
                </div>
                <button class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search activities...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select">
                                <option>All actions</option>
                                <?php 
                                    while($action_row = mysqli_fetch_assoc($action_result)) {
                                        $val = $action_row['action'];
                                        $friendly_val = ucfirst(str_replace('_', ' ', $val));
                                        
                                        // Keep the option selected after page reload
                                        $selected = (isset($_GET['action_filter']) && $_GET['action_filter'] == $val) ? 'selected' : '';
                                        
                                        echo "<option value='$val' $selected>$friendly_val</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="date" class="form-control" placeholder="From date">
                                <span class="input-group-text bg-transparent border-0">→</span>
                                <input type="date" class="form-control" placeholder="To date">
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="reset" class="btn btn-link text-decoration-none text-dark">Clear filters</button>
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
                                <th class="text-muted fw-normal small">Date & Time <i class="bi bi-chevron-expand"></i></th>
                                <th class="text-muted fw-normal small">Action <i class="bi bi-chevron-expand"></i></th>
                                <th class="text-muted fw-normal small">Details <i class="bi bi-chevron-expand"></i></th>
                                <th class="text-muted fw-normal small text-end pe-4">User <i class="bi bi-chevron-expand"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): 
                                $icon = $img_map[$row['action']] ?? 'imgs/default.png';
                                $formatted_date = date("M d, Y h:i A", strtotime($row['created_at']));
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="<?= $icon ?>" style="width: 24px; height: 24px;" alt="icon">
                                </td>
                                <td class="small"><?= $formatted_date ?></td>
                                <td><span class="fw-semibold"><?= $row['action'] ?></span></td>
                                <td class="text-muted"><?= $row['description'] ?></td>
                                <td class="text-end pe-4 text-muted">User #<?= $row['entity_id'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php
    include "build/footer.php";
?>