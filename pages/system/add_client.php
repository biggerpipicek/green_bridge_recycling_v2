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

        $name           = trim($_POST['name'] ?? '');
        $type           = trim($_POST['type'] ?? '');
        $contact_info   = trim($_POST['con_info'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');

        if ($name === '' || $contact_info === '' || !in_array($type, ['customer', 'supplier'], true)) {
            echo json_encode(['success' => false, 'error' => 'Missing or invalid fields.']);
            exit;
        }

        // Quick-add only captures the essentials; address/VAT/IČO are filled in later via client.php
        $sql = "INSERT INTO partners (name, type, contact_info, contact_person, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $name, $type, $contact_info, $contact_person, $phone);

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
        $name           = trim($_POST['name']);
        $type           = trim($_POST['type']);
        $contact_info   = trim($_POST['con_info']);
        $contact_person = trim($_POST['contact_person'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $address_street = trim($_POST['address_street'] ?? '');
        $address_city   = trim($_POST['address_city'] ?? '');
        $address_zip    = trim($_POST['address_zip'] ?? '');
        $address_country = trim($_POST['address_country'] ?? '');
        $vat_id         = trim($_POST['vat_id'] ?? '');
        $ico            = trim($_POST['ico'] ?? '');

        $sql = "INSERT INTO partners
                    (name, type, contact_info, contact_person, phone,
                     address_street, address_city, address_zip, address_country, vat_id, ico)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssssss",
            $name, $type, $contact_info, $contact_person, $phone,
            $address_street, $address_city, $address_zip, $address_country, $vat_id, $ico
        );

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
                        <option value="" disabled selected>Select a type</option>
                        <option value="customer">Customer</option>
                        <option value="supplier">Supplier</option>
                    </select>

                    <label for="contact_person" class="form-label">Contact person</label>
                    <input type="text" name="contact_person" class="form-control">

                    <label for="con_info" class="form-label">Contact email</label>
                    <input type="email" name="con_info" class="form-control" required>

                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control">

                    <label for="address_street" class="form-label">Street address</label>
                    <input type="text" name="address_street" class="form-control">

                    <div class="row g-2">
                        <div class="col-6">
                            <label for="address_city" class="form-label">City</label>
                            <input type="text" name="address_city" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="address_zip" class="form-label">ZIP / Postal code</label>
                            <input type="text" name="address_zip" class="form-control">
                        </div>
                    </div>

                    <label for="address_country" class="form-label">Country</label>
                    <input type="text" name="address_country" class="form-control">

                    <div class="row g-2">
                        <div class="col-6">
                            <label for="ico" class="form-label">IČO</label>
                            <input type="text" name="ico" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="vat_id" class="form-label">VAT / DIČ</label>
                            <input type="text" name="vat_id" class="form-control">
                        </div>
                    </div>

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
