<div class="wrapper">
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-toy-brick"></i> ToyShop Admin</h3>
        </div>
        
        <ul class="list-unstyled components">
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Tổng quan</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'interface.php' ? 'active' : ''; ?>">
                <a href="interface.php"><i class="fas fa-paint-brush"></i> Giao diện</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                <a href="products.php"><i class="fas fa-box"></i> Sản phẩm</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                <a href="categories.php"><i class="fas fa-tags"></i> Danh mục</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> Đơn hàng</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
                <a href="customers.php"><i class="fas fa-users"></i> Khách hàng</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'discounts.php' ? 'active' : ''; ?>">
                <a href="discounts.php"><i class="fas fa-tags"></i> Mã giảm giá</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : ''; ?>">
                <a href="news.php"><i class="fas fa-newspaper"></i> Tin tức</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
                <a href="reviews.php"><i class="fas fa-star"></i> Đánh giá</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'revenue.php' ? 'active' : ''; ?>">
                <a href="revenue.php"><i class="fas fa-chart-line"></i> Doanh thu</a>
            </li>
        </ul>
    </nav>
