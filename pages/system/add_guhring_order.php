<?php
    // MICHAEL D. PHILLIPS - 17.04.2026
    // ORDER TEMPLATE PAGE (FIXED FOR CREATE/UPDATE + ORDER TYPE + DOCUMENTS)

    require_once "../../vendor/autoload.php";
    use chillerlan\QRCode\{QRCode, QROptions};
    use chillerlan\QRCode\Output\QROutputInterface;
    require "../../build/auth.php";
    require "../../build/functions.php";
    require "../../build/mailer.php";

    $page_title = "Guhring Add Order";
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // --- 1. INITIALIZE DEFAULT DATA (For New Orders) ---
    $order_data = [
        'partner_id'     => '',
        'type'           => 'guh-in',
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

        $om_sql = "SELECT material_id, quantity as weight FROM order_materials WHERE order_id = ?";
        $om_stmt = mysqli_prepare($conn, $om_sql);
        $om_stmt->bind_param("i", $id);
        $om_stmt->execute();
        $order_materials = $om_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $at_sql = "SELECT file_path FROM order_attachments WHERE order_id = ?";
        $at_stmt = mysqli_prepare($conn, $at_sql);
        $at_stmt->bind_param("i", $id);
        $at_stmt->execute();
        $attachments = $at_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- 3. HANDLE FORM SUBMISSION ---
    if ($_SERVER['REQUEST_METHOD'] === "POST") {

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $calculated_netto = isset($_POST['weights']) ? array_sum($_POST['weights']) : 0;
        $created_by = $_SESSION['user_id'];

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
            $up_sql = "UPDATE orders SET partner_id=?, type=?, date=?, price=?, currency=?, pallet_no=?, brutto_w=?, netto_w=?, approve_status=?, order_status=?, updated_at=NOW() WHERE id=?";
            $up_stmt = mysqli_prepare($conn, $up_sql);
            mysqli_stmt_bind_param($up_stmt, "issdssssssi", 
                $sub_data['partner_id'], $sub_data['type'], $sub_data['date'], $sub_data['price'], 
                $sub_data['currency'], $sub_data['pallet_no'], $sub_data['brutto_w'], 
                $sub_data['netto_w'], $sub_data['approve_status'], $sub_data['order_status'], $id
            );
            $action_type = 'update';
            $final_order_no = $order_data['order_no'];
        } else {
            $temp_order_no = "GBR-GUH-" . date('Y') . "-" . rand(10000, 99999);
            $temp_track_id = "TRK-" . strtoupper(bin2hex(random_bytes(3)));

            $up_sql = "INSERT INTO orders (partner_id, type, date, price, currency, pallet_no, brutto_w, netto_w, approve_status, order_status, order_no, track_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $up_stmt = mysqli_prepare($conn, $up_sql);
            mysqli_stmt_bind_param($up_stmt, "issdssssssssi", 
                $sub_data['partner_id'], $sub_data['type'], $sub_data['date'], $sub_data['price'], 
                $sub_data['currency'], $sub_data['pallet_no'], $sub_data['brutto_w'], 
                $sub_data['netto_w'], $sub_data['approve_status'], $sub_data['order_status'],
                $temp_order_no, $temp_track_id, $created_by
            );
            $action_type = 'create';
        }

        if(mysqli_stmt_execute($up_stmt)) {
            if ($id === 0) {
                $id = mysqli_insert_id($conn);
                $final_order_no = "GBR-GUH-" . date('Y') . "-" . str_pad($id, 5, "0", STR_PAD_LEFT);
                mysqli_query($conn, "UPDATE orders SET order_no = '$final_order_no' WHERE id = $id");

                // --- GENERATE QR CODE ---
                $qr_dir = "../../uploads/qrcodes/";
                if (!is_dir($qr_dir)) mkdir($qr_dir, 0777, true);

                $qr_url      = "https://gbrguh.eu/pages/system/template/guhring_order.php?id=" . $id;
                $qr_filename = "qr_order_" . $id . ".png";
                $qr_path     = $qr_dir . $qr_filename;
                $qr_db_path  = $qr_filename;

                $options = new QROptions([
                    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel'   => QRCode::ECC_M,
                    'scale'      => 8,
                ]);

                (new QRCode($options))->render($qr_url, $qr_path);

                $qr_stmt = mysqli_prepare($conn, "INSERT INTO order_qrcodes (order_id, file_path) VALUES (?, ?)");
                mysqli_stmt_bind_param($qr_stmt, "is", $id, $qr_db_path);
                mysqli_stmt_execute($qr_stmt);
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

            // --- INVENTORY MOVEMENTS ---
            mysqli_query($conn, "DELETE FROM inventory_movements WHERE order_id = $id");
            if (!empty($_POST['materials']) && $sub_data['order_status'] === 'completed' && $sub_data['approve_status'] === 'approved') {
                $mov_sql = "INSERT INTO inventory_movements (order_id, material_id, quantity, direction) VALUES (?, ?, ?, ?)";
                $stmt_mov = mysqli_prepare($conn, $mov_sql);
                foreach ($_POST['materials'] as $key => $m_id) {
                    $m_weight  = (float)$_POST['weights'][$key];
                    $direction = ($sub_data['type'] === 'guh-in') ? 'in' : 'out';
                    mysqli_stmt_bind_param($stmt_mov, "iids", $id, $m_id, $m_weight, $direction);
                    mysqli_stmt_execute($stmt_mov);
                }
                mysqli_stmt_close($stmt_mov);
            }

            // --- LOG STATUS HISTORY ---
            if ($action_type === 'create') {
                $hist_sql = "INSERT INTO order_status_history (order_id, status, changed_by) VALUES (?, ?, ?)";
                $hist_stmt = mysqli_prepare($conn, $hist_sql);
                mysqli_stmt_bind_param($hist_stmt, "isi", $id, $sub_data['order_status'], $_SESSION['user_id']);
                mysqli_stmt_execute($hist_stmt);
            }
            if ($action_type === 'update' && $sub_data['order_status'] !== $order_data['order_status']) {
                $hist_sql = "INSERT INTO order_status_history (order_id, status, changed_by) VALUES (?, ?, ?)";
                $hist_stmt = mysqli_prepare($conn, $hist_sql);
                mysqli_stmt_bind_param($hist_stmt, "isi", $id, $sub_data['order_status'], $_SESSION['user_id']);
                mysqli_stmt_execute($hist_stmt);
            }

            // --- SAVE DOCUMENTS ---
            if (!empty($_FILES['documents']['name'][0])) {
                // FIX: upload dir and DB path must match
                $upload_dir = "../../order_attachments/guhring/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                foreach ($_FILES['documents']['name'] as $key => $name) {
                    if ($_FILES['documents']['error'][$key] !== UPLOAD_ERR_OK) continue;
                    $tmp_name = $_FILES['documents']['tmp_name'][$key];
                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = "order_" . $id . "_" . time() . "_" . $key . "." . $file_ext;
                    $target_file = $upload_dir . $new_filename;
                    $db_path = "order_attachments/guhring/" . $new_filename;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $ins_at = mysqli_prepare($conn, "INSERT INTO order_attachments (order_id, file_path) VALUES (?, ?)");
                        mysqli_stmt_bind_param($ins_at, "is", $id, $db_path);
                        mysqli_stmt_execute($ins_at);
                    }
                }
            }

            logActivity($conn, $_SESSION['user_id'], $action_type, 'order', $id, "User #{$_SESSION['user_id']} {$action_type}d order No. {$final_order_no}");

            // --- FETCH PARTNER NAME FOR EMAIL ---
            $pn_stmt = mysqli_prepare($conn, "SELECT name FROM partners WHERE id = ?");
            mysqli_stmt_bind_param($pn_stmt, "i", $sub_data['partner_id']);
            mysqli_stmt_execute($pn_stmt);
            $pn_row = mysqli_stmt_get_result($pn_stmt)->fetch_assoc();
            $partner_name_for_mail = $pn_row['name'] ?? 'Unknown Partner';

            // --- SEND EMAIL NOTIFICATION ---
            $mail_order_data = array_merge($sub_data, [
                'order_no' => $final_order_no,
                'track_id' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT track_id FROM orders WHERE id = $id"))['track_id'] ?? ''
            ]);

            if ($action_type === 'create') {
                mailOrderCreated($conn, $id, $mail_order_data, $partner_name_for_mail);
            } else {
                mailOrderUpdated($conn, $id, $order_data, $sub_data, $partner_name_for_mail);
            }

            // FIX: absolute redirect URL so it always works regardless of current path
            header("Location: /pages/system/add_guhring_order.php?id=$id&success=1");
            exit;
        } else {
            die("SQL Error: " . mysqli_error($conn));
        }
    }

    // --- header AFTER post handling so redirect can work ---
    $m_res = mysqli_query($conn, "SELECT id, name FROM materials ORDER BY name ASC");
    $materials_list = mysqli_fetch_all($m_res, MYSQLI_ASSOC);

    include "../../build/header.php";
