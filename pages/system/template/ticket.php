<?php

    // MICHAEL D. PHILLIPS - 16.04.2026
    // TICKET DETAIL PAGE

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    if(!isset($_SESSION))
    {
        session_start();
    }

    $id = (int)$_GET['id'];

    // UPDATE STATUS
    if(isset($_POST['update_status']))
    {
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        mysqli_query($conn, "
            UPDATE tickets
            SET status = '$status'
            WHERE id = '$id'
        ");

        header("Location: ticket.php?id=" . $id);
        exit;
    }

    // GET TICKET
    $query = mysqli_query($conn, "
        SELECT *
        FROM tickets
        WHERE id = '$id'
    ");

    $ticket = mysqli_fetch_assoc($query);

    if(!$ticket)
    {
        die("Ticket not found.");
    }

    $page_title = "GBR Ticket #" . $id;

    include "../../../build/header.php";

?>

<div class="container-fluid">
    <div class="container-sm">
        <h1>
            Ticket #<?= $ticket['id']; ?>
        </h1>
        <hr>
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- TITLE -->
                <h3><?= htmlspecialchars($ticket['title']); ?></h3>
                <br>
                <!-- DESCRIPTION -->
                <p><?= nl2br(htmlspecialchars($ticket['description'])); ?></p>
                <hr>
                <div class="row">
                    <!-- PRIORITY -->
                    <div class="col-md-3 mb-3">
                        <strong>Priority</strong>
                        <br>
                        <?php
                            if($ticket['priority'] == 'high')
                            {
                                echo '<span class="badge bg-danger">High</span>';
                            }
                            elseif($ticket['priority'] == 'medium')
                            {
                                echo '<span class="badge bg-warning text-dark">Medium</span>';
                            }
                            else
                            {
                                echo '<span class="badge bg-secondary">Low</span>';
                            }
                        ?>
                    </div>
                    <!-- STATUS -->
                    <div class="col-md-3 mb-3">
                        <strong>Status</strong>
                        <br>
                        <?php
                            if($ticket['status'] == 'open')
                            {
                                echo '<span class="badge bg-danger">Open</span>';
                            }
                            elseif($ticket['status'] == 'in_progress')
                            {
                                echo '<span class="badge bg-warning text-dark">In Progress</span>';
                            }
                            else
                            {
                                echo '<span class="badge bg-success">Closed</span>';
                            }
                        ?>
                    </div>
                    <!-- CREATED BY -->
                    <div class="col-md-3 mb-3">
                        <strong>Created By</strong>
                        <br>
                        <?= htmlspecialchars($ticket['created_by']); ?>
                    </div>
                    <!-- CREATED AT -->
                    <div class="col-md-3 mb-3">
                        <strong>Created At</strong>
                        <br>
                        <?= date("d.m.Y H:i", strtotime($ticket['created_at'])); ?>
                    </div>
                </div>
                <hr>
                <!-- UPDATE STATUS FORM -->
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">
                            Update Status
                        </label>
                        <select name="status" class="form-select">
                            <option value="open" <?= $ticket['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?= $ticket['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="closed" <?= $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    <a href="../tickets.php" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
    include "../../../build/footer.php";
?>