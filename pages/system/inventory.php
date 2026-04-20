<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // INVENTORY SYSTEM

    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "GBR Inventory";
    include "../../build/header.php";
    ?>

    <!-- NAVIGATION -->
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-50 mx-auto rounded-3">
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
                    <a href="add_inventory.php" class="nav-link">Add</a>
                </li>
                <li class="nav-item">
                    <a href="" class="nav-link">Export</a>
                </li>

            </ul>
        </div>
    </nav>
    <br>
    <!-- TABLE WITH ITEM LIST -->
    <div class="container-fluid">
        <div class="container-sm w-50 l-25">
            <table class="table align-middle text-center">
                <thead>
                    <th>Item code</th>
                    <th>Item name</th>
                    <th>In Stock weight</th>
                </thead>
                <tbody>
                    <!--<tr>
                        <td>7401</td>
                        <td>End mills</td>
                        <td>150 kg</td>
                    </tr>
                    <tr>
                        <td>7402</td>
                        <td>Inserts</td>
                        <td>1759 kg</td>
                    </tr>
                    <tr>
                        <td>7403</td>
                        <td>Pieces</td>
                        <td>351 kg</td>
                    </tr>-->
                    <?php
                    
                        $sql = "SELECT item_code, name from materials";
                        $result = mysqli_query($conn, $sql);

                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr><td>".$row['item_code']."</td><td>".$row['name']."</td><td>?</td></tr>";
                            }
                        }

                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- 5 LAST ADDED WEIGHTS -->

    <?php
        include "../../build/footer.php";
    ?>