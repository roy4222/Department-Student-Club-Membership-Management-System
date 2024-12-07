<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => '缺少活動ID']);
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            COUNT(DISTINCT ap.member_id) as participant_count
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
    
    if (isset($_GET['participants'])) {
        $stmt = $conn->prepare("
            SELECT m.*, ap.created_at as joined_at
            FROM members m
            JOIN activity_participants ap ON m.id = ap.member_id
            WHERE ap.activity_id = ?
            ORDER BY ap.created_at ASC
        ");
        $stmt->execute([$id]);
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $html = '<div class="list-group">';
        foreach ($participants as $participant) {
            $html .= '<div class="list-group-item d-flex justify-content-between align-items-center">';
            $html .= '<div>';
            $html .= '<h6 class="mb-0">' . htmlspecialchars($participant['name']) . '</h6>';
            $html .= '<small class="text-muted">加入時間：' . date('Y/m/d H:i', strtotime($participant['joined_at'])) . '</small>';
            $html .= '</div>';
            if ($isAdmin) {
                $html .= '<button class="btn btn-outline-danger btn-sm" onclick="deleteParticipant('.$id.', '.$participant['id'].', \''.htmlspecialchars($participant['name']).'\')">';
                $html .= '<i class="fas fa-user-minus"></i> 移除';
                $html .= '</button>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        
        if (count($participants) == 0) {
            $html = '<div class="alert alert-info">目前還沒有參與者</div>';
        }
        
        echo $html;
        exit;
    }
    
    echo json_encode($activity);
} catch (PDOException $e) {
    echo json_encode(['error' => '資料庫錯誤：' . $e->getMessage()]);
}
