<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '27003378';  // XAMPP 預設 root 密碼為空
$db_name = 'club_db';

try {
    // 先嘗試連接到 MySQL
    $conn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 檢查資料庫是否存在，如果不存在就建立
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE `$db_name`");
    
    // 檢查資料表是否存在，如果不存在就建立
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `members` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `student_id` VARCHAR(10) UNIQUE,
            `name` VARCHAR(50),
            `password` VARCHAR(32),
            `email` VARCHAR(100),
            `phone` VARCHAR(20),
            `role` ENUM('admin', 'staff', 'member') DEFAULT 'member',
            `entry_date` DATE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    return $conn;
} catch(PDOException $e) {
    die("資料庫連接錯誤: " . $e->getMessage());
}
?>
