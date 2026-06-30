<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // UPDATED: session guard + redirect-back support

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // AUTHORISATION
    
    define("DB_HOST", "db.dw357.endora.cz");
    define("DB_USER", "gbrguh_eu");
    define("DB_PASS", "D8BiL3K#?.");
    define("DB_NAME", "gbrguh");
    
    /*
    define("DB_HOST", "127.0.0.1");
    define("DB_USER", "root");
    define("DB_PASS", "");
    define("DB_NAME", "green_bridge_recycling_v2");
    */
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if(!$conn) {
        #die("Connection failed: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, "utf8");

    // --- SESSION GUARD ---
    // Pages that don't require login (whitelist)
    $public_pages = ['/index.php', '/green_bridge_recycling_v2/pages/public/track_trace.php', '/green_bridge_recycling_v2/pages/public/profile.php'];

    $current_uri = strtok($_SERVER['REQUEST_URI'], '?'); // strip query string

    $is_public = false;
    foreach ($public_pages as $p) {
        if (str_ends_with($current_uri, $p)) {
            $is_public = true;
            break;
        }
    }

    if (!$is_public && !isset($_SESSION['user_id'])) {
        // Save the page they wanted so we can redirect back after login
        $redirect_to = $_SERVER['REQUEST_URI'];
        header("Location: /index.php?redirect=" . urlencode($redirect_to));
        exit();
    }