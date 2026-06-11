<?php
$pageTitle = 'Danh sách yêu thích';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$customer_id = $_SESSION['customer_id'];

// Xử lý xóa sản phẩm khỏi wishlist
if ($_POST['action'] ?? '' == 'remove_wishlist') {
    $product_id = (int)$_POST['product_id'];
    executeQuery("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?", [$customer_id, $product_id]);
    setMessage('Đã xóa sản phẩm khỏi danh sách yêu thích!', 'success');
}

// Lấy danh sách yêu thích
$wishlistItems = fetchAll("SELECT w.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
                           FROM wishlist w 
                           JOIN products p ON w.product_id = p.id 
                           WHERE w.customer_id = ? 
                           ORDER BY w.created_at DESC", [$customer_id]);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách yêu thích</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($wishlistItems)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có sản phẩm yêu thích nào</p>
                            <a href="../products.php" class="btn btn-primary">KHÁM PHÁ SẢN PHẨM</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($wishlistItems as $item): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="position-relative">
                                            <img src="../assets/img/<?php echo $item['image'] ?: 'default.jpg'; ?>" 
                                                 class="card-img-top" alt="<?php echo $item['name']; ?>" style="height: 200px; object-fit: cover;">
                                            <form method="POST" class="position-absolute top-0 end-0 m-2">
                                                <input type="hidden" name="action" value="remove_wishlist">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo $item['name']; ?></h6>
                                            <div class="price mb-3">
                                                <?php if ($item['sale_price']): ?>
                                                    <span class="text-danger fw-bold"><?php echo formatMoney($item['sale_price']); ?></span>
                                                    <span class="text-muted text-decoration-line-through ms-2"><?php echo formatMoney($item['price']); ?></span>
                                                <?php else: ?>
                                                    <span class="fw-bold"><?php echo formatMoney($item['price']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <?php if ($item['stock_quantity'] > 0): ?>
                                                    <button class="btn btn-primary btn-sm flex-fill" 
                                                            onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm flex-fill" disabled>
                                                        Hết hàng
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="../product_detail.php?id=<?php echo $item['product_id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
