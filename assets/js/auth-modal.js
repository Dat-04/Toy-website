// Auth Modal JavaScript
let currentAuthMode = "login"

function switchToRegister() {
  const container = document.getElementById("authModalContainer")
  const loginSection = container.querySelector(".login-section")
  const registerSection = container.querySelector(".register-section")

  if (currentAuthMode === "login") {
    loginSection.classList.remove("active")
    loginSection.classList.add("slide-out-left")

    setTimeout(() => {
      registerSection.classList.add("active")
      registerSection.classList.add("slide-in-right")
      currentAuthMode = "register"
    }, 250)
  }
}

function switchToLogin() {
  const container = document.getElementById("authModalContainer")
  const loginSection = container.querySelector(".login-section")
  const registerSection = container.querySelector(".register-section")

  // Luôn chuyển về login, không kiểm tra trạng thái
  registerSection.classList.remove("active", "slide-in-right", "slide-out-left")
  loginSection.classList.add("active", "slide-in-right")
  loginSection.classList.remove("slide-out-left")
  currentAuthMode = "login"
}

// Handle Modal Login Form
document.addEventListener("DOMContentLoaded", () => {
  const modalLoginForm = document.getElementById("modalLoginForm")
  const modalRegisterForm = document.getElementById("modalRegisterForm")

  if (modalLoginForm) {
    modalLoginForm.addEventListener("submit", async function (e) {
      e.preventDefault()

      const loading = this.querySelector(".auth-loading")
      const button = this.querySelector(".auth-submit-btn")
      const alert = document.getElementById("loginAlert")

      loading.style.display = "inline-block"
      button.disabled = true

      const formData = new FormData(this)
      formData.append("action", "login")

      try {
        const response = await fetch("api/auth.php", {
          method: "POST",
          body: formData,
        })

        const result = await response.json()

        if (result.success) {
          alert.innerHTML = `<div class="alert alert-success">${result.message}</div>`
          setTimeout(() => {
            if (result.user_type === 'admin' && result.redirect) {
              window.location.href = '/' + result.redirect.replace(/^\/+/, '');
            } else {
              window.location.reload();
            }
          }, 1000)
        } else {
          alert.innerHTML = `<div class="alert alert-danger">${result.message}</div>`
        }
      } catch (error) {
        alert.innerHTML = `<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại!</div>`
      }

      loading.style.display = "none"
      button.disabled = false
    })
  }

  if (modalRegisterForm) {
    modalRegisterForm.addEventListener("submit", async function (e) {
      e.preventDefault()

      const loading = this.querySelector(".auth-loading")
      const button = this.querySelector(".auth-submit-btn")
      const alert = document.getElementById("registerAlert")

      loading.style.display = "inline-block"
      button.disabled = true

      const formData = new FormData(this)
      formData.append("action", "register")

      try {
        const response = await fetch("api/auth.php", {
          method: "POST",
          body: formData,
        })

        const result = await response.json()

        if (result.success) {
          alert.innerHTML = `<div class="alert alert-success">${result.message}</div>`
          this.reset()
          setTimeout(() => {
            switchToLogin()
          }, 2000)
        } else {
          alert.innerHTML = `<div class="alert alert-danger">${result.message}</div>`
        }
      } catch (error) {
        alert.innerHTML = `<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại!</div>`
      }

      loading.style.display = "none"
      button.disabled = false
    })
  }

  // Reset modal when closed
  const authModal = document.getElementById("authModal")
  if (authModal) {
    authModal.addEventListener("hidden.bs.modal", () => {
      // Reset to login mode
      const container = document.getElementById("authModalContainer")
      const loginSection = container.querySelector(".login-section")
      const registerSection = container.querySelector(".register-section")

      loginSection.classList.add("active")
      loginSection.classList.remove("slide-out-left")
      registerSection.classList.remove("active", "slide-in-right")
      currentAuthMode = "login"

      // Clear forms and alerts
      document.getElementById("modalLoginForm").reset()
      document.getElementById("modalRegisterForm").reset()
      document.getElementById("loginAlert").innerHTML = ""
      document.getElementById("registerAlert").innerHTML = ""
    })
  }
})
