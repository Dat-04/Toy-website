<?php
// Các hàm tiện ích chung

// Hàm bảo mật input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}

// Hàm kiểm tra admin
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// Hàm redirect - sử dụng JavaScript để tránh lỗi header
function redirect($url) {
    echo "<script>window.location.href = '$url';</script>";
    exit();
}

// Hàm format tiền
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

// Hàm tạo mã đơn hàng
function generateOrderCode() {
    return 'TOY' . date('Ymd') . rand(1000, 9999);
}

// Hàm upload file
function uploadFile($file, $folder = 'uploads/') {
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    
    $fileName = time() . '_' . $file['name'];
    $filePath = $folder . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $fileName;
    }
    
    return false;
}

// Hàm gửi email
function sendEmail($to, $subject, $message) {
    $headers = "From: noreply@toyshop.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Hàm tạo thông báo
function setMessage($message, $type = 'info') {
    $_SESSION['message'] = [
        'text' => $message,
        'type' => $type
    ];
}

// Hàm hiển thị thông báo
function showMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification('" . addslashes($message['text']) . "', '" . $message['type'] . "');
            });
        </script>";
        unset($_SESSION['message']);
    }
}

// Hàm phân trang
function paginate($total, $limit, $page, $url) {
    $totalPages = ceil($total / $limit);
    $pagination = '';
    
    if ($totalPages > 1) {
        $pagination .= '<nav><ul class="pagination justify-content-center">';
        
        // Previous
        if ($page > 1) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($page - 1) . '">Trước</a></li>';
        }
        
        // Numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i == $page) ? 'active' : '';
            $pagination .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
        }
        
        // Next
        if ($page < $totalPages) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($page + 1) . '">Sau</a></li>';
        }
        
        $pagination .= '</ul></nav>';
    }
    
    return $pagination;
}
function getConnection() {
    $host = 'localhost';      // hoặc tên server MySQL
    $user = 'root';           // tài khoản mặc định của XAMPP/WAMP
    $pass = '';               // mật khẩu rỗng mặc định, nếu bạn đặt mật khẩu thì thay ở đây
    $db   = 'toy_shop';       // đúng tên database bạn đã import toy_shop.sql

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die('Kết nối database thất bại: ' . $conn->connect_error);
    }
    return $conn;
}
?>
