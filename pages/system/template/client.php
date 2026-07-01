<?php

    // MICHAEL D. PHILLIPS - 27/04/2026
    // CLIENT TEMPLATE PAGE

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    $client_id = (int)$_GET['id']; // CASTED INT FOR SAFETY

    $sql = "SELECT id, name, type, contact_info, contact_person, phone,
                   address_street, address_city, address_zip, address_country, vat_id, ico
            FROM partners WHERE id = ?";
    $stmt_fetch = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt_fetch, "i", $client_id);
    mysqli_stmt_execute($stmt_fetch);
    $result = mysqli_stmt_get_result($stmt_fetch);
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $client_name = $row['name'];
        $page_title = "GBR Client - {$client_name}";
    }

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    } 

    if ($_SERVER['REQUEST_METHOD'] === "POST" && hasRole('staff')) {
        $sub_data = [
            'name'            => trim($_POST['name']),
            'type'            => trim($_POST['type']),
            'contact_info'    => trim($_POST['con_info']),
            'contact_person'  => trim($_POST['contact_person'] ?? ''),
            'phone'           => trim($_POST['phone'] ?? ''),
            'address_street'  => trim($_POST['address_street'] ?? ''),
            'address_city'    => trim($_POST['address_city'] ?? ''),
            'address_zip'     => trim($_POST['address_zip'] ?? ''),
            'address_country' => trim($_POST['address_country'] ?? ''),
            'vat_id'          => trim($_POST['vat_id'] ?? ''),
            'ico'             => trim($_POST['ico'] ?? '')
        ];

        $difference = getChangedFields($row, $sub_data);

        if(!empty($difference)) {
            // DESCRIPTION
            $changed_summary = [];
            foreach ($difference as $field => $values) {
                $changed_summary[] = "$field ('{$values['from']}' -> '{$values['to']}')";
            }

            $description = "Updated Client data: ". implode(", ", $changed_summary);

            // UPDATE TABLE
            $stmt = mysqli_prepare($conn, "UPDATE partners SET
                        name=?, type=?, contact_info=?, contact_person=?, phone=?,
                        address_street=?, address_city=?, address_zip=?, address_country=?, vat_id=?, ico=?
                    WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssssssssssi",
                $sub_data['name'], $sub_data['type'], $sub_data['contact_info'], $sub_data['contact_person'], $sub_data['phone'],
                $sub_data['address_street'], $sub_data['address_city'], $sub_data['address_zip'], $sub_data['address_country'],
                $sub_data['vat_id'], $sub_data['ico'], $client_id
            );

            if(mysqli_stmt_execute($stmt)) {
                // LOG ACTIVITY
                logActivity($conn, $_SESSION['user_id'], 'update', 'partners', $client_id, $description);

                $row = array_merge($row, $sub_data);
                $success_msg = "Changes saved and logged successfully!";
            }
        } else {
            $info_msg = "No changes made.";
        }
    }

    logActivity($conn, $_SESSION['user_id'], 'checking', 'partners', $client_id, "User #{$_SESSION['user_id']} checked partner {$client_id}");

    include "../../../build/header.php";


    ?>
    <?php if (isset($_SESSION['user'])):
            if(mysqli_num_rows($result) > 0):
        ?>
        <!-- FORM WITH CLIENT DATA - NAME, TYPE, CONTACT INFO -->
        <div class="container-fluid">
            <div class="container-sm d-flex justify-content-center">
                <?php if(isset($success_msg)): ?>
                    <div class="alert alert-success w-50 mb-3 text-center">
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($info_msg)): ?>
                    <div class="alert alert-info w-50 mb-3 text-center">
                        <?php echo $info_msg; ?>
                    </div>
                <?php endif; ?>

                <?php $is_viewer = !hasRole('staff'); ?>
                <form action="" method="post" class="border rounded-4 w-50 p-4">
                    <label for="name" class="form-label">Client name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>" required <?= $is_viewer ? 'readonly' : '' ?>>

                    <label for="type" class="form-label">Client type</label>
                    <select name="type" class="form-select" required <?= $is_viewer ? 'disabled' : '' ?>>
                        <option value="" disabled>Select a type</option>
                        <option value="customer" <?php echo ($row['type'] == "customer") ? 'selected' : '' ?>>Customer</option>
                        <option value="supplier" <?php echo ($row['type'] == "supplier") ? 'selected' : '' ?>>Supplier</option>
                    </select>

                    <label for="contact_person" class="form-label">Contact person</label>
                    <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($row['contact_person'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $is_viewer ? 'readonly' : '' ?>>

                    <label for="con_info" class="form-label">Contact email</label>
                    <input type="email" name="con_info" class="form-control" value="<?= htmlspecialchars($row['contact_info'], ENT_QUOTES, 'UTF-8') ?>" required <?= $is_viewer ? 'readonly' : '' ?>>

                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $is_viewer ? 'readonly' : '' ?>>

                    <label for="address_street" class="form-label">Street address</label>
                    <input type="text" name="address_street" class="form-control" value="<?= htmlspecialchars($row['address_street'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $is_viewer ? 'readonly' : '' ?>>

                    <div class="row g-2">
                        <div class="col-6">
                            <label for="address_city" class="form-label">City</label>
                            <input type="text" name="address_city" class="form-control" value="<?= htmlspecialchars($row['address_city'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $is_viewer ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-6">
                            <label for="address_zip" class="form-label">ZIP / Postal code</label>
                            <input type="text" name="address_zip" class="form-control" value="<?= htmlspecialchars($row['address_zip'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $is_viewer ? 'readonly' : '' ?>>
                        </div>
                    </div>

                    <label for="address_country" class="form-label">Country</label>
                    <input type="text" name="address_country" class="form-control" value="<?= htmlspecialchars($row['address_country'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $is_viewer ? 'readonly' : '' ?>>

                    <div class="row g-2">
                        <div class="col-6">
                            <label for="ico" class="form-label">IČO</label>
                            <input type="text" name="ico" class="form-control" value="<?= htmlspecialchars($row['ico'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $is_viewer ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-6">
                            <label for="vat_id" class="form-label">VAT / DIČ</label>
                            <input type="text" name="vat_id" class="form-control" value="<?= htmlspecialchars($row['vat_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $is_viewer ? 'readonly' : '' ?>>
                        </div>
                    </div>

                    <br>
                    <div class="d-flex gap-2">
                        <input type="submit" value="<?= $is_viewer ? 'View Only — No Edit Access' : 'Submit' ?>" class="btn btn-primary w-100" <?= $is_viewer ? 'disabled' : '' ?>>
                        <a href="/pages/system/clients.php" class="btn btn-secondary w-100">Back to Partners</a>
                    </div>
                </form>
            </div>
        </div>
        <?php
            endif;
        ?>




<?php
    endif;
    include "../../../build/footer.php";
?>