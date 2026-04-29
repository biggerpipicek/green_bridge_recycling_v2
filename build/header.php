<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // HEADER

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    } 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <?php if(isset($extra_css)): ?>
        <link rel="stylesheet" href="<?= $extra_css;?>">
    <?php endif; ?>
    <title><?php echo $page_title; ?></title>
</head>
<body class="body">
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
        <div class="container-fluid">
            <a href="/green_bridge_recycling_v2/index.php" class="navbar-brand"><b>Home</b></a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav">
                    <li class="nav-item"><a href="/green_bridge_recycling_v2/pages/system/dashboard.php" class="nav-link">Dashboard</a></li>
                    <li class="nav-item"><a href="/green_bridge_recycling_v2/pages/system/orders.php" class="nav-link">Orders</a></li>
                    <li class="nav-item"><a href="/green_bridge_recycling_v2/pages/system/inventory.php" class="nav-link">Inventory</a></li>
                    <li class="nav-item"><a href="/green_bridge_recycling_v2/pages/public/track_trace.php" class="nav-link">Track & Trace</a></li>
                    <li class="nav-item"><a href="/green_bridge_recycling_v2/pages/system/clients.php" class="nav-link">Clients</a></li>                    
                    <li class="nav-item"><a href="/green_bridge_recycling_v2/pages/system/tickets.php" class="nav-link">Tickets</a></li>
                    <li class="nav-item"><a href="/green_bridge_recycling_v2/pages/public/profile.php" class="nav-link">Profile</a></li>
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