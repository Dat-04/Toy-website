<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$current_password = $input['current_password'];
$new_password = $input['new_password'];
$customer_id = $_SESSION['customer_id'];

// Lấy thông tin tài khoản
$customer = fetchOne("SELECT c.*, a.password FROM customers c 
                      JOIN accounts a ON c.account_id = a.id 
                      WHERE c.id = ?", [$customer_id]);

if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản']);
    exit;
}

// Kiểm tra mật khẩu hiện tại
if (!password_verify($current_password, $customer['password'])) {
    echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
    exit;
}

// Cập nhật mật khẩu mới
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$result = executeQuery("UPDATE accounts SET password = ? WHERE id = ?", [$hashed_password, $customer['account_id']]);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
