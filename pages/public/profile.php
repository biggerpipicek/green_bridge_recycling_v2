<?php
    // MICHAEL D. PHILLIPS - UPDATED 05/25/2026
    // SHOW USER PROFILE DATA SECURELY

    require "../../build/auth.php";
    require "../../build/functions.php";

    // Operational Best Practice: If an unauthenticated agent attempts access, 
    // intercept execution immediately to save server processing overhead.
    if (!isset($_SESSION['user'])) {
        header("Location: ../../index.php");
        exit();
    }

    $page_title = "GBR Profile";
    include "../../build/header.php";

    // Escape session data elements to mitigate Cross-Site Scripting (XSS) actions
    $clean_username = htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8');
    $clean_email    = htmlspecialchars($_SESSION['email'] ?? 'No email assigned', ENT_QUOTES, 'UTF-8');
?>

<div class="container fluid py-5">
    <div class="col-12 col-md-8 col-lg-6 mx-auto">
        
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <img src="../../imgs/user.png" alt="Profile avatar picture" class="img-fluid rounded-circle bg-light p-1" style="width: 100px; height: 100px; object-fit: cover;">
                </div>
                <div class="col">
                    <h4 class="fw-bold text-dark mb-0"><?= $clean_username ?></h4>
                    <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i><?= $clean_email ?></p>
                </div>
            </div>

            <hr class="my-4 opacity-10">

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="form-label text-uppercase text-muted fw-semibold small mb-1">Username</label>
                    <p class="fw-medium text-dark mb-0"><?= $clean_username ?></p>
                </div>
                
                <div class="col-auto d-flex justify-content-center">
                    <div class="vr text-secondary opacity-25"></div>
                </div>
                
                <div class="col">
                    <label class="form-label text-uppercase text-muted fw-semibold small mb-1">E-mail Address</label>
                    <p class="fw-medium text-dark mb-0 text-truncate"><?= $clean_email ?></p>
                </div>
            </div>

            <div class="row g-2 pt-2">
                <div class="col-sm-6">
                    <a href="profile/changepassword.php" class="btn btn-outline-primary btn-sm w-100 rounded-3 py-2 fw-medium">
                        <i class="bi bi-shield-lock me-1"></i> Change Password
                    </a>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <a href="profile/logout.php" class="btn btn-danger btn-sm w-100 rounded-3 py-2 fw-medium">
                        <i class="bi bi-box-arrow-right me-1"></i> Log out
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include "../../build/footer.php"; ?>