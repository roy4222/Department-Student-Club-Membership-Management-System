<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

$success_message = '';
$error_message = '';

// 檢查是否有提供 ID
if (!isset($_GET['id'])) {
    $error_message = '無效的請求';
} else {
    $payment_id = $_GET['id'];

    // 處理表單提交
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $stmt = $conn->prepare("UPDATE fee_payments SET member_id = ?, semester = ?, amount = ?, payment_date = ? WHERE id = ?");
            $result = $stmt->execute([
                $_POST['member_id'],
                $_POST['semester'],
                $_POST['amount'],
                $_POST['payment_date'],
                $payment_id
            ]);

            if ($result) {
                $success_message = '更新成功';
            }
        } catch (PDOException $e) {
            $error_message = '更新失敗：' . $e->getMessage();
        }
    }

    // 獲取繳費記錄詳情
    $stmt = $conn->prepare("
        SELECT fp.*, m.student_id, m.name 
        FROM fee_payments fp 
        JOIN members m ON fp.member_id = m.id 
        WHERE fp.id = ?
    ");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch();

    if (!$payment) {
        $error_message = '找不到該筆繳費記錄';
    }
}

// 獲取會員列表
$stmt = $conn->query("SELECT id, student_id, name FROM members ORDER BY student_id");
$members = $stmt->fetchAll();

// 如果更新成功，使用JavaScript重新導向
if ($success_message) {
    echo "<script>
        alert('更新成功！');
        window.location.href = 'fees.php';
    </script>";
    exit;
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-edit text-primary me-2"></i>
                            編輯繳費記錄
                        </h5>
                        <a href="fees.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!$error_message && isset($payment)): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">會員</label>
                            <select class="form-select" name="member_id" required>
                                <option value="">請選擇會員</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?php echo $member['id']; ?>" <?php echo $member['id'] == $payment['member_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($member['student_id'] . ' - ' . $member['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">學期</label>
                            <select class="form-select" name="semester" required>
                                <option value="112-1" <?php echo $payment['semester'] == '112-1' ? 'selected' : ''; ?>>112-1</option>
                                <option value="112-2" <?php echo $payment['semester'] == '112-2' ? 'selected' : ''; ?>>112-2</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">金額</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control" 
                                       name="amount" 
                                       value="<?php echo $payment['amount']; ?>" 
                                       required
                                       min="0"
                                       max="99999999.99"
                                       step="0.01">
                            </div>
                            <div class="form-text">請輸入0-99999999.99之間的金額</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">繳費日期</label>
                            <input type="date" class="form-control" name="payment_date" value="<?php echo $payment['payment_date']; ?>" required>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>儲存變更
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
