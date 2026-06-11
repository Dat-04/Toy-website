<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Dashboard - Admin';

// Thống kê tổng quan
$totalProducts = fetchOne("SELECT COUNT(*) as total FROM products")['total'];
$totalOrders = fetchOne("SELECT COUNT(*) as total FROM orders")['total'];
$totalCustomers = fetchOne("SELECT COUNT(*) as total FROM customers")['total'];
$totalRevenue = fetchOne("SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'")['total'] ?? 0;

// Đơn hàng gần đây
$recentOrders = fetchAll("SELECT o.*, c.full_name as customer_name 
                          FROM orders o 
                          LEFT JOIN customers c ON o.customer_id = c.id 
                          ORDER BY o.created_at DESC LIMIT 10");

// Thông báo mới
$notifications = fetchAll("SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");

// Lấy dữ liệu doanh thu 12 tháng trong năm hiện tại
$year = date('Y');
$monthlyRevenue = array_fill(1, 12, 0);
$rows = fetchAll("SELECT MONTH(created_at) as m, SUM(total_amount) as revenue FROM orders WHERE status = 'delivered' AND YEAR(created_at) = $year GROUP BY m");
foreach ($rows as $row) {
    $monthlyRevenue[(int)$row['m']] = (int)$row['revenue'];
}
$revenueLabels = array_map(function($m) { return 'Tháng ' . $m; }, range(1, 12));
$revenueData = array_values($monthlyRevenue);
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
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-toy-brick"></i> ToyShop Admin</h3>
            </div>
            
            <ul class="list-unstyled components">
                <li class="active">
                    <a href="index.php"><i class="fas fa-tachometer-alt"></i> Tổng quan</a>
                </li>
                <li>
                    <a href="interface.php"><i class="fas fa-paint-brush"></i> Giao diện</a>
                </li>
                <li>
                    <a href="products.php"><i class="fas fa-box"></i> Sản phẩm</a>
                </li>
                <li>
                    <a href="categories.php"><i class="fas fa-tags"></i> Danh mục</a>
                </li>
                <li>
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> Đơn hàng</a>
                </li>
                <li>
                    <a href="customers.php"><i class="fas fa-users"></i> Khách hàng</a>
                </li>
                <li>
                    <a href="discounts.php"><i class="fas fa-tags"></i> Mã giảm giá</a> 
                <li>
                    <a href="news.php"><i class="fas fa-newspaper"></i> Tin tức</a>
                </li>
                <li>
                    <a href="reviews.php"><i class="fas fa-star"></i> Đánh giá</a>
                </li>
                <li>
                    <a href="revenue.php"><i class="fas fa-chart-line"></i> Doanh thu</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center">
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <?php if (count($notifications) > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo count($notifications); ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php foreach ($notifications as $notification): ?>
                                    <li><a class="dropdown-item" href="#"><?php echo $notification['title']; ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Chat -->
                        <button class="btn btn-outline-success me-3" onclick="openAdminChat()">
                            <i class="fas fa-comments"></i>
                        </button>
                        
                        <!-- User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> Administrator
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Hồ sơ</a></li>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid">
                <h1 class="h3 mb-4">Dashboard</h1>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="color: black; !important;">
                                            Tổng sản phẩm</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalProducts; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-box fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
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

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Khách hàng</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCustomers; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Doanh thu</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatMoney($totalRevenue); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                <h6 class="m-0 font-weight-bold text-primary" style="color: white;">Sản phẩm bán chạy</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $topProducts = fetchAll("SELECT p.name, SUM(od.quantity) as total_sold 
                                                        FROM products p 
                                                        JOIN order_details od ON p.id = od.product_id 
                                                        JOIN orders o ON od.order_id = o.id 
                                                        WHERE o.status = 'delivered' 
                                                        GROUP BY p.id 
                                                        ORDER BY total_sold DESC 
                                                        LIMIT 5");
                                foreach ($topProducts as $product):
                                ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-dark fw-semibold"><?php echo $product['name']; ?></span>
                                        <span class="badge bg-primary"><?php echo $product['total_sold']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Đơn hàng gần đây</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><?php echo $order['order_code']; ?></td>
                                            <td><?php echo $order['customer_name']; ?></td>
                                            <td><?php echo formatMoney($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($order['status']) {
                                                        'pending' => 'warning',
                                                        'confirmed' => 'info',
                                                        'shipping' => 'primary',
                                                        'delivered' => 'success',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php 
                                                    echo match($order['status']) {
                                                        'pending' => 'Chờ xác nhận',
                                                        'confirmed' => 'Đã xác nhận',
                                                        'shipping' => 'Đang giao hàng',
                                                        'delivered' => 'Đã giao',
                                                        'cancelled' => 'Đã hủy',
                                                        default => 'Không xác định'
                                                    };
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Xem</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Widget -->
    <div id="adminChatWidget" class="admin-chat-widget">
        <div class="chat-header" onclick="toggleAdminChat()">
            <i class="fas fa-comments"></i> Chat với khách hàng
            <i class="fas fa-times chat-close"></i>
        </div>
        <div class="chat-body">
            <div id="adminChatMessages" class="chat-messages"></div>
            <div class="chat-input">
                <input type="text" id="adminChatInput" placeholder="Nhập tin nhắn...">
                <button onclick="sendAdminMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($revenueLabels); ?>,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: <?php echo json_encode($revenueData); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
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
    </script>
</body>
</html>
