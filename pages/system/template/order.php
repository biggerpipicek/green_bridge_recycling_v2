<?php
    // MICHAEL D. PHILLIPS - 17.04.2026
    // ORDER TEMPLATE PAGE
    $page_title = "GBR ORDER {$id}";
    require "../../../build/auth.php";
    require "../../../build/functions.php";
    require "../../../build/mailer.php";
    include "../../../build/header.php";

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // --- 1. FETCH MAIN ORDER DATA ---
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $order_data = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$order_data) { die("Order not found."); }

    // --- 2. FETCH MATERIALS LINKED TO THIS ORDER ---
    $om_sql = "SELECT material_id, quantity as weight FROM order_materials WHERE order_id = ?";
    $om_stmt = mysqli_prepare($conn, $om_sql);
    mysqli_stmt_bind_param($om_stmt, "i", $id);
    mysqli_stmt_execute($om_stmt);
    $om_res = mysqli_stmt_get_result($om_stmt);
    $order_materials = mysqli_fetch_all($om_res, MYSQLI_ASSOC);

    // --- 3. FETCH ATTACHMENTS ---
    $at_sql = "SELECT file_path FROM order_attachments WHERE order_id = ?";
    $at_stmt = mysqli_prepare($conn, $at_sql);
    mysqli_stmt_bind_param($at_stmt, "i", $id);
    mysqli_stmt_execute($at_stmt);
    $at_res = mysqli_stmt_get_result($at_stmt);
    $attachments = mysqli_fetch_all($at_res, MYSQLI_ASSOC);

    // --- 4. HANDLE FORM SUBMISSION (POST) ---
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        
        $calculated_netto = isset($_POST['weights']) ? array_sum($_POST['weights']) : 0;

        $sub_data = [
            'partner_id'     => $_POST['customer'],
            'date'           => $_POST['date'],
            'price'          => $_POST['price'],
            'currency'       => $_POST['currency'],
            'pallet_no'      => $_POST['pallet_no'],
            'brutto_w'       => $_POST['brutto_weight'],
            'netto_w'        => $calculated_netto,
            'approve_status' => $_POST['approve_status'],
            'order_status'   => $_POST['order_status']
        ];

        $difference = getChangedFields($order_data, $sub_data);

        if(!empty($difference) || !empty($_POST['materials'])) {
            $changed_summary = [];
            foreach ($difference as $field => $values) {
                $changed_summary[] = "$field ('{$values['from']}' -> '{$values['to']}')";
            }
            $description = "Updated Order: " . implode(", ", $changed_summary);

            $up_sql = "UPDATE orders SET partner_id=?, date=?, price=?, currency=?, pallet_no=?, brutto_w=?, netto_w=?, approve_status=?, order_status=? WHERE id=?";
            $up_stmt = mysqli_prepare($conn, $up_sql);
            mysqli_stmt_bind_param($up_stmt, "isdssssssi", 
                $sub_data['partner_id'], $sub_data['date'], $sub_data['price'], 
                $sub_data['currency'], $sub_data['pallet_no'], $sub_data['brutto_w'], 
                $sub_data['netto_w'], $sub_data['approve_status'], $sub_data['order_status'], $id
            );

            if(mysqli_stmt_execute($up_stmt)) {
                // --- UPDATE MATERIALS TABLE ---
                mysqli_query($conn, "DELETE FROM order_materials WHERE order_id = $id");
                
                // --- UPDATE INVENTORY LEDGER ---
                // Always clear old movements for this order first to prevent double counting
                mysqli_query($conn, "DELETE FROM inventory_movements WHERE order_id = $id");

                $is_fully_approved = ($sub_data['order_status'] === 'completed' && $sub_data['approve_status'] === 'approved');
                
                $direction_map = [
                    'in'      => 'in',
                    'guh-in'  => 'in',
                    'out'     => 'out',
                    'guh-out' => 'out'
                ];
                $direction = $direction_map[$order_data['type']] ?? 'in';

                if (!empty($_POST['materials'])) {
                    foreach ($_POST['materials'] as $key => $m_id) {
                        $m_weight = $_POST['weights'][$key];
                        
                        // 1. Insert into order_materials
                        $ins_m = mysqli_prepare($conn, "INSERT INTO order_materials (order_id, material_id, quantity) VALUES (?, ?, ?)");
                        mysqli_stmt_bind_param($ins_m, "iid", $id, $m_id, $m_weight);
                        mysqli_stmt_execute($ins_m);

                        // 2. Insert into inventory_movements ONLY if completed and approved
                        if ($is_fully_approved) {
                            $ins_move = mysqli_prepare($conn, "INSERT INTO inventory_movements (material_id, order_id, quantity, direction, created_at) VALUES (?, ?, ?, ?, NOW())");
                            mysqli_stmt_bind_param($ins_move, "iids", $m_id, $id, $m_weight, $direction);
                            mysqli_stmt_execute($ins_move);
                        }
                    }
                }

                logActivity($conn, $_SESSION['user_id'], 'update', 'orders', $id, $description);

                // --- FETCH PARTNER NAME & SEND EMAIL ---
                $pn_stmt = mysqli_prepare($conn, "SELECT name FROM partners WHERE id = ?");
                mysqli_stmt_bind_param($pn_stmt, "i", $order_data['partner_id']);
                mysqli_stmt_execute($pn_stmt);
                $pn_row = mysqli_stmt_get_result($pn_stmt)->fetch_assoc();
                $partner_name_for_mail = $pn_row['name'] ?? 'Unknown Partner';
                mailOrderUpdated($conn, $id, $order_data, $sub_data, $partner_name_for_mail);
                
                $order_data = array_merge($order_data, $sub_data);
                $success_msg = "Order and materials updated successfully! " . ($is_fully_approved ? "Stock has been updated." : "Stock not updated (Order must be Approved & Completed).");
                
                $om_stmt->execute();
                $order_materials = mysqli_fetch_all($om_stmt->get_result(), MYSQLI_ASSOC);
            }
        }

        // --- 5. HANDLE FILE UPLOADS ---
        if (!empty($_FILES['documents']['name'][0])) {
            $type = $order_data['type'];
            $subfolder = 'Incoming'; 

            if ($type === 'out') {
                $subfolder = 'Outgoing';
            } elseif ($type === 'guh-in' || $type === 'guh-out') {
                $subfolder = 'guhring';
            }

            $upload_base = "../../../order_attachments/" . $subfolder . "/";
            $db_base = "order_attachments/" . $subfolder . "/";

            if (!is_dir($upload_base)) {
                mkdir($upload_base, 0777, true);
            }

            foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = time() . "_" . basename($_FILES['documents']['name'][$key]);
                    $target_file = $upload_base . $file_name;
                    $db_path = $db_base . $file_name;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $at_ins = mysqli_prepare($conn, "INSERT INTO order_attachments (order_id, file_path) VALUES (?, ?)");
                        mysqli_stmt_bind_param($at_ins, "is", $id, $db_path);
                        mysqli_stmt_execute($at_ins);
                    }
                }
            }
            
            $at_stmt->execute();
            $attachments = mysqli_fetch_all($at_stmt->get_result(), MYSQLI_ASSOC);
        }
    }

    // --- 6. FETCH ALL MATERIALS FOR DROPDOWNS ---
    $m_res = mysqli_query($conn, "SELECT id, name FROM materials ORDER BY name ASC");
    $materials = mysqli_fetch_all($m_res, MYSQLI_ASSOC);
