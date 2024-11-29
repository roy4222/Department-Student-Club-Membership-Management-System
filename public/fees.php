<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// 處理會費繳納
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'pay') {
        $stmt = $conn->prepare("INSERT INTO fee_payments (member_id, semester, amount, payment_date, status) VALUES (?, ?, ?, ?, 'paid')");
        $stmt->execute([
            $_POST['member_id'],
            $_POST['semester'],
            $_POST['amount'],
            date('Y-m-d')
        ]);
    }
}

// 獲取會費記錄
$stmt = $conn->query("
    SELECT fp.*, m.student_id, m.name 
    FROM fee_payments fp 
    JOIN members m ON fp.member_id = m.id 
    ORDER BY fp.payment_date DESC
");
$payments = $stmt->fetchAll();

// 獲取會員列表（用於下拉選單）
$stmt = $conn->query("SELECT id, student_id, name FROM members ORDER BY student_id");
$members = $stmt->fetchAll();

// 計算統計資料
$totalPayments = count($payments);
$totalAmount = array_sum(array_column($payments, 'amount'));
$latestPayment = !empty($payments) ? $payments[0]['payment_date'] : '無';
?>

<div class="container mt-4">
    <!-- 頁面標題和操作按鈕 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-dollar-sign text-primary me-2"></i>
            <span>會費管理</span>
        </h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
            <i class="fas fa-plus me-2"></i>新增繳費紀錄
        </button>
    </div>

    <!-- 統計卡片 -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary bg-gradient text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">總繳費人次</h6>
                            <h3 class="mb-0"><?php echo number_format($totalPayments); ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success bg-gradient text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">總繳費金額</h6>
                            <h3 class="mb-0">$<?php echo number_format($totalAmount); ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info bg-gradient text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">最近繳費日期</h6>
                            <h3 class="mb-0"><?php echo $latestPayment; ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 會費記錄表格 -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">繳費記錄列表</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3">學號</th>
                            <th class="py-3">姓名</th>
                            <th class="py-3">學期</th>
                            <th class="py-3">金額</th>
                            <th class="py-3">繳費日期</th>
                            <th class="py-3">狀態</th>
                            <th class="py-3">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                目前沒有繳費記錄
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($payment['name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['semester']); ?></td>
                                <td>$<?php echo number_format($payment['amount']); ?></td>
                                <td><?php echo date('Y/m/d', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $payment['status'] == 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo $payment['status'] == 'paid' ? '已繳費' : '未繳費'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="printReceipt(<?php echo $payment['id']; ?>)">
                                        <i class="fas fa-print me-1"></i>列印
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 新增繳費紀錄 Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    新增繳費紀錄
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="pay">
                    <div class="mb-3">
                        <label class="form-label">會員</label>
                        <select class="form-select" name="member_id" required>
                            <option value="">請選擇會員</option>
                            <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['id']; ?>">
                                <?php echo htmlspecialchars($member['student_id'] . ' - ' . $member['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">學期</label>
                        <select class="form-select" name="semester" required>
                            <option value="112-1">112-1</option>
                            <option value="112-2" selected>112-2</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">金額</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="amount" value="500" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>儲存
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function printReceipt(paymentId) {
    window.open('print_receipt.php?id=' + paymentId, '_blank');
}

// 初始化 Select2（如果需要的話）
$(document).ready(function() {
    if ($.fn.select2) {
        $('select[name="member_id"]').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addPaymentModal')
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
