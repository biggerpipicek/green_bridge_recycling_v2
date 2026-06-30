<?php
    // MICHAEL D. PHILLIPS - 20.04.2026
    // ADD ITEMS TO INVENTORY
    // UPDATED: AJAX JSON support so other pages (e.g. add_guhring_order.php)
    // can create a partner inline without losing form data / leaving the page.

    require "../../build/auth.php";
    require "../../build/functions.php";
    requireRole('staff'); // viewers cannot add/edit partners

    // --- AJAX BRANCH: handle inline "add partner" requests from other pages ---
    if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'Not logged in.']);
            exit;
        }

        $name         = trim($_POST['name'] ?? '');
        $type         = trim($_POST['type'] ?? '');
        $contact_info = trim($_POST['con_info'] ?? '');

        if ($name === '' || $contact_info === '' || !in_array($type, ['customer', 'supplier'], true)) {
            echo json_encode(['success' => false, 'error' => 'Missing or invalid fields.']);
            exit;
        }

        $sql = "INSERT INTO partners (name, type, contact_info) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $name, $type, $contact_info);

        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($conn);
            logActivity($conn, $_SESSION['user_id'], 'client', 'list', $new_id, "User #{$_SESSION['user_id']} added client {$name} to list");
            echo json_encode(['success' => true, 'id' => $new_id, 'name' => $name, 'type' => $type]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
        exit;
    }

    $page_title = "GBR Add Client";
    include "../../build/header.php"; 

    if($_SERVER['REQUEST_METHOD'] === "POST") {
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $contact_info = trim($_POST['con_info']);

        $sql = "INSERT INTO partners (name, type, contact_info) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $name, $type, $contact_info);

        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($conn);
            logActivity($conn, $_SESSION['user_id'], 'client', 'list', $new_id, "User #{$_SESSION['user_id']} added client {$name} to list");
            header("Location: add_client.php?success=1");
            exit;
            //echo "Client added to the list!";
        } else {
            echo "Error: ". mysqli_error($conn);
        }
    }
    ?>

    <?php if (isset($_SESSION['user'])): ?>
        <!-- FORM WITH CLIENT DATA - NAME, TYPE, CONTACT INFO -->
        <div class="container-fluid">
            <div class="container-sm d-flex justify-content-center">
                <form action="" method="post" class="border rounded-4 w-50 p-4">
                    <label for="name" class="form-label">Client name</label>
                    <input type="text" name="name" class="form-control" required>
                    <label for="type" class="form-label">Client type</label>
                    <select name="type" class="form-select" required>
                        <option value="" disabled>Select a type</option>
                        <option value="customer">Customer</option>
                        <option value="supplier">Supplier</option>
                    </select>
                    <label for="con_info" class="form-label">Contact info</label>
                    <input type="email" name="con_info" class="form-control" required>
                    <br>
                    <input type="submit" value="Submit" class="btn btn-primary">
                </form>
            </div>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <br>
            <div class="container-fluid">
                <div class="container-sm">
                    <div class="alert alert-success">Client added successfully to list.</div>
                </div>
            </div>
        <?php endif; else: ?>
            <br>
            <div class="container-fluid">
                <div class="container-sm">
                    <div class="alert alert-danger">You need to be logged in to add Clients.</div>
                </div>
            </div>
    <?php
        endif;
        include "../../build/footer.php";
    ?>
