<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

$pageTitle = 'Quản lý sản phẩm - Admin';

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $price = (float)$_POST['price'];
            $sale_price = $_POST['sale_price'] ? (float)$_POST['sale_price'] : null;
            $category_id = (int)$_POST['category_id'];
            $stock_quantity = (int)$_POST['stock_quantity'];
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            // Upload ảnh chính
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadFile($_FILES['image'], '../assets/img/');
            }
            
            $sql = "INSERT INTO products (name, description, price, sale_price, category_id, image, stock_quantity, featured) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            if (executeQuery($sql, [$name, $description, $price, $sale_price, $category_id, $image, $stock_quantity, $featured])) {
                setMessage('Thêm sản phẩm thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'edit':
            $id = (int)$_POST['id'];
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $price = (float)$_POST['price'];
            $sale_price = $_POST['sale_price'] ? (float)$_POST['sale_price'] : null;
            $category_id = (int)$_POST['category_id'];
            $stock_quantity = (int)$_POST['stock_quantity'];
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            $sql = "UPDATE products SET name = ?, description = ?, price = ?, sale_price = ?, category_id = ?, stock_quantity = ?, featured = ?";
            $params = [$name, $description, $price, $sale_price, $category_id, $stock_quantity, $featured];
            
            // Upload ảnh mới nếu có
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadFile($_FILES['image'], '../assets/img/');
                if ($image) {
                    $sql .= ", image = ?";
                    $params[] = $image;
                }
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            if (executeQuery($sql, $params)) {
                setMessage('Cập nhật sản phẩm thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'delete':
            $id = (int)$_POST['id'];
            if (executeQuery("DELETE FROM products WHERE id = ?", [$id])) {
                setMessage('Xóa sản phẩm thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'toggle_status':
            $id = (int)$_POST['id'];
            $status = $_POST['status'] == 'active' ? 'inactive' : 'active';
            if (executeQuery("UPDATE products SET status = ? WHERE id = ?", [$status, $id])) {
                setMessage('Cập nhật trạng thái thành công!', 'success');
            }
            break;
    }
}

// Lấy danh sách sản phẩm
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = "WHERE 1=1";
$params = [];

if ($search) {
    $whereClause .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}

if ($category_filter) {
    $whereClause .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

$totalQuery = "SELECT COUNT(*) as total FROM products p $whereClause";
$total = fetchOne($totalQuery, $params)['total'];

$productsQuery = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  $whereClause 
                  ORDER BY p.created_at DESC 
                  LIMIT $limit OFFSET $offset";
$products = fetchAll($productsQuery, $params);

// Lấy danh mục
$categories = fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Quản lý sản phẩm</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </button>
            </div>
            
            <?php showMessage(); ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="category">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="products.php" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Kho</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <img src="../assets/img/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                                 width="50" height="50" class="rounded">
                                        </td>
                                        <td>
                                            <strong><?php echo $product['name']; ?></strong>
                                            <?php if ($product['featured']): ?>
                                                <span class="badge bg-warning ms-1">Nổi bật</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $product['category_name']; ?></td>
                                        <td>
                                            <?php if ($product['sale_price']): ?>
                                                <span class="text-danger fw-bold"><?php echo formatMoney($product['sale_price']); ?></span><br>
                                                <small class="text-muted text-decoration-line-through"><?php echo formatMoney($product['price']); ?></small>
                                            <?php else: ?>
                                                <?php echo formatMoney($product['price']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $product['status']; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $product['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $product['status'] == 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total > $limit): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <?php
                            $queryParams = $_GET;
                            unset($queryParams['page']);
                            $queryString = http_build_query($queryParams);
                            $baseUrl = 'products.php' . ($queryString ? '?' . $queryString . '&' : '?');
                            echo paginate($total, $limit, $page, $baseUrl);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Tên sản phẩm *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Danh mục *</label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">Giá gốc *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="sale_price" class="form-label">Giá khuyến mãi</label>
                                <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="stock_quantity" class="form-label">Số lượng kho *</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                    <label class="form-check-label" for="featured">
                                        Sản phẩm nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editProductForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_name" class="form-label">Tên sản phẩm *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_category_id" class="form-label">Danh mục *</label>
                                <select class="form-control" id="edit_category_id" name="category_id" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="edit_description" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="edit_price" class="form-label">Giá gốc *</label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="edit_sale_price" class="form-label">Giá khuyến mãi</label>
                                <input type="number" class="form-control" id="edit_sale_price" name="sale_price" step="0.01">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="edit_stock_quantity" class="form-label">Số lượng kho *</label>
                                <input type="number" class="form-control" id="edit_stock_quantity" name="stock_quantity" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_image" class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                                <div id="current_image" class="mt-2"></div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_featured" name="featured">
                                    <label class="form-check-label" for="edit_featured">
                                        Sản phẩm nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
    function editProduct(product) {
        document.getElementById('edit_id').value = product.id;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_category_id').value = product.category_id;
        document.getElementById('edit_description').value = product.description;
        document.getElementById('edit_price').value = product.price;
        document.getElementById('edit_sale_price').value = product.sale_price || '';
        document.getElementById('edit_stock_quantity').value = product.stock_quantity;
        document.getElementById('edit_featured').checked = product.featured == 1;
        
        // Hiển thị ảnh hiện tại
        const currentImageDiv = document.getElementById('current_image');
        if (product.image) {
            currentImageDiv.innerHTML = `<img src="../assets/img/${product.image}" width="100" class="rounded">`;
        } else {
            currentImageDiv.innerHTML = '';
        }
        
        new bootstrap.Modal(document.getElementById('editProductModal')).show();
    }
    </script>
</body>
</html>
