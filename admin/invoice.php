<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

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
$order_items = fetchAll("SELECT od.*, p.name FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?", [$order_id]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn #<?php echo $order['order_code']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .invoice-box {
            background: #fff;
            max-width: 800px;
            margin: 30px auto;
            padding: 32px 40px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(44,62,80,0.07);
        }
        .invoice-title { font-size: 2rem; font-weight: 700; color: #283046; }
        .table th, .table td { font-size: 1rem; }
        @media print {
            .no-print { display: none !important; }
            .invoice-box { box-shadow: none; border: none; margin: 0; }
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="invoice-title">TOY SHOP</div>
            <div>Địa chỉ: 218 Lĩnh Nam, Vĩnh Hưng, Hoàng Mai, Hà Nội</div>
            <div>Điện thoại: 0123 456 789</div>
            <div>Email: info@toyshop.com</div>
        </div>
        <div class="text-end">
            <h4>HÓA ĐƠN</h4>
            <div><strong>Mã đơn:</strong> #<?php echo $order['order_code']; ?></div>
            <div><strong>Ngày:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
        </div>
    </div>
    <hr>
    <div class="mb-4">
        <h6>Khách hàng:</h6>
        <div><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></div>
        <div>Email: <?php echo htmlspecialchars($order['email']); ?></div>
        <div>Điện thoại: <?php echo htmlspecialchars($order['phone']); ?></div>
        <div>Địa chỉ: <?php echo htmlspecialchars($order['address']); ?></div>
    </div>
    <table class="table table-bordered mb-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
        <?php $i=1; foreach ($order_items as $item): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo formatMoney($item['price']); ?></td>
                <td><?php echo formatMoney($item['price'] * $item['quantity']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="d-flex justify-content-end mb-3">
        <div>
            <div><strong>Tổng tiền:</strong> <span class="h5 text-danger"><?php echo formatMoney($order['total_amount']); ?></span></div>
            <div><strong>Trạng thái thanh toán:</strong> <?php echo $order['payment_status'] == 'paid' ? '<span class="badge bg-success">Đã thanh toán</span>' : '<span class="badge bg-warning">Chưa thanh toán</span>'; ?></div>
        </div>
    </div>
    <div class="text-center no-print">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> In hóa đơn</button>
        <a href="orders.php" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html> 