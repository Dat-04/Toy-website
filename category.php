<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$category_id = $_GET['id'] ?? 0;
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Get category info
$category_query = "SELECT * FROM categories WHERE id = ? AND status = 'active'";
$category_stmt = $conn->prepare($category_query);
$category_stmt->bind_param("i", $category_id);
$category_stmt->execute();
$category = $category_stmt->get_result()->fetch_assoc();

if (!$category) {
    header('Location: index.php');
    exit();
}

// Get total products count
$count_query = "SELECT COUNT(*) as total FROM products WHERE category_id = ? AND status = 'active'";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $category_id);
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// Get products
$products_query = "SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY name LIMIT ? OFFSET ?";
$products_stmt = $conn->prepare($products_query);
$products_stmt->bind_param("iii", $category_id, $limit, $offset);
$products_stmt->execute();
$products = $products_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($category['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><?php echo htmlspecialchars($category['name']); ?></h2>
            <?php if (!empty($category['description'])): ?>
                <p class="text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
            <?php endif; ?>
            <small class="text-muted"><?php echo $total_products; ?> sản phẩm</small>
        </div>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <h5>Chưa có sản phẩm nào trong danh mục này</h5>
            <p class="text-muted">Vui lòng quay lại sau hoặc xem các danh mục khác</p>
            <a href="index.php" class="btn btn-primary">Về trang chủ</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card h-100">
                        <div class="product-image">
                            <img src="assets/img/<?php echo htmlspecialchars($product['image']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-overlay">
                                <button class="btn btn-light btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <button class="btn btn-light btn-sm" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <div class="price">
                                <span class="sale-price"><?php echo number_format($product['price']); ?>đ</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm w-100">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
