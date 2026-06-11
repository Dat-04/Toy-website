<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập!']);
    exit;
}

$customer_id = $_SESSION['customer_id'];
$today = date('Y-m-d');

// Lấy danh sách mã giảm giá còn hiệu lực và chưa được khách hàng sử dụng
$sql = "SELECT d.id, d.code, d.name, d.type, d.value, d.min_order_amount 
        FROM discounts d
        WHERE d.status = 'active' 
            AND (d.end_date IS NULL OR d.end_date >= ?) 
            AND (d.start_date IS NULL OR d.start_date <= ?)
            AND (d.max_uses = 0 OR d.used_count < d.max_uses)
            AND NOT EXISTS (
                SELECT 1 FROM orders o 
                WHERE o.customer_id = ? 
                AND o.discount_id = d.id 
                AND o.status != 'cancelled'
            )
        ORDER BY d.created_at DESC 
        LIMIT 20";

$discounts = fetchAll($sql, [$today, $today, $customer_id]);
echo json_encode(['success' => true, 'discounts' => $discounts]); 