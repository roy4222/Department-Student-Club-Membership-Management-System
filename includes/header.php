<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /week10/public/index.php");
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
            --primary-color: #2D7A6D;
            --secondary-color: #48B5A3;
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
            padding: 1rem;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            color: white !important;
            font-size: 1.25rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .navbar-brand i {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem;
            transition: all 0.15s ease;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white !important;
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white !important;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            padding: 0.5rem;
            min-width: 12rem;
        }

        .dropdown-item {
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.15s ease;
        }

        .dropdown-item:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .dropdown-item i {
            width: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: rgba(255, 255, 255, 0.1);
                margin: 1rem -1rem -1rem;
                padding: 1rem;
                border-radius: 0.75rem;
            }
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
            <a class="navbar-brand" href="/week10/public/dashboard.php">
                <i class="fas fa-users-gear"></i>
                <span class="ms-2">社團管理系統</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/week10/public/dashboard.php">
                            <i class="fas fa-home"></i> 首頁
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/week10/public/members/members.php">
                            <i class="fas fa-users"></i> 會員管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/week10/public/activities/activities.php">
                            <i class="fas fa-calendar-alt"></i> 活動管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/week10/public/fees.php">
                            <i class="fas fa-dollar-sign"></i> 會費管理
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/week10/public/exports/export_report.php">
                            <i class="fas fa-file-export"></i> 匯出報表
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php 
                            $displayName = isset($_SESSION['name']) ? $_SESSION['name'] : 
                                         (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '未知使用者');
                            $displayName = htmlspecialchars($displayName);
                            $role = $_SESSION['role'] === 'admin' ? '管理員' : '會員';
                            echo $displayName . ' <small class="text-light-50">(' . $role . ')</small>'; 
                            ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="/week10/public/auth/change_password.php">
                                    <i class="fas fa-key me-2"></i>修改密碼
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="/week10/public/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>登出
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <div class="container">
