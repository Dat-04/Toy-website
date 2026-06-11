<?php
$pageTitle = 'Trang chủ - Cửa hàng đồ chơi';
include 'includes/header.php';

// Lấy banner
$banners = fetchAll("SELECT * FROM banner WHERE status = 'active' ORDER BY position");

// Lấy sản phẩm nổi bật
$featuredProducts = fetchAll("SELECT p.*, c.name as category_name FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.featured = 1 AND p.status = 'active' 
                              ORDER BY p.created_at DESC LIMIT 8");

// Lấy sản phẩm mới
$newProducts = fetchAll("SELECT p.*, c.name as category_name FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         WHERE p.status = 'active' 
                         ORDER BY p.created_at DESC LIMIT 8");
?>

<!-- Banner Slider -->
<section class="banner-slider">
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($banners as $index => $banner): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="assets/img/banners/<?php echo $banner['image']; ?>" class="d-block w-100" alt="<?php echo $banner['image']; ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

<!-- Categories -->
<section class="categories py-5">
    <div class="container">
        <h2 class="text-center mb-4">Danh mục sản phẩm</h2>
        <div class="row justify-content-center">
            <?php
            $categories = fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
            foreach ($categories as $category):
            ?>
                <div class="col-md-2 col-6 mb-3">
                    <a href="category.php?id=<?php echo $category['id']; ?>" class="category-card text-decoration-none">
                        <div class="card h-100 text-center category-hover">
                            <div class="card-body">
                                <i class="fas fa-toy-brick fa-3x text-primary mb-3"></i>
                                <h6 class="card-title"><?php echo $category['name']; ?></h6>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Sản phẩm nổi bật</h2>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="product-card card h-100">
                        <div class="product-image">
                            <img src="assets/img/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo $product['name']; ?>">
                            <div class="product-overlay">
                                <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $product['name']; ?></h6>
                            <p class="card-text text-muted small"><?php echo substr($product['description'], 0, 50) . '...'; ?></p>
                            <div class="price">
                                <?php if ($product['sale_price']): ?>
                                    <span class="sale-price"><?php echo formatMoney($product['sale_price']); ?></span>
                                    <span class="original-price text-muted text-decoration-line-through"><?php echo formatMoney($product['price']); ?></span>
                                <?php else: ?>
                                    <span class="price"><?php echo formatMoney($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="products.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
        </div>
    </div>
</section>

<!-- New Products -->
<section class="new-products py-5">
    <div class="container">
        <h2 class="text-center mb-4">Sản phẩm mới</h2>
        <div class="row">
            <?php foreach ($newProducts as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="product-card card h-100">
                        <div class="product-image">
                            <img src="assets/img/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo $product['name']; ?>">
                            <div class="product-overlay">
                                <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $product['name']; ?></h6>
                            <p class="card-text text-muted small"><?php echo substr($product['description'], 0, 50) . '...'; ?></p>
                            <div class="price">
                                <?php if ($product['sale_price']): ?>
                                    <span class="sale-price"><?php echo formatMoney($product['sale_price']); ?></span>
                                    <span class="original-price text-muted text-decoration-line-through"><?php echo formatMoney($product['price']); ?></span>
                                <?php else: ?>
                                    <span class="price"><?php echo formatMoney($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3>Đăng ký nhận tin khuyến mãi</h3>
                <p>Nhận thông tin về sản phẩm mới và ưu đãi đặc biệt</p>
            </div>
            <div class="col-md-6">
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Nhập email của bạn">
                        <button class="btn btn-light" type="submit">Đăng ký</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
