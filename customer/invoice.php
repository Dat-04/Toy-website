<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isLoggedIn()) {
    redirect('../login.php');
}

$customer_id = $_SESSION['customer_id'];
$order_id = (int)$_GET['id'];

// Lấy thông tin đơn hàng
$order = fetchOne("SELECT o.*, c.full_name, c.email, c.phone, c.address 
                   FROM orders o 
                   JOIN customers c ON o.customer_id = c.id 
                   WHERE o.id = ? AND o.customer_id = ? AND o.status IN ('confirmed', 'delivered')", 
                  [$order_id, $customer_id]);

if (!$order) {
    die('Đơn hàng không tồn tại hoặc chưa được xác nhận!');
}

// Lấy chi tiết đơn hàng
$orderDetails = fetchAll("SELECT od.*, p.name 
                          FROM order_details od 
                          JOIN products p ON od.product_id = p.id 
                          WHERE od.order_id = ?", [$order_id]);

// Tạo hoặc lấy hóa đơn
$invoice = fetchOne("SELECT * FROM invoices WHERE order_id = ?", [$order_id]);

if (!$invoice) {
    $invoice_number = 'INV' . date('Ymd') . str_pad($order_id, 4, '0', STR_PAD_LEFT);
    executeQuery("INSERT INTO invoices (order_id, invoice_number, issue_date, total_amount) VALUES (?, ?, CURDATE(), ?)", 
                 [$order_id, $invoice_number, $order['total_amount']]);
    $invoice = fetchOne("SELECT * FROM invoices WHERE order_id = ?", [$order_id]);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #<?php echo $invoice['invoice_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12pt; }
        }
        .invoice-header { border-bottom: 2px solid #007bff; }
        .company-info { color: #007bff; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="invoice-header pb-3 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="company-info mb-0">
                                <i class="fas fa-toy-brick"></i> ToyShop
                            </h2>
                            <p class="mb-0">Cửa hàng đồ chơi uy tín</p>
                            <p class="mb-0">123 Đường ABC, Quận 1, TP.HCM</p>
                            <p class="mb-0">Tel: 0123-456-789 | Email: info@toyshop.com</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h3>HÓA ĐƠN BÁN HÀNG</h3>
                            <p class="mb-1"><strong>Số hóa đơn:</strong> <?php echo $invoice['invoice_number']; ?></p>
                            <p class="mb-1"><strong>Ngày xuất:</strong> <?php echo date('d/m/Y', strtotime($invoice['issue_date'])); ?></p>
                            <p class="mb-0"><strong>Mã đơn hàng:</strong> <?php echo $order['order_code']; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Thông tin khách hàng</h5>
                        <p class="mb-1"><strong>Họ tên:</strong> <?php echo $order['full_name']; ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo $order['email']; ?></p>
                        <p class="mb-1"><strong>Điện thoại:</strong> <?php echo $order['phone']; ?></p>
                        <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo $order['shipping_address']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Thông tin giao hàng</h5>
                        <p class="mb-1"><strong>Ngày đặt hàng:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                        <p class="mb-1"><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method'] ?: 'Thanh toán khi nhận hàng'; ?></p>
                        <p class="mb-0"><strong>Trạng thái:</strong> 
                            <?php 
                            echo match($order['status']) {
                                'confirmed' => 'Đã xác nhận',
                                'delivered' => 'Đã giao hàng',
                                default => 'Đang xử lý'
                            };
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Order Details -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>STT</th>
                                <th>Tên sản phẩm</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderDetails as $index => $detail): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $detail['name']; ?></td>
                                    <td class="text-center"><?php echo $detail['quantity']; ?></td>
                                    <td class="text-end"><?php echo formatMoney($detail['price']); ?></td>
                                    <td class="text-end"><?php echo formatMoney($detail['total']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Tổng cộng:</th>
                                <th class="text-end"><?php echo formatMoney($order['total_amount']); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Footer -->
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Ghi chú:</strong></p>
                        <p><?php echo $order['notes'] ?: 'Không có ghi chú'; ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p><strong>Người lập hóa đơn</strong></p>
                        <br><br>
                        <p>ToyShop</p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> In hóa đơn
                    </button>
                    <a href="my_orders.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
