<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// 獲取會員ID
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 獲取會員資訊
$stmt = $conn->prepare("
    SELECT m.*, COUNT(DISTINCT ap.activity_id) as total_activities
    FROM members m
    LEFT JOIN activity_participants ap ON m.id = ap.member_id
    WHERE m.id = ?
    GROUP BY m.id
");
$stmt->execute([$member_id]);
$member = $stmt->fetch();

if (!$member) {
    echo '<div class="alert alert-danger">找不到指定的會員</div>';
    exit;
}

// 獲取活動參與記錄
$stmt = $conn->prepare("
    SELECT 
        a.*,
        ap.attendance_status,
        ap.created_at as registration_date
    FROM 
        activities a
        JOIN activity_participants ap ON a.id = ap.activity_id
    WHERE 
        ap.member_id = ?
    ORDER BY 
        a.event_date DESC
");
$stmt->execute([$member_id]);
$activities = $stmt->fetchAll();

// 計算統計資料
$totalActivities = count($activities);
$attendedActivities = array_filter($activities, function($activity) {
    return $activity['attendance_status'] === 'attended';
});
$attendanceRate = $totalActivities > 0 ? round(count($attendedActivities) / $totalActivities * 100) : 0;
?>

<div class="container mt-4">
    <!-- 返回按鈕 -->
    <div class="mb-4">
        <a href="members.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>返回會員列表
        </a>
    </div>

    <!-- 會員資訊卡片 -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                     style="width: 48px; height: 48px; font-size: 1.5rem;">
                    <?php echo strtoupper(mb_substr($member['name'], 0, 1)); ?>
                </div>
                <div>
                    <h4 class="mb-1"><?php echo htmlspecialchars($member['name']); ?></h4>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($member['student_id']); ?> | 
                        <?php echo htmlspecialchars($member['department']); ?>
                    </p>
                </div>
            </div>
            
            <!-- 活動參與統計 -->
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <h6 class="text-muted mb-2">總參與活動</h6>
                        <h3 class="mb-0"><?php echo $totalActivities; ?> 次</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <h6 class="text-muted mb-2">實際出席活動</h6>
                        <h3 class="mb-0"><?php echo count($attendedActivities); ?> 次</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <h6 class="text-muted mb-2">出席率</h6>
                        <h3 class="mb-0"><?php echo $attendanceRate; ?>%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 活動參與記錄 -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">活動參與記錄</h5>
        </div>
        <div class="card-body">
            <?php if (empty($activities)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p class="mb-0">目前沒有活動參與記錄</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>活動名稱</th>
                            <th>活動日期</th>
                            <th>報名日期</th>
                            <th>出席狀態</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="activity-icon me-3">
                                        <i class="fas fa-calendar-day fa-lg text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($activity['name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo $activity['description'] ? htmlspecialchars($activity['description']) : '無說明'; ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo date('Y/m/d', strtotime($activity['event_date'])); ?></td>
                            <td><?php echo date('Y/m/d', strtotime($activity['registration_date'])); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $activity['attendance_status'] === 'attended' ? 'success' : 
                                        ($activity['attendance_status'] === 'absent' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php 
                                    echo $activity['attendance_status'] === 'attended' ? '已出席' : 
                                        ($activity['attendance_status'] === 'absent' ? '未出席' : '已報名');
                                    ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
