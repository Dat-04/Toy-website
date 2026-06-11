<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập!']);
    exit;
}

$customer_id = $_SESSION['customer_id'];
$data = json_decode(file_get_contents('php://input'), true);
$discount_code = trim($data['discount_code'] ?? '');

if (!$discount_code) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá!']);
    exit;
}

// Kiểm tra mã giảm giá có tồn tại và còn hiệu lực không
$discount = fetchOne("SELECT * FROM discounts WHERE code = ? AND status = 'active'", [$discount_code]);

if (!$discount) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ!']);
    exit;
}

// Kiểm tra xem khách hàng đã sử dụng mã này chưa
$used_discount = fetchOne("SELECT id FROM orders WHERE customer_id = ? AND discount_id = ? AND status != 'cancelled'", 
    [$customer_id, $discount['id']]);

if ($used_discount) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã sử dụng mã giảm giá này rồi!']);
    exit;
}

$today = date('Y-m-d');
if (($discount['start_date'] && $discount['start_date'] > $today) || ($discount['end_date'] && $discount['end_date'] < $today)) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết hạn hoặc chưa bắt đầu!']);
    exit;
}

if ($discount['max_uses'] > 0 && $discount['used_count'] >= $discount['max_uses']) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng!']);
    exit;
}

// Lấy tổng tiền giỏ hàng
$cartItems = fetchAll("SELECT p.price, p.sale_price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.customer_id = ?", [$customer_id]);
$totalAmount = 0;
foreach ($cartItems as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $totalAmount += $price * $item['quantity'];
}

if ($totalAmount < $discount['min_order_amount']) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng chưa đủ điều kiện áp dụng mã giảm giá!']);
    exit;
}

// Tính số tiền giảm
$discount_value = 0;
if ($discount['type'] === 'percentage') {
    $discount_value = round($totalAmount * $discount['value'] / 100);
} else {
    $discount_value = min($discount['value'], $totalAmount);
}
$newTotal = $totalAmount - $discount_value;

// Lưu thông tin mã giảm giá vào session để sử dụng khi tạo đơn hàng
$_SESSION['applied_discount'] = [
    'id' => $discount['id'],
    'value' => $discount_value
];

// Trả về kết quả
echo json_encode([
    'success' => true,
    'discount_value' => $discount_value,
    'new_total' => $newTotal,
    'message' => 'Áp dụng mã giảm giá thành công!'
]); 