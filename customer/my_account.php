<?php
$pageTitle = 'Tài khoản của tôi';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$customer_id = $_SESSION['customer_id'];

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    
    // Xử lý upload avatar
    $avatar = '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $avatar = uploadFile($_FILES['avatar'], '../assets/img/avatars/');
    }
    
    $sql = "UPDATE customers SET full_name = ?, phone = ?, address = ?, date_of_birth = ?, gender = ?";
    $params = [$full_name, $phone, $address, $date_of_birth, $gender];
    
    if ($avatar) {
        $sql .= ", avatar = ?";
        $params[] = $avatar;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $customer_id;
    
    if (executeQuery($sql, $params)) {
        setMessage('Cập nhật thông tin thành công!', 'success');
    } else {
        setMessage('Có lỗi xảy ra, vui lòng thử lại!', 'danger');
    }
}

// Lấy thông tin khách hàng
$customer = fetchOne("SELECT * FROM customers WHERE id = ?", [$customer_id]);
?>

<div class="container account-container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="account-sidebar">
                <div class="sidebar-header">
                    <img src="../assets/img/avatars/<?php echo $customer['avatar'] ?: 'default.png'; ?>" 
                         class="sidebar-avatar" alt="Avatar">
                    <div class="sidebar-name"><?php echo $customer['full_name']; ?></div>
                    <div class="sidebar-email"><?php echo $customer['email']; ?></div>
                </div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="my_account.php" class="sidebar-nav-link active">
                            <i class="fas fa-user"></i> Thông tin tài khoản
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="my_orders.php" class="sidebar-nav-link">
                            <i class="fas fa-shopping-bag"></i> Đơn hàng của tôi
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="my_wishlist.php" class="sidebar-nav-link">
                            <i class="fas fa-heart"></i> Danh sách yêu thích
                        </a>
                    </li>
                   
                    <li class="sidebar-nav-item">
                        <a href="logout.php" class="sidebar-nav-link">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Thông tin tài khoản -->
            <div class="account-card fade-in">
                <div class="account-card-header">
                    <h5><i class="fas fa-user"></i> Thông tin tài khoản</h5>
                </div>
                <div class="account-card-body">
                    <form method="POST" enctype="multipart/form-data" class="account-form">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="avatar-upload">
                                    <img src="../assets/img/avatars/<?php echo $customer['avatar'] ?: 'default.png'; ?>" 
                                         class="avatar-preview" id="avatarPreview" alt="Avatar">
                                    <div>
                                        <label for="avatar" class="avatar-upload-btn">
                                            <i class="fas fa-camera"></i> Đổi ảnh đại diện
                                        </label>
                                        <input type="file" id="avatar" name="avatar" class="d-none" 
                                               accept="image/*" onchange="previewImage(this, 'avatarPreview')">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="full_name" class="form-label">
                                                <i class="fas fa-user"></i> Họ và tên
                                            </label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                                   value="<?php echo $customer['full_name']; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope"></i> Email
                                            </label>
                                            <input type="email" class="form-control" value="<?php echo $customer['email']; ?>" disabled>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">
                                                <i class="fas fa-phone"></i> Số điện thoại
                                            </label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo $customer['phone']; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_of_birth" class="form-label">
                                                <i class="fas fa-birthday-cake"></i> Ngày sinh
                                            </label>
                                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                                   value="<?php echo $customer['date_of_birth']; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gender" class="form-label">
                                                <i class="fas fa-venus-mars"></i> Giới tính
                                            </label>
                                            <select class="form-control form-select" id="gender" name="gender">
                                                <option value="">Chọn giới tính</option>
                                                <option value="male" <?php echo $customer['gender'] == 'male' ? 'selected' : ''; ?>>Nam</option>
                                                <option value="female" <?php echo $customer['gender'] == 'female' ? 'selected' : ''; ?>>Nữ</option>
                                                <option value="other" <?php echo $customer['gender'] == 'other' ? 'selected' : ''; ?>>Khác</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="address" class="form-label">
                                                <i class="fas fa-map-marker-alt"></i> Địa chỉ
                                            </label>
                                            <textarea class="form-control" id="address" name="address" rows="3" 
                                                      placeholder="Nhập địa chỉ của bạn..."><?php echo $customer['address']; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary hover-lift">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Đổi mật khẩu -->
            <div class="account-card password-section fade-in">
                <div class="account-card-header password-header">
                    <h5><i class="fas fa-key"></i> Đổi mật khẩu</h5>
                </div>
                <div class="account-card-body password-body">
                    <div class="password-requirements">
                        <h6><i class="fas fa-info-circle"></i> Yêu cầu mật khẩu:</h6>
                        <ul>
                            <li>Ít nhất 6 ký tự</li>
                            <li>Nên có chữ hoa, chữ thường và số</li>
                            <li>Không sử dụng thông tin cá nhân</li>
                        </ul>
                    </div>
                    
                    <form id="changePasswordForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="current_password" class="form-label">
                                        <i class="fas fa-lock"></i> Mật khẩu hiện tại
                                    </label>
                                    <input type="password" class="form-control" id="current_password" 
                                           placeholder="Nhập mật khẩu hiện tại" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="new_password" class="form-label">
                                        <i class="fas fa-key"></i> Mật khẩu mới
                                    </label>
                                    <input type="password" class="form-control" id="new_password" 
                                           placeholder="Nhập mật khẩu mới" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-check-circle"></i> Xác nhận mật khẩu
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           placeholder="Xác nhận mật khẩu mới" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-warning hover-lift">
                                <i class="fas fa-key"></i> Cập nhật mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Preview image function
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Change password form handler
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    
    // Validation
    if (newPassword !== confirmPassword) {
        alert('Mật khẩu xác nhận không khớp!');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    if (newPassword.length < 6) {
        alert('Mật khẩu mới phải có ít nhất 6 ký tự!');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    // Send request
    fetch('../api/change_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đổi mật khẩu thành công!');
            this.reset();
        } else {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra!');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Add fade-in animation to cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.account-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 200);
    });
});
</script>

<?php include '../includes/footer.php'; ?>