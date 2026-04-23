<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // TRACK & TRACE SYSTEM

    require "../../build/auth.php";
    require "../../build/functions.php";

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    } 

    $page_title = "GBR Track & Trace";

    include "../../build/header.php";

    $track_id = $_GET['track_id'] ?? '';

    $order_type = [
        "created" => "badge bg-danger",
        "received" => "badge bg-warning",
        "in process" => "badge bg-info",
        "completed" => "badge bg-success",
        "cancelled" => "badge bg-danger"
    ];

    $order_currency = [
        "EUR" => "€",
        "USD" => "$",
        "YEN" => "¥",
        "ZL" => "zł",
        "CZK" => "Kč"
    ];
    ?>
    <link rel="stylesheet" href="../../styles/track_trace.css">
    <!-- TRACK & TRACE FORM -->
    <div class="container-fluid">
        <div class="container-sm w-50 border border-secondary-subtle rounded-4 p-4">
            <form action="" method="get">
                <label for="track_id" class="form-label">Track ID</label>
                <input type="text" name="track_id" class="form-control" value="<?php if(isset($track_id)): echo $track_id; ?>">
                <br>
                <button type="submit" class="btn btn-primary">Track</button>
            </form>
        </div>
    </div>

    <!-- CONTAINER FILLED WITH ORDER DATA -->
    <?php if(!empty(isset($track_id))): ?>
        <br>
        <div class="container-fluid">
            <div class="container-sm">
                <?php
                    $sql = "SELECT order_no, partner_id, partners.name AS partner_name, order_status, track_id, orders.created_at, price, currency, pallet_no, brutto_w FROM orders JOIN partners ON orders.partner_id = partners.id WHERE track_id = '$track_id'";
                    $result = mysqli_query($conn, $sql);

                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $date = date("d M Y", strtotime($row['created_at']));

                            $currency = $row['currency'];
                            $symbol_currency = $order_currency[$currency] ?? "XXX";

                            $o_stat = $row['order_status'];
                            $o_badge = $order_type[$o_stat] ?? "badge bg-secondary";
                            echo "<div class='container justify-content-center main-container'>
                                    <div class='col align-items-center left'>
                                        <div class='d-inline-grid' style='grid-auto-flow: column;'>
                                            <img src='../../imgs/package.png' style='width: 50px; height: 50px;'>&nbsp;&nbsp;<h1>Order details</h1>
                                        </div>
                                        <br>
                                        <div class='row'>
                                            <img src='../../imgs/person.png' style='width: 75px; height: 50px;'>
                                            <div class='col'>
                                                <p>Customer</p>
                                                <p>{$row['partner_name']}</p>
                                            </div>
                                        </div>
                                        <br>
                                        <div class='row'>
                                            <img src='../../imgs/list.png' style='width: 75px; height: 50px;'>
                                            <div class='col'>
                                                <p>Status</p>
                                                <span class='{$o_badge}'>{$row['order_status']}</span>                                            
                                            </div>
                                        </div>
                                        <br>
                                        <div class='row'>
                                            <img src='../../imgs/barcode.png' style='width: 75px; height: 50px;'>
                                            <div class='col'>
                                                <p>Track ID</p>
                                                <p>{$row['track_id']}</p>
                                            </div>
                                        </div>
                                        <br>
                                        <div class='row'>
                                            <img src='../../imgs/calendar.png' style='width: 75px; height: 50px;'>
                                            <div class='col'>
                                                <p>Created at: <span>{$date}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                            <div class='ver-line'></div>
                                        <div class='col align-items-center right'>
                                            <div class='d-inline-grid' style='grid-auto-flow: column;'>
                                                <img src='../../imgs/truck.png' style='width: 50px; height: 50px;'>&nbsp;&nbsp;<h1>Shipment info</h1>
                                            </div>
                                            <br>
                                            <div class='row'>
                                                <img src='../../imgs/dollar.png' style='width: 75px; height: 50px;'>
                                                <div class='col'>
                                                    <p>Price</p>
                                                    <p>{$row['price']} {$symbol_currency}</p>
                                                </div>
                                            </div>
                                            <br>
                                            <div class='row'>
                                                <img src='../../imgs/weight.png' style='width: 75px; height: 50px;'>
                                                <div class='col'>
                                                    <p>Pallet No.</p>
                                                    <p>{$row['pallet_no']}</p>
                                                </div>
                                            </div>
                                            <br>
                                            <div class='row'>
                                                <img src='../../imgs/weight.png' style='width: 75px; height: 50px;'>
                                                <div class='col'>
                                                    <p>Brutto Weight</p>
                                                    <p>{$row['brutto_w']}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>";
                        }
                    }

                    logActivity($conn, $_SESSION['user_id'], 'track_and_trace', 'order', $_SESSION['user_id'], "User #{$_SESSION['user_id']} tracked order {$track_id}");
                ?>
            </div>
        </div>
    <?php
        endif;
        endif;
        include "../../build/footer.php";
    ?>
