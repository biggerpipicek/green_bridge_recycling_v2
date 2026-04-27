<?php

    // MICHAEL D. PHILLIPS - 27/04/2026
    // CLIENT TEMPLATE PAGE

    require "../../../build/auth.php";
    require "../../../build/functions.php";

    $client_id = $_GET['id'];

    $sql = "SELECT id, name, type, contant_info FROM partners";

    $page_title = "GBR Client - {$client_name}";
    include "../../../build/header.php";
    ?>




<?php
    include "../../../build/footer.php";
?>