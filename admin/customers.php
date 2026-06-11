<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

$pageTitle = 'Quản lý khách hàng - Admin';

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'toggle_status':
            $customer_id = (int)$_POST['customer_id'];
            $account_id = (int)$_POST['account_id'];
            $status = $_POST['status'] == 'active' ? 'inactive' : 'active';
            
            if (executeQuery("UPDATE accounts SET status = ? WHERE id = ?", [$status, $account_id])) {
                setMessage('Cập nhật trạng thái khách hàng thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'delete':
            $customer_id = (int)$_POST['customer_id'];
            $account_id = (int)$_POST['account_id'];
            
            // Kiểm tra xem khách hàng có đơn hàng không
            $orderCount = fetchOne("SELECT COUNT(*) as count FROM orders WHERE customer_id = ?", [$customer_id])['count'];
            
            if ($orderCount > 0) {
                setMessage('Không thể xóa khách hàng này vì đã có đơn hàng!', 'warning');
            } else {
                if (executeQuery("DELETE FROM accounts WHERE id = ?", [$account_id])) {
                    setMessage('Xóa khách hàng thành công!', 'success');
                } else {
                    setMessage('Có lỗi xảy ra!', 'danger');
                }
            }
            break;
    }
}

// Lấy tham số tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 15;
$offset = ($page - 1) * $limit;

// Xây dựng query
$whereClause = "WHERE 1=1";
$params = [];

if ($search) {
    $whereClause .= " AND (c.full_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $whereClause .= " AND a.status = ?";
    $params[] = $status_filter;
}

// Lấy tổng số khách hàng
$totalQuery = "SELECT COUNT(*) as total FROM customers c 
               JOIN accounts a ON c.account_id = a.id 
               $whereClause";
$total = fetchOne($totalQuery, $params)['total'];

// Lấy danh sách khách hàng
$customersQuery = "SELECT c.*, a.status as account_status, a.created_at as register_date,
                          COUNT(o.id) as total_orders,
                          COALESCE(SUM(o.total_amount), 0) as total_spent
                   FROM customers c 
                   JOIN accounts a ON c.account_id = a.id 
                   LEFT JOIN orders o ON c.id = o.customer_id AND o.status = 'delivered'
                   $whereClause 
                   GROUP BY c.id
                   ORDER BY c.created_at DESC 
                   LIMIT $limit OFFSET $offset";
$customers = fetchAll($customersQuery, $params);
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
                <h1 class="h3">Quản lý khách hàng</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exportCustomers()">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </button>
                </div>
            </div>
            
            <?php showMessage(); ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Tìm theo tên, email, số điện thoại..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Tạm khóa</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="customers.php" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Customer Statistics -->
            <div class="row mb-4">
                <?php
                $totalCustomers = fetchOne("SELECT COUNT(*) as count FROM customers")['count'];
                $activeCustomers = fetchOne("SELECT COUNT(*) as count FROM customers c JOIN accounts a ON c.account_id = a.id WHERE a.status = 'active'")['count'];
                $newCustomersThisMonth = fetchOne("SELECT COUNT(*) as count FROM customers WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")['count'];
                ?>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo $totalCustomers; ?></h4>
                            <small>Tổng khách hàng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-success"><?php echo $activeCustomers; ?></h4>
                            <small>Đang hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-info"><?php echo $newCustomersThisMonth; ?></h4>
                            <small>Mới tháng này</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Customers Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Liên hệ</th>
                                    <th>Đơn hàng</th>
                                    <th>Tổng chi tiêu</th>
                                    <th>Ngày đăng ký</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo $customer['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/img/avatars/<?php echo $customer['avatar'] ?: 'default.png'; ?>" 
                                                     class="rounded-circle me-2" width="40" height="40">
                                                <div>
                                                    <strong><?php echo $customer['full_name']; ?></strong><br>
                                                    <small class="text-muted"><?php echo $customer['gender'] ? ucfirst($customer['gender']) : ''; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="fas fa-envelope"></i> <?php echo $customer['email']; ?><br>
                                                <?php if ($customer['phone']): ?>
                                                    <i class="fas fa-phone"></i> <?php echo $customer['phone']; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $customer['total_orders']; ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo formatMoney($customer['total_spent']); ?></strong>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y', strtotime($customer['register_date'])); ?></small>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                <input type="hidden" name="account_id" value="<?php echo $customer['account_id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $customer['account_status']; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $customer['account_status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $customer['account_status'] == 'active' ? 'Hoạt động' : 'Tạm khóa'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="customer_detail.php?id=<?php echo $customer['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                <input type="hidden" name="account_id" value="<?php echo $customer['account_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        <?php echo $customer['total_orders'] > 0 ? 'disabled title="Không thể xóa vì đã có đơn hàng"' : ''; ?>>
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
                            $baseUrl = 'customers.php' . ($queryString ? '?' . $queryString . '&' : '?');
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
    function exportCustomers() {
        window.open('export_customers.php', '_blank');
    }
    </script>
</body>
</html>