?>
<script src="../../../js/script.js"></script>
<div class="container-fluid">
    <div class="container-sm">
        <h1>Order <?php echo $order_data['order_no']; ?></h1>
        
        <?php if(isset($success_msg)) echo "<div class='alert alert-success'>$success_msg</div>"; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="container mt-4">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Customer</label>
                    <select name="customer" class="form-select" required>
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
                    <input type="text" name="order_no" class="form-control" disabled value="<?php echo $order_data['order_no']; ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Track ID</label>
                    <input type="text" name="track_id" class="form-control" disabled value="<?php echo $order_data['track_id']; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" required value="<?php echo $order_data['date']; ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Price</label>
                    <input type="number" inputmode="decimal" pattern="[0-9]*" step="0.01" name="price" class="form-control" required value="<?php echo $order_data['price'] ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select" required>
                        <option value="EUR" <?php echo ($order_data['currency'] == "EUR") ? 'selected' : '' ?>>€ EUR</option>
                        <option value="USD" <?php echo ($order_data['currency'] == "USD") ? 'selected' : '' ?>>$ USD</option>
                        <option value="CZK" <?php echo ($order_data['currency'] == "CZK") ? 'selected' : '' ?>>Kč CZK</option>
                        <option value="PLN" <?php echo ($order_data['currency'] == "PLN") ? 'selected' : '' ?>>zł PLN</option>
                        <option value="JPY" <?php echo ($order_data['currency'] == "JPY") ? 'selected' : '' ?>>¥ JPY</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Pallet No</label>
                    <input type="text" name="pallet_no" class="form-control" value="<?php echo $order_data['pallet_no'] ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Brutto Weight (kg)</label>
                    <input type="number" inputmode="decimal" pattern="[0-9]*" step="0.01" name="brutto_weight" class="form-control" value="<?php echo $order_data['brutto_w'] ?>">
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
                                        <?php foreach($materials as $m): ?>
                                            <option value="<?= $m['id']; ?>" <?= ($m['id'] == $om['material_id']) ? 'selected' : '' ?>>
                                                <?= $m['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" inputmode="decimal" pattern="[0-9]*" step="0.01" name="weights[]" class="form-control weight-input" 
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
                    <input type="number" inputmode="decimal" pattern="[0-9]*" step="0.01" name="netto_weight" 
                           id="netto_weight" class="form-control" readonly value="<?php echo $order_data['netto_w'] ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Approve Status</label>
                    <select name="approve_status" class="form-select">
                        <option value="approved" <?php echo ($order_data['approve_status'] == 'approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="not approved" <?php echo ($order_data['approve_status'] == 'not approved') ? 'selected' : '' ?>>Not Approved</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Order Status</label>
                    <select name="order_status" class="form-select">
                        <option value="created" <?php echo ($order_data['order_status'] == 'created') ? 'selected' : '' ?>>Created</option>
                        <option value="received" <?php echo ($order_data['order_status'] == 'received') ? 'selected' : '' ?>>Received</option>
                        <option value="in process" <?php echo ($order_data['order_status'] == 'in process') ? 'selected' : '' ?>>In process</option>
                        <option value="completed" <?php echo ($order_data['order_status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?php echo ($order_data['order_status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label">Documents (Images / PDFs)</label>
                    <input type="file" name="documents[]" class="form-control mb-2" multiple accept=".jpg,.jpeg,.png,.pdf">
                    
                    <label class="form-label mt-2"><strong>Existing Attachments:</strong></label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if(!empty($attachments)): ?>
                            <?php foreach($attachments as $file): ?>
                                <div class="border p-2 rounded bg-light d-flex align-items-center">
                                    <?php 
                                        $ext = pathinfo($file['file_path'], PATHINFO_EXTENSION);
                                        $full_web_path = "" . $file['file_path'];
                                        
                                        if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): 
                                    ?>
                                        <img src="<?= $full_web_path ?>" style="height: 30px; width: 30px; object-fit: cover;" class="me-2 rounded">
                                    <?php else: ?>
                                        <span class="me-2">📄</span> 
                                    <?php endif; ?>

                                    <a href="<?= $full_web_path ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        View Document
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No documents attached to this order yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        Update Order & Save Changes
                    </button>
                </div>

            </div> 
        </form> 
    </div>
</div>

<?php include "../../../build/footer.php"; ?>