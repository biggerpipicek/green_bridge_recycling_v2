<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // DASHBOARD - SHOWING SPECIFIC DATA

    require "../../build/auth.php";
    require "../../build/functions.php";
    include "../../chartphp/lib/inc/chartphp_dist.php";

    $page_title = "GBR Dashboard";
    $extra_css = "../../chartphp/lib/js/chartphp.css";
    $extra_js = [
        "../../chartphp/lib/js/jquery.min.js",
        "../../chartphp/lib/js/chartphp.js"
    ];

    // p - NEW CHART
    $p = new chartphp();
    
    // SELECT CHART TYPE
    $p->chart_type = "area";

    // RENDER CHART
    $out = $p->render('c1');

    include "../../build/header.php";

    $filter = $_GET['filter'] ?? '';
?>

    <div class="container-fluid">
        <div class="container-sm">
            <form action="" method="get">
                <label for="filter" class="form-label">Select</label>
                <select name="filter" class="form-select">
                    <option value="weekly" <?php echo ($filter == "weekly") ? 'selected' : '' ?>>Weekly</option>
                    <option value="monthly" <?php echo ($filter == "monthly") ? 'selected' : '' ?>>Monthly</option>
                    <option value="semi-annually" <?php echo ($filter== "semi-annually") ? 'selected' : '' ?>>Semi-Annualy</option>
                    <option value="annually" <?php echo ($filter == "annually") ? 'selected' : '' ?>>Annually</option>
                    <option value="user-selected" <?php echo ($filter == "user-selected") ? 'selected' : '' ?>>User Selected</option>
                </select>
                <br>
                <input type="submit" value="Submit" class="btn btn-primary">
            </form>
            <br>
            <?php
                // FILTER SWITCH
                switch($filter) {
                    case 'weekly':
                        echo "<hr>";
                        echo "Weekly data";
                        $p->data = array(array(4,8,10,2,5,7,9), array(6,4,9,3,7,3,10));
                        break;

                    case 'monthly':
                        echo "<hr>";
                        echo "Monthly data";
                        $p->data = array(array(9,15,13,7), array(11,9,14,8));
                        break;
                    
                    case 'semi-annually':
                        echo "<hr>";
                        echo "Semi-annually data";
                        $p->data = array(array(7,13,11,5,11,5), array(9,7,12,6,12,6));
                        break;
                    
                    case 'annually':
                        echo "<hr>";
                        echo "Annually data";
                        $p->data = array(array(9,15,13,7,9,15,16,11,13,18,16,11), array(11,9,14,8,11,9,14,8,11,9,14,8));
                        break;

                    case 'user-selected':
                        echo "<hr>";
                        echo "User Selected data";
                        $p->data = array(array(1,2), array(9,3));
                        break;
                    default:
                        break;
                }

                $out = $p->render('c1');
                
                // ECHO CHART
                echo $out;

            ?>
        </div>
    </div>

    <?php
        include "../../build/footer.php";
    ?>