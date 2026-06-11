<?php
$pageTitle = 'Chi tiết đơn hàng';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$customer_id = $_SESSION['customer_id'];
$order_id = (int)$_GET['id'];

// Lấy thông tin đơn hàng
$order = fetchOne("SELECT * FROM orders WHERE id = ? AND customer_id = ?", [$order_id, $customer_id]);

if (!$order) {
    setMessage('Đơn hàng không tồn tại!', 'danger');
    redirect('my_orders.php');
}

// Lấy chi tiết đơn hàng
$orderDetails = fetchAll("SELECT od.*, p.name, p.image 
                          FROM order_details od 
                          JOIN products p ON od.product_id = p.id 
                          WHERE od.order_id = ?", [$order_id]);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chi tiết đơn hàng #<?php echo $order['order_code']; ?></h5>
                    <span class="badge bg-<?php 
                        echo match($order['status']) {
                            'pending' => 'warning',
                            'confirmed' => 'info',
                            'shipping' => 'primary',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                            default => 'secondary'
                        };
                    ?> fs-6">
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
                <div class="card-body">
                    <!-- Thông tin đơn hàng -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Thông tin đơn hàng</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Mã đơn hàng:</strong></td>
                                    <td><?php echo $order['order_code']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày đặt:</strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Phương thức thanh toán:</strong></td>
                                    <td><?php echo $order['payment_method'] ?: 'Thanh toán khi nhận hàng'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái thanh toán:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo $order['payment_status'] == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Địa chỉ giao hàng</h6>
                            <div class="border rounded p-3">
                                <?php echo nl2br($order['shipping_address']); ?>
                            </div>
                            
                            <?php if ($order['notes']): ?>
                                <h6 class="mt-3">Ghi chú</h6>
                                <div class="border rounded p-3">
                                    <?php echo nl2br($order['notes']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Sản phẩm trong đơn hàng -->
                    <h6>Sản phẩm đã đặt</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderDetails as $detail): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/img/<?php echo $detail['image'] ?: 'default.jpg'; ?>" 
                                                     width="60" height="60" class="rounded me-3">
                                                <div>
                                                    <h6 class="mb-0"><?php echo $detail['name']; ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo formatMoney($detail['price']); ?></td>
                                        <td><?php echo $detail['quantity']; ?></td>
                                        <td><?php echo formatMoney($detail['total']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Tổng cộng:</th>
                                    <th><?php echo formatMoney($order['total_amount']); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Các nút hành động -->
                    <div class="text-end mt-4">
                        <?php if ($order['status'] == 'pending'): ?>
                            <form method="POST" action="my_orders.php" class="d-inline" 
                                  onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                                <input type="hidden" name="action" value="cancel_order">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-times"></i> Hủy đơn hàng
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] == 'delivered'): ?>
                            <a href="review_product.php?order_id=<?php echo $order['id']; ?>" 
                               class="btn btn-outline-success">
                                <i class="fas fa-star"></i> Đánh giá sản phẩm
                            </a>
                        <?php endif; ?>
                        
                        <?php if (in_array($order['status'], ['confirmed', 'delivered'])): ?>
                            <a href="invoice.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-outline-info" target="_blank">
                                <i class="fas fa-file-pdf"></i> Xuất hóa đơn
                            </a>
                        <?php endif; ?>
                        
                        <a href="my_orders.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
