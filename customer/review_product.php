<?php
$pageTitle = 'Đánh giá sản phẩm';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$customer_id = $_SESSION['customer_id'];
$order_id = (int)$_GET['order_id'];

// Kiểm tra đơn hàng
$order = fetchOne("SELECT * FROM orders WHERE id = ? AND customer_id = ? AND status = 'delivered'", 
                  [$order_id, $customer_id]);

if (!$order) {
    setMessage('Đơn hàng không tồn tại hoặc chưa được giao!', 'danger');
    redirect('my_orders.php');
}

// Lấy sản phẩm trong đơn hàng
$orderProducts = fetchAll("SELECT od.*, p.name, p.image 
                           FROM order_details od 
                           JOIN products p ON od.product_id = p.id 
                           WHERE od.order_id = ?", [$order_id]);

// Xử lý gửi đánh giá
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    
    // Xử lý upload ảnh
    $images = [];
    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] == 0) {
                $fileName = uploadFile([
                    'name' => $_FILES['images']['name'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['images']['error'][$key]
                ], '../assets/img/reviews/');
                
                if ($fileName) {
                    $images[] = $fileName;
                }
            }
        }
    }
    
    $imagesJson = json_encode($images);
    
    // Kiểm tra đã đánh giá chưa
    $existingReview = fetchOne("SELECT id FROM reviews WHERE product_id = ? AND customer_id = ? AND order_id = ?", 
                               [$product_id, $customer_id, $order_id]);
    
    if ($existingReview) {
        setMessage('Bạn đã đánh giá sản phẩm này rồi!', 'warning');
    } else {
        $sql = "INSERT INTO reviews (product_id, customer_id, order_id, rating, comment, images) VALUES (?, ?, ?, ?, ?, ?)";
        if (executeQuery($sql, [$product_id, $customer_id, $order_id, $rating, $comment, $imagesJson])) {
            setMessage('Đánh giá thành công!', 'success');
        } else {
            setMessage('Có lỗi xảy ra, vui lòng thử lại!', 'danger');
        }
    }
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <?php include 'sidebar.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5>Đánh giá sản phẩm - Đơn hàng #<?php echo $order['order_code']; ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($orderProducts as $product): ?>
                        <?php
                        // Kiểm tra đã đánh giá chưa
                        $existingReview = fetchOne("SELECT * FROM reviews WHERE product_id = ? AND customer_id = ? AND order_id = ?", 
                                                   [$product['product_id'], $customer_id, $order_id]);
                        ?>
                        
                        <div class="product-review-item border rounded p-3 mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <img src="../assets/img/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                         class="img-fluid rounded" alt="<?php echo $product['name']; ?>">
                                </div>
                                
                                <div class="col-md-9">
                                    <h6><?php echo $product['name']; ?></h6>
                                    <p class="text-muted">Số lượng: <?php echo $product['quantity']; ?></p>
                                    
                                    <?php if ($existingReview): ?>
                                        <div class="alert alert-info">
                                            <h6>Đánh giá của bạn:</h6>
                                            <div class="rating mb-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $existingReview['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p><?php echo nl2br($existingReview['comment']); ?></p>
                                            <small class="text-muted">Đánh giá ngày: <?php echo date('d/m/Y', strtotime($existingReview['created_at'])); ?></small>
                                        </div>
                                    <?php else: ?>
                                        <form method="POST" enctype="multipart/form-data" class="review-form">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Đánh giá sao:</label>
                                                <div class="star-rating">
                                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                                        <input type="radio" id="star<?php echo $i; ?>_<?php echo $product['product_id']; ?>" 
                                                               name="rating" value="<?php echo $i; ?>" required>
                                                        <label for="star<?php echo $i; ?>_<?php echo $product['product_id']; ?>">
                                                            <i class="fas fa-star"></i>
                                                        </label>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="comment_<?php echo $product['product_id']; ?>" class="form-label">Nhận xét:</label>
                                                <textarea class="form-control" id="comment_<?php echo $product['product_id']; ?>" 
                                                          name="comment" rows="3" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..." required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="images_<?php echo $product['product_id']; ?>" class="form-label">Hình ảnh (tùy chọn):</label>
                                                <input type="file" class="form-control" id="images_<?php echo $product['product_id']; ?>" 
                                                       name="images[]" multiple accept="image/*">
                                                <small class="text-muted">Có thể chọn nhiều ảnh</small>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-star"></i> Gửi đánh giá
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.star-rating input {
    display: none;
}

.star-rating label {
    cursor: pointer;
    width: 30px;
    height: 30px;
    display: block;
    color: #ddd;
    font-size: 20px;
    transition: color 0.2s;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #ffc107;
}
</style>

<?php include '../includes/footer.php'; ?>
