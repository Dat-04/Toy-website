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
$message = trim($input['message'] ?? '');
$customer_id = $_SESSION['customer_id'];

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
    exit();
}

// Insert message
$insert_query = "INSERT INTO chat_messages (customer_id, message, sender_type, created_at) VALUES (?, ?, 'customer', NOW())";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("is", $customer_id, $message);

if ($insert_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Tin nhắn đã được gửi']);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
