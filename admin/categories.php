<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

$pageTitle = 'Quản lý danh mục - Admin';

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);

            // Kiểm tra trùng tên danh mục
            $exist = fetchOne("SELECT id FROM categories WHERE name = ?", [$name]);
            if ($exist) {
                setMessage('Tên danh mục đã tồn tại!', 'danger');
                break;
            }

            // Upload ảnh
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadFile($_FILES['image'], '../assets/img/categories/');
            }
            $sql = "INSERT INTO categories (name, description, image) VALUES (?, ?, ?)";
            if (executeQuery($sql, [$name, $description, $image])) {
                setMessage('Thêm danh mục thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'edit':
            $id = (int)$_POST['id'];
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            
            $sql = "UPDATE categories SET name = ?, description = ?";
            $params = [$name, $description];
            
            // Upload ảnh mới nếu có
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadFile($_FILES['image'], '../assets/img/categories/');
                if ($image) {
                    $sql .= ", image = ?";
                    $params[] = $image;
                }
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            if (executeQuery($sql, $params)) {
                setMessage('Cập nhật danh mục thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'delete':
            $id = (int)$_POST['id'];
            // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
            $productCount = fetchOne("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$id])['count'];
            
            if ($productCount > 0) {
                setMessage('Không thể xóa danh mục này vì còn có sản phẩm!', 'warning');
            } else {
                if (executeQuery("DELETE FROM categories WHERE id = ?", [$id])) {
                    setMessage('Xóa danh mục thành công!', 'success');
                } else {
                    setMessage('Có lỗi xảy ra!', 'danger');
                }
            }
            break;
            
        case 'toggle_status':
            $id = (int)$_POST['id'];
            $status = $_POST['status'] == 'active' ? 'inactive' : 'active';
            if (executeQuery("UPDATE categories SET status = ? WHERE id = ?", [$status, $id])) {
                setMessage('Cập nhật trạng thái thành công!', 'success');
            }
            break;
    }
}

// Lấy danh sách danh mục
$categories = fetchAll("SELECT c.*, COUNT(p.id) as product_count 
                        FROM categories c 
                        LEFT JOIN products p ON c.id = p.category_id 
                        GROUP BY c.id 
                        ORDER BY c.created_at DESC");
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
                <h1 class="h3">Quản lý danh mục</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Thêm danh mục
                </button>
            </div>
            
            <?php showMessage(); ?>
            
            <!-- Categories Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên danh mục</th>
                                    <th>Mô tả</th>
                                    <th>Số sản phẩm</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td>
                                            <?php if ($category['image']): ?>
                                                <img src="../assets/img/categories/<?php echo $category['image']; ?>" 
                                                     width="50" height="50" class="rounded">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo $category['name']; ?></strong></td>
                                        <td><?php echo substr($category['description'], 0, 50) . '...'; ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $category['product_count']; ?></span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $category['status']; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $category['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $category['status'] == 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        <?php echo $category['product_count'] > 0 ? 'disabled title="Không thể xóa vì còn có sản phẩm"' : ''; ?>>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm danh mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên danh mục *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm danh mục</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Tên danh mục *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <div id="current_image" class="mt-2"></div>
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
    function editCategory(category) {
        document.getElementById('edit_id').value = category.id;
        document.getElementById('edit_name').value = category.name;
        document.getElementById('edit_description').value = category.description;
        
        // Hiển thị ảnh hiện tại
        const currentImageDiv = document.getElementById('current_image');
        if (category.image) {
            currentImageDiv.innerHTML = `<img src="../assets/img/categories/${category.image}" width="100" class="rounded">`;
        } else {
            currentImageDiv.innerHTML = '';
        }
        
        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
    }
    </script>
</body>
</html>
