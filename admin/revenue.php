<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

$pageTitle = 'Báo cáo doanh thu - Admin';

// Lấy tham số thời gian
$period = $_GET['period'] ?? 'month';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

// Tính toán doanh thu theo thời gian
switch ($period) {
    case 'day':
        $dateCondition = "DATE(o.created_at) = CURDATE()";
        $groupBy = "DATE(o.created_at)";
        break;
    case 'week':
        $dateCondition = "YEARWEEK(o.created_at) = YEARWEEK(CURDATE())";
        $groupBy = "YEARWEEK(o.created_at)";
        break;
    case 'month':
        $dateCondition = "YEAR(o.created_at) = $year AND MONTH(o.created_at) = $month";
        $groupBy = "DATE(o.created_at)";
        break;
    case 'year':
        $dateCondition = "YEAR(o.created_at) = $year";
        $groupBy = "MONTH(o.created_at)";
        break;
    default:
        $dateCondition = "MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
        $groupBy = "DATE(o.created_at)";
}

// Thống kê tổng quan
$totalRevenue = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders o WHERE status = 'delivered' AND $dateCondition")['total'];
$totalOrders = fetchOne("SELECT COUNT(*) as total FROM orders o WHERE status = 'delivered' AND $dateCondition")['total'];
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// Doanh thu theo ngày/tháng
$revenueData = fetchAll("SELECT $groupBy as period, SUM(total_amount) as revenue, COUNT(*) as orders 
                         FROM orders o 
                         WHERE status = 'delivered' AND $dateCondition 
                         GROUP BY $groupBy 
                         ORDER BY period");

// Top sản phẩm bán chạy
$topProducts = fetchAll("SELECT p.name, p.image, SUM(od.quantity) as total_sold, SUM(od.total) as revenue
                         FROM order_details od
                         JOIN products p ON od.product_id = p.id
                         JOIN orders o ON od.order_id = o.id
                         WHERE o.status = 'delivered' AND $dateCondition
                         GROUP BY p.id, p.name, p.image
                         ORDER BY total_sold DESC
                         LIMIT 10");

// Doanh thu theo danh mục
$categoryRevenue = fetchAll("SELECT c.name, COALESCE(SUM(od.total), 0) as revenue
                             FROM categories c
                             LEFT JOIN products p ON c.id = p.category_id
                             LEFT JOIN order_details od ON p.id = od.product_id
                             LEFT JOIN orders o ON od.order_id = o.id AND o.status = 'delivered' AND $dateCondition
                             GROUP BY c.id, c.name
                             ORDER BY revenue DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Báo cáo doanh thu</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exportRevenue()">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </button>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Thời gian</label>
                            <select class="form-control" name="period">
                                <option value="day" <?php echo $period == 'day' ? 'selected' : ''; ?>>Hôm nay</option>
                                <option value="week" <?php echo $period == 'week' ? 'selected' : ''; ?>>Tuần này</option>
                                <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>Tháng</option>
                                <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>Năm</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Năm</label>
                            <select class="form-control" name="year">
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tháng</label>
                            <select class="form-control" name="month">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" 
                                            <?php echo $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                                        Tháng <?php echo $m; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">
                                <i class="fas fa-search"></i> Xem báo cáo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Revenue Statistics -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Tổng doanh thu</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatMoney($totalRevenue); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Tổng đơn hàng</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalOrders; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Giá trị đơn hàng TB</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatMoney($avgOrderValue); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo danh mục</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top sản phẩm bán chạy</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng bán</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/img/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                                     width="50" height="50" class="rounded me-3">
                                                <span><?php echo $product['name']; ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo $product['total_sold']; ?></td>
                                        <td><?php echo formatMoney($product['revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return '"' . $item['period'] . '"'; }, $revenueData)); ?>],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: [<?php echo implode(',', array_column($revenueData, 'revenue')); ?>],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return '"' . $item['name'] . '"'; }, $categoryRevenue)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($categoryRevenue, 'revenue')); ?>],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function exportRevenue() {
            window.open('export_revenue.php?<?php echo http_build_query($_GET); ?>', '_blank');
        }
    </script>
</body>
</html>
