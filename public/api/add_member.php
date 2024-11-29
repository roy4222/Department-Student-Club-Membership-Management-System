<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_POST['student_id']) || !isset($_POST['name'])) {
    $response['message'] = '缺少必要欄位';
    echo json_encode($response);
    exit;
}

try {
    $conn->beginTransaction();

    // 檢查學號是否已存在
    $stmt = $conn->prepare("SELECT COUNT(*) FROM members WHERE student_id = ?");
    $stmt->execute([$_POST['student_id']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('此學號已經存在');
    }

    // 新增會員基本資料
    $stmt = $conn->prepare("
        INSERT INTO members (
            student_id, name, department, class, 
            email, phone, entry_date
        ) VALUES (
            ?, ?, ?, ?, 
            ?, ?, ?
        )
    ");

    $stmt->execute([
        $_POST['student_id'],
        $_POST['name'],
        $_POST['department'],
        $_POST['class'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['entry_date']
    ]);

    $memberId = $conn->lastInsertId();

    // 新增職位
    if (isset($_POST['positions']) && is_array($_POST['positions'])) {
        $stmt = $conn->prepare("
            INSERT INTO member_positions (member_id, position_id, start_date) 
            VALUES (?, ?, CURRENT_DATE)
        ");
        foreach ($_POST['positions'] as $position_id) {
            $stmt->execute([$memberId, $position_id]);
        }
    }

    $conn->commit();
    $response['success'] = true;
    $response['message'] = '會員新增成功';
} catch (Exception $e) {
    $conn->rollBack();
    $response['message'] = '新增失敗：' . $e->getMessage();
}

echo json_encode($response);
