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
$customer_id = $_SESSION['customer_id'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

// Check if product exists
$product_query = "SELECT id FROM products WHERE id = ? AND status = 'active'";
$product_stmt = $conn->prepare($product_query);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product = $product_stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit();
}

// Check if already in wishlist
$wishlist_check_query = "SELECT id FROM wishlist WHERE customer_id = ? AND product_id = ?";
$wishlist_check_stmt = $conn->prepare($wishlist_check_query);
$wishlist_check_stmt->bind_param("ii", $customer_id, $product_id);
$wishlist_check_stmt->execute();
$existing_item = $wishlist_check_stmt->get_result()->fetch_assoc();

if ($existing_item) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm đã có trong danh sách yêu thích']);
    exit();
}

// Add to wishlist
$insert_query = "INSERT INTO wishlist (customer_id, product_id, created_at) VALUES (?, ?, NOW())";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("ii", $customer_id, $product_id);

if ($insert_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Đã thêm vào danh sách yêu thích']);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
