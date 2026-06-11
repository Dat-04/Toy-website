<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="products_export_'.date('Ymd_His').'.csv"');

echo "\xEF\xBB\xBF"; // BOM UTF-8

$out = fopen('php://output', 'w');
fputcsv($out, ['ID', 'Tên sản phẩm', 'Danh mục', 'Giá', 'Giá KM', 'Kho', 'Trạng thái', 'Ngày tạo']);

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC";
$products = fetchAll($sql);
foreach ($products as $product) {
    fputcsv($out, [
        $product['id'],
        $product['name'],
        $product['category_name'],
        $product['price'],
        $product['sale_price'],
        $product['stock_quantity'],
        $product['status'] == 'active' ? 'Hoạt động' : 'Tạm dừng',
        date('d/m/Y', strtotime($product['created_at']))
    ]);
}
fclose($out);
exit; 