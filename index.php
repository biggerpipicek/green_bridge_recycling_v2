<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // GREEN BRIDGE RECYCLING V2 - START
    require "build/auth.php";

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

        // VERIFYING IF USER AND PASSWORD MATCH → SET SESSION & COOKIE
        if($user_val && password_verify($pass, $user_val['password'])) {
            $_SESSION['user'] = $user_val['username'];
            $_SESSION['email'] = $user_val['email'];
            $_SESSION['user_id'] = $user_val['id'];

            setcookie('user_login', $user_val['username'], time() + (86400*7), "/");
            

            header("Location: index.php?success=1");
            exit();
        } else {

            header("Location: index.php?fail=1");
            exit();
        }
    }

    include "build/header.php";
?>

    <?php if(!isset($_SESSION['user'])): ?>
    <div class="container-fluid">
        <div class="container-sm">
            <form action="" method="post" class="border border-secondary-subtle rounded p-4">
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
            <h1>Hello, <?php echo $_SESSION['user']; ?></h1>
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