<?php
    // MICHAEL D. PHILLIPS - 17.04.2026
    // ORDER TEMPLATE PAGE (FIXED FOR CREATE/UPDATE + ORDER TYPE + DOCUMENTS)
    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "Guhring Add Order";

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    include "../../build/header.php";

    // --- 1. INITIALIZE DEFAULT DATA (For New Orders) ---
    $order_data = [
        'partner_id'     => '',
        'type'           => 'guh-in', // Default to Incoming
        'date'           => date('Y-m-d'),
        'price'          => 0.00,
        'currency'       => 'EUR',
        'pallet_no'      => '',
        'brutto_w'       => 0,
        'netto_w'        => 0,
        'approve_status' => 'not approved',
        'order_status'   => 'created',
        'order_no'       => 'NEW',
        'track_id'       => 'NEW'
    ];
    $order_materials = [];
    $attachments = [];

    // --- 2. FETCH EXISTING DATA (If ID exists) ---
    if ($id > 0) {
        $sql = "SELECT * FROM orders WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if (!$res) { 
            die("Order with ID $id not found."); 
        }
        $order_data = $res;

        // Fetch Materials
        $om_sql = "SELECT material_id, quantity as weight FROM order_materials WHERE order_id = ?";
        $om_stmt = mysqli_prepare($conn, $om_sql);
        $om_stmt->bind_param("i", $id);
        $om_stmt->execute();
        $order_materials = $om_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Fetch Attachments
        $at_sql = "SELECT file_path FROM order_attachments WHERE order_id = ?";
        $at_stmt = mysqli_prepare($conn, $at_sql);
        $at_stmt->bind_param("i", $id);
        $at_stmt->execute();
        $attachments = $at_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- 3. HANDLE FORM SUBMISSION ---
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        
        $calculated_netto = isset($_POST['weights']) ? array_sum($_POST['weights']) : 0;

        $sub_data = [
            'partner_id'     => $_POST['customer'],
            'type'           => $_POST['type'],
            'date'           => $_POST['date'],
            'price'          => $_POST['price'],
            'currency'       => $_POST['currency'],
            'pallet_no'      => $_POST['pallet_no'],
            'brutto_w'       => $_POST['brutto_weight'],
            'netto_w'        => $calculated_netto,
            'approve_status' => $_POST['approve_status'],
            'order_status'   => $_POST['order_status']
        ];

        if ($id > 0) {
            // UPDATE EXISTING
            $up_sql = "UPDATE orders SET partner_id=?, type=?, date=?, price=?, currency=?, pallet_no=?, brutto_w=?, netto_w=?, approve_status=?, order_status=? WHERE id=?";
            $up_stmt = mysqli_prepare($conn, $up_sql);
            mysqli_stmt_bind_param($up_stmt, "issdssssssi", 
                $sub_data['partner_id'], $sub_data['type'], $sub_data['date'], $sub_data['price'], 
                $sub_data['currency'], $sub_data['pallet_no'], $sub_data['brutto_w'], 
                $sub_data['netto_w'], $sub_data['approve_status'], $sub_data['order_status'], $id
            );
            $action_type = 'update';
            $final_order_no = $order_data['order_no']; // Keep existing
        } else {
            // INSERT NEW
            // Temp order no format updated to match GBR-GUH-2026-RAND
            $temp_order_no = "GBR-GUH-" . date('Y') . "-" . rand(10000, 99999);
            $temp_track_id = "TRK-" . strtoupper(bin2hex(random_bytes(3)));

            $up_sql = "INSERT INTO orders (partner_id, type, date, price, currency, pallet_no, brutto_w, netto_w, approve_status, order_status, order_no, track_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $up_stmt = mysqli_prepare($conn, $up_sql);
            
            mysqli_stmt_bind_param($up_stmt, "issdssssssss", 
                $sub_data['partner_id'], $sub_data['type'], $sub_data['date'], $sub_data['price'], 
                $sub_data['currency'], $sub_data['pallet_no'], $sub_data['brutto_w'], 
                $sub_data['netto_w'], $sub_data['approve_status'], $sub_data['order_status'],
                $temp_order_no, $temp_track_id
            );
            $action_type = 'create';
        }

        if(mysqli_stmt_execute($up_stmt)) {
            if ($id === 0) {
                $id = mysqli_insert_id($conn);
                
                // --- UPDATED ORDER NO FORMAT HERE ---
                // Format: GBR-GUH-2026-XXXXX (where XXXXX is the ID padded to 5 digits)
                $final_order_no = "GBR-GUH-" . date('Y') . "-" . str_pad($id, 5, "0", STR_PAD_LEFT);
                mysqli_query($conn, "UPDATE orders SET order_no = '$final_order_no' WHERE id = $id");
            }

            // --- SAVE MATERIALS ---
            mysqli_query($conn, "DELETE FROM order_materials WHERE order_id = $id");
            if (!empty($_POST['materials'])) {
                foreach ($_POST['materials'] as $key => $m_id) {
                    $m_weight = $_POST['weights'][$key];
                    $ins_m = mysqli_prepare($conn, "INSERT INTO order_materials (order_id, material_id, quantity) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($ins_m, "iid", $id, $m_id, $m_weight);
                    mysqli_stmt_execute($ins_m);
                }
            }

            // --- SAVE DOCUMENTS ---
            if (!empty($_FILES['documents']['name'][0])) {
                $upload_dir = "../../uploads/orders/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                foreach ($_FILES['documents']['name'] as $key => $name) {
                    $tmp_name = $_FILES['documents']['tmp_name'][$key];
                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = "order_" . $id . "_" . time() . "_" . $key . "." . $file_ext;
                    $target_file = $upload_dir . $new_filename;
                    $db_path = "uploads/orders/" . $new_filename;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $ins_at = mysqli_prepare($conn, "INSERT INTO order_attachments (order_id, file_path) VALUES (?, ?)");
                        mysqli_stmt_bind_param($ins_at, "is", $id, $db_path);
                        mysqli_stmt_execute($ins_at);
                    }
                }
            }

            logActivity($conn, $_SESSION['user_id'], $action_type, 'order', $id, "User #{$_SESSION['user_id']} created order No. {$final_order_no}");
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id&success=1");
            exit;
        } else {
            die("SQL Error: " . mysqli_error($conn));
        }
    }

    $m_res = mysqli_query($conn, "SELECT id, name FROM materials ORDER BY name ASC");
    $materials_list = mysqli_fetch_all($m_res, MYSQLI_ASSOC);
