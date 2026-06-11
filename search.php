<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$search_query = $_GET['q'] ?? '';
$category_id = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'name';

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Build search query
$where_conditions = ["p.status = 'active'"];
$params = [];
$param_types = "";

if (!empty($search_query)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

if (!empty($category_id)) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $param_types .= "i";
}

if (!empty($min_price)) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
    $param_types .= "d";
}

if (!empty($max_price)) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
    $param_types .= "d";
}

$where_clause = implode(" AND ", $where_conditions);

// Sort options
$sort_options = [
    'name' => 'p.name ASC',
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'newest' => 'p.created_at DESC'
];
$order_by = $sort_options[$sort] ?? 'p.name ASC';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// Get products
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE $where_clause 
                   ORDER BY $order_by 
                   LIMIT ? OFFSET ?";

$all_params = array_merge($params, [$limit, $offset]);
$all_param_types = $param_types . "ii";

$products_stmt = $conn->prepare($products_query);
if (!empty($all_params)) {
    $products_stmt->bind_param($all_param_types, ...$all_params);
}
$products_stmt->execute();
$products = $products_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get categories for filter
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories = $conn->query($categories_query)->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-filter"></i> Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Khoảng giá</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control" 
                                           placeholder="Từ" value="<?php echo htmlspecialchars($min_price); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control" 
                                           placeholder="Đến" value="<?php echo htmlspecialchars($max_price); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Lọc sản phẩm
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>Kết quả tìm kiếm</h4>
                    <?php if (!empty($search_query)): ?>
                        <p class="text-muted">Tìm kiếm cho: "<?php echo htmlspecialchars($search_query); ?>"</p>
                    <?php endif; ?>
                    <small class="text-muted">Tìm thấy <?php echo $total_products; ?> sản phẩm</small>
                </div>
                
                <div>
                    <form method="GET" class="d-inline">
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_id); ?>">
                        <input type="hidden" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>">
                        <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>">
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                        </select>
                    </form>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>Không tìm thấy sản phẩm nào</h5>
                    <p class="text-muted">Hãy thử tìm kiếm với từ khóa khác hoặc điều chỉnh bộ lọc</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card product-card h-100">
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
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
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
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
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
