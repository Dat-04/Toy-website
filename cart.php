<?php
$pageTitle = 'Giỏ hàng';
include 'includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$customer_id = $_SESSION['customer_id'];

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_quantity':
            $cart_id = (int)$_POST['cart_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity > 0) {
                executeQuery("UPDATE cart SET quantity = ? WHERE id = ? AND customer_id = ?", 
                           [$quantity, $cart_id, $customer_id]);
                setMessage('Cập nhật số lượng thành công!', 'success');
            } else {
                executeQuery("DELETE FROM cart WHERE id = ? AND customer_id = ?", [$cart_id, $customer_id]);
                setMessage('Đã xóa sản phẩm khỏi giỏ hàng!', 'success');
            }
            break;
            
        case 'remove_item':
            $cart_id = (int)$_POST['cart_id'];
            executeQuery("DELETE FROM cart WHERE id = ? AND customer_id = ?", [$cart_id, $customer_id]);
            setMessage('Đã xóa sản phẩm khỏi giỏ hàng!', 'success');
            break;
            
        case 'clear_cart':
            executeQuery("DELETE FROM cart WHERE customer_id = ?", [$customer_id]);
            setMessage('Đã xóa tất cả sản phẩm khỏi giỏ hàng!', 'success');
            break;
    }
}

// Lấy sản phẩm trong giỏ hàng
$cartItems = fetchAll("SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.customer_id = ? 
                       ORDER BY c.created_at DESC", [$customer_id]);

// Tính tổng tiền
$totalAmount = 0;
foreach ($cartItems as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $totalAmount += $price * $item['quantity'];
}
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active">Giỏ hàng</li>
        </ol>
    </nav>
    
    <h1 class="h3 mb-4">Giỏ hàng của bạn</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h4>Giỏ hàng trống</h4>
            <p class="text-muted">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Sản phẩm trong giỏ hàng (<?php echo count($cartItems); ?>)</h5>
                        <form method="POST" class="d-inline" 
                              onsubmit="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm?')">
                            <input type="hidden" name="action" value="clear_cart">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash"></i> Xóa tất cả
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item border-bottom p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="assets/img/<?php echo $item['image'] ?: 'default.jpg'; ?>" 
                                             class="img-fluid rounded" alt="<?php echo $item['name']; ?>">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                        <div class="price">
                                            <?php if ($item['sale_price']): ?>
                                                <span class="text-danger fw-bold"><?php echo formatMoney($item['sale_price']); ?></span>
                                                <span class="text-muted text-decoration-line-through ms-2"><?php echo formatMoney($item['price']); ?></span>
                                            <?php else: ?>
                                                <span class="fw-bold"><?php echo formatMoney($item['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            Còn lại: <?php echo $item['stock_quantity']; ?> sản phẩm
                                        </small>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <form method="POST" class="quantity-form">
                                            <input type="hidden" name="action" value="update_quantity">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <div class="input-group">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="changeCartQuantity(this, -1)">-</button>
                                                <input type="number" class="form-control text-center" 
                                                       name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock_quantity']; ?>"
                                                       onchange="this.form.submit()">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="changeCartQuantity(this, 1)">+</button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <?php 
                                        $itemPrice = $item['sale_price'] ?: $item['price'];
                                        $itemTotal = $itemPrice * $item['quantity'];
                                        ?>
                                        <strong><?php echo formatMoney($itemTotal); ?></strong>
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                            <input type="hidden" name="action" value="remove_item">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span><?php echo formatMoney($totalAmount); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-primary"><?php echo formatMoney($totalAmount); ?></strong>
                        </div>
                        
                        <!-- Mã giảm giá -->
                        <div class="mb-3 position-relative">
                            <label for="discount_code" class="form-label">Mã giảm giá</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="discount_code" placeholder="Nhập mã giảm giá" autocomplete="off" onfocus="showDiscountSuggestions()" oninput="filterDiscountSuggestions()">
                                <button class="btn btn-outline-secondary" type="button" onclick="applyDiscount()">
                                    Áp dụng
                                </button>
                            </div>
                            <ul class="discount-suggestions" id="discountSuggestions" style="display:none;"></ul>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-credit-card"></i> Thanh toán
                        </a>
                    </div>
                </div>
                
                <!-- Sản phẩm gợi ý -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Có thể bạn quan tâm</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $suggestedProducts = fetchAll("SELECT * FROM products 
                                                       WHERE status = 'active' AND featured = 1 
                                                       ORDER BY RAND() LIMIT 3");
                        foreach ($suggestedProducts as $product):
                        ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="assets/img/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                     width="60" height="60" class="rounded me-3">
                                <div class="flex-fill">
                                    <h6 class="mb-1"><?php echo substr($product['name'], 0, 30) . '...'; ?></h6>
                                    <div class="price">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="text-danger fw-bold"><?php echo formatMoney($product['sale_price']); ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold"><?php echo formatMoney($product['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm mt-1" 
                                            onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Toast thông báo -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
  <div id="toast-discount" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toast-discount-body">
        <!-- Nội dung thông báo -->
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script>
function changeCartQuantity(button, delta) {
    const quantityInput = button.parentNode.querySelector('input[name="quantity"]');
    const currentValue = parseInt(quantityInput.value);
    const newValue = currentValue + delta;
    const max = parseInt(quantityInput.max);
    const min = parseInt(quantityInput.min);
    
    if (newValue >= min && newValue <= max) {
        quantityInput.value = newValue;
        quantityInput.form.submit();
    } else {
        if (newValue > max) {
            showNotification('Số lượng vượt quá tồn kho!', 'error');
            quantityInput.value = max;
        }
    }
}

// Thêm validation khi nhập trực tiếp số lượng
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('input[name="quantity"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const value = parseInt(this.value);
            const max = parseInt(this.max);
            const min = parseInt(this.min);
            
            if (value > max) {
                showNotification('Số lượng vượt quá tồn kho!', 'error');
                this.value = max;
            } else if (value < min) {
                this.value = min;
            }
            
            if (value >= min && value <= max) {
                this.form.submit();
            }
        });
    });
});

