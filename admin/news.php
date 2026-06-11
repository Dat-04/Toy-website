<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

$pageTitle = 'Quản lý tin tức - Admin';

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $title = sanitize($_POST['title']);
            $content = sanitize($_POST['content']);
            
            // Upload ảnh
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadFile($_FILES['image'], '../assets/img/news/');
            }
            
            $sql = "INSERT INTO news (title, content, image) VALUES (?, ?, ?)";
            if (executeQuery($sql, [$title, $content, $image])) {
                setMessage('Thêm tin tức thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'edit':
            $id = (int)$_POST['id'];
            $title = sanitize($_POST['title']);
            $content = sanitize($_POST['content']);
            
            $sql = "UPDATE news SET title = ?, content = ?";
            $params = [$title, $content];
            
            // Upload ảnh mới nếu có
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadFile($_FILES['image'], '../assets/img/news/');
                if ($image) {
                    $sql .= ", image = ?";
                    $params[] = $image;
                }
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            if (executeQuery($sql, $params)) {
                setMessage('Cập nhật tin tức thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'delete':
            $id = (int)$_POST['id'];
            if (executeQuery("DELETE FROM news WHERE id = ?", [$id])) {
                setMessage('Xóa tin tức thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'toggle_status':
            $id = (int)$_POST['id'];
            $status = $_POST['status'] == 'active' ? 'inactive' : 'active';
            if (executeQuery("UPDATE news SET status = ? WHERE id = ?", [$status, $id])) {
                setMessage('Cập nhật trạng thái thành công!', 'success');
            }
            break;
    }
}

// Lấy danh sách tin tức
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = "WHERE 1=1";
$params = [];

if ($search) {
    $whereClause .= " AND title LIKE ?";
    $params[] = "%$search%";
}

$totalQuery = "SELECT COUNT(*) as total FROM news $whereClause";
$total = fetchOne($totalQuery, $params)['total'];

$newsQuery = "SELECT * FROM news $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$news_list = fetchAll($newsQuery, $params);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Quản lý tin tức</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                    <i class="fas fa-plus"></i> Thêm tin tức
                </button>
            </div>
            
            <?php showMessage(); ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Tìm kiếm tin tức..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="news.php" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- News Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tiêu đề</th>
                                    <th>Nội dung</th>
                                    <th>Ngày tạo</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($news_list as $news): ?>
                                    <tr>
                                        <td><?php echo $news['id']; ?></td>
                                        <td>
                                            <img src="../assets/img/news/<?php echo $news['image'] ?: 'default.jpg'; ?>" 
                                                 width="50" height="50" class="rounded">
                                        </td>
                                        <td>
                                            <strong><?php echo $news['title']; ?></strong>
                                        </td>
                                        <td><?php echo substr(strip_tags($news['content']), 0, 100) . '...'; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($news['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $news['status']; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $news['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $news['status'] == 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editNews(<?php echo htmlspecialchars(json_encode($news)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa tin tức này?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
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
                            $baseUrl = 'news.php' . ($queryString ? '?' . $queryString . '&' : '?');
                            echo paginate($total, $limit, $page, $baseUrl);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add News Modal -->
    <div class="modal fade" id="addNewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm tin tức mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Nội dung *</label>
                            <textarea class="form-control summernote" id="content" name="content" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm tin tức</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit News Modal -->
    <div class="modal fade" id="editNewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa tin tức</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Tiêu đề *</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_content" class="form-label">Nội dung *</label>
                            <textarea class="form-control summernote" id="edit_content" name="content" required></textarea>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });

    function editNews(news) {
        document.getElementById('edit_id').value = news.id;
        document.getElementById('edit_title').value = news.title;
        $('#edit_content').summernote('code', news.content);
        
        // Hiển thị ảnh hiện tại
        const currentImageDiv = document.getElementById('current_image');
        if (news.image) {
            currentImageDiv.innerHTML = `<img src="../assets/img/news/${news.image}" width="100" class="rounded">`;
        } else {
            currentImageDiv.innerHTML = '';
        }
        
        new bootstrap.Modal(document.getElementById('editNewsModal')).show();
    }
    </script>
</body>
</html> 