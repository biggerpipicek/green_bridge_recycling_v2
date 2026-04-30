<?php
    // MICAHEL D. PHILLIPS - 30/04/2026
    // ACTIVITY PAGE
    require "build/auth.php";
    require "build/functions.php";

    $page_title = "GBR Home";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['user'];

    include "build/header.php";

    logActivity($conn, $_SESSION['user_id'], 'activity_check', 'user', $_SESSION['user_id'], "User #{$_SESSION['user_id']} checked all his activities");

    echo $user_id . "<br>". $username;
?>

    <!-- DYNAMICALLY DISPLAY ALL ACTIVITIES USER DID -->

<?php
    include "build/footer.php";
?>