<?php
    // MICHAEL D. PHILLIPS - UPDATED SECURITY & UI IMPROVEMENTS
    // TICKETS SYSTEM

    require "../../build/auth.php";
    require "../../build/functions.php";
    require "../../build/mailer.php";

    // --- 1. HANDLE TICKET CREATION BEFORE ANY OUTPUT (PRG PATTERN) ---
    $success_msg = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
        requireRole('staff', "Users with a role 'viewer' are not eligible to access this page."); // viewers cannot create tickets
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority    = trim($_POST['priority'] ?? 'medium');
        $created_by  = $_SESSION['user_id'];

        if (!empty($title) && !empty($description)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO tickets (title, description, priority, created_by) VALUES (?, ?, ?, ?)");
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $priority, $created_by);
                
                if (mysqli_stmt_execute($stmt)) {
                    $new_ticket_id = mysqli_insert_id($conn);
                    logActivity($conn, $created_by, 'create', 'ticket', $new_ticket_id, "Created new ticket: {$title}");

                    // --- SEND EMAIL NOTIFICATION ---
                    mailTicketCreated($conn, $new_ticket_id, $title, $description, $priority, $_SESSION['user'] ?? "User #{$created_by}");

                    // Redirect to clear post data and prevent duplicate insertions
                    header("Location: tickets.php?created=1");
                    exit();
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if (isset($_GET['created'])) {
        $success_msg = "Ticket created successfully!";
    }

    logActivity($conn, $_SESSION['user_id'], 'checking', 'tickets', $_SESSION['user_id'], "User #{$_SESSION['user_id']} checked tickets");

    $page_title = "GBR Tickets";
    include "../../build/header.php";
?>

<div class="container-fluid py-4">
    <div class="container-sm">
        <h1 class="fw-bold mb-1">Tickets</h1>
        <p class="text-muted mb-4">Submit a new request or check the operational log below.</p>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (hasRole('staff')): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
            <h5 class="fw-bold text-secondary mb-3">Create New Ticket</h5>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold text-muted small">Title</label>
                    <input type="text" name="title" id="title" class="form-control rounded-3" placeholder="Brief summary of the issue" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold text-muted small">Description</label>
                    <textarea name="description" id="description" class="form-control rounded-3" rows="4" placeholder="Provide details..." required></textarea>
                </div>
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-md-6">
                        <label Chil for="priority" class="form-label fw-semibold text-muted small">Priority Level</label>
                        <select name="priority" id="priority" class="form-select rounded-3">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button type="submit" name="create_ticket" class="btn btn-success px-4 py-2 rounded-3 fw-medium shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Create Ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="alert alert-secondary rounded-4 mb-5">
            Users with a role 'viewer' are not eligible to access this page.
        </div>
        <?php endif; ?>

        <hr class="my-4 opacity-10">

        <h4 class="fw-bold text-dark mb-3">Active Logs</h4>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light table-light border-bottom">
                        <tr>
                            <th class="ps-4 text-muted small uppercase" style="width: 100px;">Ticket ID</th>
                            <th class="text-muted small uppercase">Title</th>
                            <th class="text-muted small uppercase" style="width: 120px;">Priority</th>
                            <th class="text-muted small uppercase" style="width: 120px;">Status</th>
                            <th class="text-muted small uppercase">Created By</th>
                            <th class="text-muted small uppercase">Date & Time</th>
                            <th class="text-end pe-4 text-muted small uppercase" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Optimized querying mechanism joining target profile properties cleanly
                            $sql = "SELECT t.*, u.username 
                                    FROM tickets t 
                                    LEFT JOIN users u ON t.created_by = u.id 
                                    ORDER BY t.created_at DESC";
                            $query = mysqli_query($conn, $sql);

                            if ($query && mysqli_num_rows($query) > 0) {
                                while($ticket = mysqli_fetch_assoc($query)) {
                                    
                                    // Sanitize variables explicitly against XSS vulnerabilities
                                    $clean_id       = htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8');
                                    $clean_title    = htmlspecialchars($ticket['title'], ENT_QUOTES, 'UTF-8');
                                    $clean_priority = htmlspecialchars($ticket['priority'], ENT_QUOTES, 'UTF-8');
                                    $clean_status   = htmlspecialchars($ticket['status'] ?? 'Open', ENT_QUOTES, 'UTF-8');
                                    $clean_creator  = htmlspecialchars($ticket['username'] ?? "User #{$ticket['created_by']}", ENT_QUOTES, 'UTF-8');
                                    $formatted_date = date("d.m.Y H:i", strtotime($ticket['created_at']));

                                    // Map priority profiles directly into structural badge configurations
                                    $priority_badge = "bg-secondary";
                                    if ($clean_priority === 'low')    $priority_badge = "bg-info text-dark";
                                    if ($clean_priority === 'medium') $priority_badge = "bg-warning text-dark";
                                    if ($clean_priority === 'high')   $priority_badge = "bg-danger";

                                    // Map operational statuses to style classes
                                    $status_badge = ($clean_status === 'Closed' || $clean_status === 'resolved') ? "bg-light text-muted border" : "bg-primary text-white";
                                    ?>
                                    <tr>
                                        <td class="ps-4 font-monospace fw-bold text-secondary">#<?= $clean_id ?></td>
                                        <td class="fw-medium text-dark text-truncate" style="max-width: 240px;"><?= $clean_title ?></td>
                                        <td>
                                            <span class="badge rounded-pill text-uppercase px-2.5 py-1.5 <?= $priority_badge ?>" style="font-size: 0.75rem;">
                                                <?= $clean_priority ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-3 text-uppercase px-2 py-1.5 <?= $status_badge ?>" style="font-size: 0.75rem;">
                                                <?= $clean_status ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small"><i class="bi bi-person me-1"></i><?= $clean_creator ?></td>
                                        <td class="text-muted small"><?= $formatted_date ?></td>
                                        <td class="text-end pe-4">
                                            <a href="template/ticket.php?id=<?= $clean_id ?>" class="btn btn-outline-primary btn-sm px-3 rounded-2 fw-medium">
                                                Open
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center py-4 text-muted">No system tickets found in the log directory.</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "../../build/footer.php"; ?>