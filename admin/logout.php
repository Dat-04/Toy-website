<?php
session_start();

// Xóa tất cả session
session_destroy();

// Chuyển hướng về trang đăng nhập của khách hàng
header('Location:../index.php');
exit();
?>
