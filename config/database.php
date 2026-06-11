<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'toy_shop');

// Tạo kết nối toàn cục
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Đặt charset là utf8mb4
$conn->set_charset("utf8mb4");

// Hàm thực thi query
function executeQuery($sql, $params = []) {
    global $conn;
    
    // Kiểm tra kết nối
    if (!$conn) {
        error_log("Database connection error");
        return false;
    }
    
    // Chuẩn bị câu lệnh
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error . " for query: " . $sql);
        return false;
    }
    
    // Bind parameters nếu có
    if (!empty($params)) {
        // Xác định kiểu dữ liệu cho từng tham số
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 's';
            }
        }
        
        // Bind parameters
        if (!$stmt->bind_param($types, ...$params)) {
            error_log("Binding parameters failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    // Thực thi câu lệnh
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    // Xử lý kết quả
    if (strpos(strtoupper($sql), 'SELECT') === 0) {
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    
    $stmt->close();
    return true;
}

// Hàm lấy một bản ghi
function fetchOne($sql, $params = []) {
    $result = executeQuery($sql, $params);
    if ($result && $result !== true) {
        return $result->fetch_assoc();
    }
    return null;
}

// Hàm lấy nhiều bản ghi
function fetchAll($sql, $params = []) {
    $result = executeQuery($sql, $params);
    $data = [];
    if ($result && $result !== true) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}
?>
