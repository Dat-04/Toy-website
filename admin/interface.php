<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Xử lý các thao tác CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        // BANNER
        case 'add_banner':
            $title = sanitize($_POST['title']);
            $link = sanitize($_POST['link']);
            $position = (int)$_POST['position'];
            $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadFile($_FILES['image'], '../assets/img/banners/');
            }
            $sql = "INSERT INTO banner (title, image, link, position, status) VALUES (?, ?, ?, ?, ?)";
            if (executeQuery($sql, [$title, $image, $link, $position, $status])) {
                setMessage('Thêm banner thành công!', 'success');
            } else {
                setMessage('Có lỗi khi thêm banner!', 'danger');
            }
            redirect('interface.php');
            break;
        case 'edit_banner':
            $id = (int)$_POST['id'];
            $title = sanitize($_POST['title']);
            $link = sanitize($_POST['link']);
            $position = (int)$_POST['position'];
            $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
            $sql = "UPDATE banner SET title = ?, link = ?, position = ?, status = ?";
            $params = [$title, $link, $position, $status];
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadFile($_FILES['image'], '../assets/img/banners/');
                if ($image) {
                    $sql .= ", image = ?";
                    $params[] = $image;
                }
            }
            $sql .= " WHERE id = ?";
            $params[] = $id;
            if (executeQuery($sql, $params)) {
                setMessage('Cập nhật banner thành công!', 'success');
            } else {
                setMessage('Có lỗi khi cập nhật banner!', 'danger');
            }
            redirect('interface.php');
            break;
        case 'delete_banner':
            $id = (int)$_POST['id'];
            if (executeQuery("DELETE FROM banner WHERE id = ?", [$id])) {
                setMessage('Xóa banner thành công!', 'success');
            } else {
                setMessage('Có lỗi khi xóa banner!', 'danger');
            }
            redirect('interface.php');
            break;
        // TOP MENU
        case 'add_menu':
            $name = sanitize($_POST['name']);
            $link = sanitize($_POST['link']);
            $position = (int)$_POST['position'];
            $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
            $sql = "INSERT INTO top_menu (name, link, position, status) VALUES (?, ?, ?, ?)";
            if (executeQuery($sql, [$name, $link, $position, $status])) {
                setMessage('Thêm menu thành công!', 'success');
            } else {
                setMessage('Có lỗi khi thêm menu!', 'danger');
            }
            redirect('interface.php');
            break;
        case 'edit_menu':
            $id = (int)$_POST['id'];
            $name = sanitize($_POST['name']);
            $link = sanitize($_POST['link']);
            $position = (int)$_POST['position'];
            $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
            $sql = "UPDATE top_menu SET name = ?, link = ?, position = ?, status = ? WHERE id = ?";
            if (executeQuery($sql, [$name, $link, $position, $status, $id])) {
                setMessage('Cập nhật menu thành công!', 'success');
            } else {
                setMessage('Có lỗi khi cập nhật menu!', 'danger');
            }
            redirect('interface.php');
            break;
        case 'delete_menu':
            $id = (int)$_POST['id'];
            if (executeQuery("DELETE FROM top_menu WHERE id = ?", [$id])) {
                setMessage('Xóa menu thành công!', 'success');
            } else {
                setMessage('Có lỗi khi xóa menu!', 'danger');
            }
            redirect('interface.php');
            break;
    }
}

$pageTitle = 'Quản lý giao diện - Admin';

// Lấy dữ liệu các bảng
$banners = fetchAll("SELECT * FROM banner ORDER BY position ASC, id DESC");
$topMenus = fetchAll("SELECT * FROM top_menu ORDER BY position ASC, id DESC");
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
    <h1 class="h3 mb-4">Quản lý giao diện</h1>
    <?php showMessage(); ?>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-image me-2"></i>Banner</span>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBannerModal"><i class="fas fa-plus"></i> Thêm banner</button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Hình ảnh</th><th>Link</th><th>Vị trí</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                        <tbody>
                        <?php foreach ($banners as $b): ?>
                        <tr>
                            <td><img src="../assets/img/banners/<?php echo $b['image']; ?>" width="80"></td>
                            <td><?php echo $b['link']; ?></td>
                            <td><?php echo $b['position']; ?></td>
                            <td><span class="badge bg-<?php echo $b['status']=='active'?'success':'secondary'; ?>"><?php echo $b['status']=='active'?'Hiện':'Ẩn'; ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editBanner(<?php echo htmlspecialchars(json_encode($b)); ?>)"><i class="fas fa-edit"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Xóa banner này?')">
                                    <input type="hidden" name="action" value="delete_banner">
                                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 mb-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bars me-2"></i>Top Menu</span>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMenuModal"><i class="fas fa-plus"></i> Thêm menu</button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Tên</th><th>Link</th><th>Vị trí</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                        <tbody>
                        <?php foreach ($topMenus as $m): ?>
                        <tr>
                            <td><?php echo $m['name']; ?></td>
                            <td><?php echo $m['link']; ?></td>
                            <td><?php echo $m['position']; ?></td>
                            <td><span class="badge bg-<?php echo $m['status']=='active'?'success':'secondary'; ?>"><?php echo $m['status']=='active'?'Hiện':'Ẩn'; ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editMenu(<?php echo htmlspecialchars(json_encode($m)); ?>)"><i class="fas fa-edit"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Xóa menu này?')">
                                    <input type="hidden" name="action" value="delete_menu">
                                    <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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

