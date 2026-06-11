<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

$pageTitle = 'Quản lý đơn hàng - Admin';

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $order_id = (int)$_POST['order_id'];
    
    switch ($action) {
        case 'update_status':
            $status = $_POST['status'];
            $current_order = fetchOne("SELECT status FROM orders WHERE id = ?", [$order_id]);
            
            // Kiểm tra thứ tự trạng thái
            $status_order = ['pending', 'confirmed', 'shipping', 'delivered'];
            $current_index = array_search($current_order['status'], $status_order);
            $new_index = array_search($status, $status_order);
            
            if ($new_index <= $current_index) {
                setMessage('Không thể chọn trạng thái cũ!', 'danger');
                break;
            }

            // Cập nhật trạng thái thanh toán
            $payment_status = ($status == 'pending') ? 'pending' : 'paid';

            if (executeQuery("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?", 
                [$status, $payment_status, $order_id])) {
                setMessage('Cập nhật trạng thái đơn hàng thành công!', 'success');
                
                // Tạo thông báo
                $order = fetchOne("SELECT * FROM orders WHERE id = ?", [$order_id]);
                $message = "Đơn hàng #{$order['order_code']} đã được cập nhật trạng thái: " . match($status) {
                    'confirmed' => 'Đã xác nhận',
                    'shipping' => 'Đang giao hàng',
                    'delivered' => 'Đã giao hàng',
                    default => $status
                };
                executeQuery("INSERT INTO notifications (title, message, type) VALUES (?, ?, 'order')", 
                           ["Cập nhật đơn hàng", $message]);
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
    }
}

// Lấy tham số lọc
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 15;
$offset = ($page - 1) * $limit;

// Xây dựng query
$whereClause = "WHERE 1=1";
$params = [];