let discountList = [];

function showDiscountSuggestions() {
    const input = document.getElementById('discount_code');
    const suggestionBox = document.getElementById('discountSuggestions');
    if (discountList.length === 0) {
        fetch('api/available_discounts.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    discountList = data.discounts;
                    renderDiscountSuggestions();
                }
            });
    } else {
        renderDiscountSuggestions();
    }
    suggestionBox.style.display = 'block';
}

function renderDiscountSuggestions(filter = '') {
    const suggestionBox = document.getElementById('discountSuggestions');
    let html = '';
    const filtered = discountList.filter(d => d.code.toLowerCase().includes(filter.toLowerCase()));
    if (filtered.length === 0) {
        html = '<li>Không có mã phù hợp</li>';
    } else {
        html = filtered.map(d => `<li onclick="selectDiscountCode('${d.code}')"><b>${d.code}</b> - ${d.name} (${d.type === 'percentage' ? d.value + '%': d.value + 'đ'})</li>`).join('');
    }
    suggestionBox.innerHTML = html;
    suggestionBox.style.display = 'block';
}

function filterDiscountSuggestions() {
    const input = document.getElementById('discount_code');
    renderDiscountSuggestions(input.value);
}

function selectDiscountCode(code) {
    document.getElementById('discount_code').value = code;
    document.getElementById('discountSuggestions').style.display = 'none';
}

function showDiscountToast(message, type = 'success') {
    const toastEl = document.getElementById('toast-discount');
    const toastBody = document.getElementById('toast-discount-body');
    toastBody.textContent = message;
    toastEl.className = 'toast align-items-center text-bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0';
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

function applyDiscount() {
    const discountCode = document.getElementById('discount_code').value.trim();
    
    if (!discountCode) {
        showDiscountToast('Vui lòng nhập mã giảm giá', 'danger');
        return;
    }
    
    fetch('api/apply_discount.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            discount_code: discountCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDiscountToast(data.message, 'success');
            // Cập nhật tổng tiền mới trên giao diện
            const totalEls = document.querySelectorAll('.text-primary');
            totalEls.forEach(el => el.textContent = formatMoney(data.new_total));
            // Hiển thị số tiền giảm giá nếu muốn
            let discountInfo = document.getElementById('discountInfo');
            if (!discountInfo) {
                discountInfo = document.createElement('div');
                discountInfo.id = 'discountInfo';
                discountInfo.className = 'd-flex justify-content-between mb-2 text-success';
                const parent = document.querySelector('.card-body');
                parent.insertBefore(discountInfo, parent.querySelector('hr'));
            }
            discountInfo.innerHTML = `<span>Giảm giá:</span><span>-` + formatMoney(data.discount_value) + `</span>`;
        } else {
            showDiscountToast(data.message || 'Mã giảm giá không hợp lệ!', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showDiscountToast('Có lỗi xảy ra, vui lòng thử lại!', 'danger');
    });
}

function formatMoney(amount) {
    return amount.toLocaleString('vi-VN') + ' VNĐ';
}

document.addEventListener('click', function(e) {
    const box = document.getElementById('discountSuggestions');
    const input = document.getElementById('discount_code');
    if (!box.contains(e.target) && e.target !== input) {
        box.style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
