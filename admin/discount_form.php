<?php
require_once '../config/database.php';
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$discount = null;
$title = "Thêm mã giảm giá";

if ($id) {
    $discount = fetchOne("SELECT * FROM discounts WHERE id = ?", [$id]);
    if (!$discount) {
        header('Location: discounts.php');
        exit;
    }
    $title = "Chỉnh sửa mã giảm giá";
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $value = floatval($_POST['value']);
    $min_order_amount = floatval($_POST['min_order_amount']);
    $max_uses = intval($_POST['max_uses']);
    $start_date = $_POST['start_date'] ?: null;
    $end_date = $_POST['end_date'] ?: null;
    $status = $_POST['status'];

    // Kiểm tra mã giảm giá đã tồn tại chưa
    $exists = fetchOne("SELECT id FROM discounts WHERE code = ? AND id != ?", 
        [$code, $id ?: 0]);
    
    if ($exists) {
        $error = "Mã giảm giá đã tồn tại!";
    } else {
        if ($id) {
            // Cập nhật
            execute("UPDATE discounts SET 
                code = ?, name = ?, type = ?, value = ?, 
                min_order_amount = ?, max_uses = ?, 
                start_date = ?, end_date = ?, status = ?
                WHERE id = ?", 
                [$code, $name, $type, $value, $min_order_amount, $max_uses, 
                 $start_date, $end_date, $status, $id]
            );
            header('Location: discounts.php?msg=updated');
        } else {
            // Thêm mới
            execute("INSERT INTO discounts 
                (code, name, type, value, min_order_amount, max_uses, 
                 start_date, end_date, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())", 
                [$code, $name, $type, $value, $min_order_amount, $max_uses, 
                 $start_date, $end_date, $status]
            );
            header('Location: discounts.php?msg=added');
        }
        exit;
    }
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= $title ?></h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label">Mã giảm giá</label>
                        <input type="text" class="form-control" name="code" 
                               value="<?= $discount['code'] ?? '' ?>" required>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Tên mã giảm giá</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?= $discount['name'] ?? '' ?>">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Loại giảm giá</label>
                        <select class="form-select" name="type" required>
                            <option value="percentage" <?= ($discount['type'] ?? '') === 'percentage' ? 'selected' : '' ?>>
                                Theo phần trăm
                            </option>
                            <option value="fixed" <?= ($discount['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>
                                Số tiền cố định
                            </option>
                        </select>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Giá trị</label>
                        <input type="number" class="form-control" name="value" 
                               value="<?= $discount['value'] ?? 0 ?>" required min="0" step="0.01">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Giá trị đơn hàng tối thiểu</label>
                        <input type="number" class="form-control" name="min_order_amount" 
                               value="<?= $discount['min_order_amount'] ?? 0 ?>" required min="0">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Số lần sử dụng tối đa (0 = không giới hạn)</label>
                        <input type="number" class="form-control" name="max_uses" 
                               value="<?= $discount['max_uses'] ?? 0 ?>" required min="0">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Ngày bắt đầu</label>
                        <input type="date" class="form-control" name="start_date" 
                               value="<?= $discount['start_date'] ?? '' ?>">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Ngày kết thúc</label>
                        <input type="date" class="form-control" name="end_date" 
                               value="<?= $discount['end_date'] ?? '' ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status" required>
                            <option value="active" <?= ($discount['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                                Hoạt động
                            </option>
                            <option value="inactive" <?= ($discount['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                                Không hoạt động
                            </option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <button class="btn btn-primary" type="submit">
                    <?= $id ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
                <a href="discounts.php" class="btn btn-secondary">Hủy</a>
            </form>
        </main>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>
