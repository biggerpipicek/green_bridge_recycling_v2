<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // DASHBOARD - SHOWING SPECIFIC DATA

    require "../../build/auth.php";
    require "../../build/functions.php";
    $page_title = "GBR Dashboard";
    include "../../build/header.php";

    // CASE SWITCHES
    $filter = $_GET['filter'] ?? 'monthly';
    switch($filter) {
        case 'weekly':
            break;

        case 'monthly':
            break;
        
        case 'semi-annually':
            break;
        
        case 'annually':
            break;

        case 'user-selected':
            break;
    }
    ?>

    <div class="container-fluid">
        <div class="container-sm">
            <form action="" method="get">
                <label for="filter" class="form-label">Select</label>
                <select name="filter" class="form-select">
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="semi-annually">Semi-Annualy</option>
                    <option value="annually">Annually</option>
                    <option value="user-selected">User Selected</option>
                </select>
            </form>
        </div>
    </div>

    <?php
        include "../../build/footer.php";
    ?>