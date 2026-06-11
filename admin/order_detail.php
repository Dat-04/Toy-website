<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = fetchOne("SELECT o.*, c.full_name, c.email, c.phone, c.address FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ?", [$order_id]);
if (!$order) {
    echo '<div class="alert alert-danger m-4">Không tìm thấy đơn hàng!</div>';
    exit();
}

// Lấy chi tiết sản phẩm trong đơn hàng
$order_items = fetchAll("SELECT od.*, p.name, p.image FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?", [$order_id]);

$pageTitle = 'Chi tiết đơn hàng';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/header.php'; ?>
        <div class="container-fluid py-4">
            <a href="orders.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
            <h2 class="mb-4">Chi tiết đơn hàng #<?php echo htmlspecialchars($order['order_code']); ?></h2>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-primary text-white">Thông tin khách hàng</div>
                        <div class="card-body">
                            <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                            <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-info text-white">Thông tin đơn hàng</div>
                        <div class="card-body">
                            <p><strong>Trạng thái:</strong> <span class="badge bg-<?php 
                                echo match($order['status']) {
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'shipping' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            ?>"><?php 
                                echo match($order['status']) {
                                    'pending' => 'Chờ xác nhận',
                                    'confirmed' => 'Đã xác nhận',
                                    'shipping' => 'Đang giao hàng',
                                    'delivered' => 'Đã giao',
                                    'cancelled' => 'Đã hủy',
                                    default => 'Không xác định'
                                };
                            ?></span></p>
                            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                            <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                            <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">Danh sách sản phẩm</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><img src="../assets/img/<?php echo $item['image'] ?: 'default.jpg'; ?>" width="60" class="rounded"></td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo formatMoney($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatMoney($item['price'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mb-4">
                <div class="card" style="min-width: 300px;">
                    <div class="card-body">
                        <h5 class="mb-3">Tổng tiền đơn hàng</h5>
                        <div class="h4 text-danger fw-bold"><?php echo formatMoney($order['total_amount']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html> 