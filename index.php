<?
    // MICHAEL D. PHILLIPS - 16.04.2026
    // GREEN BRIDGE RECYCLING V2 - START

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GBR</title>
</head>
<body>
    <?php
        include "build/header.php";
    ?>

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

    <?php
        include "build/footer.php";
    ?>
</body>
</html>