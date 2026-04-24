<?php
    // MICHAEL D. PHILLIPS - 17.04.2026
    // ORDER TEMPLATE PAGE

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    } 

    $id = $_GET['id'];

    // 1. Fetch available materials for the dropdowns
    $m_sql = "SELECT id, name FROM materials ORDER BY name ASC";
    $m_res = mysqli_query($conn, $m_sql);
    $materials = mysqli_fetch_all($m_res, MYSQLI_ASSOC);

    // 2. Fetch specific items for THIS order from your 'order_materials' table
    $om_sql = "SELECT material_id, quantity as weight FROM order_materials WHERE order_id = $id";
    $om_res = mysqli_query($conn, $om_sql);
    $order_materials = mysqli_fetch_all($om_res, MYSQLI_ASSOC);

    // 3. Fetch attachments for this order
    $at_sql = "SELECT file_path FROM order_attachments WHERE order_id = $id";
    $at_res = mysqli_query($conn, $at_sql);
    $attachments = mysqli_fetch_all($at_res, MYSQLI_ASSOC);

    $page_title = "GBR ORDER {$id}";
    include "../../../build/header.php";

    $sql = "SELECT * FROM orders WHERE id = {$id}";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0):
        while($row_m = mysqli_fetch_assoc($result)) {
    ?>
    <script src="../../../js/script.js></script>
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
                                                echo "<option value='".$row['id']."' ".(($row['id'] == $row_m['partner_id']) ? 'selected' : '').">".$row['name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>

                            <!-- Order No -->
                            <div class="col-md-3">
                                <label class="form-label">Order No</label>
                                <input type="text" name="order_no" class="form-control" disabled value="<?php echo $row_m['order_no']; ?>">
                            </div>

                            <!-- Track ID -->
                            <div class="col-md-3">
                                <label class="form-label">Track ID</label>
                                <input type="text" name="track_id" class="form-control" disabled value="<?php echo $row_m['track_id']; ?>">
                            </div>

                            <!-- Date -->
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo $row_m['date']; ?>">
                            </div>

                            <!-- Price -->
                            <div class="col-md-2">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $row_m['price'] ?>">
                            </div>

                            <!-- Currency -->
                            <div class="col-md-2">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select" required>
                                    <option value="EUR" <?php echo ($row_m['currency'] == "EUR") ? 'selected' : '' ?>>€ EUR</option>
                                    <option value="USD" <?php echo ($row_m['currency'] == "USD") ? 'selected' : '' ?>>$ USD</option>
                                    <option value="CZK" <?php echo ($row_m['currency'] == "CZK") ? 'selected' : '' ?>>Kč CZK</option>
                                    <option value="PLN" <?php echo ($row_m['currency'] == "PLN") ? 'selected' : '' ?>>zł PLN</option>
                                    <option value="JPY" <?php echo ($row_m['currency'] == "YEN") ? 'selected' : '' ?>>¥ JPY</option>
                                </select>
                            </div>

                            <!-- Pallet No -->
                            <div class="col-md-6">
                                <label class="form-label">Pallet No</label>
                                <input type="text" name="pallet_no" class="form-control" value="<?php echo $row_m['pallet_no'] ?>">
                            </div>

                            <!-- Brutto Weight -->
                            <div class="col-md-6">
                                <label class="form-label">Brutto Weight (kg)</label>
                                <input type="number" step="0.01" name="brutto_weight" class="form-control" value="<?php echo $row_m['brutto_w'] ?>">
                            </div>

                            <!-- ================= MATERIALS SECTION ================= -->
                            <div class="col-12">
                                <label class="form-label">Materials</label>
                                <div id="materials-container">
                                    <?php 
                                    // Use your order_materials data, or one empty row if none exists
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

                            <!-- Auto Netto -->
                            <div class="col-md-6">
                                <label class="form-label">Netto Weight (kg)</label>
                                <input type="number" step="0.01" name="netto_weight" 
                                    id="netto_weight" class="form-control" readonly value="<?php echo $row_m['netto_w'] ?>">
                            </div>
                            <!-- ===================================================== -->

                            <!-- Approve Status -->
                            <div class="col-md-3">
                                <label class="form-label">Approve Status</label>
                                <select name="approve_status" class="form-select">
                                    <option value="approved" <?php echo ($row_m['approve_status'] == 'approved') ? 'selected' : '' ?>>Approved</option>
                                    <option value="not approved" <?php echo ($row_m['approve_status'] == 'not approved') ? 'selected' : '' ?>>Not Approved</option>
                                </select>
                            </div>

                            <!-- Order Status -->
                            <div class="col-md-3">
                                <label class="form-label">Order Status</label>
                                <select name="order_status" class="form-select">
                                    <option value="created" <?php echo ($row_m['order_status'] == 'created') ? 'selected' : '' ?>>Created</option>
                                    <option value="received" <?php echo ($row_m['order_status'] == 'received') ? 'selected' : '' ?>>Received</option>
                                    <option value="in process" <?php echo ($row_m['order_status'] == 'in process') ? 'selected' : '' ?>>In process</option>
                                    <option value="completed" <?php echo ($row_m['order_status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?php echo ($row_m['order_status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>

                            <!-- Documents -->
                            <div class="col-12">
                                <label class="form-label">Documents (Images / PDFs)</label>
                                <input type="file" name="documents[]" class="form-control mb-2" multiple accept=".jpg,.jpeg,.png,.pdf">
                                
                                <?php if(!empty($attachments)): ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach($attachments as $file): ?>
                                            <div class="border p-1 rounded">
                                                <a href="<?= $file['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                    View Document
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
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
        }
        endif;
        include "../../../build/footer.php";
    ?>