<?php
    // MICHAEL D. PHILLIPS - 20.04.2026
    // CLIENT LIST - ADD, REMOVE

    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "GBR Clients";
    include "../../build/header.php";

    $client_type = [
        "supplier" => "badge bg-info",
        "customer" => "badge bg-warning"
    ];
    ?>

    <!-- NAVIGATION -->
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-75 mx-auto rounded-3">
        <div class="container-fluid justify-content-between">

            <!-- SEARCH -->
            <form action="" method="get" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search..">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <!-- RIGHT SIDE -->
            <ul class="navbar-nav">

                <!-- SORT DROPDOWN -->
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Sort</a>
                    <ul class="dropdown-menu">
                        <li><a href="?sort=az" class="dropdown-item">A → Z</a></li>
                        <li><a href="?sort=za" class="dropdown-item">Z → A</a></li>
                        <li><a href="?sort=weight_asc" class="dropdown-item">Weight: Low → High</a></li>
                        <li><a href="?sort=weight_desc" class="dropdown-item">Weight: High → Low</a></li>
                        <li><a href="?sort=code_asc" class="dropdown-item">Item Code: Low → High</a></li>
                        <li><a href="?sort=code_desc" class="dropdown-item">Item Code: High → Low</a></li>
                    </ul>
                </li>

                <!-- ACTIONS -->
                <li class="nav-item">
                    <a href="" class="nav-link">Refresh</a>
                </li>
                <li class="nav-item">
                    <a href="add_client.php" class="nav-link">Add</a>
                </li>
                <li class="nav-item">
                    <a href="" class="nav-link">Export</a>
                </li>

            </ul>
        </div>
    </nav>
    <br>

    <!-- TABLE CLIENT LIST -->
    <div class="container-fluid">
        <div class="container-sm w-75">
            <table class="table align-middle text-center">
                <thead>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Contact Info</th>
                    <th>Edit</th>
                </thead>
                <tbody>
                    <!--<tr>
                        <td>Schredder - Wojciech Kania</td>
                        <td><div class="badge bg-info">Supplier</div></td>
                        <td>w.kania@schredder.pl</td>
                    </tr>-->
                    <?php
                        $sql = "SELECT name, type, contact_info FROM partners";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $type = $row['type'];
                                $badge = $client_type[$type] ?? "badge bg-secondary";
                                echo "<tr><td>".$row['name']."</td><td><span class='{$badge}'>".ucfirst($type)."</span></td><td>".$row['contact_info']."</td><td><a href='' class='btn btn-outline-primary'>Edit</a> <a href='' class='btn btn-outline-danger'>Delete</a></td></tr>";
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
    include "../../build/footer.php";
?>