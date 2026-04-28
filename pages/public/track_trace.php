<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // TRACK & TRACE SYSTEM

    require "../../build/auth.php";
    require "../../build/functions.php";

    $page_title = "GBR Track & Trace";
    include "../../build/header.php";

    // Get the track_id if it exists
    $track_id = $_GET['track_id'] ?? '';
    $is_searching = isset($_GET['track_id']); // Check if the user actually clicked 'Track'

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

<div class="container-fluid">
    <div class="container-sm w-50 border border-secondary-subtle rounded-4 p-4">
        <form action="" method="get">
            <label for="track_id" class="form-label">Track ID</label>
            <input type="text" name="track_id" class="form-control" value="<?php echo htmlspecialchars($track_id); ?>" required>
            <br>
            <button type="submit" class="btn btn-primary">Track</button>
        </form>
    </div>
</div>

<?php 
if($is_searching): 
    if(!empty($track_id)): 
        // Use Prepared Statements to prevent SQL Injection
        $stmt = mysqli_prepare($conn, "SELECT order_no, partner_id, partners.name AS partner_name, order_status, track_id, orders.created_at, price, currency, pallet_no, brutto_w 
                                      FROM orders 
                                      JOIN partners ON orders.partner_id = partners.id 
                                      WHERE track_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $track_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0):
            while($row = mysqli_fetch_assoc($result)):
                $date = date("d M Y", strtotime($row['created_at']));
                $currency = $row['currency'];
                $symbol_currency = $order_currency[$currency] ?? "XXX";
                $o_stat = $row['order_status'];
                $o_badge = $order_type[$o_stat] ?? "badge bg-secondary";
?>
                <br>
                <div class="container-fluid">
                    <div class="container-sm">
                        <div class='container justify-content-center main-container'>
                            <div class='col align-items-center left'>
                                <div class='d-inline-grid' style='grid-auto-flow: column;'>
                                    <img src='../../imgs/package.png' style='width: 50px; height: 50px;'>&nbsp;&nbsp;<h1>Order details</h1>
                                </div>
                                <br>
                                <div class='row'>
                                    <img src='../../imgs/person.png' style='width: 75px; height: 50px;'>
                                    <div class='col'>
                                        <p class='mb-0'>Customer</p>
                                        <h4><?= $row['partner_name'] ?></h4>
                                    </div>
                                </div>
                                <br>
                                <div class='row'>
                                    <img src='../../imgs/list.png' style='width: 75px; height: 50px;'>
                                    <div class='col'>
                                        <p class='mb-0'>Status</p>
                                        <h4><span class='<?= $o_badge ?>'><?= ucfirst($row['order_status']) ?></span></h4>
                                    </div>
                                </div>
                                <br>
                                <div class='row'>
                                    <img src='../../imgs/barcode.png' style='width: 75px; height: 50px;'>
                                    <div class='col'>
                                        <p class='mb-0'>Track ID</p>
                                        <h4 class='text-center' style='background-color: rgba(13,110,253,0.1); border-radius: 5px;'><?= $row['track_id'] ?></h4>
                                    </div>
                                </div>
                                <br>
                                <div class='row'>
                                    <img src='../../imgs/calendar.png' style='width: 75px; height: 50px;'>
                                    <div class='col'>
                                        <p style='margin: auto;'>Created at: <span><b><?= $date ?></b></span></p>
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
                                        <p class='mb-0'>Price</p>
                                        <h4><?= $row['price'] ?> <?= $symbol_currency ?></h4>
                                    </div>
                                </div>
                                <br>
                                <div class='row'>
                                    <img src='../../imgs/weight.png' style='width: 75px; height: 50px;'>
                                    <div class='col'>
                                        <p class='mb-0'>Pallet No.</p>
                                        <h4><?= $row['pallet_no'] ?></h4>
                                    </div>
                                </div>
                                <br>
                                <div class='row'>
                                    <img src='../../imgs/weight.png' style='width: 75px; height: 50px;'>
                                    <div class='col'>
                                        <p class='mb-0'>Brutto Weight</p>
                                        <h4><?= $row['brutto_w'] ?> kg</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<?php 
            endwhile;
            logActivity($conn, $_SESSION['user_id'], 'track_and_trace', 'order', $_SESSION['user_id'], "User #{$_SESSION['user_id']} tracked order {$track_id}");
        else: 
            // TRACK ID PROVIDED BUT NO RESULTS FOUND
            echo "<div class='container-fluid mt-4'><div class='container-sm d-flex justify-content-center'><div class='alert alert-danger alert-dismissible w-75'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><b>Whoops!</b><br>No result found for Track ID: ".htmlspecialchars($track_id)."</div></div></div>";
            logActivity($conn, $_SESSION['user_id'], 'track_and_trace', 'order', $_SESSION['user_id'], "User #{$_SESSION['user_id']} tried invalid ID: {$track_id}");
        endif;

    else: 
        // TRACK ID KEY EXISTS BUT INPUT WAS EMPTY
        echo "<div class='container-fluid mt-4'><div class='container-sm d-flex justify-content-center'><div class='alert alert-warning alert-dismissible w-75'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><b>Notice:</b><br>Please enter a Track ID.</div></div></div>";
    endif;
endif;

include "../../build/footer.php";
?>