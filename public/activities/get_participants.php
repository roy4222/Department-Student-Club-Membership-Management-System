<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['activity_id'])) {
    echo json_encode(['error' => '缺少活動ID']);
    exit;
}

$activity_id = $_GET['activity_id'];

try {
    $stmt = $conn->prepare("
        SELECT 
            m.id as member_id,
            m.student_id,
            m.name,
            ap.created_at as registration_time
        FROM activity_participants ap
        JOIN members m ON ap.member_id = m.id
        WHERE ap.activity_id = ?
        ORDER BY ap.created_at ASC
    ");
    
    $stmt->execute([$activity_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($participants);
} catch (PDOException $e) {
    echo json_encode(['error' => '資料庫錯誤：' . $e->getMessage()]);
}
