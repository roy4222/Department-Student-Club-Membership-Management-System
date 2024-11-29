<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = MD5($_POST['old_password']);
    $new_password = MD5($_POST['new_password']);
    $confirm_password = MD5($_POST['confirm_password']);
    
    // 檢查舊密碼是否正確
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ? AND password = ?");
    $stmt->execute([$_SESSION['user_id'], $old_password]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = "舊密碼不正確";
    } elseif ($new_password !== $confirm_password) {
        $error = "新密碼與確認密碼不符";
    } else {
        // 更新密碼
        $stmt = $conn->prepare("UPDATE members SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $_SESSION['user_id']]);
        $success = "密碼已成功更新";
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-key"></i> 修改密碼</h5>
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
                            <label>目前密碼</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>新密碼</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>確認新密碼</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">更新密碼</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
