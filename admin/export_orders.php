<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="orders_export_'.date('Ymd_His').'.csv"');

echo "\xEF\xBB\xBF"; // BOM UTF-8

$out = fopen('php://output', 'w');
fputcsv($out, ['Mã đơn', 'Khách hàng', 'Email', 'Tổng tiền', 'Trạng thái', 'Thanh toán', 'Ngày đặt']);

$sql = "SELECT o.*, c.full_name, c.email FROM orders o JOIN customers c ON o.customer_id = c.id ORDER BY o.created_at DESC";
$orders = fetchAll($sql);
foreach ($orders as $order) {
    $status = match($order['status']) {
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao hàng',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
        default => 'Không xác định'
    };
    $payment = match($order['payment_status']) {
        'pending' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán lỗi',
        default => 'Không xác định'
    };
    fputcsv($out, [
        $order['order_code'],
        $order['full_name'],
        $order['email'],
        $order['total_amount'],
        $status,
        $payment,
        date('d/m/Y H:i', strtotime($order['created_at']))
    ]);
}
fclose($out);
exit; 