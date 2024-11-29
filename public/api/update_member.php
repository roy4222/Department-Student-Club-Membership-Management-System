<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_POST['id']) || !isset($_POST['student_id']) || !isset($_POST['name'])) {
    $response['message'] = '缺少必要欄位';
    echo json_encode($response);
    exit;
}

try {
    $conn->beginTransaction();

    // 更新會員基本資料
    $stmt = $conn->prepare("
        UPDATE members 
        SET 
            student_id = ?,
            name = ?,
            department = ?,
            class = ?,
            email = ?,
            phone = ?,
            entry_date = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['student_id'],
        $_POST['name'],
        $_POST['department'],
        $_POST['class'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['entry_date'],
        $_POST['id']
    ]);

    // 更新職位
    // 先刪除現有職位
    $stmt = $conn->prepare("DELETE FROM member_positions WHERE member_id = ?");
    $stmt->execute([$_POST['id']]);

    // 新增新的職位
    if (isset($_POST['positions']) && is_array($_POST['positions'])) {
        $stmt = $conn->prepare("
            INSERT INTO member_positions (member_id, position_id, start_date) 
            VALUES (?, ?, CURRENT_DATE)
        ");
        foreach ($_POST['positions'] as $position_id) {
            $stmt->execute([$_POST['id'], $position_id]);
        }
    }

    $conn->commit();
    $response['success'] = true;
    $response['message'] = '會員資料已更新';
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = '更新失敗：' . $e->getMessage();
}

echo json_encode($response);
