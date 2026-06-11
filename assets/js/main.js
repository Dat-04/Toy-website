// Main JavaScript file
document.addEventListener("DOMContentLoaded", () => {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new window.bootstrap.Tooltip(tooltipTriggerEl))

  // Tạm thời tắt chat để tránh lỗi JS
  // initializeChat()

  // Auto-hide alerts
  setTimeout(() => {
    const alerts = document.querySelectorAll(".alert")
    alerts.forEach((alert) => {
      const bsAlert = new window.bootstrap.Alert(alert)
      bsAlert.close()
    })
  }, 5000)
})

// Chat functionality
if (typeof window.chatOpen === "undefined") {
  window.chatOpen = false;
}

function openChat() {
  toggleChat()
}

function toggleChat() {
  const chatWidget = document.getElementById("chatWidget")
  if (window.chatOpen) {
    chatWidget.style.display = "none"
    window.chatOpen = false
  } else {
    chatWidget.style.display = "block"
    window.chatOpen = true
    loadChatMessages()
  }
}

function initializeChat() {
  // Load existing messages when page loads
  if (isLoggedIn()) {
    loadChatMessages()
  }
}

function loadChatMessages() {
  fetch("api/chat_messages.php")
    .then((response) => response.json())
    .then((data) => {
      const messagesContainer = document.getElementById("chatMessages")
      messagesContainer.innerHTML = ""

      data.forEach((message) => {
        addMessageToChat(message.message, message.sender_type)
      })

      scrollChatToBottom()
    })
    .catch((error) => console.error("Error loading messages:", error))
}

function sendMessage() {
  const input = document.getElementById("chatInput")
  const message = input.value.trim()

  if (message === "") return

  // Add message to chat immediately
  addMessageToChat(message, "user")
  input.value = ""

  // Send to server
  fetch("api/send_message.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      message: message,
      sender_type: "customer",
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Message sent successfully
        console.log("Message sent")
      } else {
        console.error("Error sending message")
      }
    })
    .catch((error) => console.error("Error:", error))
}

function addMessageToChat(message, senderType) {
  const messagesContainer = document.getElementById("chatMessages")
  const messageDiv = document.createElement("div")
  messageDiv.className = `message ${senderType}`
  messageDiv.textContent = message

  messagesContainer.appendChild(messageDiv)
  scrollChatToBottom()
}

function scrollChatToBottom() {
  const messagesContainer = document.getElementById("chatMessages")
  messagesContainer.scrollTop = messagesContainer.scrollHeight
}

function handleChatEnter(event) {
  if (event.key === "Enter") {
    sendMessage()
  }
}

// Product functions
function addToCart(productId) {
  if (!isLoggedIn()) {
    alert("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng")
    window.location.href = "login.php"
    return
  }

  fetch("api/add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      product_id: productId,
      quantity: 1,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Đã thêm sản phẩm vào giỏ hàng!", "success")
        updateCartCount()
      } else {
        showNotification("Có lỗi xảy ra, vui lòng thử lại!", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Có lỗi xảy ra, vui lòng thử lại!", "error")
    })
}

function addToWishlist(productId) {
  if (!isLoggedIn()) {
    alert("Vui lòng đăng nhập để thêm sản phẩm vào danh sách yêu thích")
    window.location.href = "login.php"
    return
  }

  fetch("api/add_to_wishlist.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      product_id: productId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Đã thêm vào danh sách yêu thích!", "success")
      } else {
        showNotification("Có lỗi xảy ra, vui lòng thử lại!", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Có lỗi xảy ra, vui lòng thử lại!", "error")
    })
}

function updateCartCount() {
  fetch("api/cart_count.php")
    .then((response) => response.json())
    .then((data) => {
      const cartBadge = document.querySelector(".fa-shopping-cart + .badge")
      if (cartBadge) {
        cartBadge.textContent = data.count
        if (data.count > 0) {
          cartBadge.style.display = "inline"
        } else {
          cartBadge.style.display = "none"
        }
      }
    })
    .catch((error) => console.error("Error updating cart count:", error))
}

