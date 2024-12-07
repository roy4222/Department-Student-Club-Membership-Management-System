<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社團會費管理系統 - 登入</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2D7A6D;
            --primary-light: #48B5A3;
            --bg-gradient: linear-gradient(120deg, #f6f8fb 0%, #e5ebf3 100%);
            --card-shadow: 0 8px 24px rgba(45, 122, 109, 0.1);
        }
        
        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            padding-top: 5vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .system-logo {
            width: 64px;
            height: 64px;
            background-color: var(--primary-color);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.8rem;
            box-shadow: 0 4px 12px rgba(45, 122, 109, 0.2);
        }
        
        .system-title {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .system-title h1 {
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 600;
            margin: 0;
        }
        
        .system-title p {
            color: #95a5a6;
            margin: 0.5rem 0 0;
            font-size: 0.9rem;
        }
        
        .card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #34495e;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #edf2f7;
            border-radius: 12px;
            padding: 0.8rem 1rem 0.8rem 3rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(45, 122, 109, 0.1);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            z-index: 4;
            font-size: 1.1rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #95a5a6;
            z-index: 4;
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 500;
            font-size: 1rem;
            color: white;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(45, 122, 109, 0.2);
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .default-password {
            text-align: center;
            margin-top: 1.5rem;
            color: #95a5a6;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .default-password i {
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="system-logo">
                <i class="fas fa-users"></i>
            </div>
            <div class="system-title">
                <h1>社團會費管理系統</h1>
                <p>Club Fee Management System</p>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <form action="/week10/public/auth/login_process.php" method="POST">
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text" class="form-control" id="student_id" name="student_id" 
                                   required placeholder="請輸入學號">
                        </div>
                        
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   required placeholder="請輸入密碼">
                            <span class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>登入系統
                        </button>
                    </form>
                    
                    <div class="default-password">
                        <i class="fas fa-info-circle"></i>
                        <span>預設密碼：2486</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
