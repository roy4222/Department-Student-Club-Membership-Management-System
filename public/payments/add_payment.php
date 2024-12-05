<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

// 檢查權限
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: ../dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $conn->prepare("INSERT INTO fee_payments (member_id, semester, amount, payment_date, status) VALUES (?, ?, ?, ?, 'paid')");
        $stmt->execute([
            $_POST['member_id'],
            $_POST['semester'],
            $_POST['amount'],
            $_POST['payment_date']
        ]);
        $success = "繳費記錄已成功新增";
    } catch (PDOException $e) {
        $error = "新增失敗：" . $e->getMessage();
    }
}

// 獲取會員列表
$stmt = $conn->query("SELECT id, student_id, name FROM members ORDER BY student_id");
$members = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-plus"></i> 新增會費繳納記錄</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>會員</label>
                            <select name="member_id" class="form-control" required>
                                <option value="">請選擇會員</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?php echo $member['id']; ?>">
                                        <?php echo htmlspecialchars($member['student_id'] . ' - ' . $member['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>學期</label>
                            <select name="semester" class="form-control" required>
                                <option value="112-1">112-1</option>
                                <option value="112-2" selected>112-2</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>金額</label>
                            <input type="number" name="amount" class="form-control" value="500" required>
                        </div>
                        <div class="form-group">
                            <label>繳費日期</label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">新增繳費記錄</button>
                        <a href="../fees.php" class="btn btn-secondary">返回</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