?>

<script src="../../js/script.js"></script>
<div class="container-fluid">
    <div class="container-sm">
        <h1><?= ($id > 0) ? "Edit Order #$id" : "New Order" ?></h1>
        
        <?php if(isset($_GET['success'])): ?>
            <div class='alert alert-success'>Order saved successfully!</div>
        <?php endif; ?>

        <form method="POST" action="?id=<?= $id ?>" enctype="multipart/form-data" class="container mt-4">
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

                    <?php
                        $preloaded_partner_name = '';
                        if (!empty($order_data['partner_id'])) {
                            $pl = mysqli_prepare($conn, "SELECT name FROM partners WHERE id = ?");
                            mysqli_stmt_bind_param($pl, "i", $order_data['partner_id']);
                            mysqli_stmt_execute($pl);
                            $pl_row = mysqli_stmt_get_result($pl)->fetch_assoc();
                            $preloaded_partner_name = $pl_row['name'] ?? '';
                        }
                    ?>

                    <input type="hidden" name="customer" id="customer_id" value="<?= htmlspecialchars($order_data['partner_id']) ?>" required>

                    <div class="position-relative">
                        <input type="text"
                               id="partner_search"
                               class="form-control"
                               placeholder="Type to search partner..."
                               autocomplete="off"
                               value="<?= htmlspecialchars($preloaded_partner_name) ?>">

                        <ul id="partner_results"
                            class="list-group position-absolute w-100 shadow-sm"
                            style="z-index:1000; display:none; max-height:220px; overflow-y:auto; top:100%; left:0;">
                        </ul>
                    </div>

                    <div id="partner_selected" class="mt-1" style="<?= empty($order_data['partner_id']) ? 'display:none' : '' ?>">
                        <span class="badge bg-success fs-6 px-3 py-2">
                            ✓ <span id="partner_selected_name"><?= htmlspecialchars($preloaded_partner_name) ?></span>
                            <button type="button" id="partner_clear" class="btn-close btn-close-white ms-2" style="font-size:0.6rem;" title="Clear"></button>
                        </span>
                    </div>

                    <div id="add_partner_panel" class="border rounded-3 p-3 mt-2 bg-light" style="display:none;">
                        <p class="mb-2 text-muted small">Partner not found — add a new one:</p>
                        <div class="mb-2">
                            <input type="text" id="new_partner_name" class="form-control form-control-sm" placeholder="Full name *">
                        </div>
                        <div class="mb-2">
                            <input type="email" id="new_partner_email" class="form-control form-control-sm" placeholder="Email *">
                        </div>
                        <div class="mb-2">
                            <select id="new_partner_type" class="form-select form-select-sm">
                                <option value="" disabled selected>Type *</option>
                                <option value="customer">Customer</option>
                                <option value="supplier">Supplier</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" id="add_partner_btn" class="btn btn-sm btn-primary">Add Partner</button>
                            <button type="button" id="cancel_add_partner" class="btn btn-sm btn-secondary">Cancel</button>
                        </div>
                        <div id="add_partner_error" class="text-danger small mt-1" style="display:none;"></div>
                    </div>
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
                    <input type="number" inputmode="decimal" pattern="[0-9]*" step="0.01" name="price" class="form-control" required value="<?= $order_data['price'] ?>">
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
                    <input type="number" inputmode="decimal" pattern="[0-9]*" step="0.01" name="brutto_weight" class="form-control" value="<?= $order_data['brutto_w'] ?>">
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
                                    <a href="/<?= $file['file_path'] ?>" target="_blank" class="btn btn-sm btn-link text-decoration-none">
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

