<?php
$pageTitle = 'Tin tức';
include 'includes/header.php';

// Lấy tham số
$page = (int)($_GET['page'] ?? 1);
$limit = 6;
$offset = ($page - 1) * $limit;

// Lấy tổng số tin tức
$total = fetchOne("SELECT COUNT(*) as total FROM news WHERE status = 'active'")['total'];

// Lấy danh sách tin tức
$newsList = fetchAll("SELECT * FROM news WHERE status = 'active' ORDER BY created_at DESC LIMIT $limit OFFSET $offset");

// Lấy tin tức nổi bật
$featuredNews = fetchAll("SELECT * FROM news WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active">Tin tức</li>
        </ol>
    </nav>
    
    <h1 class="h3 mb-4">Tin tức</h1>
    
    <div class="row">
        <div class="col-lg-8">
            <?php if (empty($newsList)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <h4>Chưa có tin tức nào</h4>
                    <p class="text-muted">Hãy quay lại sau để xem tin tức mới nhất</p>
                </div>
            <?php else: ?>
                <?php foreach ($newsList as $news): ?>
                    <article class="card mb-4">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <?php if ($news['image']): ?>
                                    <img src="assets/img/news/<?php echo $news['image']; ?>" 
                                         class="img-fluid rounded-start h-100" style="object-fit: cover;" 
                                         alt="<?php echo $news['title']; ?>">
                                <?php else: ?>
                                    <div class="bg-light h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="news_detail.php?id=<?php echo $news['id']; ?>" 
                                           class="text-decoration-none"><?php echo $news['title']; ?></a>
                                    </h5>
                                    <p class="card-text"><?php echo substr(strip_tags($news['content']), 0, 200) . '...'; ?></p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($news['created_at'])); ?>
                                        </small>
                                    </p>
                                    <a href="news_detail.php?id=<?php echo $news['id']; ?>" 
                                       class="btn btn-primary btn-sm">Đọc thêm</a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                
                <!-- Pagination -->
                <?php if ($total > $limit): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo paginate($total, $limit, $page, 'news.php?'); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <!-- Tin tức nổi bật -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Tin tức nổi bật</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($featuredNews as $featured): ?>
                        <div class="d-flex mb-3">
                            <?php if ($featured['image']): ?>
                                <img src="assets/img/news/<?php echo $featured['image']; ?>" 
                                     width="80" height="60" class="rounded me-3" style="object-fit: cover;">
                            <?php endif; ?>
                            <div>
                                <h6 class="mb-1">
                                    <a href="news_detail.php?id=<?php echo $featured['id']; ?>" 
                                       class="text-decoration-none"><?php echo substr($featured['title'], 0, 50) . '...'; ?></a>
                                </h6>
                                <small class="text-muted">
                                    <?php echo date('d/m/Y', strtotime($featured['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Newsletter -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Đăng ký nhận tin</h6>
                </div>
                <div class="card-body">
                    <p class="small">Đăng ký để nhận tin tức và khuyến mãi mới nhất từ ToyShop</p>
                    <form class="newsletter-form">
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Nhập email của bạn" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
