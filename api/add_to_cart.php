<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = $input['product_id'] ?? 0;
$quantity = $input['quantity'] ?? 1;
$customer_id = $_SESSION['customer_id'];

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

// Check if product exists and has enough stock
$product_query = "SELECT stock_quantity FROM products WHERE id = ? AND status = 'active'";
$product_stmt = $conn->prepare($product_query);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product = $product_stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit();
}

if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho']);
    exit();
}

// Check if item already in cart
$cart_check_query = "SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?";
$cart_check_stmt = $conn->prepare($cart_check_query);
$cart_check_stmt->bind_param("ii", $customer_id, $product_id);
$cart_check_stmt->execute();
$existing_item = $cart_check_stmt->get_result()->fetch_assoc();

if ($existing_item) {
    // Update quantity
    $new_quantity = $existing_item['quantity'] + $quantity;
    
    if ($new_quantity > $product['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho']);
        exit();
    }
    
    $update_query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $new_quantity, $existing_item['id']);
    $success = $update_stmt->execute();
} else {
    // Add new item
    $insert_query = "INSERT INTO cart (customer_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iii", $customer_id, $product_id, $quantity);
    $success = $insert_stmt->execute();
}

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ hàng']);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
