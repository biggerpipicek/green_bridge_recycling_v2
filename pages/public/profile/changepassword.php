<?php
    // MICHAEL D. PHILLIPS - 17/04/2026
    // CHANGE PASSWORD

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    $page_title = "GBR Change Password";

    include "../../../build/header.php";

    $msg = "";

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        $new_pass_check = $_POST['new_pass_check'];

        $user_id = $_SESSION['user_id'];

        if($new_pass !== $new_pass_check) {
            $msg = "New passwords do not match!";
        } elseif (strlen($new_pass) < 8) {
            $msg = "Password mush be at least 8 characters.";
        } else {
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($old_pass, $user['password'])) {
            
            // 3. Hash New Password and Update
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            
            mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $msg = "Password updated successfully!";
                logActivity($conn, $_SESSION['user_id'], 'password', 'user', $_SESSION['user_id'], "User #{$_SESSION['user_id']} changed its password");
            } else {
                $msg = "Error updating password: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($update_stmt);
            } else {
                $msg = "Current password is incorrect.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
?>
    <div class="container-fluid">
        <h1 class="text-center">Change password</h1>
        <?php if ($msg): ?>
            <div class="alert"><?php echo $msg; ?></div>
        <?php endif; ?>
        <div class="container-sm d-flex justify-content-center">
            <form action="" method="post" class="w-50">
                <label for="old_pass" class="form-label">Current password</label>
                <input type="password" name="old_pass" class="form-control">
                <label for="new_pass" class="form-label">New password</label>
                <input type="password" name="new_pass" class="form-control">
                <label for="new_pass_check" class="form-label">New password again</label>
                <input type="password" name="new_pass_check" class="form-control">
                <br>
                <input type="submit" value="Submit" class="btn btn-primary">
            </form>
        </div>
    </div>

<?php
    include "../../../build/footer.php";
?>