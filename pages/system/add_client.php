<?php
    // MICHAEL D. PHILLIPS - 20.04.2026
    // ADD ITEMS TO INVENTORY

    require "../../build/auth.php";
    require "../../build/functions.php";
    
    $page_title = "GBR Add Client";
    include "../../build/header.php";

    if($_SERVER['REQUEST_METHOD'] === "POST") {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $contact_info = $_POST['con_info'];
        $sql = "INSERT INTO partners (name, type, contact_info) VALUES ('$name', '$type', '$contact_info')";

        if(mysqli_query($conn, $sql)) {
            header("Location: add_client.php?success=1");
            //echo "Client added to the list!";
        } else {
            echo "Error: ". $sql . "<br>" . mysqli_error($sql);
        }
    }
    ?>

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
    <?php
        endif;
        include "../../build/footer.php";
    ?>