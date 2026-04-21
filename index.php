<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // GREEN BRIDGE RECYCLING V2 - START
    require "build/auth.php";
    require "build/functions.php";

    $page_title = "GBR Home";

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
            <div class="container-sm w-50" style="position: relative; left: -25%;">
                <?php
                    // RECENT/LAST ACTIVITY BY USER
                    $activity_limit = 5;
                    $sql = "SELECT `action`, entity_type, `description` FROM activity_log WHERE entity_id = {$_SESSION['user_id']} LIMIT {$activity_limit}";

                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            //echo $row['action'] . " - " . $row['entity_type'] . " - " . $_SESSION['user'] . " - " . $row['description'] . "<br>";
                            echo "<div class='card'><div class='card-body'><h4 class='card-title'>".$row['action']."</h4><p class='card-text'>".$row['description'].".</p></div></div>&nbsp;";
                        }
                    }

                ?>
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