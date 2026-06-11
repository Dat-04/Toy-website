<div class="card">
    <div class="card-body">
        <div class="text-center mb-3">
            <img src="../assets/img/avatar-default.png" class="rounded-circle" width="80" height="80" alt="Avatar">
            <h6 class="mt-2"><?php echo $_SESSION['customer_name']; ?></h6>
        </div>
        
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a href="my_account.php" class="text-decoration-none">
                    <i class="fas fa-user-cog"></i> Tài khoản của tôi
                </a>
            </li>
            <li class="list-group-item">
                <a href="my_orders.php" class="text-decoration-none">
                    <i class="fas fa-box"></i> Đơn hàng của tôi
                </a>
            </li>
            <li class="list-group-item">
                <a href="my_wishlist.php" class="text-decoration-none">
                    <i class="fas fa-heart"></i> Danh sách yêu thích
                </a>
            </li>
            <li class="list-group-item">
                <a href="my_reviews.php" class="text-decoration-none">
                    <i class="fas fa-star"></i> Đánh giá của tôi
                </a>
            </li>
            <li class="list-group-item">
                <a href="../logout.php" class="text-decoration-none text-danger">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </li>
        </ul>
    </div>
</div>
