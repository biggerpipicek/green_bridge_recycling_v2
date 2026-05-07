<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // TICKETS SYSTEM

    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "GBR Tickets";
    include "../../build/header.php";

    if(isset($_POST['create_ticket']))
        {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $priority = mysqli_real_escape_string($conn, $_POST['priority']);

            $created_by = $_SESSION['user_id'];

            mysqli_query($conn, "
                INSERT INTO tickets
                (
                    title,
                    description,
                    priority,
                    created_by
                )
                VALUES
                (
                    '$title',
                    '$description',
                    '$priority',
                    '$created_by'
                )
            ");

        $sql = "SELECT username FROM users WHERE id = {$_SESSION['user_id']}";
    }
    ?>

    <div class="container-fluid">
        <div class="container-sm">
            <h1>Tickets</h1>
            <br>
            <form method="POST">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <button type="submit" name="create_ticket" class="btn btn-success">
                    Create Ticket
                </button>
            </form>
            <hr>
            <table class="table table-striped table-bordered">
                <!-- TABLE FOR TICKETING -->
                <thead>

                </thead>
                <tbody>
                    <?php

                    $query = mysqli_query($conn, "
                        SELECT *
                        FROM tickets
                        ORDER BY created_at DESC
                    ");

                    while($ticket = mysqli_fetch_assoc($query))
                        {
                            ?>
                            <tr>
                                <td>#<?= $ticket['id']; ?></td>

                                <td><?= htmlspecialchars($ticket['title']); ?></td>

                                <td>
                                    <?= ucfirst($ticket['priority']); ?>
                                </td>

                                <td>
                                    <?= ucfirst($ticket['status']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($ticket['created_by']); ?>
                                </td>

                                <td>
                                    <?= date("d.m.Y H:i", strtotime($ticket['created_at'])); ?>
                                </td>

                                <td>
                                    <a href="template/ticket.php?id=<?= $ticket['id']; ?>"
                                    class="btn btn-primary btn-sm">
                                    Open
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }

                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <?php
        include "../../build/footer.php";
    ?>