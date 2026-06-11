<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Thanh toán';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Get cart items
$cart_items = [];
$cart_query = "SELECT c.*, p.name, p.price, p.image, p.stock_quantity 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.customer_id = ?";

if ($cart_stmt = $conn->prepare($cart_query)) {
    $cart_stmt->bind_param("i", $customer_id);
    $cart_stmt->execute();
    $result = $cart_stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    $cart_stmt->close();
} else {
    die("Lỗi truy vấn: " . $conn->error);
}

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping_fee = $subtotal > 500000 ? 0 : 30000; // Free shipping over 500k VND
$tax = $subtotal * 0.1; // 10% tax
$total = $subtotal + $shipping_fee + $tax;

// Get customer info
$customer = [];
$customer_query = "SELECT c.full_name, c.phone, c.address, c.email 
                  FROM customers c 
                  WHERE c.id = ?";

if ($customer_stmt = $conn->prepare($customer_query)) {
    $customer_stmt->bind_param("i", $customer_id);
    $customer_stmt->execute();
    $result = $customer_stmt->get_result();
    $customer = $result->fetch_assoc();
    $customer_stmt->close();
} else {
    die("Lỗi truy vấn: " . $conn->error);
}

// Process order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = $_POST['shipping_address'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'] ?? '';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Generate order code
        $order_code = 'ORD' . date('YmdHis') . rand(100, 999);
        
        // Get discount info from session
        $discount_id = null;
        $discount_value = 0;
        if (isset($_SESSION['applied_discount'])) {
            $discount_id = $_SESSION['applied_discount']['id'];
            $discount_value = $_SESSION['applied_discount']['value'];
            $total -= $discount_value; // Trừ giá trị giảm giá vào tổng tiền
        }
        
        // Create order
        $order_query = "INSERT INTO orders (customer_id, order_code, total_amount, discount_id, shipping_address, payment_method, payment_status, notes, status, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, 'pending', NOW())";
        $order_stmt = $conn->prepare($order_query);
        $order_stmt->bind_param("isdisss", $customer_id, $order_code, $total, $discount_id, $shipping_address, $payment_method, $notes);
        $order_stmt->execute();
        
        $order_id = $conn->insert_id;
        
        // Add order details
        foreach ($cart_items as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $order_detail_query = "INSERT INTO order_details (order_id, product_id, quantity, price, total) 
                                 VALUES (?, ?, ?, ?, ?)";
            $detail_stmt = $conn->prepare($order_detail_query);
            $detail_stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item_total);
            $detail_stmt->execute();
            
            // Update product stock
            $update_stock_query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
            $update_stock_stmt = $conn->prepare($update_stock_query);
            $update_stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stock_stmt->execute();
        }
        
        // Update discount usage count if discount was applied
        if ($discount_id) {
            $update_discount_query = "UPDATE discounts SET used_count = used_count + 1 WHERE id = ?";
            $update_discount_stmt = $conn->prepare($update_discount_query);
            $update_discount_stmt->bind_param("i", $discount_id);
            $update_discount_stmt->execute();
        }
        
        // Create payment record
        $payment_query = "INSERT INTO payments (order_id, payment_method, amount) VALUES (?, ?, ?)";
        $payment_stmt = $conn->prepare($payment_query);
        $payment_stmt->bind_param("isd", $order_id, $payment_method, $total);
        $payment_stmt->execute();
        
        // Clear cart
        $clear_cart_query = "DELETE FROM cart WHERE customer_id = ?";
        $clear_cart_stmt = $conn->prepare($clear_cart_query);
        $clear_cart_stmt->bind_param("i", $customer_id);
        $clear_cart_stmt->execute();
        
        // Clear applied discount from session
        unset($_SESSION['applied_discount']);
        
        $conn->commit();

        // Redirect based on payment method
        if ($payment_method === 'vnpay') {
            // Load VNPay config
            $vnp_config = require_once('config/vnpay.php');
            
            $vnp_Url = $vnp_config['vnp_Url'];
            $vnp_Returnurl = $vnp_config['vnp_ReturnUrl'];
            $vnp_TmnCode = $vnp_config['vnp_TmnCode'];
            $vnp_HashSecret = $vnp_config['vnp_HashSecret'];

            $startTime = date("YmdHis");
            $expire = date('YmdHis',strtotime('+15 minutes',strtotime($startTime)));

            $vnp_TxnRef = $order_code;
            $vnp_OrderInfo = 'Thanh toan don hang ' . $order_code;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $total * 100; // Convert to VND (cents)
            $vnp_Locale = 'vn';
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
            
            $inputData = array(
                "vnp_Version" => $vnp_config['vnp_Version'],
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => $vnp_config['vnp_Command'],
                "vnp_CreateDate" => $startTime,
                "vnp_CurrCode" => $vnp_config['vnp_CurrCode'],
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_ExpireDate" => $expire
            );

            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }
            
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            header('Location: ' . $vnp_Url);
            exit();
        } elseif ($payment_method === 'momo') {
            // Xử lý thanh toán MoMo
            header('Location: momo_payment.php?order_id=' . $order_id);
            exit();
        } else {
            $_SESSION['success'] = "Đặt hàng thành công! Mã đơn hàng: " . $order_code;
            header('Location: customer/my_orders.php');
            exit();
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại!";
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-shopping-cart"></i> Thông tin đặt hàng</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" id="checkoutForm">
                        <div class="mb-4">
                            <h5>Thông tin giao hàng</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Họ tên</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($customer['full_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($customer['phone']); ?>" readonly>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Địa chỉ giao hàng *</label>
                                <textarea name="shipping_address" class="form-control" rows="3" required><?php echo htmlspecialchars($customer['address']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Phương thức thanh toán</h5>
                            <div class="payment-methods">
                                <!-- COD -->
                                <div class="payment-method">
                                    <input type="radio" class="btn-check" name="payment_method" value="cod" id="cod" checked>
                                    <label class="btn btn-outline-primary w-100 text-start" for="cod">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span class="ms-2">Thanh toán khi nhận hàng (COD)</span>
                                    </label>
                                </div>

                                <!-- Bank Transfer -->
                                <div class="payment-method mt-3">
                                    <input type="radio" class="btn-check" name="payment_method" value="bank_transfer" id="bank_transfer">
                                    <label class="btn btn-outline-primary w-100 text-start" for="bank_transfer">
                                        <i class="fas fa-university"></i>
                                        <span class="ms-2">Chuyển khoản ngân hàng</span>
                                    </label>
                                    <div class="bank-info mt-2 ms-4 d-none">
                                        <div class="alert alert-info">
                                            <p class="mb-1"><strong>Thông tin chuyển khoản:</strong></p>
                                            <p class="mb-1">Ngân hàng: <strong>Vietcombank</strong></p>
                                            <p class="mb-1">Số tài khoản: <strong>1234567890</strong></p>
                                            <p class="mb-1">Chủ tài khoản: <strong>CÔNG TY TNHH TOY SHOP</strong></p>
                                            <p class="mb-0">Nội dung: <strong>TOY [Số điện thoại]</strong></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- VNPAY -->
                                <div class="payment-method mt-3">
                                    <input type="radio" class="btn-check" name="payment_method" value="vnpay" id="vnpay">
                                    <label class="btn btn-outline-primary w-100 text-start" for="vnpay">
                                        <img src="assets/img/vnpay-logo.jpg" alt="VNPAY" height="20">
                                        <span class="ms-2">Thanh toán qua VNPAY</span>
                                        <small class="d-block ms-4 text-muted">Thanh toán online qua thẻ ATM/Visa/Master/JCB/QR Code</small>
                                    </label>
                                </div>

                                <!-- MoMo -->
                                <div class="payment-method mt-3">
                                    <input type="radio" class="btn-check" name="payment_method" value="momo" id="momo">
                                    <label class="btn btn-outline-primary w-100 text-start" for="momo">
                                        <img src="assets/img/momo-logo.jpg" alt="MoMo" height="20">
                                        <span class="ms-2">Thanh toán qua MoMo</span>
                                        <small class="d-block ms-4 text-muted">Thanh toán qua ví điện tử MoMo</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Ghi chú đơn hàng</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-check"></i> Đặt hàng
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Đơn hàng của bạn</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img src="assets/img/<?php echo htmlspecialchars($item['image']); ?>" alt="" class="me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                                </div>
                            </div>
                            <span><?php echo number_format($item['price'] * $item['quantity']); ?>đ</span>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($subtotal); ?>đ</span>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <span>Phí vận chuyển:</span>
                        <span><?php echo $shipping_fee > 0 ? number_format($shipping_fee) . 'đ' : 'Miễn phí'; ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <span>Thuế:</span>
                        <span><?php echo number_format($tax); ?>đ</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Tổng cộng:</span>
                        <span class="text-danger"><?php echo number_format($total); ?>đ</span>
                    </div>
                </div>
            </div>

            <!-- Mã giảm giá -->
            <div class="card mt-3 position-relative">
                <div class="card-body">
                    <h5 class="card-title">Mã giảm giá</h5>
                    <div class="input-group">
                        <input type="text" class="form-control" id="couponCode" placeholder="Nhập mã giảm giá" autocomplete="off" onfocus="showDiscountSuggestions2()" oninput="filterDiscountSuggestions2()">
                        <button class="btn btn-outline-primary" type="button" onclick="applyCoupon()">
                            Áp dụng
                        </button>
                    </div>
                    <ul class="discount-suggestions" id="discountSuggestions2" style="display:none;"></ul>
                </div>
            </div>
        </div>
    </div>
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

<style>
.payment-method label {
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.payment-method label:hover {
    background-color: #f8f9fa;
}

.btn-check:checked + label {
    background-color: #e7f1ff;
    border-color: #0d6efd;
}

.payment-method small {
    margin-top: 5px;
}

.payment-method img {
    vertical-align: middle;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankTransferRadio = document.getElementById('bank_transfer');
    const bankInfo = document.querySelector('.bank-info');
    
    // Toggle bank info
    document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'bank_transfer') {
                bankInfo.classList.remove('d-none');
            } else {
                bankInfo.classList.add('d-none');
            }
        });
    });

    // Áp dụng mã giảm giá
    window.applyCoupon = function() {
        const couponCode = document.getElementById('couponCode').value;
        if (!couponCode) {
            showDiscountToast('Vui lòng nhập mã giảm giá!', 'danger');
            return;
        }
        // Gọi API kiểm tra mã giảm giá
        fetch('api/apply_discount.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                discount_code: couponCode
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDiscountToast('Áp dụng mã giảm giá thành công!', 'success');
                // Cập nhật lại giá tiền
                const totalEls = document.querySelectorAll('.text-danger');
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
    };

    function formatMoney(amount) {
        return amount.toLocaleString('vi-VN') + ' VNĐ';
    }
});

