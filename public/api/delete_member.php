<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_POST['id'])) {
    $response['message'] = '缺少會員ID';
    echo json_encode($response);
    exit;
}

$id = intval($_POST['id']);

try {
    $conn->beginTransaction();

    // 刪除會員職位關聯
    $stmt = $conn->prepare("DELETE FROM member_positions WHERE member_id = ?");
    $stmt->execute([$id]);

    // 刪除活動參與記錄
    $stmt = $conn->prepare("DELETE FROM activity_participants WHERE member_id = ?");
    $stmt->execute([$id]);

    // 刪除會費記錄
    $stmt = $conn->prepare("DELETE FROM fee_payments WHERE member_id = ?");
    $stmt->execute([$id]);

    // 刪除會員
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id]);

    $conn->commit();
    $response['success'] = true;
    $response['message'] = '會員已成功刪除';
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = '刪除失敗：' . $e->getMessage();
}

echo json_encode($response);
