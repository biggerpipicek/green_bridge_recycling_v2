<?php

    // MICHAEL D. PHILLIPS - 27/04/2026
    // CLIENT TEMPLATE PAGE

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    $client_id = (int)$_GET['id']; // CASTED INT FOR SAFETY

    $sql = "SELECT id, name, type, contact_info FROM partners WHERE id = $client_id";

    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $client_name = $row['name'];
        $page_title = "GBR Client - {$client_name}";
    }

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    } 

    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $sub_data = [
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'contact_info' => $_POST['con_info']
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
            $stmt = mysqli_prepare($conn, "UPDATE partners SET name=?, type=?, contact_info=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssi", $_POST['name'], $_POST['type'], $_POST['con_info'], $client_id);

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

                <form action="" method="post" class="border rounded-4 w-50 p-4">
                    <label for="name" class="form-label">Client name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" required>
                    <label for="type" class="form-label">Client type</label>
                    <select name="type" class="form-select" required>
                        <option value="" disabled>Select a type</option>
                        <option value="customer" <?php echo ($row['type'] == "customer") ? 'selected' : '' ?>>Customer</option>
                        <option value="supplier" <?php echo ($row['type'] == "supplier") ? 'selected' : '' ?>>Supplier</option>
                    </select>
                    <label for="con_info" class="form-label">Contact info</label>
                    <input type="email" name="con_info" class="form-control" value="<?php echo $row['contact_info']; ?>" required>
                    <br>
                    <input type="submit" value="Submit" class="btn btn-primary">
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