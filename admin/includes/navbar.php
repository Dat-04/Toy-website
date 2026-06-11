<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <button type="button" id="sidebarCollapse" class="btn btn-info">
            <i class="fas fa-align-left"></i>
        </button>
        
        <div class="ms-auto d-flex align-items-center">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <?php
                    $notifications = fetchAll("SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");
                    if (count($notifications) > 0):
                    ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo count($notifications); ?>
                        </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if (empty($notifications)): ?>
                        <li><span class="dropdown-item-text">Không có thông báo mới</span></li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <li><a class="dropdown-item" href="#"><?php echo $notification['title']; ?></a></li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="notifications.php">Xem tất cả</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Chat -->
            <button class="btn btn-outline-success me-3" onclick="openAdminChat()">
                <i class="fas fa-comments"></i>
            </button>
            
            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user"></i> <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog"></i> Hồ sơ</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Cài đặt</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
