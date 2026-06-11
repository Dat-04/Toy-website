<?php
$pageTitle = 'Đơn hàng của tôi';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$customer_id = $_SESSION['customer_id'];
$status = $_GET['status'] ?? 'all';

// Xử lý hủy đơn hàng
if ($_POST['action'] ?? '' == 'cancel_order') {
    $order_id = (int)$_POST['order_id'];
    executeQuery("UPDATE orders SET status = 'cancelled' WHERE id = ? AND customer_id = ? AND status = 'pending'", 
                 [$order_id, $customer_id]);
    setMessage('Đã hủy đơn hàng thành công!', 'success');
}

// Lấy đơn hàng theo trạng thái
$whereClause = "WHERE customer_id = ?";
$params = [$customer_id];

if ($status != 'all') {
    $whereClause .= " AND status = ?";
    $params[] = $status;
}

$orders = fetchAll("SELECT * FROM orders $whereClause ORDER BY created_at DESC", $params);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Đơn hàng của tôi</h5>
                </div>
                <div class="card-body">
                    <!-- Tabs trạng thái -->
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status == 'all' ? 'active' : ''; ?>" 
                               href="?status=all">Tất cả</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status == 'pending' ? 'active' : ''; ?>" 
                               href="?status=pending">Chờ xác nhận</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status == 'confirmed' ? 'active' : ''; ?>" 
                               href="?status=confirmed">Đã xác nhận</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status == 'shipping' ? 'active' : ''; ?>" 
                               href="?status=shipping">Đang giao hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status == 'delivered' ? 'active' : ''; ?>" 
                               href="?status=delivered">Đã giao</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status == 'cancelled' ? 'active' : ''; ?>" 
                               href="?status=cancelled">Đã hủy</a>
                        </li>
                    </ul>
                    
                    <!-- Danh sách đơn hàng -->
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có đơn hàng nào</p>
                            <a href="../products.php" class="btn btn-primary">MUA SẮM NGAY</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item border rounded p-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6>Đơn hàng #<?php echo $order['order_code']; ?></h6>
                                        <p class="text-muted mb-1">
                                            Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </p>
                                        <p class="mb-1">
                                            Tổng tiền: <strong class="text-primary"><?php echo formatMoney($order['total_amount']); ?></strong>
                                        </p>
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
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> Chi tiết</a>
                                            <?php if ($order['status'] == 'pending'): ?>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                                                    <input type="hidden" name="action" value="cancel_order">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-times"></i> Hủy</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($order['status'] == 'delivered'): ?>
                                                <a href="review_product.php?order_id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-outline-success btn-sm"><i class="fas fa-star"></i> Đánh giá</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
