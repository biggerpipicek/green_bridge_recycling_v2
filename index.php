<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // GREEN BRIDGE RECYCLING V2 - START
    require "build/auth.php";

    $page_title = "GBR Home";

    if(session_start() === PHP_SESSION_NONE) {
        session_start();
    }   

    $log_in_system = false;

    if($_SERVER['REQUEST_METHOD'] === "POST") {
        header("Location: index.php?success=1");
        $log_in_system = true;
    }

        include "build/header.php";
?>

    <div class="container-fluid">
        <div class="container-sm">
            <form action="" method="post" class="border border-secondary-subtle rounded p-4">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control">
                <br>
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control">
                <br>
                <input type="submit" value="Log in" class="btn btn-primary">
            </form>
        </div>
    </div>
    
    <div class="container-sm mt-4 d-flex justify-content-center">
        <br>
        <?php if(isset($_GET['success'])): ?>
                <div class='alert alert-success alert-dismissible w-75'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>Log in successful!</div>
        <?php endif; ?>
    </div>
   
   <?php
        include "build/footer.php";
    ?>