<!-- Modal Thêm Banner -->
<div class="modal fade" id="addBannerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header"><h5 class="modal-title">Thêm banner</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add_banner">
          <div class="mb-3"><label class="form-label">Tiêu đề</label><input type="text" class="form-control" name="title" required></div>
          <div class="mb-3"><label class="form-label">Hình ảnh</label><input type="file" class="form-control" name="image" accept="image/*" required></div>
          <div class="mb-3"><label class="form-label">Link</label><input type="text" class="form-control" name="link"></div>
          <div class="mb-3"><label class="form-label">Vị trí</label><input type="number" class="form-control" name="position" value="0"></div>
          <div class="mb-3"><label class="form-label">Trạng thái</label><select class="form-control" name="status"><option value="active">Hiện</option><option value="inactive">Ẩn</option></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary">Thêm</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Sửa Banner -->
<div class="modal fade" id="editBannerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Sửa banner</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="edit_banner">
          <input type="hidden" name="id" id="edit_banner_id">
          <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" name="title" id="edit_banner_title" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Hình ảnh (để nguyên nếu không đổi)</label>
            <input type="file" class="form-control" name="image" accept="image/*">
          </div>
          <div class="mb-3">
            <label class="form-label">Link</label>
            <input type="text" class="form-control" name="link" id="edit_banner_link">
          </div>
          <div class="mb-3">
            <label class="form-label">Vị trí</label>
            <input type="number" class="form-control" name="position" id="edit_banner_position" value="0">
          </div>
          <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select class="form-control" name="status" id="edit_banner_status">
              <option value="active">Hiện</option>
              <option value="inactive">Ẩn</option>
            </select>
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

<!-- Modal Thêm Menu -->
<div class="modal fade" id="addMenuModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h5 class="modal-title">Thêm menu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add_menu">
          <div class="mb-3"><label class="form-label">Tên</label><input type="text" class="form-control" name="name" required></div>
          <div class="mb-3"><label class="form-label">Link</label><input type="text" class="form-control" name="link"></div>
          <div class="mb-3"><label class="form-label">Vị trí</label><input type="number" class="form-control" name="position" value="0"></div>
          <div class="mb-3"><label class="form-label">Trạng thái</label><select class="form-control" name="status"><option value="active">Hiện</option><option value="inactive">Ẩn</option></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary">Thêm</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Sửa Menu -->
<div class="modal fade" id="editMenuModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Sửa menu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="edit_menu">
          <input type="hidden" name="id" id="edit_menu_id">
          <div class="mb-3">
            <label class="form-label">Tên</label>
            <input type="text" class="form-control" name="name" id="edit_menu_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Link</label>
            <input type="text" class="form-control" name="link" id="edit_menu_link">
          </div>
          <div class="mb-3">
            <label class="form-label">Vị trí</label>
            <input type="number" class="form-control" name="position" id="edit_menu_position" value="0">
          </div>
          <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select class="form-control" name="status" id="edit_menu_status">
              <option value="active">Hiện</option>
              <option value="inactive">Ẩn</option>
            </select>
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
function editBanner(banner) {
    document.getElementById('edit_banner_id').value = banner.id;
    document.getElementById('edit_banner_title').value = banner.title;
    document.getElementById('edit_banner_link').value = banner.link;
    document.getElementById('edit_banner_position').value = banner.position;
    document.getElementById('edit_banner_status').value = banner.status;
    var editBannerModal = new bootstrap.Modal(document.getElementById('editBannerModal'));
    editBannerModal.show();
}

function editMenu(menu) {
    document.getElementById('edit_menu_id').value = menu.id;
    document.getElementById('edit_menu_name').value = menu.name;
    document.getElementById('edit_menu_link').value = menu.link;
    document.getElementById('edit_menu_position').value = menu.position;
    document.getElementById('edit_menu_status').value = menu.status;
    var editMenuModal = new bootstrap.Modal(document.getElementById('editMenuModal'));
    editMenuModal.show();
}
</script>
</body>
</html>