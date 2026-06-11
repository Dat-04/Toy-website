<?php
$pageTitle = 'Đánh giá của tôi';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$customer_id = $_SESSION['customer_id'];

// Lấy danh sách đánh giá
$reviews = fetchAll("SELECT r.*, p.name as product_name, p.image as product_image 
                     FROM reviews r 
                     JOIN products p ON r.product_id = p.id 
                     WHERE r.customer_id = ? 
                     ORDER BY r.created_at DESC", [$customer_id]);
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <?php include 'sidebar.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5>Đánh giá của tôi</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có đánh giá nào</p>
                            <a href="../products.php" class="btn btn-primary">Mua sắm ngay</a>
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
                                        <h6><?php echo $review['product_name']; ?></h6>
                                        
                                        <div class="rating mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="ms-2 text-muted">
                                                <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                            </span>
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
                                            <div class="admin-reply bg-light p-3 rounded mt-2">
                                                <strong>Phản hồi từ shop:</strong>
                                                <p class="mb-0 mt-1"><?php echo nl2br($review['admin_reply']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
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
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
