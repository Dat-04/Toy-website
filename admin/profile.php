<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';

$admin_id = $_SESSION['admin_id'];
$admin = null;
$message = '';
$message_type = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    
    $update_query = "UPDATE admin SET full_name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $full_name, $email, $admin_id);
    
    if ($stmt->execute()) {
        $message = 'Cập nhật thông tin thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi cập nhật!';
        $message_type = 'danger';
    }
    $stmt->close();
}

// Lấy thông tin admin
$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
if (!$stmt) {
    die("Lỗi prepare: " . $conn->error);
}
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    echo '<div class="alert alert-danger">Không tìm thấy thông tin admin!</div>';
    require_once 'includes/footer.php';
    exit();
}
?>
<style>
.main-content {
    margin-left: 250px;
    padding: 20px;
    min-height: 100vh;
    background: #f4f6f9;
}
.admin-profile-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    max-width: 800px;
    margin: 20px auto;
}
.admin-profile-header {
    background: #0866c6;
    color: #fff;
    padding: 20px;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.admin-profile-header i {
    font-size: 1.5rem;
}
.admin-profile-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0;
}
.admin-profile-body {
    padding: 30px;
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-group label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #333;
}
.form-control:focus {
    border-color: #0866c6;
    box-shadow: 0 0 0 0.2rem rgba(8,102,198,0.25);
}
.admin-profile-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #eee;
    margin-top: 20px;
}
.btn-update {
    background-color: #0866c6;
    color: white;
}
.btn-update:hover {
    background-color: #0757ac;
    color: white;
}
.alert {
    margin-bottom: 20px;
}
</style>

<div class="main-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="admin-profile-card">
                    <div class="admin-profile-header">
                        <i class="fas fa-user-shield"></i>
                        <span class="admin-profile-title">Thông tin tài khoản quản trị</span>
                    </div>
                    <div class="admin-profile-body">
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tên đăng nhập</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                                        <small class="text-muted">Tên đăng nhập không thể thay đổi</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Họ tên</label>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="admin-profile-actions">
                                <button type="submit" name="update_profile" class="btn btn-update">
                                    <i class="fas fa-save"></i> Cập nhật thông tin
                                </button>
                                <a href="change_password.php" class="btn btn-warning">
                                    <i class="fas fa-key"></i> Đổi mật khẩu
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




