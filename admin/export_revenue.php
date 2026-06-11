<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Lấy tham số thời gian
$period = $_GET['period'] ?? 'month';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

// Tính toán điều kiện thời gian
switch ($period) {
    case 'day':
        $dateCondition = "DATE(o.created_at) = CURDATE()";
        $groupBy = "DATE(o.created_at)";
        break;
    case 'week':
        $dateCondition = "YEARWEEK(o.created_at) = YEARWEEK(CURDATE())";
        $groupBy = "YEARWEEK(o.created_at)";
        break;
    case 'month':
        $dateCondition = "YEAR(o.created_at) = $year AND MONTH(o.created_at) = $month";
        $groupBy = "DATE(o.created_at)";
        break;
    case 'year':
        $dateCondition = "YEAR(o.created_at) = $year";
        $groupBy = "MONTH(o.created_at)";
        break;
    default:
        $dateCondition = "MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
        $groupBy = "DATE(o.created_at)";
}

// Lấy dữ liệu
$totalRevenue = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders o WHERE status = 'delivered' AND $dateCondition")['total'];
$totalOrders = fetchOne("SELECT COUNT(*) as total FROM orders o WHERE status = 'delivered' AND $dateCondition")['total'];
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

$revenueData = fetchAll("SELECT $groupBy as period, SUM(total_amount) as revenue, COUNT(*) as orders 
                         FROM orders o 
                         WHERE status = 'delivered' AND $dateCondition 
                         GROUP BY $groupBy 
                         ORDER BY period");

$topProducts = fetchAll("SELECT p.name, p.image, SUM(od.quantity) as total_sold, SUM(od.total) as revenue
                         FROM order_details od
                         JOIN products p ON od.product_id = p.id
                         JOIN orders o ON od.order_id = o.id
                         WHERE o.status = 'delivered' AND $dateCondition
                         GROUP BY p.id, p.name, p.image
                         ORDER BY total_sold DESC
                         LIMIT 10");

$categoryRevenue = fetchAll("SELECT c.name, COALESCE(SUM(od.total), 0) as revenue
                             FROM categories c
                             LEFT JOIN products p ON c.id = p.category_id
                             LEFT JOIN order_details od ON p.id = od.product_id
                             LEFT JOIN orders o ON od.order_id = o.id AND o.status = 'delivered' AND $dateCondition
                             GROUP BY c.id, c.name
                             ORDER BY revenue DESC");

// Xuất file CSV với định dạng cải tiến
$filename = 'BaoCaoDoanhThu_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

// Thêm BOM để hỗ trợ UTF-8 trong Excel
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Tiêu đề gộp ô
fputcsv($output, ['=HYPERLINK("","BÁO CÁO DOANH THU")'], ',', '"'); // Gộp và căn giữa
fputcsv($output, []); // Dòng trống

// Tổng quan
fputcsv($output, ['=HYPERLINK("","Tổng quan")'], ',', '"'); // Tiêu đề gộp và căn giữa
fputcsv($output, ['=HYPERLINK("","Tổng doanh thu:")', '=HYPERLINK("","' . number_format($totalRevenue, 0, ',', '.') . ' VNĐ")'], ',', '"');
fputcsv($output, ['=HYPERLINK("","Tổng đơn hàng:")', '=HYPERLINK("","' . $totalOrders . '")'], ',', '"');
fputcsv($output, ['=HYPERLINK("","Giá trị đơn hàng trung bình:")', '=HYPERLINK("","' . number_format($avgOrderValue, 0, ',', '.') . ' VNĐ")'], ',', '"');
fputcsv($output, []); // Dòng trống

// Doanh thu theo thời gian
fputcsv($output, ['=HYPERLINK("","Doanh thu theo thời gian")'], ',', '"'); // Tiêu đề gộp và căn giữa
fputcsv($output, ['=HYPERLINK("","Thời gian")', '=HYPERLINK("","Doanh thu (VNĐ)")', '=HYPERLINK("","Số đơn hàng")'], ',', '"'); // Header
foreach ($revenueData as $data) {
    fputcsv($output, [$data['period'], number_format($data['revenue'], 0, ',', '.'), $data['orders']], ',', '"');
}
fputcsv($output, []); // Dòng trống

// Top sản phẩm bán chạy
fputcsv($output, ['=HYPERLINK("","Top sản phẩm bán chạy")'], ',', '"'); // Tiêu đề gộp và căn giữa
fputcsv($output, ['=HYPERLINK("","Sản phẩm")', '=HYPERLINK("","Số lượng bán")', '=HYPERLINK("","Doanh thu (VNĐ)")'], ',', '"'); // Header
foreach ($topProducts as $product) {
    fputcsv($output, [$product['name'], $product['total_sold'], number_format($product['revenue'], 0, ',', '.')], ',', '"');
}
fputcsv($output, []); // Dòng trống

// Doanh thu theo danh mục
fputcsv($output, ['=HYPERLINK("","Doanh thu theo danh mục")'], ',', '"'); // Tiêu đề gộp và căn giữa
fputcsv($output, ['=HYPERLINK("","Danh mục")', '=HYPERLINK("","Doanh thu (VNĐ)")'], ',', '"'); // Header
foreach ($categoryRevenue as $category) {
    fputcsv($output, [$category['name'], number_format($category['revenue'], 0, ',', '.')], ',', '"');
}

fclose($output);
exit;
?>