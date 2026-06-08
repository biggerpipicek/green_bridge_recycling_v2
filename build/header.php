<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // HEADER

    $current_page = $_SERVER['REQUEST_URI'];

    function nav_link($href, $label) {
        $is_active = (parse_url($href, PHP_URL_PATH) === parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $class = 'nav-link' . ($is_active ? ' active' : '');
        echo "<a href=\"$href\" class=\"$class\">$label</a>";
    }

    if (isset($_SESSION['user_id'])) {
        mysqli_query($conn, "UPDATE users SET last_activity = NOW() WHERE id = " . $_SESSION['user_id']);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <?php 
        if(isset($extra_css) && is_array($extra_css)): 
            foreach($extra_css as $css_file): ?>
                <link rel="stylesheet" href="<?php echo $css_file; ?>">
            <?php endforeach; 
        endif; 
    ?>
    <?php 
        if(isset($extra_js) && is_array($extra_js)): 
            foreach($extra_js as $js_file): ?>
                <script src="<?php echo $js_file; ?>"></script>
            <?php endforeach; 
        endif; ?>
    <title><?php echo $page_title; ?></title>
</head>
<body class="body">
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
        <div class="container-fluid">
            <a href="/green_bridge_recycling_v2/index.php" class="navbar-brand"><b>Home</b></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item"><?php nav_link('/green_bridge_recycling_v2/pages/system/dashboard.php',  'Dashboard');  ?></li>
                    <li class="nav-item"><?php nav_link('/green_bridge_recycling_v2/pages/system/guhring_orders.php',     'Orders');     ?></li>
                    <li class="nav-item"><?php nav_link('/green_bridge_recycling_v2/pages/system/inventory.php',  'Inventory');  ?></li>
                    <li class="nav-item"><?php nav_link('/green_bridge_recycling_v2/pages/public/track_trace.php','Track & Trace'); ?></li>
                    <li class="nav-item"><?php nav_link('/green_bridge_recycling_v2/pages/system/clients.php',    'Partners');    ?></li>
                    <li class="nav-item"><?php nav_link('/green_bridge_recycling_v2/pages/system/tickets.php',    'Tickets');    ?></li>
                    <li class="nav-item"><?php nav_link('/green_bridge_recycling_v2/pages/public/profile.php',    'Profile');    ?></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid mt-4 mb-4">
        <div class="container-sm text-center">
            <h1>Green Bridge Recycling</h1>
            <h2>Internal Company System</h2>
            <h3 class="text-secondary">For Gühring</h3>
        </div>
    </div>