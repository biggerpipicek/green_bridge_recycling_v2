<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // NEW ORDERS - INCOMING/OUTGOING
        $page_title = "GBR Create Order";
        include "../../build/header.php";

        $action = $_GET['action'] ?? '';
    
        if($action == "incoming_orders"):
    ?>
        <div class="container-fuild">
            <div class="container-sm">
                <h1>Incoming Order</h1>
            </div>
        </div>
        
    <?php elseif ($action == "outgoing_orders"): ?>
        <div class="container-fuild">
            <div class="container-sm">
                <h1>Outgoing Order</h1>
            </div>
        </div>
    <?php
        endif;
        if(!isset($_GET['action'])): ?>
        <div class="container-fluid">
            <div class="container-sm">
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>    
                        <h1>Whoops!</h1><br>You didn't select the order type.
                </div>
            </div>
        </div>
        <?php
            endif;
        include "../../build/footer.php";
    ?>
</body>
</html>