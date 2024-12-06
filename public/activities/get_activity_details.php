<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => '缺少活動ID']);
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            COUNT(ap.member_id) as participant_count
        FROM activities a
        LEFT JOIN activity_participants ap ON a.id = ap.activity_id
        WHERE a.id = ?
        GROUP BY a.id
    ");
    
    $stmt->execute([$id]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activity) {
        echo json_encode(['error' => '找不到活動']);
        exit;
    }
    
    echo json_encode($activity);
} catch (PDOException $e) {
    echo json_encode(['error' => '資料庫錯誤：' . $e->getMessage()]);
}
