<?php
session_start();

// 正確引入資料庫連接
$conn = require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = MD5($_POST['password']);  // 使用 MD5 加密密碼
    
    try {
        $stmt = $conn->prepare("SELECT * FROM members WHERE student_id = ? AND password = ?");
        $stmt->execute([$student_id, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: /week10/public/dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "學號或密碼錯誤";
            header("Location: /week10/public/index.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "系統錯誤，請稍後再試";
        header("Location: /week10/public/index.php");
        exit();
    }
}
?>
