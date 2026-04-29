<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // ADD ITEMS TO INVENTORY

    require "../../build/auth.php";
    require "../../build/functions.php";
    
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
            header("Location: add_inventory.php?action=item&success=1");
            exit;   

        } elseif($action == "weight") {
            $material_id  = (int)$_POST['material_id'];
            $item_weight  = (float)$_POST['item_weight'];
            $direction    = $_POST['direction'];  // 'in' or 'out'

            // Validate direction
            if (!in_array($direction, ['in', 'out'])) {
                $direction = 'in';
            }

            // Insert the movement into inventory_movements
            $stmt = mysqli_prepare($conn, "INSERT INTO inventory_movements (material_id, quantity, direction) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ids", $material_id, $item_weight, $direction);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Fetch item code for the log message
            $mat_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT item_code FROM materials WHERE id = $material_id"));
            $item_code = $mat_row['item_code'] ?? $material_id;

            logActivity($conn, $_SESSION['user_id'], 'inventory', 'weight', $_SESSION['user_id'], "User #{$_SESSION['user_id']} added {$item_weight} kg ({$direction}) to {$item_code}");
            header("Location: add_inventory.php?action=weight&success=1");
            exit;
        }
    }

    ?>

    <!-- ADD ITEM/WEIGHT NAVIGATION -->
    <ul class="nav nav-tabs container-sm">
        <li class="nav-item"><a href="?action=item" class="nav-link <?php echo (($_GET['action'] ?? '') === 'item') ? 'active' : ''; ?>">Add item</a></li>
        <li class="nav-item"><a href="?action=weight" class="nav-link <?php echo (($_GET['action'] ?? '') === 'weight') ? 'active' : ''; ?>">Add weight</a></li>
    </ul>

    <!-- FORM TO ADD ITEM TO INVENTORY -->
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

    <!-- FORM TO ADD/REMOVE WEIGHT FROM AN ITEM -->
    <?php elseif ($action == "weight"): ?>
        <br>
        <div class="container-fluid">
            <div class="container-sm w-50 border rounded-4 p-4">
                <form action="" method="post">

                    <label for="material_id" class="form-label">Select item</label>
                    <select name="material_id" id="material_id" class="form-select" required>
                        <option value="" disabled selected>-- Choose an item --</option>
                        <?php
                            $materials = mysqli_query($conn, "SELECT id, item_code, name FROM materials ORDER BY item_code ASC");
                            while($mat = mysqli_fetch_assoc($materials)) {
                                echo "<option value=\"{$mat['id']}\">{$mat['item_code']} – {$mat['name']}</option>";
                            }
                        ?>
                    </select>

                    <label for="item_weight" class="form-label mt-3">Weight (kg)</label>
                    <input type="number" step="0.01" min="0.01" name="item_weight" id="item_weight" class="form-control" required>

                    <label for="direction" class="form-label mt-3">Direction</label>
                    <select name="direction" id="direction" class="form-select" required>
                        <option value="in">In (adding stock)</option>
                        <option value="out">Out (removing stock)</option>
                    </select>

                    <br>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

    <?php endif; ?>

    <?php if(isset($_GET['success'])): ?>
        <br>
        <div class="container-fluid">
            <div class="container-sm">
                <div class="alert alert-success">Item added successfully to inventory.</div>
            </div>
        </div>
    <?php endif; ?>

    <?php if(empty($action)): ?>
        <br>
        <div class="container-fluid">
            <div class="container-sm">
                <div class="alert alert-info">Please select either Add item or Add weight.</div>
            </div>
        </div>
    <?php endif; ?>

    <?php
        include "../../build/footer.php";
    ?>
