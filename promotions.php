<?php
$pageTitle = 'Khuyến mãi';
include 'includes/header.php';

// Lấy danh sách mã giảm giá đang hoạt động và chưa được sử dụng
$activeDiscounts = [];
if (isset($_SESSION['customer_id'])) {
    $sql = "SELECT d.* 
            FROM discounts d
            WHERE d.status = 'active' 
            AND (d.end_date IS NULL OR d.end_date >= CURDATE())
            AND (d.start_date IS NULL OR d.start_date <= CURDATE())
            AND (d.max_uses = 0 OR d.used_count < d.max_uses)
            AND NOT EXISTS (
                SELECT 1 FROM orders o 
                WHERE o.customer_id = ? 
                AND o.discount_id = d.id 
                AND o.status != 'cancelled'
            )
            ORDER BY d.created_at DESC";
    $activeDiscounts = fetchAll($sql, [$_SESSION['customer_id']]);
}

// Lấy sản phẩm đang giảm giá
$saleProducts = fetchAll("SELECT p.*, c.name as category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         WHERE p.sale_price > 0 
                         AND p.status = 'active' 
                         ORDER BY (p.price - p.sale_price)/p.price DESC 
                         LIMIT 12");
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active">Khuyến mãi</li>
        </ol>
    </nav>

    <!-- Banner khuyến mãi -->
    <div class="promo-banner-large mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 text-danger fw-bold">MEGA SALE</h1>
                <p class="lead">Giảm giá lên đến 70% cho nhiều sản phẩm</p>
                <a href="products.php" class="btn btn-danger btn-lg">Mua ngay</a>
            </div>
            <div class="col-md-4">
                <img src="assets/img/banners/sale.jpg" alt="Mega Sale" class="img-fluid rounded">
            </div>
        </div>
    </div>

    <!-- Mã giảm giá -->
    <section class="discount-codes mb-5">
        <h2 class="section-title mb-4">Mã giảm giá</h2>
        <div class="row">
            <?php if (!isset($_SESSION['customer_id'])): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Vui lòng <a href="login.php">đăng nhập</a> để xem các mã giảm giá có sẵn.
                    </div>
                </div>
            <?php elseif (empty($activeDiscounts)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Hiện tại chưa có mã giảm giá nào có thể sử dụng.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($activeDiscounts as $discount): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="discount-card">
                            <div class="discount-header">
                                <h3 class="discount-name"><?= htmlspecialchars($discount['name']) ?></h3>
                                <div class="discount-code"><?= htmlspecialchars($discount['code']) ?></div>
                            </div>
                            <div class="discount-body">
                                <div class="discount-value">
                                    <?php if ($discount['type'] == 'percentage'): ?>
                                        Giảm <?= number_format($discount['value']) ?>%
                                    <?php else: ?>
                                        Giảm <?= number_format($discount['value']) ?>đ
                                    <?php endif; ?>
                                </div>
                                <?php if ($discount['min_order_amount'] > 0): ?>
                                    <div class="discount-min">
                                        Đơn hàng tối thiểu: <?= number_format($discount['min_order_amount']) ?>đ
                                    </div>
                                <?php endif; ?>
                                <?php if ($discount['end_date']): ?>
                                    <div class="discount-expiry">
                                        Hết hạn: <?= date('d/m/Y', strtotime($discount['end_date'])) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($discount['max_uses']): ?>
                                    <div class="discount-uses">
                                        Còn lại: <?= $discount['max_uses'] - $discount['used_count'] ?> lượt
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-code" 
                                    data-code="<?= htmlspecialchars($discount['code']) ?>">
                                Sao chép mã
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Sản phẩm giảm giá -->
    <section class="sale-products">
        <h2 class="section-title mb-4">Sản phẩm giảm giá</h2>
        <div class="row">
            <?php if (empty($saleProducts)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Hiện tại chưa có sản phẩm nào đang giảm giá.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($saleProducts as $product): ?>
                    <div class="col-md-3 mb-4">
                        <div class="product-card card h-100">
                            <div class="product-image">
                                <img src="assets/img/<?= $product['image'] ?: 'default.jpg' ?>" 
                                     class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="product-overlay">
                                    <button class="btn btn-primary btn-sm" onclick="addToCart(<?= $product['id'] ?>)">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="addToWishlist(<?= $product['id'] ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                                
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                    -<?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%
                                </span>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                                <p class="card-text text-muted small"><?= htmlspecialchars($product['category_name']) ?></p>
                                <div class="price">
                                    <span class="sale-price text-danger fw-bold">
                                        <?= number_format($product['sale_price']) ?>đ
                                    </span>
                                    <span class="original-price text-muted text-decoration-line-through ms-2">
                                        <?= number_format($product['price']) ?>đ
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="product_detail.php?id=<?= $product['id'] ?>" 
                                   class="btn btn-primary btn-sm w-100">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
// Copy discount code to clipboard
document.querySelectorAll('.copy-code').forEach(button => {
    button.addEventListener('click', function() {
        const code = this.dataset.code;
        navigator.clipboard.writeText(code).then(() => {
            // Change button text temporarily
            const originalText = this.innerHTML;
            this.innerHTML = 'Đã sao chép!';
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 