<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // ADD ITEMS TO INVENTORY

    require "../../build/auth.php";
    require "../../build/functions.php";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    } 
    
    $page_title = "GBR Add Inventory";
    include "../../build/header.php"; 

    $action = $_GET['action'] ?? '';
    
    if($_SERVER['REQUEST_METHOD'] === "POST") {

        if ($action == "item") {
            $item_code = $_POST['item_code'];
            $item_name = $_POST['item_name'];

            $sql = "INSERT INTO materials (item_code, name) VALUES ('$item_code', '$item_name')";

            if(mysqli_query($conn, $sql)) {
                //echo "Item added to inventory";
            } else {
                //echo "Error: ".$sql . "<br>". mysqli_error($conn);
            }


            logActivity($conn, $_SESSION['user_id'], 'inventory', 'item', $_SESSION['user_id'], "User #{$_SESSION['user_id']} added {$item_code}/{$item_name} to inventory");
            header("Location: add_inventory.php?success=1");
            exit;   
        } elseif($action == "weight") {
            $item_code = $_POST['item_code'];
            $item_weight = $_POST['item_weight'];

            logActivity($conn, $_SESSION['user_id'], 'inventory', 'item', $_SESSION['user_id'], "User #{$_SESSION['user_id']} added {$item_weight} kg to {$item_code}");
            header("Location: add_inventory.php?success=1");
            exit;
        }
    }

    ?>

    <!-- ADD ITEM/WEIGHT NAVIGATION -->
    <ul class="nav nav-tabs container-sm">
        <li class="nav-item"><a href="?action=item" class="nav-link <?php echo (($_GET['action'] ?? '') === 'item') ? 'active' : ''; ?>">Add item</a></li>
        <li class="nav-item"><a href="?action=weight" class="nav-link <?php echo (($_GET['action'] ?? '') === 'weight') ? 'active' : ''; ?>">Add weight</a></li>
    </ul>

    <!-- FORM TO ADD ITEM TO INVENTORY - ITEM NAME, ITEM CODE, ADD WEIGHT -->
    <?php if($action == "item"): ?>
        <br>
        <div class="container-fluid">
            <div class="container-sm w-50 border rounded-4 p-4">
                <form action="" method="post">
                    <label for="item_name" class="form-label">Item name</label>
                    <input type="text" name="item_name" class="form-control" required>
                    <label for="item_code" class="form-label">Item code</label>
                    <input type="number" name="item_code" class="form-control" length="4" required>
                    <br>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>


    <!-- FORM TO ADD WEIGHT TO ITEM - ITEM CODE, ADD WEIGHT -->
    <?php elseif ($action == "weight"): ?>
    <div class="container-fluid">
        <div class="container-sm w-50 border rounded-4">
            <form action="" method="post">
                yoo
            </form>
        </div>
    </div>


    <?php if(isset($_GET['success'])): ?>
            <br>
            <div class="container-fluid">
                <div class="container-sm">
                    <div class="alert alert-success">Item added successfully to inventory.</div>
                </div>
            </div>
    <?php endif;  if(empty($action)): ?>
            <br>
            <div class="container-fluid">
                <div class="container-sm">
                    <div class="alert alert-info">Please select either Add item or Add weight.</div>
                </div>
            </div>
    <?php
        endif;
        endif;
        include "../../build/footer.php";
    ?>