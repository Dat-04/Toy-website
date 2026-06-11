<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Không có output trước khi session_start()
ob_start();

$base = '/toy';

// Định nghĩa menu items
$menuItems = [
    ['name' => 'Trang chủ', 'link' => 'index.php'],
    ['name' => 'Sản phẩm', 'link' => 'products.php'],
    ['name' => 'Khuyến mãi', 'link' => 'promotions.php'],
    ['name' => 'Tin tức', 'link' => 'news.php'],
    ['name' => 'Liên hệ', 'link' => 'contact.php']
];

// Lấy danh sách danh mục sản phẩm
$categories = [];
$sql = "SELECT id, name FROM categories WHERE status = 1 ORDER BY name";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>ToyShop - Cửa hàng đồ chơi trẻ em</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= $base ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/custom.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/auth-modal.css" rel="stylesheet">
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <meta name="description" content="ToyShop - Cửa hàng đồ chơi trẻ em uy tín, chất lượng cao với giá tốt nhất">
    <meta name="keywords" content="đồ chơi, đồ chơi trẻ em, toy shop, đồ chơi an toàn">
</head>
<body data-logged-in="<?php echo isLoggedIn() ? 'true' : 'false'; ?>">

<!-- Promotional Banner -->
<div class="promo-banner">
    <div class="container-fluid">
        <div class="promo-content d-flex justify-content-between align-items-center">
            <div class="promo-left d-flex align-items-center">
                <span class="promo-text">🎉 MEGA SALE ONLINE 🎉</span>
                <div class="discount-badge">
                    <div class="discount-percent">-70%</div>
                    <div class="discount-text">LỚN NHẤT NĂM</div>
                </div>
                <span class="promo-date">01.07 - 06.07</span>
                <span class="exclusive-text">ĐỘC QUYỀN ONLINE</span>
            </div>
            <div class="promo-right">
                <span class="hotline-text">📞 Hotline: 0123-456-789</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header py-3">
    <div class="container">
        <div class="row align-items-center">
            <!-- Logo -->
            <div class="col-md-2">
                <a href="<?= $base ?>/index.php" class="text-decoration-none">
                    <h2 class="logo-text mb-0">ToyShop</h2>
                </a>
            </div>
            
            <!-- Search Bar -->
            <div class="col-md-6">
                <div class="search-container">
                    <form action="search.php" method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control search-input" 
                               placeholder="Nhập từ khóa để tìm kiếm (vd: lắp ráp, mô hình, ba lô,...)">
                        <button type="submit" class="btn search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- User Actions -->
            <div class="col-md-4">
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn user-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['customer_name']; ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $base ?>/customer/my_account.php">Tài khoản</a></li>
                                <li><a class="dropdown-item" href="<?= $base ?>/customer/my_orders.php">Đơn hàng</a></li>
                                <li><a class="dropdown-item" href="<?= $base ?>/customer/my_wishlist.php">Yêu thích</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $base ?>/logout.php">Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php elseif (isAdmin()): ?>
                        <div class="dropdown">
                            <button class="btn user-btn dropdown-toggle" type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-shield"></i> <?php echo $_SESSION['admin_name']; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="admin/index.php">Quản trị</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/logout.php">Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <button class="btn user-btn" data-bs-toggle="modal" data-bs-target="#authModal">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </button>
                    <?php endif; ?>
                    
                        <button class="btn cart-btn position-relative" onclick="window.location.href='<?= $base ?>/cart.php'">
        <i class="fas fa-shopping-cart"></i> Giỏ hàng
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count" style="display: none;">
            0
        </span>
    </button>
                    
                    <button class="btn chat-btn" onclick="openChat()">
                        <i class="fas fa-comments"></i> Chat
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="row mt-3">
            <div class="col-12">
                <nav class="nav-menu">
                    <ul class="nav justify-content-center">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base ?>/index.php">Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base ?>/products.php">Sản phẩm</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownProduct" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Danh mục
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownProduct">
                                <?php foreach ($categories as $cate): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= $base ?>/products.php?category=<?= $cate['id'] ?>">
                                            <?= htmlspecialchars($cate['name']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base ?>/promotions.php">Khuyến mãi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base ?>/news.php">Tin tức</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base ?>/contact.php">Liên hệ</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</header>

<!-- Messages -->
<div class="container mt-3">
    <?php showMessage(); ?>
</div>

<!-- Chat Widget -->
<div id="chatWidget" class="chat-widget">
    <div class="chat-header" onclick="toggleChat()">
        <span><i class="fas fa-comments"></i> Hỗ trợ trực tuyến</span>
        <i class="fas fa-times"></i>
    </div>
    <div class="chat-body">
        <div id="chatMessages" class="chat-messages">
            <div class="message admin">Xin chào! Chúng tôi có thể giúp gì cho bạn?</div>
        </div>
        <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." onkeypress="handleChatEnter(event)">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<!-- Auth Modal -->
<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content auth-modal-content">
            <button type="button" class="btn-close auth-modal-close" data-bs-dismiss="modal"></button>
            
            <div class="auth-modal-container" id="authModalContainer">
                <!-- Login Form -->
                <div class="auth-form-section login-section active">
                    <div class="row h-100">
                        <div class="col-md-6 auth-form-col">
                            <div class="auth-form-wrapper">
                                <h3 class="auth-title">Chào mừng trở lại</h3>
                                <div id="loginAlert"></div>
                                
                                <form id="modalLoginForm">
                                    <div class="form-group mb-3">
                                        <input type="email" class="form-control auth-input" name="email" placeholder="Nhập địa chỉ email..." required>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <input type="password" class="form-control auth-input" name="password" placeholder="Nhập mật khẩu..." required>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <a href="#" class="forgot-password">Quên mật khẩu?</a>
                                    </div>
                                    
                                    <button type="submit" class="btn auth-submit-btn w-100">
                                        <span class="auth-loading"></span>
                                        ĐĂNG NHẬP
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-6 auth-promo-col">
                            <div class="auth-promo-wrapper">
                                <h4 class="promo-title">Thành viên mới?</h4>
                                <p class="promo-text">Đăng ký và khám phá cực nhiều ưu đãi mới!</p>
                                <div class="promo-image">
                                    <img src="<?= $base ?>/public/1.png" alt="Đồ chơi" class="img-fluid">
                                </div>
                                <button class="btn promo-btn" onclick="switchToRegister()">ĐĂNG KÝ</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Register Form -->
                <div class="auth-form-section register-section">
                    <div class="row h-100">
                        <div class="col-md-6 auth-form-col">
                            <div class="auth-form-wrapper">
                                <h3 class="auth-title">Cùng là một nhà!</h3>
                                <div id="registerAlert"></div>
                                
                                <form id="modalRegisterForm">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <input type="text" class="form-control auth-input" name="first_name" placeholder="Họ" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <input type="text" class="form-control auth-input" name="last_name" placeholder="Tên" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <input type="email" class="form-control auth-input" name="email" placeholder="Nhập địa chỉ email..." required>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <input type="password" class="form-control auth-input" name="password" placeholder="Nhập mật khẩu..." required minlength="6">
                                    </div>
                                    
                                    <button type="submit" class="btn auth-submit-btn w-100">
                                        <span class="auth-loading"></span>
                                        ĐĂNG KÝ
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-6 auth-promo-col">
                            <div class="auth-promo-wrapper">
                                <h4 class="promo-title">Thành viên thân thiết?</h4>
                                <p class="promo-text">Đăng nhập để tiếp tục hành trình mua sắm!</p>
                                <div class="promo-image">
                                    <img src="<?= $base ?>/public/1.png" alt="Đồ chơi" class="img-fluid">
                                </div>
                                <button class="btn promo-btn" onclick="switchToLogin()">ĐĂNG NHẬP</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= $base ?>/assets/js/main.js"></script>
<script src="<?= $base ?>/assets/js/auth-modal.js"></script>

<?php if (isLoggedIn()): ?>
<script>
// Cập nhật số lượng giỏ hàng khi tải trang
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
</script>
<?php endif; ?>

</body>
</html>