<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // GREEN BRIDGE RECYCLING V2 - START

    // SAVED THE SQL TO THE DATE OF 22/04/2026
    require "build/auth.php";
    require "build/functions.php";

    $page_title = "GBR Home";

    $extra_css = "styles/index-quickaction.css";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }   

    if($_SERVER['REQUEST_METHOD'] === "POST") {

        $user = $_POST['username'];
        $pass = $_POST['password'];

        // SQL & FETCHING DATA
        $stmt = mysqli_prepare($conn, "SELECT id, username, email, password FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user_val = mysqli_fetch_assoc($result);

        // VERIFYING IF USER AND PASSWORD MATCH → SET SESSION & COOKIE THEN MOVE TO INDEX.PHP
        if($user_val && password_verify($pass, $user_val['password'])) {
            $_SESSION['user'] = $user_val['username'];
            $_SESSION['email'] = $user_val['email'];
            $_SESSION['user_id'] = $user_val['id'];

            setcookie('user_login', $user_val['username'], time() + (86400*7), "/");
            
            logActivity($conn, $_SESSION['user_id'], 'login', 'user', $_SESSION['user_id'], "User #{$_SESSION['user_id']} logged in");

            header("Location: index.php?success=1");

            exit();
        } else {

            header("Location: index.php?fail=1");
            logActivity($conn, null, 'login_failed', 'user', 'null', "Failed log in attempt for username: {$user}");
            exit();
        }
    }

    $img_map = [
        "track_and_trace" => "imgs/pointer.png",
        "password" => "imgs/padlock.png",
        "order" => "imgs/package.png",
        "create" => "imgs/package.png",
        "client" => "imgs/person.png",
        "login" => "imgs/person.png",
        "activity_check" => "imgs/person.png",
        "login_failed" => "imgs/person.png",
        "logout" => "imgs/person.png",
        "update" => "imgs/package.png"
    ];

    include "build/header.php";
?>

    <?php if(!isset($_SESSION['user'])): ?>
    <div class="container-fluid">
        <div class="container-sm">
            <form action="" method="post" class="border border-secondary-subtle rounded-4 p-4">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control">
                <br>
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control">
                <br>
                <input type="submit" value="Log in" class="btn btn-primary">
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="container-fluid">
        <div class="container-sm">
            <h1>Hello, <?php echo $_SESSION['user']; ?>!</h1>
            <h2><i>Recent activities,</i></h2>
            <!-- USER'S RECENT ACTIVITIES -->
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
                                    $sql = "SELECT `action`, entity_type, `description`, created_at FROM activity_log WHERE entity_id = {$_SESSION['user_id']} ORDER BY id DESC LIMIT {$activity_limit}";
                                    $result = mysqli_query($conn, $sql);
                                    $total_rows = mysqli_num_rows($result);
                                    $current_row = 0;

                                    if($total_rows > 0) {
                                        while($row = mysqli_fetch_assoc($result)) {
                                            $current_row++;
                                            $action = $row['action'];
                                            $action_icon = $img_map[$action] ?? "imgs/default.png";
                                            $time_ago = time_elapsed_string($row['created_at']); // Placeholder

                                            echo "
                                            <div class='d-flex align-items-center py-3' style='gap: 15px;'>
                                                <div style='background-color: #eef4ff; padding: 10px; border-radius: 8px; display: flex; align-items: center; justify-content: center;'>
                                                    <img src='{$action_icon}' style='width: 20px; height: 20px;' alt='icon'>
                                                </div>

                                                <div class='flex-grow-1'>
                                                    <h6 class='mb-0 text-capitalize' style='font-weight: 600; color: #333;'>
                                                        " . str_replace('_', ' ', $action) . "
                                                    </h6>
                                                    <small class='text-muted'>{$row['description']}</small>
                                                </div>

                                                <div class='text-muted' style='font-size: 0.8rem; white-space: nowrap;'>
                                                    {$time_ago}
                                                </div>
                                            </div>";

                                            if($current_row < $total_rows) {
                                                echo "<hr class='my-0' style='opacity: 0.05;'>";
                                            }
                                        }
                                    } else {
                                        echo "<p class='text-center p-4 text-muted'>No recent activity.</p>";
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
                                <!--<p class="text-muted small">Select an action to get started.</p>-->
                                <?php
                                    // PHP LINKS LIST
                                    $quick_links = [
                                        ['name' => "Track & Track", 'url' => "pages/public/track_trace.php", 'icon' => "imgs/pointer.png"],
                                        ['name' => "Orders", 'url' => "pages/system/orders.php", 'icon' => "imgs/package.png"],
                                        ['name' => "Inventory", 'url' => "pages/system/inventory.php", 'icon' => "imgs/package.png"],
                                        ['name' => "Clients", 'url' => "pages/system/clients.php", 'icon' => "imgs/person.png"],
                                        ['name' => "Tickets", 'url' => "pages/system/tickets.php", 'icon' => "imgs/tickets.png"]
                                    ];

                                    foreach($quick_links as $link) {
                                        echo "
                                        <a href='{$link['url']}' class='btn btn-outline-secondary d-flex align-items-center justify-content-between py-3 px-4 rounded-4 text-dark shadow-sm border-light-subtle action-btn' style='transition: all 0.2s ease'>
                                            <div class='dflex align-items-center' style='gap: 15px;'>
                                                <img src='{$link['icon']}' style='width: 24px; height: 24px; opacity: 0.7;' alt='icon'>
                                                <span style='font-weight: 500; font-size: 1.05rem;'>{$link['name']}</span>
                                            </div>
                                            <i class='bi bi-chevron-right text-muted'></i><span style='font-weight: bold; color: #ccc;'>&gt;</span>
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
    
    <div class="container-sm mt-4 d-flex justify-content-center">
        <br>
        <?php if(isset($_GET['success'])): ?>
                <div class='alert alert-success alert-dismissible w-75'><button type='button' class='btn-close' data-bs-dismiss='alert'></button>Log in successful, <?php echo $_SESSION['user']; ?>!</div>
        <?php endif; ?>

        <?php if(isset($_GET['fail'])): ?>
                <div class='alert alert-danger alert-dismissible w-75'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><b>Whoops!</b><br>Unable to log in. Wrong username or password.</div>
        <?php endif; ?>
    </div>
   
   <?php
        include "build/footer.php";
    ?>