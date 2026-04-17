<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // AUTHORISATION
    define("DB_HOST", "127.0.0.1");
    define("DB_USER", "root");
    define("DB_PASS", "");
    define("DB_NAME", "green_bridge_recycling_v2");

    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if(!$conn) {
        #die("Connection failed: " . mysqli_connect_error());
    }

    #echo "Connected successfully!";

    mysqli_set_charset($conn, "utf8");