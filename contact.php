<?php
$pageTitle = 'Liên hệ';
include 'includes/header.php';

// Xử lý form liên hệ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validate form
    $errors = [];
    if (empty($name)) {
        $errors[] = "Vui lòng nhập họ tên";
    }
    if (empty($email)) {
        $errors[] = "Vui lòng nhập email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    if (empty($message)) {
        $errors[] = "Vui lòng nhập nội dung";
    }
    
    if (empty($errors)) {
        // Lưu vào database hoặc gửi email
        $_SESSION['success'] = "Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất!";
        header('Location: contact.php');
        exit();
    }
}
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active">Liên hệ</li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Thông tin liên hệ -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Thông tin liên hệ</h4>
                    
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="fs-6">Địa chỉ</h5>
                            <p class="mb-0">218 Lĩnh Nam, Vĩnh Hưng, Hoàng Mai, Hà Nội</p>
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="fs-6">Điện thoại</h5>
                            <p class="mb-0">
                                <a href="tel:0123456789" class="text-decoration-none">0123 456 789</a><br>
                                <a href="tel:0987654321" class="text-decoration-none">0987 654 321</a>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="fs-6">Email</h5>
                            <p class="mb-0">
                                <a href="mailto:info@toyshop.com" class="text-decoration-none">info@toyshop.com</a><br>
                                <a href="mailto:support@toyshop.com" class="text-decoration-none">support@toyshop.com</a>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="fs-6">Giờ làm việc</h5>
                            <p class="mb-0">
                                Thứ 2 - Thứ 6: 8:00 - 20:00<br>
                                Thứ 7 - Chủ nhật: 9:00 - 18:00
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">Kết nối với chúng tôi</h4>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-outline-primary"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-info"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-danger"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="btn btn-outline-warning"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form liên hệ -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Gửi tin nhắn cho chúng tôi</h4>
                    
                    <form method="POST" class="contact-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ tên *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chủ đề</label>
                                <select name="subject" class="form-select">
                                    <option value="general">Chung</option>
                                    <option value="product">Sản phẩm</option>
                                    <option value="order">Đơn hàng</option>
                                    <option value="support">Hỗ trợ kỹ thuật</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung *</label>
                            <textarea name="message" class="form-control" rows="5" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bản đồ -->
    <div class="card border-0 shadow-sm mt-5">
        <div class="card-body p-0">
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.3082660162527!2d105.87334587400763!3d20.98027668944449!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ad8325fe9061%3A0x17cc9e09d9270e15!2sUneti%20L%C4%A9nh%20Nam!5e0!3m2!1sen!2s!4v1752205188653!5m2!1sen!2s" 
                    style="border:0; width: 100%; height: 450px;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</div>

<!-- CSS cho trang liên hệ -->
<style>
.contact-form .form-control:focus,
.contact-form .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.social-links a {
    width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.social-links a:hover {
    transform: translateY(-3px);
}

.map-container {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.map-container iframe {
    display: block;
}
</style>

<?php include 'includes/footer.php'; ?> 