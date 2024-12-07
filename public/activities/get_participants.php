<?php
require_once '../../config/database.php';
session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

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
    
    $html = '<div class="list-group">';
    foreach ($participants as $participant) {
        $html .= '<div class="list-group-item d-flex justify-content-between align-items-center">';
        $html .= '<div>';
        $html .= '<h6 class="mb-0">' . htmlspecialchars($participant['name']) . ' (' . htmlspecialchars($participant['student_id']) . ')</h6>';
        $html .= '<small class="text-muted">報名時間：' . date('Y/m/d H:i', strtotime($participant['registration_time'])) . '</small>';
        $html .= '</div>';
        if ($isAdmin) {
            $html .= '<button class="btn btn-outline-danger btn-sm" onclick="deleteParticipant('.$activity_id.', '.$participant['member_id'].', \''.htmlspecialchars($participant['name']).'\')">';
            $html .= '<i class="fas fa-user-minus"></i> 刪除';
            $html .= '</button>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    
    if (empty($participants)) {
        $html = '<div class="alert alert-info">目前還沒有參與者</div>';
    }
    
    echo $html;
} catch (PDOException $e) {
    echo json_encode(['error' => '資料庫錯誤：' . $e->getMessage()]);
}
?>