<script>
(function () {
    const searchInput   = document.getElementById('partner_search');
    const resultsList   = document.getElementById('partner_results');
    const hiddenId      = document.getElementById('customer_id');
    const selectedBadge = document.getElementById('partner_selected');
    const selectedName  = document.getElementById('partner_selected_name');
    const clearBtn      = document.getElementById('partner_clear');
    const addPanel      = document.getElementById('add_partner_panel');
    const addBtn        = document.getElementById('add_partner_btn');
    const cancelBtn     = document.getElementById('cancel_add_partner');
    const errorDiv      = document.getElementById('add_partner_error');
    const newName       = document.getElementById('new_partner_name');
    const newEmail      = document.getElementById('new_partner_email');
    const newType       = document.getElementById('new_partner_type');

    const SEARCH_URL = '/build/ajax_search_partners.php';
    const ADD_URL    = '/pages/system/add_client.php';

    let debounceTimer = null;

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(debounceTimer);
        resultsList.style.display = 'none';
        resultsList.innerHTML = '';
        if (q.length < 2) { addPanel.style.display = 'none'; return; }

        debounceTimer = setTimeout(() => {
            fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(partners => {
                    resultsList.innerHTML = '';
                    if (partners.length === 0) {
                        addPanel.style.display = 'block';
                        newName.value = q;
                        return;
                    }
                    addPanel.style.display = 'none';
                    partners.forEach(p => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                        li.style.cursor = 'pointer';
                        li.innerHTML = `<span>${escapeHtml(p.name)}</span><span class="badge bg-secondary">${escapeHtml(p.type)}</span>`;
                        li.addEventListener('mousedown', () => selectPartner(p.id, p.name));
                        resultsList.appendChild(li);
                    });
                    const liAdd = document.createElement('li');
                    liAdd.className = 'list-group-item list-group-item-action text-primary small';
                    liAdd.style.cursor = 'pointer';
                    liAdd.textContent = '+ Add new partner\u2026';
                    liAdd.addEventListener('mousedown', () => {
                        resultsList.style.display = 'none';
                        addPanel.style.display = 'block';
                        newName.value = q;
                        newName.focus();
                    });
                    resultsList.appendChild(liAdd);
                    resultsList.style.display = 'block';
                })
                .catch(() => {});
        }, 300);
    });

    searchInput.addEventListener('blur', () => {
        setTimeout(() => { resultsList.style.display = 'none'; }, 150);
    });

    function selectPartner(id, name) {
        hiddenId.value = id;
        searchInput.value = name;
        selectedName.textContent = name;
        selectedBadge.style.display = 'block';
        resultsList.style.display = 'none';
        addPanel.style.display = 'none';
    }

    clearBtn.addEventListener('click', () => {
        hiddenId.value = '';
        searchInput.value = '';
        selectedBadge.style.display = 'none';
        addPanel.style.display = 'none';
        searchInput.focus();
    });

    addBtn.addEventListener('click', () => {
        errorDiv.style.display = 'none';
        const name  = newName.value.trim();
        const email = newEmail.value.trim();
        const type  = newType.value;
        if (!name || !email || !type) {
            errorDiv.textContent = 'Please fill in all fields.';
            errorDiv.style.display = 'block';
            return;
        }
        addBtn.disabled = true;
        addBtn.textContent = 'Adding\u2026';
        const body = new FormData();
        body.append('ajax', '1');
        body.append('name', name);
        body.append('con_info', email);
        body.append('type', type);
        fetch(ADD_URL, { method: 'POST', body })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    selectPartner(res.id, res.name);
                    newName.value = ''; newEmail.value = ''; newType.value = '';
                    addPanel.style.display = 'none';
                } else {
                    errorDiv.textContent = res.error || 'Something went wrong.';
                    errorDiv.style.display = 'block';
                }
            })
            .catch(() => {
                errorDiv.textContent = 'Network error \u2014 please try again.';
                errorDiv.style.display = 'block';
            })
            .finally(() => { addBtn.disabled = false; addBtn.textContent = 'Add Partner'; });
    });

    cancelBtn.addEventListener('click', () => {
        addPanel.style.display = 'none';
        newName.value = ''; newEmail.value = ''; newType.value = '';
        errorDiv.style.display = 'none';
    });

    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

<?php include "../../build/footer.php"; ?>