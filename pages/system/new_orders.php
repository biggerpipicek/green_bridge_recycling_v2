<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // NEW ORDERS - INCOMING/OUTGOING

    require "../../build/auth.php";
    require "../../build/functions.php";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }   

    $action = $_GET['action'] ?? '';
    
    $page_title = "GBR Create Order";
    include "../../build/header.php";

    if($_SERVER['REQUEST_METHOD'] === "POST") {

        $type_prefix = ($action == "incoming_orders") ? "in" : "out";
        $year = date("Y");

        $sql = "SELECT order_no 
                FROM orders 
                WHERE order_no LIKE ?
                ORDER BY id DESC 
                LIMIT 1";

        $like = "GBR-$type_prefix-$year-%";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $like);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if($row) {
            $last_order_no = $row['order_no'];
            $last_number = (int)substr($last_order_no, -5);
            $next_number = $last_number + 1;
        } else {
            $next_number = 1;
        }

        $order_no = sprintf("GBR-%s-%s-%05d", $type_prefix, $year, $next_number);

        $order_type = ($action == "incoming_orders") ? "incoming" : "outgoing";

        if($action == "incoming_orders") {
            $partner_id = $_POST['customer'];
            $date = $_POST['date'];
            $price = $_POST['price'];
            $currency = $_POST['currency'];
            $pallet_no = $_POST['pallet_no'];
            $netto_w = $_POST['netto_weight'];
            $brutto_w = $_POST['brutto_weight'];
            $approve_status = $_POST['approve_status'];
            $order_status = $_POST['order_status'];
            $track_id = generateTrackId();
            $created_by = $_SESSION['user_id'];

            $sql = "INSERT INTO orders (order_no, track_id, date, partner_id, price, currency, pallet_no, netto_w, brutto_w, type, approve_status, order_status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            $partner_id = (int)$partner_id;
            $price = (float)$price;
            $netto_w = (float)$netto_w;
            $brutto_w = (float)$brutto_w;

            mysqli_stmt_bind_param($stmt, "sssidssddsssi", $order_no, $track_id, $date, $partner_id, $price, $currency, $pallet_no, $netto_w, $brutto_w, $order_type, $approve_status, $order_status, $created_by);

            mysqli_stmt_execute($stmt);

            logActivity($conn, $_SESSION['user_id'], 'create incoming_order', 'order', $_SESSION['user_id'], "User #{$_SESSION['user_id']} created Incoming order No.{$order_no}");
        
            header("Location: add_inventory.php?action=incoming_orders&success=1");
            exit;
        } else if($action == "outgoing_orders") {
            $partner_id = $_POST['customer'];
            $date = $_POST['date'];
            $price = $_POST['price'];
            $currency = $_POST['currency'];
            $pallet_no = $_POST['pallet_no'];
            $netto_w = $_POST['netto_weight'];
            $brutto_w = $_POST['brutto_weight'];
            $approve_status = $_POST['approve_status'];
            $order_status = $_POST['order_status'];
            $track_id = generateTrackId();
            $created_by = $_SESSION['user_id'];

            $sql = "INSERT INTO orders (order_no, track_id, date, partner_id, price, currency, pallet_no, netto_w, brutto_w, type, approve_status, order_status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            $partner_id = (int)$partner_id;
            $price = (float)$price;
            $netto_w = (float)$netto_w;
            $brutto_w = (float)$brutto_w;

            mysqli_stmt_bind_param($stmt, "sssidssddsssi", $order_no, $track_id, $date, $partner_id, $price, $currency, $pallet_no, $netto_w, $brutto_w, $order_type, $approve_status, $order_status, $created_by);

            mysqli_stmt_execute($stmt);

            logActivity($conn, $_SESSION['user_id'], 'create outgoing_order', 'order', $_SESSION['user_id'], "User #{$_SESSION['user_id']} created Outgoing order No.{$order_no}");
            
            header("Location: add_inventory.php?action=outgoing_orders&success=1");
            exit;
        }
    }
