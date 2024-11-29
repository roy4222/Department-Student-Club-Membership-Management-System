<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// 取得會員統計
$stmt = $conn->query("SELECT COUNT(*) as total_members FROM members");
$memberCount = $stmt->fetch()['total_members'];

// 取得本學期會費統計
$stmt = $conn->query("SELECT COUNT(*) as paid_count FROM fee_payments WHERE semester = '112-2' AND status = 'paid'");
$paidCount = $stmt->fetch()['paid_count'];

// 取得未繳費會員
$stmt = $conn->prepare("
    SELECT m.name, m.student_id 
    FROM members m 
    LEFT JOIN fee_payments fp ON m.id = fp.member_id AND fp.semester = '112-2'
    WHERE fp.id IS NULL 
    LIMIT 5
");
$stmt->execute();
$unpaidMembers = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <a href="export_csv.php" class="btn btn-success">
                <i class="fas fa-file-csv"></i> 匯出會員繳費狀態CSV
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> 總會員數</h5>
                    <p class="card-text display-4"><?php echo $memberCount; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-check-circle"></i> 本學期已繳費</h5>
                    <p class="card-text display-4"><?php echo $paidCount; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-exclamation-circle"></i> 待繳費人數</h5>
                    <p class="card-text display-4"><?php echo $memberCount - $paidCount; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-triangle"></i> 未繳費會員名單</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>學號</th>
                                    <th>姓名</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unpaidMembers as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                                    <td>
                                        <a href="fees.php?student_id=<?php echo $member['student_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-dollar-sign"></i> 繳費
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-alt"></i> 最近活動</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">112-2 學期會費開始收取</h6>
                                <small>3 天前</small>
                            </div>
                            <p class="mb-1">請各位同學記得繳交本學期會費。</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
