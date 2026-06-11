<?php
$pageTitle = 'Đăng ký';
include 'config/database.php';
include 'includes/functions.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Kiểm tra email đã tồn tại
    $existingUser = fetchOne("SELECT id FROM customers WHERE email = ?", [$email]);
    
    if ($existingUser) {
        setMessage('Email đã được sử dụng!', 'danger');
    } else {
        // Tạo tài khoản
        $conn = getConnection();
        $conn->begin_transaction();
        
        try {
            // Tạo account
            $stmt1 = $conn->prepare("INSERT INTO accounts (username, password, email) VALUES (?, ?, ?)");
            $stmt1->bind_param("sss", $email, $password, $email);
            $stmt1->execute();
            $account_id = $conn->insert_id;
            
            // Tạo customer
            $stmt2 = $conn->prepare("INSERT INTO customers (account_id, full_name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $account_id, $full_name, $email, $phone);
            $stmt2->execute();
            
            $conn->commit();
            setMessage('Đăng ký thành công! Vui lòng đăng nhập.', 'success');
            redirect('login.php');
            
        } catch (Exception $e) {
            $conn->rollback();
            setMessage('Có lỗi xảy ra, vui lòng thử lại!', 'danger');
        }
        
        $conn->close();
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center">Đăng ký tài khoản</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agree" required>
                            <label class="form-check-label" for="agree">
                                Tôi đồng ý với <a href="#">điều khoản sử dụng</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Mật khẩu xác nhận không khớp!');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
