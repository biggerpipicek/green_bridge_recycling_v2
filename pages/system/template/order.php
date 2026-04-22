<?php
    // MICHAEL D. PHILLIPS - 17.04.2026
    // ORDER TEMPLATE PAGE

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    $page_title = "GBR Add Order";
    include "../../../build/header.php";

    $id = $_GET['id'];

    $sql = "SELECT * FROM orders WHERE id = {$id}";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0):
        while(mysqli_fetch_assoc($result)) {
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
                                                    // INSERT HERE AN SQL COMMAND TO SELECT ALL MATERIALS FROM THE SPECIFIC ORDER
                                                    $sql = "SELECT id, name from materials";
                                                    $result = mysqli_query($conn, $sql);

                                                    if(mysqli_num_rows($result) > 0) {
                                                        while($row = mysqli_fetch_assoc($result)) {
                                                            echo "<option value='".$row['id']."'>".$row['name']."</option>";
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
                                <?php
                                    // INSERT HERE AN SQL COMMAND TO SELECT ALL ATTACHMENTS FOR THE SPECIFIC ORDER
                                ?>
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