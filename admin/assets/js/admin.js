// JavaScript cho trang quản trị

document.addEventListener("DOMContentLoaded", () => {
  // Thu gọn/mở rộng sidebar
  const sidebarCollapse = document.getElementById("sidebarCollapse")
  const sidebar = document.getElementById("sidebar")
  const content = document.getElementById("content")

  if (sidebarCollapse) {
    sidebarCollapse.addEventListener("click", () => {
      sidebar.classList.toggle("active")
      content.classList.toggle("active")
    })
  }

  // Tự động ẩn thông báo sau 5 giây
  setTimeout(() => {
    const alerts = document.querySelectorAll(".alert")
    alerts.forEach((alert) => {
      const bsAlert = new window.bootstrap.Alert(alert)
      if (bsAlert) {
        bsAlert.close()
      }
    })
  }, 5000)

  // Khởi tạo tooltip
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new window.bootstrap.Tooltip(tooltipTriggerEl))

  // Khởi tạo chat admin
  initializeAdminChat()
})

// Chức năng chat admin
let adminChatOpen = false

function openAdminChat() {
  toggleAdminChat()
}

function toggleAdminChat() {
  const chatWidget = document.getElementById("adminChatWidget")
  if (adminChatOpen) {
    chatWidget.style.display = "none"
    adminChatOpen = false
  } else {
    chatWidget.style.display = "block"
    adminChatOpen = true
    loadAdminChatMessages()
  }
}

function initializeAdminChat() {
  // Tải tin nhắn khi trang vừa load
  loadAdminChatMessages()
}

function loadAdminChatMessages() {
  fetch("../api/admin_chat_messages.php")
    .then((response) => response.json())
    .then((data) => {
      const messagesContainer = document.getElementById("adminChatMessages")
      if (messagesContainer) {
        messagesContainer.innerHTML = ""

        data.forEach((message) => {
          addAdminMessageToChat(message.message, message.sender_type, message.customer_name)
        })

        scrollAdminChatToBottom()
      }
    })
    .catch((error) => console.error("Lỗi tải tin nhắn admin:", error))
}

function sendAdminMessage() {
  const input = document.getElementById("adminChatInput")
  const message = input.value.trim()

  if (message === "") return

  // Thêm tin nhắn vào khung chat ngay lập tức
  addAdminMessageToChat(message, "admin")
  input.value = ""

  // Gửi tin nhắn lên server
  fetch("../api/send_admin_message.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      message: message,
      sender_type: "admin",
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        console.log("Đã gửi tin nhắn admin")
      } else {
        console.error("Lỗi gửi tin nhắn admin")
      }
    })
    .catch((error) => console.error("Lỗi:", error))
}

function addAdminMessageToChat(message, senderType, customerName = "") {
  const messagesContainer = document.getElementById("adminChatMessages")
  if (!messagesContainer) return

  const messageDiv = document.createElement("div")
  messageDiv.className = `message ${senderType}`

  if (senderType === "customer" && customerName) {
    messageDiv.innerHTML = `<strong>${customerName}:</strong><br>${message}`
  } else if (senderType === "admin") {
    messageDiv.innerHTML = `<strong>Admin:</strong><br>${message}`
  } else {
    messageDiv.textContent = message
  }

  messagesContainer.appendChild(messageDiv)
  scrollAdminChatToBottom()
}

function scrollAdminChatToBottom() {
  const messagesContainer = document.getElementById("adminChatMessages")
  if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight
  }
}

// Hàm tiện ích
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`
  notification.style.cssText = "top: 20px; right: 20px; z-index: 9999; min-width: 300px;"
  notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  document.body.appendChild(notification)

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

// Kiểm tra hợp lệ form
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

// Xem trước ảnh upload
function previewImage(input, previewId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader()
    reader.onload = (e) => {
      document.getElementById(previewId).src = e.target.result
    }
    reader.readAsDataURL(input.files[0])
  }
}

// Hiển thị overlay loading
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

// Hộp thoại xác nhận
function confirmAction(message, callback) {
  if (confirm(message)) {
    callback()
  }
}

// Các hàm xử lý bảng dữ liệu
function sortTable(table, column, direction = "asc") {
  // Sắp xếp bảng theo cột
  console.log(`Sắp xếp bảng theo cột ${column} chiều ${direction}`)
}

function filterTable(table, filterValue) {
  // Lọc bảng theo giá trị
  console.log(`Lọc bảng với giá trị: ${filterValue}`)
}

// Xuất dữ liệu
function exportToExcel(data, filename) {
  // Xuất dữ liệu ra file Excel
  console.log(`Xuất dữ liệu ra Excel: ${filename}`)
}

function exportToPDF(elementId, filename) {
  // Xuất dữ liệu ra file PDF
  console.log(`Xuất phần tử ${elementId} ra PDF: ${filename}`)
}

// Hàm tạo biểu đồ (nếu dùng Chart.js)
function createChart(canvasId, type, data, options = {}) {
  const ctx = document.getElementById(canvasId)
  if (ctx) {
    return new window.Chart(ctx, {
      type: type,
      data: data,
      options: {
        responsive: true,
        ...options,
      },
    })
  }
}

// Cập nhật thời gian thực
function startRealTimeUpdates() {
  setInterval(() => {
    updateNotifications()
    updateChatMessages()
  }, 30000) // Cập nhật mỗi 30 giây
}

function updateNotifications() {
  fetch("../api/get_notifications.php")
    .then((response) => response.json())
    .then((data) => {
      // Cập nhật số lượng thông báo
      const badge = document.querySelector(".notification-badge")
      if (badge && data.count > 0) {
        badge.textContent = data.count
        badge.style.display = "inline"
      } else if (badge) {
        badge.style.display = "none"
      }
    })
    .catch((error) => console.error("Lỗi cập nhật thông báo:", error))
}

function updateChatMessages() {
  if (adminChatOpen) {
    loadAdminChatMessages()
  }
}

// Khởi động cập nhật thời gian thực khi DOM đã sẵn sàng

document.addEventListener("DOMContentLoaded", () => {
  startRealTimeUpdates()
})
