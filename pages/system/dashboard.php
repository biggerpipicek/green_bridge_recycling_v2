<?php
    // MICHAEL D. PHILLIPS - 16.04.2026
    // DASHBOARD - SHOWING SPECIFIC DATA

    require "../../build/auth.php";
    require "../../build/functions.php";
    include "../../chartphp/lib/inc/chartphp_dist.php";

    $page_title = "GBR Dashboard";
    $extra_css = [
        "../../chartphp/lib/js/chartphp.css",
        "../../styles/dashboard.css"
    ];
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

    // 1. Get Total Orders All Time
    $total_sql = "SELECT COUNT(*) as count FROM orders";
    $total_res = mysqli_fetch_assoc(mysqli_query($conn, $total_sql));

    // 2. Get Outgoing Orders this month (Assume status = 'outgoing')
    $month_sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'outgoing' AND MONTH(created_at) = MONTH(CURRENT_DATE())";
    $month_res = mysqli_fetch_assoc(mysqli_query($conn, $month_sql));

    // 3. Pending Approvals
    $pending_sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'not_approved'";
    $pending_res = mysqli_fetch_assoc(mysqli_query($conn, $pending_sql));

    // 4. Total Value (Assuming you have a 'price' column)
    $value_sql = "SELECT SUM(price) as total FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE())";
    $value_res = mysqli_fetch_assoc(mysqli_query($conn, $value_sql));
?>

    <div class="container-fluid px-4 py-4">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-cart text-primary fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Orders</small>
                            <h4 class="fw-bold mb-0"><?= number_format($total_res['count']) ?></h4>
                            <small class="text-muted" style="font-size: 0.75rem;">All time</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-arrow-right-square text-success fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Outgoing Orders</small>
                            <h4 class="fw-bold mb-0"><?= $month_res['count'] ?></h4>
                            <small class="text-success" style="font-size: 0.75rem;">↑ 20% This month</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                            <i class="bi bi-clock text-warning fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Pending Approval</small>
                            <h4 class="fw-bold mb-0"><?= $pending_res['count'] ?></h4>
                            <small class="text-warning" style="font-size: 0.75rem;">Requires action</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-purple bg-opacity-10 p-3 me-3" style="background-color: #f3e5f5;">
                            <i class="bi bi-wallet2 fs-4" style="color: #8e24aa;"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Value</small>
                            <h4 class="fw-bold mb-0"><?= number_format($value_res['total'] ?? 0, 2) ?></h4>
                            <small class="text-success" style="font-size: 0.75rem;">↑ 15% (EUR)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <h6 class="fw-bold mb-4">Orders Overview</h6>
                <div style="height: 200px; background: #fafafa;" class="rounded d-flex align-items-center justify-content-center text-muted">
                    Chart Area
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <h6 class="fw-bold mb-3">Recent Outgoing Orders</h6>
                <table class="table table-sm table-borderless align-middle" style="font-size: 0.85rem;">
                    <thead class="text-muted">
                        <tr><th>Order No.</th><th>Customer</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>GBR-out-2026-00002</td>
                            <td>Shredder</td>
                            <td><span class="badge bg-danger rounded-pill">Not approved</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4 p-3">
                <h6 class="fw-bold mb-3">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm text-start py-2"><i class="bi bi-plus"></i> Add Outgoing Order</button>
                    <button class="btn btn-outline-primary btn-sm text-start py-2"><i class="bi bi-plus"></i> Add Incoming Order</button>
                    <button class="btn btn-outline-primary btn-sm text-start py-2"><i class="bi bi-file-earmark-arrow-up"></i> Upload Document</button>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm rounded-4 p-3">
                 <h6 class="fw-bold mb-3">System Activity</h6>
                 </div>
        </div>
    </div>
    <?php
        include "../../build/footer.php";
    ?>