<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode([]);
    exit();
}

$customer_id = $_SESSION['customer_id'];

$messages_query = "SELECT message, sender_type, created_at 
                   FROM chat_messages 
                   WHERE customer_id = ? 
                   ORDER BY created_at ASC 
                   LIMIT 50";
$messages_stmt = $conn->prepare($messages_query);
$messages_stmt->bind_param("i", $customer_id);
$messages_stmt->execute();
$messages = $messages_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($messages);
?>