?>

<script src="../../js/script.js"></script>
<div class="container-fluid">
    <div class="container-sm">
        <h1><?= ($id > 0) ? "Edit Order #$id" : "New Order" ?></h1>
        
        <?php if(isset($_GET['success'])): ?>
            <div class='alert alert-success'>Order saved successfully!</div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="container mt-4">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Order Type</label>
                    <select name="type" class="form-select" required>
                        <option value="guh-in" <?= ($order_data['type'] == 'guh-in') ? 'selected' : '' ?>>Incoming</option>
                        <option value="guh-out" <?= ($order_data['type'] == 'guh-out') ? 'selected' : '' ?>>Outgoing</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Customer / Partner</label>
                    <select name="customer" class="form-select" required>
                        <option value="" disabled <?= empty($order_data['partner_id']) ? 'selected' : '' ?>>Select Partner</option>
                        <?php
                            $cust_res = mysqli_query($conn, "SELECT id, name FROM partners");
                            while($cust = mysqli_fetch_assoc($cust_res)) {
                                $selected = ($cust['id'] == $order_data['partner_id']) ? 'selected' : '';
                                echo "<option value='".$cust['id']."' $selected>".$cust['name']."</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Order No</label>
                    <input type="text" class="form-control" disabled value="<?= $order_data['order_no']; ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Track ID</label>
                    <input type="text" class="form-control" disabled value="<?= $order_data['track_id']; ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" required value="<?= $order_data['date']; ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" required value="<?= $order_data['price'] ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select" required>
                        <?php 
                        $currencies = ['EUR' => '€ EUR', 'USD' => '$ USD', 'CZK' => 'Kč CZK', 'PLN' => 'zł PLN', 'JPY' => '¥ JPY'];
                        foreach($currencies as $code => $label): ?>
                            <option value="<?= $code ?>" <?= ($order_data['currency'] == $code) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Pallet No</label>
                    <input type="text" name="pallet_no" class="form-control" value="<?= $order_data['pallet_no'] ?>">
                </div>

                <div class="col-md-5">
                    <label class="form-label">Brutto Weight (kg)</label>
                    <input type="number" step="0.01" name="brutto_weight" class="form-control" value="<?= $order_data['brutto_w'] ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Materials</label>
                    <div id="materials-container">
                        <?php 
                        $display_items = !empty($order_materials) ? $order_materials : [['material_id' => '', 'weight' => '']];
                        foreach($display_items as $om): 
                        ?>
                            <div class="row g-2 material-row mb-2 align-items-center">
                                <div class="col-md-5">
                                    <select name="materials[]" class="form-select" required>
                                        <option value="" disabled <?= empty($om['material_id']) ? 'selected' : '' ?>>Select material</option>
                                        <?php foreach($materials_list as $m): ?>
                                            <option value="<?= $m['id']; ?>" <?= ($m['id'] == $om['material_id']) ? 'selected' : '' ?>>
                                                <?= $m['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" name="weights[]" class="form-control weight-input" 
                                           placeholder="Weight (kg)" value="<?= $om['weight']; ?>" required>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="button" class="btn btn-danger w-50 remove-material">Remove</button>
                                    <button type="button" class="btn btn-success w-50 add-material">+ Add</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Netto Weight (kg)</label>
                    <input type="number" step="0.01" name="netto_weight" 
                           id="netto_weight" class="form-control" readonly value="<?= $order_data['netto_w'] ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Approve Status</label>
                    <select name="approve_status" class="form-select">
                        <option value="approved" <?= ($order_data['approve_status'] == 'approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="not approved" <?= ($order_data['approve_status'] == 'not approved') ? 'selected' : '' ?>>Not Approved</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Order Status</label>
                    <select name="order_status" class="form-select">
                        <option value="created" <?= ($order_data['order_status'] == 'created') ? 'selected' : '' ?>>Created</option>
                        <option value="received" <?= ($order_data['order_status'] == 'received') ? 'selected' : '' ?>>Received</option>
                        <option value="in process" <?= ($order_data['order_status'] == 'in process') ? 'selected' : '' ?>>In process</option>
                        <option value="completed" <?= ($order_data['order_status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($order_data['order_status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label">Documents (Images / PDFs)</label>
                    <input type="file" name="documents[]" class="form-control mb-2" multiple>
                    
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <?php if(!empty($attachments)): ?>
                            <?php foreach($attachments as $file): ?>
                                <div class="border p-1 rounded bg-light d-flex align-items-center">
                                    <a href="../../<?= $file['file_path'] ?>" target="_blank" class="btn btn-sm btn-link text-decoration-none">
                                        View Attachment
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <?= ($id > 0) ? "Update Order & Save Changes" : "Create New Order" ?>
                    </button>
                </div>

            </div> 
        </form> 
    </div>
</div>

<?php include "../../build/footer.php"; ?>