if ($status_filter) {
    $whereClause .= " AND o.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $whereClause .= " AND (o.order_code LIKE ? OR c.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Lấy tổng số đơn hàng
$totalQuery = "SELECT COUNT(*) as total FROM orders o 
               LEFT JOIN customers c ON o.customer_id = c.id 
               $whereClause";
$total = fetchOne($totalQuery, $params)['total'];

// Lấy danh sách đơn hàng
$ordersQuery = "SELECT o.*, c.full_name as customer_name, c.email as customer_email 
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                $whereClause 
                ORDER BY o.created_at DESC 
                LIMIT $limit OFFSET $offset";
$orders = fetchAll($ordersQuery, $params);
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
    <style>
        .toast-container.center {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        .confirmation-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9998;
            backdrop-filter: blur(3px);
        }

        .custom-toast.confirmation {
            position: relative;
            transform: none;
            opacity: 1;
            min-width: 400px;
            margin-bottom: 0;
            background: #fff;
            border: none;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .custom-toast.confirmation .toast-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            padding: 12px 15px;
        }

        .custom-toast.confirmation .toast-body {
            padding: 15px;
            background: #fff;
            border-radius: 0 0 8px 8px;
        }

        .custom-toast.confirmation .toast-body p {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .custom-toast.confirmation .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 8px 16px;
        }

        .custom-toast.confirmation .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 8px 16px;
        }

        .custom-toast.confirmation .toast-icon {
            color: #ffc107;
            font-size: 1.2em;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Quản lý đơn hàng</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exportOrders()">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </button>
                </div>
            </div>
            
            <?php showMessage(); ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Tìm theo mã đơn hoặc tên khách hàng..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                <option value="shipping" <?php echo $status_filter == 'shipping' ? 'selected' : ''; ?>>Đang giao hàng</option>
                                <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Đã giao</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="orders.php" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Orders Statistics -->
            <div class="row mb-4">
                <?php
                $stats = [
                    'pending' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'],
                    'confirmed' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'confirmed'")['count'],
                    'shipping' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'shipping'")['count'],
                    'delivered' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")['count']
                ];
                ?>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-warning"><?php echo $stats['pending']; ?></h5>
                            <small>Chờ xác nhận</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-info"><?php echo $stats['confirmed']; ?></h5>
                            <small>Đã xác nhận</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-primary"><?php echo $stats['shipping']; ?></h5>
                            <small>Đang giao</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-success"><?php echo $stats['delivered']; ?></h5>
                            <small>Đã giao</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thanh toán</th>
                                    <th>Ngày đặt</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): 
                                    // Xác định các trạng thái có thể chọn tiếp theo
                                    $status_order = ['pending', 'confirmed', 'shipping', 'delivered'];
                                    $current_index = array_search($order['status'], $status_order);
                                    $available_statuses = array_slice($status_order, $current_index + 1);
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $order['order_code']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo $order['customer_name']; ?></strong><br>
                                                <small class="text-muted"><?php echo $order['customer_email']; ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo formatMoney($order['total_amount']); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($order['status'] != 'cancelled'): ?>
                                                <form method="POST" class="d-inline status-form">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="current_status" value="<?php echo $order['status']; ?>">
                                                    <select name="status" class="form-select form-select-sm" 
                                                            onchange="confirmStatusChange(this)" 
                                                            style="background-color: <?php 
                                                                echo match($order['status']) {
                                                                    'pending' => '#fff3cd',
                                                                    'confirmed' => '#d1ecf1',
                                                                    'shipping' => '#cce5ff',
                                                                    'delivered' => '#d4edda',
                                                                    default => '#ffffff'
                                                                };
                                                            ?>">
                                                        <option value="<?php echo $order['status']; ?>" selected>
                                                            <?php echo match($order['status']) {
                                                                'pending' => 'Chờ xác nhận',
                                                                'confirmed' => 'Đã xác nhận',
                                                                'shipping' => 'Đang giao hàng',
                                                                'delivered' => 'Đã giao hàng',
                                                                default => 'Không xác định'
                                                            }; ?>
                                                        </option>
                                                        <?php foreach ($available_statuses as $status): ?>
                                                            <option value="<?php echo $status; ?>">
                                                                <?php echo match($status) {
                                                                    'confirmed' => 'Đã xác nhận',
                                                                    'shipping' => 'Đang giao hàng',
                                                                    'delivered' => 'Đã giao hàng',
                                                                    default => 'Không xác định'
                                                                }; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Đã hủy</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                echo match($order['payment_status']) {
                                                    'paid' => 'bg-success',
                                                    'failed' => 'bg-danger',
                                                    default => 'bg-warning'
                                                };
                                            ?>">
                                                <?php 
                                                echo match($order['payment_status']) {
                                                    'paid' => 'Đã thanh toán',
                                                    'failed' => 'Thất bại',
                                                    default => 'Chưa thanh toán'
                                                };
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="invoice.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline-info" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
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
                            $baseUrl = 'orders.php' . ($queryString ? '?' . $queryString . '&' : '?');
                            echo paginate($total, $limit, $page, $baseUrl);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
    function exportOrders() {
        window.open('export_orders.php', '_blank');
    }

    function confirmStatusChange(selectElement) {
        const form = selectElement.closest('form');
        const newStatus = selectElement.value;
        const currentStatus = form.querySelector('input[name="current_status"]').value;
        
        if (newStatus === currentStatus) return;
        
        const statusTexts = {
            'pending': 'Chờ xác nhận',
            'confirmed': 'Đã xác nhận',
            'shipping': 'Đang giao hàng',
            'delivered': 'Đã giao hàng'
        };

        // Tạo dialog
        const dialogHtml = `
            <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-circle text-warning me-2"></i>
                                Xác nhận thay đổi
                            </h5>
                            <button type="button" class="btn-close" onclick="closeDialog('${currentStatus}', this)"></button>
                        </div>
                        <div class="modal-body">
                            <p>Bạn có chắc muốn thay đổi trạng thái đơn hàng thành "${statusTexts[newStatus]}"?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="submitStatusForm(this)">Xác nhận</button>
                            <button type="button" class="btn btn-secondary" onclick="closeDialog('${currentStatus}', this)">Hủy</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Thêm dialog vào body
        const dialogWrapper = document.createElement('div');
        dialogWrapper.innerHTML = dialogHtml;
        dialogWrapper.id = 'statusDialog';
        document.body.appendChild(dialogWrapper);

        // Lưu reference đến form
        dialogWrapper.dataset.formId = form.id = 'form_' + Math.random().toString(36).substr(2, 9);
    }

    function submitStatusForm(button) {
        const dialog = button.closest('#statusDialog');
        const form = document.getElementById(dialog.dataset.formId);
        if (form) {
            dialog.remove();
            form.submit();
        }
    }

    function closeDialog(currentStatus, element) {
        const dialog = element.closest('#statusDialog');
        const form = document.getElementById(dialog.dataset.formId);
        if (form) {
            form.querySelector('select[name="status"]').value = currentStatus;
        }
        dialog.remove();
    }
    </script>
</body>
</html>
