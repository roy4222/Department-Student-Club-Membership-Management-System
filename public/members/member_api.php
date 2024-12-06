<?php
require_once '../../config/database.php';
session_start();

// 檢查是否登入
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

// 檢查是否為管理員（用於新增/編輯/刪除操作）
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$currentUserId = $_SESSION['user_id'];

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'member' => null];

// 確保請求包含動作
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

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
            // 檢查管理員權限
            if (!$isAdmin) {
                throw new Exception('您沒有權限執行此操作');
            }
            
            if (!isset($_POST['student_id']) || !isset($_POST['name']) || !isset($_POST['password'])) {
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
                    email, phone, entry_date, password, role
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_POST['student_id'],
                $_POST['name'],
                $_POST['department'],
                $_POST['class'],
                $_POST['email'] ?? null,
                $_POST['phone'] ?? null,
                $_POST['entry_date'],
                md5($_POST['password']),
                $_POST['role'] ?? 'member'
            ]);

            $memberId = $conn->lastInsertId();

            // 處理職位
            if (isset($_POST['positions']) && is_array($_POST['positions'])) {
                $positionStmt = $conn->prepare("INSERT INTO member_positions (member_id, position_id) VALUES (?, ?)");
                foreach ($_POST['positions'] as $positionId) {
                    $positionStmt->execute([$memberId, $positionId]);
                }
            }

            $conn->commit();
            $response['success'] = true;
            $response['message'] = '會員新增成功';
            break;

        case 'edit':
            // 檢查管理員權限
            if (!$isAdmin) {
                throw new Exception('您沒有權限執行此操作');
            }

            // 檢查是否有會員ID
            if (!isset($_POST['id'])) {
                throw new Exception('缺少會員ID');
            }

            $conn->beginTransaction();

            try {
                // 準備更新欄位
                $updateFields = [];
                $params = [];

                // 檢查並添加各個欄位
                if (isset($_POST['name'])) {
                    $updateFields[] = "name = ?";
                    $params[] = $_POST['name'];
                }
                
                if (isset($_POST['department'])) {
                    $updateFields[] = "department = ?";
                    $params[] = $_POST['department'];
                }
                
                if (isset($_POST['class'])) {
                    $updateFields[] = "class = ?";
                    $params[] = $_POST['class'];
                }
                
                if (isset($_POST['email'])) {
                    $updateFields[] = "email = ?";
                    $params[] = $_POST['email'];
                }
                
                if (isset($_POST['phone'])) {
                    $updateFields[] = "phone = ?";
                    $params[] = $_POST['phone'];
                }
                
                if (isset($_POST['entry_date'])) {
                    $updateFields[] = "entry_date = ?";
                    $params[] = $_POST['entry_date'];
                }

                if (isset($_POST['role'])) {
                    $updateFields[] = "role = ?";
                    $params[] = $_POST['role'];
                }

                // 如果有提供新密碼，則更新密碼
                if (!empty($_POST['password'])) {
                    $updateFields[] = "password = ?";
                    $params[] = md5($_POST['password']);
                }

                // 如果有要更新的欄位
                if (!empty($updateFields)) {
                    // 加入ID到參數陣列
                    $params[] = $_POST['id'];

                    // 更新會員基本資料
                    $stmt = $conn->prepare("
                        UPDATE members 
                        SET " . implode(", ", $updateFields) . "
                        WHERE id = ?
                    ");
                    
                    $stmt->execute($params);
                }

                // 更新職位
                // 先刪除現有職位
                $stmt = $conn->prepare("DELETE FROM member_positions WHERE member_id = ?");
                $stmt->execute([$_POST['id']]);

                // 如果有選擇新的職位，則新增
                if (isset($_POST['positions']) && is_array($_POST['positions'])) {
                    // 去除重複的職位ID
                    $positions = array_unique($_POST['positions']);
                    
                    $stmt = $conn->prepare("INSERT INTO member_positions (member_id, position_id) VALUES (?, ?)");
                    foreach ($positions as $positionId) {
                        try {
                            $stmt->execute([$_POST['id'], $positionId]);
                        } catch (PDOException $e) {
                            // 如果是重複鍵的錯誤,則忽略
                            if ($e->getCode() != '23000') {
                                throw $e;
                            }
                        }
                    }
                }

                $conn->commit();
                $response['success'] = true;
                $response['message'] = '會員資料更新成功';
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;

        case 'delete':
            // 檢查管理員權限
            if (!$isAdmin) {
                throw new Exception('您沒有權限執行此操作');
            }
            
            if (!isset($_POST['id'])) {
                throw new Exception('缺少會員ID');
            }
            
            $memberId = intval($_POST['id']);
            
            // 防止刪除自己的帳號
            if ($memberId === $currentUserId) {
                throw new Exception('不能刪除自己的帳號');
            }

            $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
            $stmt->execute([$_POST['id']]);

            $response['success'] = true;
            $response['message'] = '會員已刪除';
            break;

        default:
            throw new Exception('不支援的操作');
    }
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
