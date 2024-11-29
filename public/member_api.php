<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'member' => null];

// 確保請求包含動作
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if (empty($action)) {
    $response['message'] = '未指定操作';
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'get':
            if (!isset($_GET['id'])) {
                throw new Exception('缺少會員ID');
            }
            $id = intval($_GET['id']);
            
            // 獲取會員基本資料和職位
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
                throw new Exception('找不到指定的會員');
            }
            break;

        case 'add':
            if (!isset($_POST['student_id']) || !isset($_POST['name'])) {
                throw new Exception('缺少必要欄位');
            }

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
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_POST['student_id'],
                $_POST['name'],
                $_POST['department'],
                $_POST['class'],
                $_POST['email'] ?? null,
                $_POST['phone'] ?? null,
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
            break;

        case 'update':
            if (!isset($_POST['id']) || !isset($_POST['student_id']) || !isset($_POST['name'])) {
                throw new Exception('缺少必要欄位');
            }

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
                $_POST['email'] ?? null,
                $_POST['phone'] ?? null,
                $_POST['entry_date'],
                $_POST['id']
            ]);

            // 更新職位
            $stmt = $conn->prepare("DELETE FROM member_positions WHERE member_id = ?");
            $stmt->execute([$_POST['id']]);

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
            break;

        case 'delete':
            if (!isset($_POST['id'])) {
                throw new Exception('缺少會員ID');
            }

            $id = intval($_POST['id']);
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
            break;

        default:
            throw new Exception('無效的操作');
    }
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
