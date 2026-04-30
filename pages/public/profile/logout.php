<?php
    // MICHAEL D. PHILLIPS - 17/04/2026
    // LOGOUT
    require "../../../build/auth.php";
    require "../../../build/functions.php";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    logActivity($conn, $_SESSION['user_id'], 'logout', 'user', $_SESSION['user_id'], "User #{$_SESSION['user_id']} logged out");

    setcookie(session_name(), '', 100);
    session_unset();
    session_destroy();
    $_SESSION = array();

    echo "Logging out.";
    header("Refresh:2; url=../../../index.php");