let discountList2 = [];

function showDiscountSuggestions2() {
    const input = document.getElementById('couponCode');
    const suggestionBox = document.getElementById('discountSuggestions2');
    if (discountList2.length === 0) {
        fetch('api/available_discounts.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    discountList2 = data.discounts;
                    renderDiscountSuggestions2();
                }
            });
    } else {
        renderDiscountSuggestions2();
    }
    suggestionBox.style.display = 'block';
}

function renderDiscountSuggestions2(filter = '') {
    const suggestionBox = document.getElementById('discountSuggestions2');
    let html = '';
    const filtered = discountList2.filter(d => d.code.toLowerCase().includes(filter.toLowerCase()));
    if (filtered.length === 0) {
        html = '<li>Không có mã phù hợp</li>';
    } else {
        html = filtered.map(d => `<li onclick=\"selectDiscountCode2('${d.code}')\"><b>${d.code}</b> - ${d.name} (${d.type === 'percentage' ? d.value + '%': d.value + 'đ'})</li>`).join('');
    }
    suggestionBox.innerHTML = html;
    suggestionBox.style.display = 'block';
}

function filterDiscountSuggestions2() {
    const input = document.getElementById('couponCode');
    renderDiscountSuggestions2(input.value);
}

function selectDiscountCode2(code) {
    document.getElementById('couponCode').value = code;
    document.getElementById('discountSuggestions2').style.display = 'none';
}

document.addEventListener('click', function(e) {
    const box = document.getElementById('discountSuggestions2');
    const input = document.getElementById('couponCode');
    if (!box.contains(e.target) && e.target !== input) {
        box.style.display = 'none';
    }
});

function showDiscountToast(message, type = 'success') {
    const toastEl = document.getElementById('toast-discount');
    const toastBody = document.getElementById('toast-discount-body');
    toastBody.textContent = message;
    toastEl.className = 'toast align-items-center text-bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0';
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}
</script>

<?php include 'includes/footer.php'; ?>
