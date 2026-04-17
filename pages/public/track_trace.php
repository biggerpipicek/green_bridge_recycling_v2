<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // TRACK & TRACE SYSTEM

    $page_title = "GBR Track & Trace";

    include "../../build/header.php";
    ?>
    <!-- TRACK & TRACE FORM -->
    <div class="container-fluid">
        <div class="container-sm w-50 border border-secondary-subtle rounded-4 p-4">
            <form action="" method="get">
                <label for="track_id" class="form-label">Track ID</label>
                <input type="text" name="track_id" class="form-control">
                <br>
                <button type="submit" class="btn btn-primary">Track</button>
            </form>
        </div>
    </div>

    <!-- CONTAINER FILLED WITH ORDER DATA -->


    <?php
        include "../../build/footer.php";
    ?>
