<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'member' => null];

if (!isset($_GET['id'])) {
    $response['message'] = '缺少會員ID';
    echo json_encode($response);
    exit;
}

$id = intval($_GET['id']);

try {
    // 獲取會員基本資料
    $stmt = $conn->prepare("
        SELECT m.*, GROUP_CONCAT(mp.position_id) as positions
        FROM members m
        LEFT JOIN member_positions mp ON m.id = mp.member_id
        WHERE m.id = ?
        GROUP BY m.id
    ");
    $stmt->execute([$id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        $response['success'] = true;
        $response['member'] = $member;
    } else {
        $response['message'] = '找不到指定的會員';
    }
} catch (PDOException $e) {
    $response['message'] = '資料庫錯誤';
}

echo json_encode($response);
