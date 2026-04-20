<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // SHOW USER PROFILE DATA

    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "GBR Profile";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }   


    include "../../build/header.php";
    ?>
    <style>
        .vr {
            width: 0.5px;
            align-self: stretch;
            background-color: #212529;
            opacity: 0.25;
        }
    </style>

    <div class="container-fluid">
        <?php if(isset($_SESSION['user'])): ?>
        <div class="container-sm w-50 border border-secondary-subtle rounded-4 p-4">
            <div class="row">
                <div class="col d-flex align-items-center">
                    <img src="../../imgs/user.png" alt="Profile picture" style="width: 115px; height: 110px;">
                    <div class="together ms-4">
                        <b><?php echo $_SESSION['user']; ?></b><br>
                        <i><?php echo $_SESSION['email']; ?></i>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col">
                    <label for="username" class="form-label">Username</label>
                    <p><?php echo $_SESSION['user']; ?></p>
                </div>
                <div class="col-auto d-flex">
                    <div class="vr"></div>
                </div>
                <div class="col">
                    <label for="email" class="form-label">E-mail</label>
                    <p><?php echo $_SESSION['email']; ?></p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="profile/changepassword.php" class="btn btn-outline-primary">Change Password</a>
                </div>
                <div class="col d-flex justify-content-end">
                    <a href="profile/logout.php" class="btn btn-danger">Log out</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="container-sm w-50 border border-secondary-subtle rounded p-4">
            <h1><b>Whoops!</b></h1>
            <p>You need to be logged in to see Your Profile.</p>
            <a href="../../index.php" class="btn btn-outline-primary">Log in</a>
        </div>
        <?php endif; ?>
    </div>

    <?php
        include "../../build/footer.php";
    ?>