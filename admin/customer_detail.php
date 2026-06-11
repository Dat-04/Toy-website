<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$customer = fetchOne("SELECT c.*, a.status as account_status FROM customers c JOIN accounts a ON c.account_id = a.id WHERE c.id = $id");
if (!$customer) {
    echo '<div class="alert alert-danger m-4">Không tìm thấy khách hàng!</div>';
    exit();
}

// Đơn hàng của khách
$orders = fetchAll("SELECT * FROM orders WHERE customer_id = $id ORDER BY created_at DESC");
// Đánh giá của khách
$reviews = fetchAll("SELECT r.*, p.name as product_name FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.customer_id = $id ORDER BY r.created_at DESC");
// Wishlist
$wishlist = fetchAll("SELECT w.*, p.name as product_name FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.customer_id = $id");

$pageTitle = 'Chi tiết khách hàng';
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
            <a href="customers.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">Thông tin khách hàng</div>
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?php echo htmlspecialchars($customer['full_name']); ?></h5>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                            <p class="mb-1"><strong>SĐT:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                            <p class="mb-1"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($customer['address']); ?></p>
                            <p class="mb-1"><strong>Ngày đăng ký:</strong> <?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></p>
                            <p class="mb-1"><strong>Trạng thái:</strong> <span class="badge bg-<?php echo ($customer['account_status'] === 'active') ? 'success' : 'danger'; ?>"><?php echo ($customer['account_status'] === 'active') ? 'Hoạt động' : 'Khóa'; ?></span></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 mb-4">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">Đơn hàng đã đặt</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Ngày đặt</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_code']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo formatMoney($order['total_amount']); ?></td>
                                            <td><span class="badge bg-<?php 
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
                                            </span></td>
                                            <td><a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Xem</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($orders)): ?>
                                        <tr><td colspan="5" class="text-center text-muted">Chưa có đơn hàng</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">Đánh giá đã viết</div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Sản phẩm</th>
                                                    <th>Điểm</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($reviews as $review): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                                                    <td><?php echo intval($review['rating']); ?>/5</td>
                                                    <td><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($reviews)): ?>
                                                <tr><td colspan="3" class="text-center text-muted">Chưa có đánh giá</td></tr>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-warning text-white">Sản phẩm yêu thích</div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Sản phẩm</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($wishlist as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($wishlist)): ?>
                                                <tr><td class="text-center text-muted">Chưa có sản phẩm yêu thích</td></tr>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
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