<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // SHOW USER PROFILE DATA

    $page_title = "GBR Profile";
    include "../../build/header.php";
    ?>

    <div class="container-fluid">
        <div class="container-md">
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
                <div class="col">
                    <label for="email" class="form-label">E-mail</label>
                    <p>phillips.m@greenbridgerecycling.com</p>
                </div>
            </div>
        </div>
    </div>

    <?php
        include "../../build/footer.php";
    ?>