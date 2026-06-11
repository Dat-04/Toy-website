<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$count = 0;

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    
    $count_query = "SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("i", $customer_id);
    $count_stmt->execute();
    $result = $count_stmt->get_result()->fetch_assoc();
    
    $count = $result['total'] ?? 0;
}

echo json_encode(['count' => (int)$count]);
?>