// Utility functions
function isLoggedIn() {
  return document.body.dataset.loggedIn === "true"
}

function showNotification(message, type = "info") {
  // Create notification element
  const notification = document.createElement("div")
  notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`
  notification.style.cssText = "top: 20px; right: 20px; z-index: 9999; min-width: 300px;"
  notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  document.body.appendChild(notification)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.parentNode.removeChild(notification)
    }
  }, 5000)
}

function formatMoney(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount)
}

// Search functionality
function performSearch() {
  const searchInput = document.querySelector('input[name="q"]')
  const query = searchInput.value.trim()

  if (query === "") {
    alert("Vui lòng nhập từ khóa tìm kiếm")
    return false
  }

  return true
}

// Form validation
function validateForm(formId) {
  const form = document.getElementById(formId)
  const inputs = form.querySelectorAll("input[required], select[required], textarea[required]")
  let isValid = true

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.classList.add("is-invalid")
      isValid = false
    } else {
      input.classList.remove("is-invalid")
    }
  })

  return isValid
}

// Image preview
function previewImage(input, previewId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader()
    reader.onload = (e) => {
      document.getElementById(previewId).src = e.target.result
    }
    reader.readAsDataURL(input.files[0])
  }
}

// Smooth scrolling
function smoothScrollTo(elementId) {
  const element = document.getElementById(elementId)
  if (element) {
    element.scrollIntoView({
      behavior: "smooth",
      block: "start",
    })
  }
}

// Loading overlay
function showLoading() {
  const overlay = document.createElement("div")
  overlay.id = "loadingOverlay"
  overlay.className = "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
  overlay.style.cssText = "background: rgba(0,0,0,0.5); z-index: 9999;"
  overlay.innerHTML =
    '<div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div>'

  document.body.appendChild(overlay)
}

function hideLoading() {
  const overlay = document.getElementById("loadingOverlay")
  if (overlay) {
    overlay.remove()
  }
}

// Newsletter subscription
function subscribeNewsletter() {
  const emailInput = document.querySelector('.newsletter-form input[type="email"]')
  const email = emailInput.value.trim()

  if (!email) {
    alert("Vui lòng nhập email")
    return
  }

  if (!isValidEmail(email)) {
    alert("Email không hợp lệ")
    return
  }

  fetch("api/newsletter_subscribe.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email: email }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Đăng ký nhận tin thành công!", "success")
        emailInput.value = ""
      } else {
        showNotification("Có lỗi xảy ra, vui lòng thử lại!", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Có lỗi xảy ra, vui lòng thử lại!", "error")
    })
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

// Initialize page-specific functionality
function initializePage() {
  const currentPage = window.location.pathname.split("/").pop()

  switch (currentPage) {
    case "index.php":
    case "":
      initializeHomePage()
      break
    case "products.php":
      initializeProductsPage()
      break
    case "cart.php":
      initializeCartPage()
      break
  }
}

function initializeHomePage() {
  // Auto-play banner slider
  const carousel = document.getElementById("bannerCarousel")
  if (carousel) {
    const bsCarousel = new window.bootstrap.Carousel(carousel, {
      interval: 5000,
      wrap: true,
    })
  }
}

function initializeProductsPage() {
  // Initialize product filters
  const filterButtons = document.querySelectorAll(".filter-btn")
  filterButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const category = this.dataset.category
      filterProducts(category)
    })
  })
}

function filterProducts(category) {
  // Placeholder function for filtering products
  console.log("Filtering products by category:", category)
}

function initializeCartPage() {
  // Initialize quantity controls
  const quantityInputs = document.querySelectorAll(".quantity-input")
  quantityInputs.forEach((input) => {
    input.addEventListener("change", function () {
      updateCartItem(this.dataset.productId, this.value)
    })
  })
}

function updateCartItem(productId, quantity) {
  // Placeholder function for updating cart item
  console.log("Updating cart item with product ID:", productId, "and quantity:", quantity)
}

// Call initialization when DOM is loaded
document.addEventListener("DOMContentLoaded", initializePage)
// DROPDOWN FIX - Thay thế toàn bộ phần dropdown cũ bằng code này
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing dropdown fix...');
    
    // Kiểm tra Bootstrap
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded!');
        return;
    }
    
    console.log('Bootstrap version:', bootstrap.Tooltip.VERSION);
    
    // Hủy tất cả instance Bootstrap dropdown cũ
    const existingDropdowns = document.querySelectorAll('.dropdown-toggle');
    existingDropdowns.forEach(dropdown => {
        const instance = bootstrap.Dropdown.getInstance(dropdown);
        if (instance) {
            instance.dispose();
        }
    });
    
    // Xử lý dropdown user và admin bằng cách thủ công
    const userDropdown = document.getElementById('userDropdown');
    const adminDropdown = document.getElementById('adminDropdown');
    
    if (userDropdown) {
        console.log('Setting up user dropdown');
        setupCustomDropdown(userDropdown);
    }
    
    if (adminDropdown) {
        console.log('Setting up admin dropdown');
        setupCustomDropdown(adminDropdown);
    }
    
    // Xử lý mega menu dropdowns với Bootstrap
    const megaMenuDropdowns = document.querySelectorAll('.mega-menu-item .dropdown-toggle');
    megaMenuDropdowns.forEach(dropdown => {
        new bootstrap.Dropdown(dropdown);
    });
    
    function setupCustomDropdown(button) {
        const menu = button.nextElementSibling;
        
        if (!menu || !menu.classList.contains('dropdown-menu')) {
            console.error('Dropdown menu not found for button:', button.id);
            return;
        }
        
        // Xóa các event listener cũ
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // Thêm event listener mới
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = menu.classList.contains('show');
            
            console.log('Dropdown clicked:', this.id, 'isOpen:', isOpen);
            
            // Đóng tất cả dropdown khác
            closeAllDropdowns();
            
            // Toggle dropdown hiện tại
            if (!isOpen) {
                menu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
                this.classList.add('show');
                console.log('Dropdown opened:', this.id);
            }
        });
        
        console.log('Custom dropdown setup completed for:', newButton.id);
    }
    
    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
            const button = menu.previousElementSibling;
            if (button && button.classList.contains('dropdown-toggle')) {
                button.setAttribute('aria-expanded', 'false');
                button.classList.remove('show');
            }
        });
    }
    
    // Đóng dropdown khi click bên ngoài
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            closeAllDropdowns();
        }
    });
    
    // Đóng dropdown khi nhấn Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
    
    console.log('Dropdown initialization completed');
});

// Debug function
function debugDropdown() {
    console.log('=== DROPDOWN DEBUG ===');
    
    const userDropdown = document.getElementById('userDropdown');
    const adminDropdown = document.getElementById('adminDropdown');
    
    console.log('User dropdown exists:', !!userDropdown);
    console.log('Admin dropdown exists:', !!adminDropdown);
    
    if (userDropdown) {
        const menu = userDropdown.nextElementSibling;
        console.log('User dropdown menu:', {
            exists: !!menu,
            isShowing: menu ? menu.classList.contains('show') : false,
            ariaExpanded: userDropdown.getAttribute('aria-expanded'),
            display: menu ? window.getComputedStyle(menu).display : 'N/A'
        });
    }
    
    if (adminDropdown) {
        const menu = adminDropdown.nextElementSibling;
        console.log('Admin dropdown menu:', {
            exists: !!menu,
            isShowing: menu ? menu.classList.contains('show') : false,
            ariaExpanded: adminDropdown.getAttribute('aria-expanded'),
            display: menu ? window.getComputedStyle(menu).display : 'N/A'
        });
    }
    
    console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap version:', bootstrap.Tooltip.VERSION);
    }
    
    console.log('=== END DEBUG ===');
}

// Test dropdown sau 3 giây
setTimeout(() => {
    debugDropdown();
}, 3000);

// Initialize dropdown menus
document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            var menu = this.nextElementSibling;
            if (menu.classList.contains('show')) {
                menu.classList.remove('show');
            } else {
                // Close other dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(function(openMenu) {
                    openMenu.classList.remove('show');
                });
                menu.classList.add('show');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
        }
    });
});