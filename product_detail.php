<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = (int)$_GET['id'];

// Lấy thông tin sản phẩm
$product = fetchOne("SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.id = ? AND p.status = 'active'", [$product_id]);

if (!$product) {
    setMessage('Sản phẩm không tồn tại!', 'danger');
    redirect('products.php');
}

$pageTitle = $product['name'];
include 'includes/header.php';

// Lấy đánh giá sản phẩm
$reviews = fetchAll("SELECT r.*, c.full_name, c.avatar 
                     FROM reviews r 
                     JOIN customers c ON r.customer_id = c.id 
                     WHERE r.product_id = ? AND r.status = 'approved' 
                     ORDER BY r.created_at DESC", [$product_id]);

// Tính điểm đánh giá trung bình
$avgRating = 0;
$totalReviews = count($reviews);
if ($totalReviews > 0) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $avgRating = round($totalRating / $totalReviews, 1);
}

// Sản phẩm liên quan
$relatedProducts = fetchAll("SELECT * FROM products 
                             WHERE category_id = ? AND id != ? AND status = 'active' 
                             ORDER BY RAND() LIMIT 4", [$product['category_id'], $product_id]);
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
            <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></li>
            <li class="breadcrumb-item active"><?php echo $product['name']; ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6">
            <div class="product-gallery">
                <div class="main-image mb-3">
                    <img src="assets/img/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                         class="img-fluid rounded" alt="<?php echo $product['name']; ?>">
                </div>
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-md-6">
            <div class="product-info">
                <h1 class="h3"><?php echo $product['name']; ?></h1>
                
                <!-- Rating -->
                <div class="rating mb-3">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $avgRating ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2">(<?php echo $totalReviews; ?> đánh giá)</span>
                    </div>
                </div>
                
                <!-- Price -->
                <div class="price mb-3">
                    <?php if ($product['sale_price']): ?>
                        <span class="sale-price h4 text-danger fw-bold"><?php echo formatMoney($product['sale_price']); ?></span>
                        <span class="original-price h5 text-muted text-decoration-line-through ms-2"><?php echo formatMoney($product['price']); ?></span>
                        <span class="badge bg-danger ms-2">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </span>
                    <?php else: ?>
                        <span class="price h4 fw-bold"><?php echo formatMoney($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Stock -->
                <div class="stock mb-3">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="text-success">
                            <i class="fas fa-check-circle"></i> Còn hàng (<?php echo $product['stock_quantity']; ?> sản phẩm)
                        </span>
                    <?php else: ?>
                        <span class="text-danger">
                            <i class="fas fa-times-circle"></i> Hết hàng
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <div class="description mb-4">
                    <h6>Mô tả sản phẩm:</h6>
                    <p><?php echo nl2br($product['description']); ?></p>
                </div>
                
                <!-- Add to Cart Form -->
                <?php if ($product['stock_quantity'] > 0): ?>
                    <form id="addToCartForm" class="mb-4">
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <label for="quantity" class="form-label mb-0 me-2">Số lượng:</label>
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(-1)">-</button>
                            <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width: 70px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(1)">+</button>
                            <button type="button" class="btn btn-primary btn-lg ms-3" onclick="addToCartWithQuantity(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                            </button>
                            <button type="button" class="btn btn-outline-danger ms-2" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <!-- Product Meta -->
                <div class="product-meta">
                    <p><strong>Danh mục:</strong> <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></p>
                    <p><strong>Mã sản phẩm:</strong> SP<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></p>
                </div>
                
                <!-- Share -->
                <div class="share-buttons">
                    <h6>Chia sẻ:</h6>
                    <a href="#" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fab fa-facebook"></i> Facebook
                    </a>
                    <a href="#" class="btn btn-outline-info btn-sm me-2">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="#" class="btn btn-outline-success btn-sm">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                            data-bs-target="#description" type="button" role="tab">
                        Mô tả chi tiết
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                            data-bs-target="#reviews" type="button" role="tab">
                        Đánh giá (<?php echo $totalReviews; ?>)
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="productTabsContent">
                <!-- Description Tab -->
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="p-4">
                        <h5>Thông tin chi tiết</h5>
                        <div class="description-content">
                            <?php echo nl2br($product['description']); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="rating-summary text-center">
                                    <h2 class="display-4"><?php echo $avgRating; ?></h2>
                                    <div class="stars mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $avgRating ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p><?php echo $totalReviews; ?> đánh giá</p>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <!-- Rating Breakdown -->
                                <?php for ($star = 5; $star >= 1; $star--): ?>
                                    <?php
                                    $starCount = count(array_filter($reviews, fn($r) => $r['rating'] == $star));
                                    $percentage = $totalReviews > 0 ? ($starCount / $totalReviews) * 100 : 0;
                                    ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2"><?php echo $star; ?> sao</span>
                                        <div class="progress flex-fill me-2" style="height: 10px;">
                                            <div class="progress-bar bg-warning" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <span class="text-muted"><?php echo $starCount; ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Reviews List -->
                        <div class="reviews-list">
                            <?php if (empty($reviews)): ?>
                                <p class="text-muted text-center py-4">Chưa có đánh giá nào cho sản phẩm này.</p>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item border-bottom py-3">
                                        <div class="d-flex">
                                            <img src="assets/img/avatars/<?php echo $review['avatar'] ?: 'default.png'; ?>" 
                                                 class="rounded-circle me-3" width="50" height="50">
                                            <div class="flex-fill">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $review['full_name']; ?></h6>
                                                        <div class="stars mb-2">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?> small"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small>
                                                </div>
                                                
                                                <p class="mb-2"><?php echo nl2br($review['comment']); ?></p>
                                                
                                                <?php if ($review['images']): ?>
                                                    <div class="review-images mb-2">
                                                        <?php 
                                                        $images = json_decode($review['images'], true);
                                                        foreach ($images as $image):
                                                        ?>
                                                            <img src="assets/img/reviews/<?php echo $image; ?>" 
                                                                 class="img-thumbnail me-2" width="80" height="80">
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($review['admin_reply']): ?>
                                                    <div class="admin-reply bg-light p-3 rounded mt-2">
                                                        <strong>Phản hồi từ shop:</strong>
                                                        <p class="mb-0 mt-1"><?php echo nl2br($review['admin_reply']); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Sản phẩm liên quan</h4>
                <div class="row">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="product-card card h-100">
                                <div class="product-image">
                                    <img src="assets/img/<?php echo $relatedProduct['image'] ?: 'default.jpg'; ?>" 
                                         class="card-img-top" alt="<?php echo $relatedProduct['name']; ?>">
                                    <div class="product-overlay">
                                        <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $relatedProduct['id']; ?>)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="addToWishlist(<?php echo $relatedProduct['id']; ?>)">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <a href="product_detail.php?id=<?php echo $relatedProduct['id']; ?>" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="product_detail.php?id=<?php echo $relatedProduct['id']; ?>" 
                                           class="text-decoration-none"><?php echo $relatedProduct['name']; ?></a>
                                    </h6>
                                    <div class="price">
                                        <?php if ($relatedProduct['sale_price']): ?>
                                            <span class="sale-price text-danger fw-bold"><?php echo formatMoney($relatedProduct['sale_price']); ?></span>
                                            <span class="original-price text-muted text-decoration-line-through ms-2"><?php echo formatMoney($relatedProduct['price']); ?></span>
                                        <?php else: ?>
                                            <span class="price fw-bold"><?php echo formatMoney($relatedProduct['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.main-image img {
    max-height: 500px;
    object-fit: cover;
}

.rating-summary {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
}

.review-item:last-child {
    border-bottom: none !important;
}

.admin-reply {
    border-left: 4px solid #007bff;
}
</style>

<script>
function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    const newValue = currentValue + delta;
    const max = parseInt(quantityInput.max);
    const min = parseInt(quantityInput.min);
    
    if (newValue >= min && newValue <= max) {
        quantityInput.value = newValue;
    }
}

function addToCartWithQuantity(productId) {
    if (!isLoggedIn()) {
        alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
        window.location.href = 'login.php';
        return;
    }
    
    const quantityInput = document.getElementById('quantity');
    const quantity = parseInt(quantityInput.value);
    const max = parseInt(quantityInput.max);
    
    if (quantity > max) {
        showNotification('Số lượng vượt quá tồn kho!', 'error');
        quantityInput.value = max;
        return;
    }
    
    fetch('api/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã thêm sản phẩm vào giỏ hàng!', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Có lỗi xảy ra, vui lòng thử lại!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra, vui lòng thử lại!', 'error');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
