<?php
    // MICHAEL D. PHILLIPS - UPDATED WITH SECURITY IMPROVEMENTS
    // GREEN BRIDGE RECYCLING V2 - START

    require "build/auth.php";
    require "build/functions.php";

    // Ensure session is active if not initialized in includes
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $page_title = "GBR Home";
    $extra_css = ["styles/index-quickaction.css"];

    if($_SERVER['REQUEST_METHOD'] === "POST") {

        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        // SQL & FETCHING DATA
        $stmt = mysqli_prepare($conn, "SELECT id, username, email, password FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user_val = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // VERIFYING IF USER AND PASSWORD MATCH
        if($user_val && password_verify($pass, $user_val['password'])) {
            
            // Mitigate Session Fixation attacks
            session_regenerate_id(true);

            $_SESSION['user'] = $user_val['username'];
            $_SESSION['email'] = $user_val['email'];
            $_SESSION['user_id'] = $user_val['id'];

            // Establish secure cookie constraints
            setcookie('user_login', $user_val['username'], [
                'expires' => time() + (86400 * 7),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            logActivity($conn, $_SESSION['user_id'], 'login', 'user', $_SESSION['user_id'], "User #{$_SESSION['user_id']} logged in");

            header("Location: /green_bridge_recycling_v2/index.php?success=1");
            exit();
        } else {
            logActivity($conn, null, 'login_failed', 'user', 'null', "Failed log in attempt for username: {$user}");
            header("Location: /green_bridge_recycling_v2/index.php?fail=1");
            exit();
        }
    }

    $img_map = [
        "track_and_trace" => "imgs/pointer.png",
        "password"        => "imgs/padlock.png",
        "order"           => "imgs/package.png",
        "create"          => "imgs/package.png",
        "client"          => "imgs/person.png",
        "login"           => "imgs/person.png",
        "activity_check"  => "imgs/person.png",
        "login_failed"    => "imgs/person.png",
        "logout"          => "imgs/person.png",
        "update"          => "imgs/package.png"
    ];

    include "build/header.php";
?>
    <div class="container-sm mt-4 d-flex justify-content-center">
        <?php if(isset($_GET['success']) && isset($_SESSION['user'])): ?>
            <div class='alert alert-success alert-dismissible w-75'>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                Log in successful, <?= htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8') ?>!
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['fail'])): ?>
            <div class='alert alert-danger alert-dismissible w-75'>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                <b>Whoops!</b><br>Unable to log in. Wrong username or password.
            </div>
        <?php endif; ?>
    </div>

    <?php if(!isset($_SESSION['user'])): ?>
    <div class="container-fluid">
        <div class="container-sm">
            <form action="" method="post" class="border border-secondary-subtle rounded-4 p-4">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
                <br>
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <br>
                <input type="submit" value="Log in" class="btn btn-primary">
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="container-fluid">
        <div class="container-sm">
            <div class="card border rounded-4 p-4 shadow-sm text-center">
                <h1>Hello, <?= htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8') ?>!</h1>
                <h4 class="text-muted"><span>Recent activities,</span></h4>
            </div>
            
            <div class="container mt-4">
                <div class="row">
                    
                    <div class="col-md-7">
                        <div class="card border rounded-4 p-2 shadow-sm">
                            <section class="d-flex align-items-center p-3" style="gap: 10px;">
                                <img src="imgs/clock.png" alt="Clock" style="width: 24px; height: 24px;">
                                <h4 class="mb-0 flex-grow-1" style="font-size: 1.1rem; font-weight: 600; color: #333;">Recent activity</h4>
                                <a href="activity.php" style="text-decoration: none; font-size: 0.9rem;">View all activity</a>
                            </section>
                            
                            <hr class="my-0 mx-3" style="opacity: 0.1;">

                            <div class="px-3">
                                <?php
                                    $activity_limit = 5;
                                    // Refactored to parameterize the logged-in user identification check safely
                                    $activity_sql = "SELECT `action`, entity_type, `description`, created_at FROM activity_log WHERE entity_id = ? ORDER BY id DESC LIMIT ?";
                                    $act_stmt = mysqli_prepare($conn, $activity_sql);
                                    
                                    if ($act_stmt) {
                                        mysqli_stmt_bind_param($act_stmt, "ii", $_SESSION['user_id'], $activity_limit);
                                        mysqli_stmt_execute($act_stmt);
                                        $result = mysqli_stmt_get_result($act_stmt);
                                        $total_rows = mysqli_num_rows($result);
                                        $current_row = 0;

                                        if($total_rows > 0) {
                                            while($row = mysqli_fetch_assoc($result)) {
                                                $current_row++;
                                                $action = $row['action'];
                                                $action_icon = $img_map[$action] ?? "imgs/default.png";
                                                $time_ago = time_elapsed_string($row['created_at']); // Setup Placeholder
                                                
                                                // Pre-escaping variables safely ahead of layout generation blocks
                                                $clean_action = htmlspecialchars(str_replace('_', ' ', $action), ENT_QUOTES, 'UTF-8');
                                                $clean_desc   = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                                                $clean_time   = htmlspecialchars($time_ago, ENT_QUOTES, 'UTF-8');

                                                echo "
                                                <div class='d-flex align-items-center py-3' style='gap: 15px;'>
                                                    <div style='background-color: #eef4ff; padding: 10px; border-radius: 8px; display: flex; align-items: center; justify-content: center;'>
                                                        <img src='{$action_icon}' style='width: 20px; height: 20px;' alt='icon'>
                                                    </div>

                                                    <div class='flex-grow-1'>
                                                        <h6 class='mb-0 text-capitalize' style='font-weight: 600; color: #333;'>
                                                            {$clean_action}
                                                        </h6>
                                                        <small class='text-muted'>{$clean_desc}</small>
                                                    </div>

                                                    <div class='text-muted' style='font-size: 0.8rem; white-space: nowrap;'>
                                                        {$clean_time}
                                                    </div>
                                                </div>";

                                                if($current_row < $total_rows) {
                                                    echo "<hr class='my-0' style='opacity: 0.05;'>";
                                                }
                                            }
                                        } else {
                                            echo "<p class='text-center p-4 text-muted'>No recent activity.</p>";
                                        }
                                        mysqli_stmt_close($act_stmt);
                                    }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card border rounded-4 p-2 shadow-sm">
                            <section class="d-flex align-items-center p-3" style="gap: 10px;">
                                <img src="imgs/thunder.png" alt="Quick Actions" style="width: 24px; height: 24px;">
                                <h4 class="mb-0" style="font-size: 1.1rem; font-weight: 600; color: #333;">Quick actions</h4>
                            </section>
                            <div class="p-3 d-flex flex-column" style="gap: 12px;">
                                <?php
                                    // Fixed data object name typo ("Track & Track" -> "Track & Trace")
                                    $quick_links = [
                                        ['name' => "Track & Trace", 'url' => "pages/public/track_trace.php", 'icon' => "imgs/pointer.png"],
                                        ['name' => "Orders",         'url' => "pages/system/guhring_orders.php",      'icon' => "imgs/package.png"],
                                        ['name' => "Inventory",      'url' => "pages/system/inventory.php",   'icon' => "imgs/package.png"],
                                        ['name' => "Clients",        'url' => "pages/system/clients.php",     'icon' => "imgs/person.png"],
                                        ['name' => "Tickets",        'url' => "pages/system/tickets.php",     'icon' => "imgs/tickets.png"]
                                    ];

                                    foreach($quick_links as $link) {
                                        $link_name = htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8');
                                        $link_url  = htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8');
                                        $link_icon = htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8');

                                        echo "
                                        <a href='{$link_url}' class='btn btn-outline-secondary d-flex align-items-center justify-content-between py-3 px-4 rounded-4 text-dark shadow-sm border-light-subtle action-btn' style='transition: all 0.2s ease'>
                                            <div class='d-flex align-items-center' style='gap: 15px;'>
                                                <img src='{$link_icon}' style='width: 24px; height: 24px; opacity: 0.7;' alt='icon'>
                                                <span style='font-weight: 500; font-size: 1.05rem;'>{$link_name}</span>
                                            </div>
                                            <span style='font-weight: bold; color: #ccc;'>&gt;</span>
                                        </a>";
                                    }
                                ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
   
<?php include "build/footer.php"; ?>