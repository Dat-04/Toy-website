</main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-md-3">
                    <h5><i class="fas fa-toy-brick"></i> ToyShop</h5>
                    <p>Cửa hàng đồ chơi uy tín, chất lượng cao dành cho trẻ em.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <h6>Danh mục sản phẩm</h6>
                    <ul class="list-unstyled">
                        <?php
                        $categories = fetchAll("SELECT * FROM categories WHERE status = 'active' LIMIT 5");
                        foreach ($categories as $category):
                        ?>
                            <li><a href="category.php?id=<?php echo $category['id']; ?>" class="text-white-50"><?php echo $category['name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h6>Hỗ trợ khách hàng</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">Chính sách bảo hành</a></li>
                        <li><a href="#" class="text-white-50">Chính sách đổi trả</a></li>
                        <li><a href="#" class="text-white-50">Hướng dẫn mua hàng</a></li>
                        <li><a href="#" class="text-white-50">Câu hỏi thường gặp</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h6>Liên hệ</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt"></i> 218 Lĩnh Nam, Vĩnh Hưng, Hoàng Mai, Hà Nội</li>
                        <li><i class="fas fa-phone"></i> 0123-456-789</li>
                        <li><i class="fas fa-envelope"></i> info@toyshop.com</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom bg-black py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <p class="mb-0">&copy; Nhóm 7 - Đồ án 1 - WebToyShop</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <style>
        .footer a {
            text-decoration: none !important;
        }
        .footer a:hover {
            text-decoration: underline !important;
        }
    </style>
</body>
</html>
