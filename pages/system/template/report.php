<?php

    // MICHAEL D. PHILLIPS - 16.06.2026
    // REPORT PAGE

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    $page_title = "GBR REPORT ORDER #" . $id;

    logActivity($conn, $_SESSION['user_id'], 'checking', 'ticket', $id, "User #{$_SESSION['user_id']} clicked on Write report for order {$id}");

    include "../../../build/header.php";
?>