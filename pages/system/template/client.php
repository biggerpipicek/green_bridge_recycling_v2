<?php

    // MICHAEL D. PHILLIPS - 27/04/2026
    // CLIENT TEMPLATE PAGE

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    $client_id = $_GET['id'];

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


    include "../../../build/header.php";


    ?>
    <?php if (isset($_SESSION['user'])):
            if(mysqli_num_rows($result) > 0):
        ?>
        <!-- FORM WITH CLIENT DATA - NAME, TYPE, CONTACT INFO -->
        <div class="container-fluid">
            <div class="container-sm d-flex justify-content-center">
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