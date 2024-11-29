<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社團管理系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1d4ed8;
            --bg-color: #f8fafc;
            --text-color: #0f172a;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 0.75rem 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .navbar-brand:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 0.75rem !important;
            margin: 0 0.125rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white !important;
            transform: translateY(-1px);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            color: white;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            padding: 0.5rem;
            margin-top: 0.5rem;
            min-width: 12rem;
        }

        .dropdown-item {
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .dropdown-item i {
            width: 1.5rem;
        }

        main {
            flex: 1;
            padding: 2rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* 通用元素樣式 */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f1f5f9;
            font-weight: 600;
            padding: 1rem;
            white-space: nowrap;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            letter-spacing: 0.025em;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-users-cog me-2"></i>
                社團管理系統
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                           href="dashboard.php">
                            <i class="fas fa-home me-2"></i>首頁
                        </a>
                    </li>
                    <?php if (in_array($_SESSION['role'], ['admin', 'staff'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active' : ''; ?>" 
                           href="members.php">
                            <i class="fas fa-users me-2"></i>會員管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'activities.php' ? 'active' : ''; ?>" 
                           href="activities.php">
                            <i class="fas fa-calendar-alt me-2"></i>活動管理
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="feesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-dollar-sign me-2"></i>會費管理
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="fees.php">
                                <i class="fas fa-list me-2"></i>會費清單
                            </a>
                            <a class="dropdown-item" href="add_payment.php">
                                <i class="fas fa-plus me-2"></i>新增繳費
                            </a>
                        </div>
                    </li>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'export_report.php' ? 'active' : ''; ?>" 
                           href="export_report.php">
                            <i class="fas fa-file-export me-2"></i>匯出報表
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="change_password.php">
                                <i class="fas fa-key me-2"></i>修改密碼
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>登出
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <div class="container">
