<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/review_helper.php';

if (!isAdmin()) {
    redirect('login.php');
}

$pageTitle = 'Quản lý đánh giá - Admin';

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'reply':
            $review_id = (int)$_POST['review_id'];
            $admin_reply = sanitize($_POST['admin_reply']);
            
            if (executeQuery("UPDATE reviews SET admin_reply = ? WHERE id = ?", [$admin_reply, $review_id])) {
                setMessage('Phản hồi đánh giá thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
            
        case 'delete':
            $review_id = (int)$_POST['review_id'];
            
            if (executeQuery("DELETE FROM reviews WHERE id = ?", [$review_id])) {
                setMessage('Xóa đánh giá thành công!', 'success');
            } else {
                setMessage('Có lỗi xảy ra!', 'danger');
            }
            break;
    }
}

// Tự động duyệt các đánh giá đang pending
$pending_reviews = fetchAll("SELECT * FROM reviews WHERE status = 'pending'");
foreach ($pending_reviews as $review) {
    $result = autoApproveReview($review);
    executeQuery(
        "UPDATE reviews SET status = ?, admin_reply = CASE WHEN ? = 'rejected' THEN ? ELSE admin_reply END WHERE id = ?",
        [$result['status'], $result['status'], $result['message'], $review['id']]
    );
}

// Lấy tham số lọc
$status_filter = $_GET['status'] ?? '';
$rating_filter = $_GET['rating'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 15;
$offset = ($page - 1) * $limit;

// Xây dựng query
$whereClause = "WHERE 1=1";
$params = [];

if ($status_filter) {
    $whereClause .= " AND r.status = ?";
    $params[] = $status_filter;
}

if ($rating_filter) {
    $whereClause .= " AND r.rating = ?";
    $params[] = $rating_filter;
}

// Lấy tổng số đánh giá
$totalQuery = "SELECT COUNT(*) as total FROM reviews r $whereClause";
$total = fetchOne($totalQuery, $params)['total'];

// Lấy danh sách đánh giá
$reviewsQuery = "SELECT r.*, p.name as product_name, p.image as product_image, 
                        c.full_name as customer_name, c.avatar as customer_avatar
                 FROM reviews r 
                 JOIN products p ON r.product_id = p.id 
                 JOIN customers c ON r.customer_id = c.id 
                 $whereClause 
                 ORDER BY r.created_at DESC 
                 LIMIT $limit OFFSET $offset";
$reviews = fetchAll($reviewsQuery, $params);
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
                <h1 class="h3">Quản lý đánh giá</h1>
            </div>
            
            <?php showMessage(); ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select class="form-control" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Bị từ chối</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="rating">
                                <option value="">Tất cả đánh giá</option>
                                <option value="5" <?php echo $rating_filter == '5' ? 'selected' : ''; ?>>5 sao</option>
                                <option value="4" <?php echo $rating_filter == '4' ? 'selected' : ''; ?>>4 sao</option>
                                <option value="3" <?php echo $rating_filter == '3' ? 'selected' : ''; ?>>3 sao</option>
                                <option value="2" <?php echo $rating_filter == '2' ? 'selected' : ''; ?>>2 sao</option>
                                <option value="1" <?php echo $rating_filter == '1' ? 'selected' : ''; ?>>1 sao</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="reviews.php" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Review Statistics -->
            <div class="row mb-4">
                <?php
                $stats = [
                    'total' => fetchOne("SELECT COUNT(*) as count FROM reviews")['count'],
                    'pending' => fetchOne("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")['count'],
                    'approved' => fetchOne("SELECT COUNT(*) as count FROM reviews WHERE status = 'approved'")['count'],
                    'rejected' => fetchOne("SELECT COUNT(*) as count FROM reviews WHERE status = 'rejected'")['count']
                ];
                ?>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-primary"><?php echo $stats['total']; ?></h5>
                            <small>Tổng đánh giá</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-warning"><?php echo $stats['pending']; ?></h5>
                            <small>Chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-success"><?php echo $stats['approved']; ?></h5>
                            <small>Đã duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-danger"><?php echo $stats['rejected']; ?></h5>
                            <small>Bị từ chối</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reviews List -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có đánh giá nào</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-2">
                                        <img src="../assets/img/<?php echo $review['product_image'] ?: 'default.jpg'; ?>" 
                                             class="img-fluid rounded" alt="<?php echo $review['product_name']; ?>">
                                    </div>
                                    
                                    <div class="col-md-10">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1"><?php echo $review['product_name']; ?></h6>
                                                <div class="d-flex align-items-center mb-2">
                                                    <img src="../assets/img/avatars/<?php echo $review['customer_avatar'] ?: 'default.png'; ?>" 
                                                         class="rounded-circle me-2" width="30" height="30">
                                                    <span><?php echo $review['customer_name']; ?></span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="stars mb-1">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        
                                        <p class="mb-2"><?php echo nl2br($review['comment']); ?></p>
                                        
                                        <?php if ($review['images']): ?>
                                            <div class="review-images mb-2">
                                                <?php 
                                                $images = json_decode($review['images'], true);
                                                foreach ($images as $image):
                                                ?>
                                                    <img src="../assets/img/reviews/<?php echo $image; ?>" 
                                                         class="img-thumbnail me-2" width="80" height="80">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($review['admin_reply']): ?>
                                            <div class="admin-reply bg-light p-3 rounded mb-2">
                                                <strong>Phản hồi từ shop:</strong>
                                                <p class="mb-0 mt-1"><?php echo nl2br($review['admin_reply']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-<?php 
                                                    echo match($review['status']) {
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php 
                                                    echo match($review['status']) {
                                                        'pending' => 'Chờ duyệt',
                                                        'approved' => 'Đã duyệt',
                                                        'rejected' => 'Bị từ chối',
                                                        default => 'Không xác định'
                                                    };
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <div>
                                                <!-- Status buttons -->
                                                <?php if ($review['status'] == 'pending'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i> Duyệt
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-times"></i> Từ chối
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <!-- Reply button -->
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="showReplyForm(<?php echo $review['id']; ?>)">
                                                    <i class="fas fa-reply"></i> Phản hồi
                                                </button>
                                                
                                                <!-- Delete button -->
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <!-- Reply form (hidden by default) -->
                                        <div id="replyForm<?php echo $review['id']; ?>" class="reply-form mt-3" style="display: none;">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="reply">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <div class="mb-2">
                                                    <textarea class="form-control" name="admin_reply" rows="3" 
                                                              placeholder="Nhập phản hồi của bạn..."><?php echo $review['admin_reply']; ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-primary">Gửi phản hồi</button>
                                                <button type="button" class="btn btn-sm btn-secondary" 
                                                        onclick="hideReplyForm(<?php echo $review['id']; ?>)">Hủy</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Pagination -->
                    <?php if ($total > $limit): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <?php
                            $queryParams = $_GET;
                            unset($queryParams['page']);
                            $queryString = http_build_query($queryParams);
                            $baseUrl = 'reviews.php' . ($queryString ? '?' . $queryString . '&' : '?');
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
    function showReplyForm(reviewId) {
        document.getElementById('replyForm' + reviewId).style.display = 'block';
    }
    
    function hideReplyForm(reviewId) {
        document.getElementById('replyForm' + reviewId).style.display = 'none';
    }
    </script>
</body>
</html>
