<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$vnp_config = require_once('config/vnpay.php');

// Lấy các tham số trả về từ VNPAY
$vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
$vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
$vnp_Amount = $_GET['vnp_Amount'] ?? 0;
$vnp_OrderInfo = $_GET['vnp_OrderInfo'] ?? '';
$vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
$vnp_BankCode = $_GET['vnp_BankCode'] ?? '';
$vnp_PayDate = $_GET['vnp_PayDate'] ?? '';
$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';

// Tạo mảng dữ liệu để kiểm tra checksum
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}
unset($inputData['vnp_SecureHash']);
ksort($inputData);

// Tạo chuỗi hash để kiểm tra
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_config['vnp_HashSecret']);

// Xử lý kết quả trả về
$success = false;
$message = '';

if ($secureHash === $vnp_SecureHash) {
    // Tìm đơn hàng
    $order = fetchOne("SELECT * FROM orders WHERE order_code = ?", [$vnp_TxnRef]);
    
    if ($order) {
        // Kiểm tra số tiền và cập nhật trạng thái
        $order_amount = $order['total_amount'] * 100;
        if ($order_amount == $vnp_Amount) {
            if ($vnp_ResponseCode == '00') {
                // Thanh toán thành công
                // Cập nhật trạng thái đơn hàng
                executeQuery("UPDATE orders SET payment_status = 'paid', status = 'confirmed' WHERE order_code = ?", 
                           [$vnp_TxnRef]);
                
                // Cập nhật payment record
                executeQuery("UPDATE payments SET 
                            payment_status = 'completed',
                            transaction_id = ?,
                            transaction_info = ?
                            WHERE order_id = ?",
                           [$vnp_TransactionNo,
                            json_encode([
                                'bank_code' => $vnp_BankCode,
                                'pay_date' => $vnp_PayDate,
                                'amount' => $vnp_Amount/100
                            ]),
                            $order['id']]);
                
                $success = true;
                $message = 'Thanh toán thành công!';
                
                // Tạo thông báo
                executeQuery("INSERT INTO notifications (title, message, type) VALUES (?, ?, 'order')",
                           ["Thanh toán thành công",
                            "Đơn hàng #{$vnp_TxnRef} đã được thanh toán thành công qua VNPAY"]);
                
            } else {
                // Thanh toán thất bại
                executeQuery("UPDATE orders SET payment_status = 'failed' WHERE order_code = ?", 
                           [$vnp_TxnRef]);
                           
                executeQuery("UPDATE payments SET 
                            payment_status = 'failed',
                            transaction_id = ?,
                            transaction_info = ?
                            WHERE order_id = ?",
                           [$vnp_TransactionNo,
                            json_encode([
                                'response_code' => $vnp_ResponseCode,
                                'bank_code' => $vnp_BankCode,
                                'pay_date' => $vnp_PayDate
                            ]),
                            $order['id']]);
                            
                $message = 'Thanh toán thất bại!';
            }
        } else {
            $message = 'Số tiền không hợp lệ!';
        }
    } else {
        $message = 'Không tìm thấy đơn hàng!';
    }
} else {
    $message = 'Chữ ký không hợp lệ!';
}

// Hiển thị kết quả
$pageTitle = 'Kết quả thanh toán';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-center">
                <div class="card-body py-5">
                    <?php if ($success): ?>
                        <div class="text-success mb-4">
                            <i class="fas fa-check-circle fa-5x"></i>
                        </div>
                        <h2 class="card-title text-success">Thanh toán thành công!</h2>
                        <p class="card-text">Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đã được xác nhận.</p>
                        <div class="mt-4">
                            <p><strong>Mã đơn hàng:</strong> <?php echo $vnp_TxnRef; ?></p>
                            <p><strong>Số tiền:</strong> <?php echo number_format($vnp_Amount/100); ?>đ</p>
                            <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($vnp_PayDate)); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="text-danger mb-4">
                            <i class="fas fa-times-circle fa-5x"></i>
                        </div>
                        <h2 class="card-title text-danger">Thanh toán thất bại!</h2>
                        <p class="card-text"><?php echo $message; ?></p>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="customer/my_orders.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Xem đơn hàng
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Về trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 