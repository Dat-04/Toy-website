function showNotification(message, type = 'info') {
    // Tạo container nếu chưa tồn tại
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // Tạo toast element
    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    
    // Icon cho từng loại thông báo
    const icons = {
        success: 'fas fa-check-circle',
        danger: 'fas fa-times-circle',
        warning: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle'
    };

    // Tiêu đề cho từng loại thông báo
    const titles = {
        success: 'Thành công',
        danger: 'Lỗi',
        warning: 'Cảnh báo',
        info: 'Thông tin'
    };

    toast.innerHTML = `
        <div class="toast-header">
            <i class="${icons[type]} toast-icon"></i>
            <strong class="me-auto">${titles[type]}</strong>
            <button type="button" class="btn-close" onclick="this.closest('.custom-toast').remove()"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;

    // Thêm toast vào container
    container.appendChild(toast);

    // Hiển thị toast với animation
    setTimeout(() => toast.classList.add('show'), 100);

    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
} 