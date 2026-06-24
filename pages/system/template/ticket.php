<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // TICKET DETAIL PAGE — updated with prepared statements + email notification

    require "../../../build/auth.php";
    require "../../../build/functions.php";
    require "../../../build/mailer.php";

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { die("Invalid ticket ID."); }

    // --- UPDATE STATUS (PRG pattern — POST before any output) ---
    if (isset($_POST['update_status'])) {
        $allowed_statuses = ['open', 'in_progress', 'closed'];
        $new_status = trim($_POST['status'] ?? '');

        if (in_array($new_status, $allowed_statuses)) {
            // Fetch old status first for the email diff
            $old_stmt = mysqli_prepare($conn, "SELECT status, title FROM tickets WHERE id = ?");
            mysqli_stmt_bind_param($old_stmt, "i", $id);
            mysqli_stmt_execute($old_stmt);
            $old_ticket = mysqli_stmt_get_result($old_stmt)->fetch_assoc();
            $old_status = $old_ticket['status'] ?? 'open';
            $ticket_title = $old_ticket['title'] ?? "Ticket #{$id}";

            if ($new_status !== $old_status) {
                $upd_stmt = mysqli_prepare($conn, "UPDATE tickets SET status = ? WHERE id = ?");
                mysqli_stmt_bind_param($upd_stmt, "si", $new_status, $id);
                mysqli_stmt_execute($upd_stmt);

                logActivity($conn, $_SESSION['user_id'], 'update', 'ticket', $id,
                    "Ticket #{$id} status changed from '{$old_status}' to '{$new_status}'");

                // --- SEND EMAIL NOTIFICATION ---
                $updater = $_SESSION['user'] ?? "User #{$_SESSION['user_id']}";
                mailTicketUpdated($conn, $id, $ticket_title, $old_status, $new_status, $updater);
            }
        }

        header("Location: ticket.php?id=" . $id);
        exit;
    }

    // --- FETCH TICKET ---
    $stmt = mysqli_prepare($conn, "SELECT t.*, u.username FROM tickets t LEFT JOIN users u ON t.created_by = u.id WHERE t.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $ticket = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$ticket) { die("Ticket not found."); }

    $page_title = "GBR Ticket #" . $id;

    logActivity($conn, $_SESSION['user_id'], 'checking', 'ticket', $id,
        "User #{$_SESSION['user_id']} checked ticket {$id}");

    include "../../../build/header.php";
?>

<div class="container-fluid">
    <div class="container-sm">
        <h1>Ticket #<?= $ticket['id'] ?></h1>
        <hr>
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- TITLE -->
                <h3><?= htmlspecialchars($ticket['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <br>
                <!-- DESCRIPTION -->
                <p><?= nl2br(htmlspecialchars($ticket['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                <hr>
                <div class="row">
                    <!-- PRIORITY -->
                    <div class="col-md-3 mb-3">
                        <strong>Priority</strong><br>
                        <?php
                            $p = $ticket['priority'];
                            if ($p === 'high')        echo '<span class="badge bg-danger">High</span>';
                            elseif ($p === 'medium')  echo '<span class="badge bg-warning text-dark">Medium</span>';
                            else                      echo '<span class="badge bg-secondary">Low</span>';
                        ?>
                    </div>
                    <!-- STATUS -->
                    <div class="col-md-3 mb-3">
                        <strong>Status</strong><br>
                        <?php
                            $s = $ticket['status'];
                            if ($s === 'open')           echo '<span class="badge bg-danger">Open</span>';
                            elseif ($s === 'in_progress') echo '<span class="badge bg-warning text-dark">In Progress</span>';
                            else                          echo '<span class="badge bg-success">Closed</span>';
                        ?>
                    </div>
                    <!-- CREATED BY -->
                    <div class="col-md-3 mb-3">
                        <strong>Created By</strong><br>
                        <?= htmlspecialchars($ticket['username'] ?? "User #{$ticket['created_by']}", ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <!-- CREATED AT -->
                    <div class="col-md-3 mb-3">
                        <strong>Created At</strong><br>
                        <?= date("d.m.Y H:i", strtotime($ticket['created_at'])) ?>
                    </div>
                </div>
                <hr>
                <!-- UPDATE STATUS FORM -->
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-select">
                            <option value="open"        <?= $ticket['status'] === 'open'        ? 'selected' : '' ?>>Open</option>
                            <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="closed"      <?= $ticket['status'] === 'closed'      ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    <a href="../tickets.php" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../../../build/footer.php"; ?>