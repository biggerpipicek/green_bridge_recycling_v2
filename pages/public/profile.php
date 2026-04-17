<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // SHOW USER PROFILE DATA

    $page_title = "GBR Profile";
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
        <div class="container-sm w-50 border border-secondary-subtle rounded p-4">
            <div class="row">
                <div class="col d-flex align-items-center">
                    <img src="../../imgs/user.png" alt="Profile picture" style="width: 115px; height: 110px;">
                    <div class="together ms-4">
                        <b>test_user</b><br>
                        <i>phillips.m@greenbridgerecycling.com</i>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col">
                    <label for="username" class="form-label">Username</label>
                    <p>test_user</p>
                </div>
                <div class="col-auto d-flex">
                    <div class="vr"></div>
                </div>
                <div class="col">
                    <label for="email" class="form-label">E-mail</label>
                    <p>phillips.m@greenbridgerecycling.com</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="" class="btn btn-outline-primary">Change Password</a>
                </div>
                <div class="col d-flex justify-content-end">
                    <a href="" class="btn btn-danger">Log out</a>
                </div>
            </div>
        </div>
    </div>

    <?php
        include "../../build/footer.php";
    ?>