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
        SELECT id, name, student_id, role
        FROM members 
        WHERE id NOT IN (
            SELECT COALESCE(member_id, 0)
            FROM activity_participants 
            WHERE activity_id = ?
        )
        ORDER BY name ASC
    ");
    
    $stmt->execute([$activity_id]);
    $available_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($available_members)) {
        // 如果沒有可用會員，檢查是否有任何會員存在
        $check_stmt = $conn->query("SELECT COUNT(*) as count FROM members");
        $total_members = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($total_members == 0) {
            echo json_encode(['error' => '目前還沒有任何會員']);
        } else {
            echo json_encode(['error' => '所有會員都已經參加此活動']);
        }
        exit;
    }
    
    echo json_encode($available_members);
} catch (PDOException $e) {
    echo json_encode(['error' => '資料庫錯誤：' . $e->getMessage()]);
}
