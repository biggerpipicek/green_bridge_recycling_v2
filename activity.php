<?php
    // MICHAEL D. PHILLIPS - 30/04/2026
    require "build/auth.php";
    require "build/functions.php";

    $page_title = "GBR Activity Log";
    include "build/header.php";


    // --- 1. PREPARE THE DROPDOWN DATA ---
    $action_query = "SELECT DISTINCT `action` FROM activity_log ORDER BY `action` ASC";
    $action_result = mysqli_query($conn, $action_query);

    // --- 2. HANDLE FILTERS ---
    $where_clauses = ["user_id = {$_SESSION['user_id']}"];

    if (!empty($_GET['search'])) {
        $search = mysqli_real_escape_string($conn, $_GET['search']);
        $where_clauses[] = "description LIKE '%$search%'";
    }

    if (!empty($_GET['action_filter'])) {
        $act_filter = mysqli_real_escape_string($conn, $_GET['action_filter']);
        $where_clauses[] = "`action` = '$act_filter'";
    }

    // NEW: Filter by Start Date
    if (!empty($_GET['start_date'])) {
        $start = mysqli_real_escape_string($conn, $_GET['start_date']);
        // We use >= and ensure it starts at the beginning of the day
        $where_clauses[] = "created_at >= '$start 00:00:00'";
    }

    // NEW: Filter by End Date
    if (!empty($_GET['end_date'])) {
        $end = mysqli_real_escape_string($conn, $_GET['end_date']);
        // We use <= and ensure it includes the full end day
        $where_clauses[] = "created_at <= '$end 23:59:59'";
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

    // Log the visit to this page
    logActivity($conn, $_SESSION['user_id'], 'activity_check', 'user', $_SESSION['user_id'], "User #{$_SESSION['user_id']} checked all his activities");
?>

<div class="container-fluid py-4">
    <div class="container-sm">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
                <h2 class="fw-bold mb-0">All activity</h2>
                <p class="text-muted">A complete log of your recent system activity.</p>
            </div>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-download"></i> Print/Export
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search activities..." value="<?= $_GET['search'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <select name="action_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">All actions</option>
                            <?php 
                                while($action_row = mysqli_fetch_assoc($action_result)) {
                                    $val = $action_row['action'];
                                    $friendly_val = ucfirst(str_replace('_', ' ', $val));
                                    $selected = (isset($_GET['action_filter']) && $_GET['action_filter'] == $val) ? 'selected' : '';
                                    echo "<option value='$val' $selected>$friendly_val</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? '' ?>">
                            <span class="input-group-text bg-transparent border-0">→</span>
                            <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="col-md-2 text-end">
                        <a href="activity.php" class="btn btn-link text-decoration-none text-dark">Clear</a>
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
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="<?= $icon ?>" style="width: 24px; height: 24px;" alt="icon">
                                </td>
                                <td class="small"><?= $formatted_date ?></td>
                                <td><span class="fw-semibold text-capitalize"><?= str_replace('_', ' ', $row['action']) ?></span></td>
                                <td class="text-muted"><?= $row['description'] ?></td>
                                <td class="text-end pe-4 text-muted">User #<?= $row['entity_id'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No activity found matching your filters.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "build/footer.php"; ?>