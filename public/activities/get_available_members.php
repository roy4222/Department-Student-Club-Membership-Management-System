<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['activity_id'])) {
    echo json_encode(['error' => '缺少活動ID']);
    exit;
}

$activity_id = $_GET['activity_id'];

try {
    // 獲取尚未參加此活動的會員
    $stmt = $conn->prepare("
        SELECT id, name, student_id 
        FROM members 
        WHERE id NOT IN (
            SELECT member_id 
            FROM activity_participants 
            WHERE activity_id = ?
        )
        ORDER BY name ASC
    ");
    
    $stmt->execute([$activity_id]);
    $available_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($available_members);
} catch (PDOException $e) {
    echo json_encode(['error' => '資料庫錯誤：' . $e->getMessage()]);
}
