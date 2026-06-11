<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Nếu đã đăng nhập thì chuyển hướng
if (isLoggedIn()) {
    redirect('index.php');
}

if (isAdmin()) {
    redirect('admin/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'login') {
            // Xử lý đăng nhập
            $email = sanitize($_POST['email']);
            $password = $_POST['password'];
            
            // Kiểm tra admin trước
            $admin = fetchOne("SELECT * FROM admin WHERE email = ? OR username = ?", [$email, $email]);
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                
                echo json_encode(['success' => true, 'redirect' => 'admin/index.php', 'message' => 'Đăng nhập admin thành công!']);
                exit;
            }
            
            // Kiểm tra khách hàng
            $customer = fetchOne("SELECT c.*, a.password FROM customers c 
                                  JOIN accounts a ON c.account_id = a.id 
                                  WHERE c.email = ? AND a.status = 'active'", [$email]);
            
            if ($customer && password_verify($password, $customer['password'])) {
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['full_name'];
                $_SESSION['customer_email'] = $customer['email'];
                
                echo json_encode(['success' => true, 'redirect' => 'index.php', 'message' => 'Đăng nhập thành công!']);
                exit;
            }
            
            echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!']);
            exit;
            
        } elseif ($_POST['action'] == 'register') {
            // Xử lý đăng ký (chỉ cho khách hàng)
            $first_name = sanitize($_POST['first_name']);
            $last_name = sanitize($_POST['last_name']);
            $full_name = $first_name . ' ' . $last_name;
            $email = sanitize($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Kiểm tra email đã tồn tại
            $existingCustomer = fetchOne("SELECT id FROM customers WHERE email = ?", [$email]);
            $existingAdmin = fetchOne("SELECT id FROM admin WHERE email = ?", [$email]);
            
            if ($existingCustomer || $existingAdmin) {
                echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng!']);
                exit;
            }
            
            // Tạo tài khoản
            $conn = getConnection();
            $conn->begin_transaction();
            
            try {
                // Tạo account
                $stmt1 = $conn->prepare("INSERT INTO accounts (username, password, email) VALUES (?, ?, ?)");
                $stmt1->bind_param("sss", $email, $password, $email);
                $stmt1->execute();
                $account_id = $conn->insert_id;
                
                // Tạo customer
                $stmt2 = $conn->prepare("INSERT INTO customers (account_id, full_name, email) VALUES (?, ?, ?)");
                $stmt2->bind_param("iss", $account_id, $full_name, $email);
                $stmt2->execute();
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Đăng ký thành công! Vui lòng đăng nhập.']);
                exit;
                
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại!']);
                exit;
            }
            
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập / Đăng ký - ToyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            position: relative;
        }

        .auth-wrapper {
            display: flex;
            height: 100%;
            position: relative;
        }

        .form-container {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: all 0.6s ease-in-out;
        }

        .sign-in-container {
            position: absolute;
            left: 0;
            width: 50%;
            height: 100%;
            transition: all 0.6s ease-in-out;
            z-index: 2;
        }

        .sign-up-container {
            position: absolute;
            left: 0;
            width: 50%;
            height: 100%;
            opacity: 0;
            z-index: 1;
            transition: all 0.6s ease-in-out;
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .overlay {
            background: linear-gradient(135deg, #ff6b9d, #ffa726);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: white;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .auth-container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        .auth-container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        .auth-container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .auth-container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .auth-container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .auth-container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        @keyframes show {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        h1 {
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-control {
            background: #f6f5f7;
            border: none;
            padding: 15px 20px;
            border-radius: 10px;
            width: 100%;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: #fff;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.3);
            outline: none;
        }

        .btn-auth {
            border-radius: 20px;
            border: 1px solid #667eea;
            background: #667eea;
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-auth:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-ghost {
            background: transparent;
            border-color: white;
            color: white;
        }

        .btn-ghost:hover {
            background: white;
            color: #667eea;
        }

        .overlay h1 {
            color: white;
            margin-bottom: 20px;
        }

        .overlay p {
            font-size: 16px;
            font-weight: 300;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .name-row {
            display: flex;
            gap: 15px;
        }

        .name-row .form-group {
            flex: 1;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 15px;
            border: none;
        }

        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-container {
                max-width: 400px;
                min-height: auto;
            }

            .auth-wrapper {
                flex-direction: column;
            }

            .sign-in-container,
            .sign-up-container {
                position: relative;
                width: 100%;
                left: 0;
                transform: none !important;
            }

            .sign-up-container {
                display: none;
            }

            .overlay-container {
                position: relative;
                left: 0;
                width: 100%;
                height: auto;
                margin-top: 20px;
            }

            .overlay {
                position: relative;
                left: 0;
                width: 100%;
                height: 200px;
                transform: none !important;
            }

            .overlay-panel {
                position: relative;
                width: 100%;
                height: 100%;
                transform: none !important;
                padding: 30px 20px;
            }

            .overlay-left {
                display: none;
            }

            .form-container {
                padding: 40px 30px;
            }

            .auth-container.right-panel-active .sign-in-container {
                display: none;
            }

            .auth-container.right-panel-active .sign-up-container {
                display: block;
                opacity: 1;
            }

            .auth-container.right-panel-active .overlay-right {
                display: none;
            }

            .auth-container.right-panel-active .overlay-left {
                display: block;
            }
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 18px;
            z-index: 1000;
        }

        .back-home:hover {
            color: #f0f0f0;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Về trang chủ
    </a>

    <div class="auth-container" id="authContainer">
        <div class="form-container sign-up-container">
            <form id="signUpForm">
                <h1>Cùng là một nhà!</h1>
                <div id="registerAlert"></div>
                
                <div class="name-row">
                    <div class="form-group">
                        <input type="text" class="form-control" name="first_name" placeholder="Họ" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="last_name" placeholder="Tên" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required minlength="6">
                </div>
                
                <button type="submit" class="btn-auth">
                    <span class="loading"></span>
                    Đăng ký
                </button>
            </form>
        </div>

        <div class="form-container sign-in-container">
            <form id="signInForm">
                <h1>Chào mừng trở lại!</h1>
                <div id="loginAlert"></div>
                
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required>
                </div>
                
                <button type="submit" class="btn-auth">
                    <span class="loading"></span>
                    Đăng nhập
                </button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Thành viên thân thiết?</h1>
                    <p>Đăng nhập để tiếp tục hành trình mua sắm đồ chơi cùng chúng tôi!</p>
                    <button class="btn-auth btn-ghost" id="signIn">Đăng nhập</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Thành viên mới?</h1>
                    <p>Tham gia cùng chúng tôi để khám phá thế giới đồ chơi tuyệt vời!</p>
                    <button class="btn-auth btn-ghost" id="signUp">Đăng ký</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('authContainer');
        const signUpForm = document.getElementById('signUpForm');
        const signInForm = document.getElementById('signInForm');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        // Handle Sign Up
        signUpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const loading = signUpForm.querySelector('.loading');
            const button = signUpForm.querySelector('.btn-auth');
            const alert = document.getElementById('registerAlert');
            
            loading.style.display = 'inline-block';
            button.disabled = true;
            
            const formData = new FormData(signUpForm);
            formData.append('action', 'register');
            
            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    signUpForm.reset();
                    setTimeout(() => {
                        container.classList.remove("right-panel-active");
                    }, 2000);
                } else {
                    alert.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                }
            } catch (error) {
                alert.innerHTML = `<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại!</div>`;
            }
            
            loading.style.display = 'none';
            button.disabled = false;
        });

        // Handle Sign In
        signInForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const loading = signInForm.querySelector('.loading');
            const button = signInForm.querySelector('.btn-auth');
            const alert = document.getElementById('loginAlert');
            
            loading.style.display = 'inline-block';
            button.disabled = true;
            
            const formData = new FormData(signInForm);
            formData.append('action', 'login');
            
            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                } else {
                    alert.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                }
            } catch (error) {
                alert.innerHTML = `<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại!</div>`;
            }
            
            loading.style.display = 'none';
            button.disabled = false;
        });
    </script>
</body>
</html>
