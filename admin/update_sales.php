<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

try {
    // Cập nhật số lượt bán cho tất cả sản phẩm
    $updateQuery = "UPDATE products p 
                   SET total_sales = (
                       SELECT COUNT(DISTINCT o.id)
                       FROM orders o 
                       JOIN order_items oi ON o.id = oi.order_id 
                       WHERE oi.product_id = p.id 
                       AND o.status = 'delivered'
                   )";
    
    $result = executeQuery($updateQuery);
    
    if ($result) {
        setMessage('Đã cập nhật số lượt bán thành công!', 'success');
    } else {
        setMessage('Có lỗi xảy ra khi cập nhật số lượt bán!', 'danger');
    }
} catch (Exception $e) {
    setMessage('Lỗi: ' . $e->getMessage(), 'danger');
}

redirect('products.php'); 