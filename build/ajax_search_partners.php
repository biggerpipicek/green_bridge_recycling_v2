<?php
    // MICHAEL D. PHILLIPS - 20.04.2026
    // AJAX ENDPOINT: live search for partners (used by autocomplete on order forms)
    // GET /build/ajax_search_partners.php?q=guh&type=customer

    require "auth.php";
    require "functions.php";

    header('Content-Type: application/json');

    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not logged in.']);
        exit;
    }

    $q = trim($_GET['q'] ?? '');

    if ($q === '') {
        echo json_encode([]);
        exit;
    }

    // Optional filter, e.g. only customers on a sales order form.
    // Pass type=customer or type=supplier from the JS if you want this restriction;
    // omit it to search all partners.
    $type = $_GET['type'] ?? '';
    $valid_types = ['customer', 'supplier'];

    if (in_array($type, $valid_types, true)) {
        $sql = "SELECT id, name, type, contact_info FROM partners WHERE name LIKE ? AND type = ? ORDER BY name ASC LIMIT 10";
        $stmt = mysqli_prepare($conn, $sql);
        $like = "%{$q}%";
        mysqli_stmt_bind_param($stmt, "ss", $like, $type);
    } else {
        $sql = "SELECT id, name, type, contact_info FROM partners WHERE name LIKE ? ORDER BY name ASC LIMIT 10";
        $stmt = mysqli_prepare($conn, $sql);
        $like = "%{$q}%";
        mysqli_stmt_bind_param($stmt, "s", $like);
    }

    mysqli_stmt_execute($stmt);
    $results = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

    echo json_encode($results);
