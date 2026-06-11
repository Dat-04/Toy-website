<?php
require_once '../config/database.php';
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Xử lý xóa mã giảm giá
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    execute("UPDATE discounts SET status = 'inactive' WHERE id = ?", [$id]);
    header('Location: discounts.php?msg=deleted');
    exit;
}

// Lấy danh sách mã giảm giá
$discounts = fetchAll("SELECT * FROM discounts ORDER BY created_at DESC");

$title = "Quản lý mã giảm giá";
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quản lý mã giảm giá</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="discount_form.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php
                    $msg = $_GET['msg'];
                    switch ($msg) {
                        case 'added':
                            echo 'Thêm mã giảm giá thành công!';
                            break;
                        case 'updated':
                            echo 'Cập nhật mã giảm giá thành công!';
                            break;
                        case 'deleted':
                            echo 'Xóa mã giảm giá thành công!';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã</th>
                            <th>Tên</th>
                            <th>Loại</th>
                            <th>Giá trị</th>
                            <th>Đơn tối thiểu</th>
                            <th>Lượt dùng</th>
                            <th>Thời hạn</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discounts as $discount): ?>
                            <tr>
                                <td><?= $discount['id'] ?></td>
                                <td><?= htmlspecialchars($discount['code']) ?></td>
                                <td><?= htmlspecialchars($discount['name']) ?></td>
                                <td>
                                    <?= $discount['type'] === 'percentage' ? 'Phần trăm' : 'Số tiền cố định' ?>
                                </td>
                                <td>
                                    <?php if ($discount['type'] === 'percentage'): ?>
                                        <?= number_format($discount['value']) ?>%
                                    <?php else: ?>
                                        <?= number_format($discount['value']) ?>đ
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($discount['min_order_amount']) ?>đ</td>
                                <td>
                                    <?= number_format($discount['used_count']) ?>/
                                    <?= $discount['max_uses'] ? number_format($discount['max_uses']) : '∞' ?>
                                </td>
                                <td>
                                    <?php if ($discount['start_date'] && $discount['end_date']): ?>
                                        <?= date('d/m/Y', strtotime($discount['start_date'])) ?> -
                                        <?= date('d/m/Y', strtotime($discount['end_date'])) ?>
                                    <?php else: ?>
                                        Không giới hạn
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $discount['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= $discount['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="discount_form.php?id=<?= $discount['id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Bạn có chắc muốn xóa mã giảm giá này?')">
                                        <input type="hidden" name="id" value="<?= $discount['id'] ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

