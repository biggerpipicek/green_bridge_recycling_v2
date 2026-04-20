<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // NEW ORDERS - INCOMING/OUTGOING

    require "../../build/auth.php";
    require "../../build/functions.php";

    $action = $_GET['action'] ?? '';
    
    $page_title = "GBR Create Order";
    include "../../build/header.php";
?>
    

    <script src="../../js/script.js"></script>


    <?php
    
        if($action == "incoming_orders"):
    ?>
        <div class="container-fuild">
            <div class="container-sm">
                <h1>Incoming Order</h1>
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
                                    <option value="">Select customer</option>
                                    <option value="1">Customer 1</option>
                                    <option value="2">Customer 2</option>
                                </select>
                            </div>

                            <!-- Order No -->
                            <div class="col-md-3">
                                <label class="form-label">Order No</label>
                                <input type="text" name="order_no" class="form-control" required>
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

                            <!-- Order Type -->
                            <div class="col-md-4">
                                <label class="form-label">Order Type</label>
                                <select name="order_type" class="form-select" required>
                                    <option value="">Select type</option>
                                    <option value="incoming">Incoming</option>
                                    <option value="outgoing">Outgoing</option>
                                </select>
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
                                                <option value="">Select material</option>
                                                <option value="plastic">Plastic</option>
                                                <option value="metal">Metal</option>
                                                <option value="paper">Paper</option>
                                                <option value="glass">Glass</option>
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
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <!-- Order Status -->
                            <div class="col-md-3">
                                <label class="form-label">Order Status</label>
                                <select name="order_status" class="form-select">
                                    <option value="new">New</option>
                                    <option value="processing">Processing</option>
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