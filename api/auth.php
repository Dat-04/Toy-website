<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
        exit;
    }
    
    // Kiểm tra admin trước
    $admin = fetchOne("SELECT * FROM admin WHERE email = ? OR username = ?", [$email, $email]);
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        
        $base = '/toy';
        echo json_encode([
            'success' => true,
            'redirect' => $base . '/admin/index.php',
            'message' => 'Đăng nhập admin thành công!',
            'user_type' => 'admin'
        ]);
        exit;
    }
    
    // Kiểm tra khách hàng
    $customer = fetchOne("SELECT c.*, a.password FROM customers c 
                          JOIN accounts a ON c.account_id = a.id 
                          WHERE c.email = ? AND a.status = 'active'", [$email]);
    
    if ($customer && password_verify($password, $customer['password'])) {
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_name'] = $customer['full_name'];
        $_SESSION['customer_email'] = $customer['email'];
        
        echo json_encode([
            'success' => true, 
            'redirect' => 'index.php', 
            'message' => 'Đăng nhập thành công!',
            'user_type' => 'customer'
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!']);
    exit;
    
} elseif ($action === 'register') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự!']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ!']);
        exit;
    }
    
    $full_name = trim($first_name . ' ' . $last_name);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Kiểm tra email đã tồn tại
    $existingCustomer = fetchOne("SELECT id FROM customers WHERE email = ?", [$email]);
    $existingAdmin = fetchOne("SELECT id FROM admin WHERE email = ?", [$email]);
    
    if ($existingCustomer || $existingAdmin) {
        echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng!']);
        exit;
    }
    
    // Tạo tài khoản
    $conn = getConnection();
    $conn->begin_transaction();
    
    try {
        // Tạo account
        $stmt1 = $conn->prepare("INSERT INTO accounts (username, password, email, status) VALUES (?, ?, ?, 'active')");
        $stmt1->bind_param("sss", $email, $hashed_password, $email);
        $stmt1->execute();
        $account_id = $conn->insert_id;
        
        // Tạo customer
        $stmt2 = $conn->prepare("INSERT INTO customers (account_id, full_name, email, created_at) VALUES (?, ?, ?, NOW())");
        $stmt2->bind_param("iss", $account_id, $full_name, $email);
        $stmt2->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công! Vui lòng đăng nhập.']);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Registration error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại!']);
    }
    
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