?>
    

    <script src="../../js/script.js"></script>


    <?php
    
        if($action == "incoming_orders"):
    ?>
        <div class="container-fuild">
            <div class="container-sm">
                <h1>Incoming Order</h1>
                    <!-- EDITABLE → CUSTOMER, DOCUMENTS, PRICE, APPROVE STATUS, WEIGHT, PALLET_NO, MATERIAL -->
                    <form method="POST" action="" enctype="multipart/form-data" class="container mt-4">
                        <div class="row g-3">

                            <!-- Customer -->
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <select name="customer" class="form-select" required>
                                    <option value="" disabled>Select customer</option>
                                    <?php
                                        $sql = "SELECT id, name FROM partners WHERE type = 'supplier'";
                                        $result = mysqli_query($conn, $sql);

                                        if(mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) {
                                                echo "<option value='".$row['id']."'>".$row['name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>

                            <!-- Order No -->
                            <div class="col-md-3">
                                <label class="form-label">Order No</label>
                                <input type="text" name="order_no" class="form-control" disabled>
                            </div>

                            <!-- Track ID -->
                            <div class="col-md-3">
                                <label class="form-label">Track ID</label>
                                <input type="text" name="track_id" class="form-control" disabled>
                            </div>

                            <!-- Date -->
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>

                            <!-- Price -->
                            <div class="col-md-2">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>

                            <!-- Currency -->
                            <div class="col-md-2">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select" required>
                                    <option value="EUR">€ EUR</option>
                                    <option value="USD">$ USD</option>
                                    <option value="CZK">Kč CZK</option>
                                    <option value="PLN">zł PLN</option>
                                    <option value="JPY">¥ JPY</option>
                                </select>
                            </div>

                            <!-- Pallet No -->
                            <div class="col-md-6">
                                <label class="form-label">Pallet No</label>
                                <input type="text" name="pallet_no" class="form-control">
                            </div>

                            <!-- Brutto Weight -->
                            <div class="col-md-6">
                                <label class="form-label">Brutto Weight (kg)</label>
                                <input type="number" step="0.01" name="brutto_weight" class="form-control">
                            </div>

                            <!-- ================= MATERIALS SECTION ================= -->
                            <div class="col-12">
                                <label class="form-label">Materials</label>

                                <div id="materials-container">
                                    <div class="row g-2 material-row mb-2 align-items-center">

                                        <!-- Material -->
                                        <div class="col-md-5">
                                            <select name="materials[]" class="form-select" required>
                                                <option value="" disabled>Select material</option>
                                                <?php
                                                    $sql = "SELECT item_code, name from materials";
                                                    $result = mysqli_query($conn, $sql);

                                                    if(mysqli_num_rows($result) > 0) {
                                                        while($row = mysqli_fetch_assoc($result)) {
                                                            echo "<option value='".strtolower($row['name'])."'>".$row['name']."</option>";
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Weight -->
                                        <div class="col-md-3">
                                            <input type="number" step="0.01" name="weights[]" 
                                                class="form-control weight-input" 
                                                placeholder="Weight (kg)" required>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="col-md-4 d-flex gap-2">
                                            <button type="button" class="btn btn-danger w-50 remove-material">
                                                Remove
                                            </button>

                                            <button type="button" class="btn btn-success w-50 add-material">
                                                + Add
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Auto Netto -->
                            <div class="col-md-6">
                                <label class="form-label">Netto Weight (kg)</label>
                                <input type="number" step="0.01" name="netto_weight" 
                                    id="netto_weight" class="form-control" readonly>
                            </div>
                            <!-- ===================================================== -->

                            <!-- Approve Status -->
                            <div class="col-md-3">
                                <label class="form-label">Approve Status</label>
                                <select name="approve_status" class="form-select">
                                    <option value="approved">Approved</option>
                                    <option value="not approved">Not Approved</option>
                                </select>
                            </div>

                            <!-- Order Status -->
                            <div class="col-md-3">
                                <label class="form-label">Order Status</label>
                                <select name="order_status" class="form-select">
                                    <option value="created">Created</option>
                                    <option value="received">Received</option>
                                    <option value="in process">In process</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <!-- Documents -->
                            <div class="col-12">
                                <label class="form-label">Documents (Images / PDFs)</label>
                                <input type="file" name="documents[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf">
                            </div>

                            <!-- Submit -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    Submit Order
                                </button>
                            </div>

                        </div>
                    </form>
            </div>
        </div>
        
    <?php elseif ($action == "outgoing_orders"): ?>
        <div class="container-fuild">
            <div class="container-sm">
                <h1>Outgoing Order</h1>
                    <!-- EDITABLE → CUSTOMER, DOCUMENTS, PRICE, APPROVE STATUS, WEIGHT, PALLET_NO, MATERIAL -->
                    <form method="POST" action="" enctype="multipart/form-data" class="container mt-4">
                        <div class="row g-3">

                            <!-- Customer -->
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <select name="customer" class="form-select" required>
                                    <option value="" disabled>Select customer</option>
                                </select>
                            </div>

                            <!-- Order No -->
                            <div class="col-md-3">
                                <label class="form-label">Order No</label>
                                <input type="text" name="order_no" class="form-control" disabled>
                            </div>

                            <!-- Track ID -->
                            <div class="col-md-3">
                                <label class="form-label">Track ID</label>
                                <input type="text" name="track_id" class="form-control" required>
                            </div>

                            <!-- Date -->
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>

                            <!-- Price -->
                            <div class="col-md-2">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>

                            <!-- Currency -->
                            <div class="col-md-2">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select" required>
                                    <option value="EUR">€ EUR</option>
                                    <option value="USD">$ USD</option>
                                    <option value="CZK">Kč CZK</option>
                                    <option value="PLN">zł PLN</option>
                                    <option value="JPY">¥ JPY</option>
                                </select>
                            </div>

                            <!-- Pallet No -->
                            <div class="col-md-6">
                                <label class="form-label">Pallet No</label>
                                <input type="text" name="pallet_no" class="form-control">
                            </div>

                            <!-- Brutto Weight -->
                            <div class="col-md-6">
                                <label class="form-label">Brutto Weight (kg)</label>
                                <input type="number" step="0.01" name="brutto_weight" class="form-control">
                            </div>

                            <!-- ================= MATERIALS SECTION ================= -->
                            <div class="col-12">
                                <label class="form-label">Materials</label>

                                <div id="materials-container">
                                    <div class="row g-2 material-row mb-2 align-items-center">

                                        <!-- Material -->
                                        <div class="col-md-5">
                                            <select name="materials[]" class="form-select" required>
                                                <option value="" disabled>Select material</option>
                                                <?php
                                                    $sql = "SELECT item_code, name from materials";
                                                    $result = mysqli_query($conn, $sql);

                                                    if(mysqli_num_rows($result) > 0) {
                                                        while($row = mysqli_fetch_assoc($result)) {
                                                            echo "<option value='".strtolower($row['name'])."'>".$row['name']."</option>";
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Weight -->
                                        <div class="col-md-3">
                                            <input type="number" step="0.01" name="weights[]" 
                                                class="form-control weight-input" 
                                                placeholder="Weight (kg)" required>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="col-md-4 d-flex gap-2">
                                            <button type="button" class="btn btn-danger w-50 remove-material">
                                                Remove
                                            </button>

                                            <button type="button" class="btn btn-success w-50 add-material">
                                                + Add
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Auto Netto -->
                            <div class="col-md-6">
                                <label class="form-label">Netto Weight (kg)</label>
                                <input type="number" step="0.01" name="netto_weight" 
                                    id="netto_weight" class="form-control" readonly>
                            </div>
                            <!-- ===================================================== -->

                            <!-- Approve Status -->
                            <div class="col-md-3">
                                <label class="form-label">Approve Status</label>
                                <select name="approve_status" class="form-select">
                                    <option value="approved">Approved</option>
                                    <option value="not approved">Not Approved</option>
                                </select>
                            </div>

                            <!-- Order Status -->
                            <div class="col-md-3">
                                <label class="form-label">Order Status</label>
                                <select name="order_status" class="form-select">
                                    <option value="created">Created</option>
                                    <option value="received">Received</option>
                                    <option value="in process">In process</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <!-- Documents -->
                            <div class="col-12">
                                <label class="form-label">Documents (Images / PDFs)</label>
                                <input type="file" name="documents[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf">
                            </div>

                            <!-- Submit -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    Submit Order
                                </button>
                            </div>

                        </div>
                    </form>
            </div>
        </div>
    <?php
        endif;
        if(empty($action)): ?>
        <div class="container-fluid">
            <div class="container-sm">
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>    
                        <h1>Whoops!</h1><br>You didn't select the order type.
                </div>
            </div>
        </div>
        <?php
            endif;
        include "../../build/footer.php";
    ?>
</body>
</html>