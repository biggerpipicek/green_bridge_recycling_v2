<?php
    // MICHAEL D. PHILLIPS - 17/04/2026
    // LOGOUT

    session_start();
    setcookie(session_name(), '', 100);
    session_unset();
    session_destroy();
    $_SESSION = array();

    echo "Logging out.";
    header("Refresh:2; url=../../../index.php");