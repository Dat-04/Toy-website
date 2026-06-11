<?php
$pageTitle = 'Sản phẩm';
include 'includes/header.php';

// Lấy tham số tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = (int)($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Xây dựng query
$whereClause = "WHERE p.status = 'active'";
$params = [];

if ($search) {
    $whereClause .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}

if ($category_id) {
    $whereClause .= " AND p.category_id = ?";
    $params[] = $category_id;
}

// Sắp xếp
$orderClause = match($sort) {
    'price_asc' => 'ORDER BY p.price ASC',
    'price_desc' => 'ORDER BY p.price DESC',
    'name' => 'ORDER BY p.name ASC',
    'oldest' => 'ORDER BY p.created_at ASC',
    default => 'ORDER BY p.created_at DESC'
};

// Lấy tổng số sản phẩm
$totalQuery = "SELECT COUNT(*) as total FROM products p $whereClause";
$total = fetchOne($totalQuery, $params)['total'];

// Lấy sản phẩm
$productsQuery = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  $whereClause 
                  $orderClause 
                  LIMIT $limit OFFSET $offset";
$products = fetchAll($productsQuery, $params);

// Lấy danh mục
$categories = fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active">Sản phẩm</li>
        </ol>
    </nav>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6>Bộ lọc</h6>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <!-- Tìm kiếm -->
                        <div class="mb-3">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" placeholder="Nhập tên sản phẩm...">
                        </div>
                        
                        <!-- Danh mục -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Danh mục</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Sắp xếp -->
                        <div class="mb-3">
                            <label for="sort" class="form-label">Sắp xếp</label>
                            <select class="form-control" id="sort" name="sort">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Cũ nhất</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
                        <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Xóa bộ lọc</a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Results info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">Hiển thị <?php echo count($products); ?> trong tổng số <?php echo $total; ?> sản phẩm</p>
                <div class="view-options">
                    <button class="btn btn-outline-secondary btn-sm active" onclick="setView('grid')">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="setView('list')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div id="productsGrid" class="row">
                <?php if (empty($products)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>Không tìm thấy sản phẩm nào</h5>
                            <p class="text-muted">Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6 mb-4 product-item">
                            <div class="product-card card h-100">
                                <div class="product-image">
                                    <img src="assets/img/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                         class="card-img-top" alt="<?php echo $product['name']; ?>">
                                    
                                    <?php if ($product['sale_price']): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['featured']): ?>
                                        <span class="badge bg-warning position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-star"></i> Nổi bật
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                                           class="text-decoration-none"><?php echo $product['name']; ?></a>
                                    </h6>
                                    <p class="card-text text-muted small">
                                        <?php echo substr($product['description'], 0, 80) . '...'; ?>
                                    </p>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-tag"></i> <?php echo $product['category_name']; ?>
                                    </p>

                                    <?php
                                    // Lấy đánh giá trung bình và số lượng đã bán
                                    $ratingQuery = "SELECT COALESCE(AVG(rating), 0) as avg_rating, 
                                                         COUNT(*) as total_ratings 
                                                  FROM reviews 
                                                  WHERE product_id = ?";
                                    $rating = fetchOne($ratingQuery, [$product['id']]);
                                    $avgRating = $rating ? $rating['avg_rating'] : 0;
                                    $totalRatings = $rating ? $rating['total_ratings'] : 0;
                                    
                                    // Đếm số đơn hàng đã giao thành công
                                    $salesQuery = "SELECT COUNT(DISTINCT o.id) as total_sales 
                                                 FROM orders o 
                                                 JOIN order_details oi ON o.id = oi.order_id 
                                                 WHERE oi.product_id = ? 
                                                 AND o.status = 'delivered'";
                                    $sales = fetchOne($salesQuery, [$product['id']]);
                                    $totalSales = $sales ? $sales['total_sales'] : 0;
                                    ?>
                                    
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="ratings">
                                            <?php if ($totalRatings > 0): ?>
                                                <?php
                                                $avgRating = round($avgRating, 1);
                                                $fullStars = floor($avgRating);
                                                $hasHalfStar = $avgRating - $fullStars >= 0.5;
                                                ?>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $fullStars): ?>
                                                        <i class="fas fa-star text-warning"></i>
                                                    <?php elseif ($hasHalfStar && $i == $fullStars + 1): ?>
                                                        <i class="fas fa-star-half-alt text-warning"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star text-warning"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                                <span class="text-muted small">(<?php echo $totalRatings; ?>)</span>
                                            <?php else: ?>
                                                <span class="text-muted small">Chưa có đánh giá</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="vr"></div>
                                        <div class="sales">
                                            <span class="text-muted small">
                                                Đã bán: <?php echo number_format($totalSales); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="price mb-2">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="sale-price text-danger fw-bold"><?php echo formatMoney($product['sale_price']); ?></span>
                                            <span class="original-price text-muted text-decoration-line-through ms-2"><?php echo formatMoney($product['price']); ?></span>
                                        <?php else: ?>
                                            <span class="price fw-bold"><?php echo formatMoney($product['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="stock-info">
                                            <?php if ($product['stock_quantity'] > 0): ?>
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle"></i> Còn hàng (<?php echo $product['stock_quantity']; ?>)
                                                </small>
                                            <?php else: ?>
                                                <small class="text-danger">
                                                    <i class="fas fa-times-circle"></i> Hết hàng
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-actions d-flex align-items-center gap-2">
                                            <button class="btn btn-sm <?php echo $product['stock_quantity'] > 0 ? 'btn-primary' : 'btn-secondary disabled'; ?>" 
                                                    onclick="addToCart(<?php echo $product['id']; ?>)"
                                                    <?php echo $product['stock_quantity'] > 0 ? '' : 'disabled'; ?>>
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total > $limit): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php
                    $currentUrl = 'products.php';
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $baseUrl = $currentUrl . ($queryString ? '?' . $queryString . '&' : '?');
                    
                    echo paginate($total, $limit, $page, $baseUrl);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function setView(viewType) {
    const grid = document.getElementById('productsGrid');
    const buttons = document.querySelectorAll('.view-options button');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    if (viewType === 'list') {
        grid.className = 'row';
        document.querySelectorAll('.product-item').forEach(item => {
            item.className = 'col-12 mb-3 product-item';
        });
    } else {
        grid.className = 'row';
        document.querySelectorAll('.product-item').forEach(item => {
            item.className = 'col-lg-4 col-md-6 mb-4 product-item';
        });
    }
}

// Auto submit form when filters change
document.getElementById('sort').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('category').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>

<?php include 'includes/footer.php'; ?>
