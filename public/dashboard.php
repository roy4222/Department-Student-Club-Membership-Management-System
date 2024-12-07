<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// 取得總會員數
$stmt = $conn->query("SELECT COUNT(*) as total FROM members");
$totalMembers = $stmt->fetch()['total'];

// 取得總會費金額
$stmt = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM fee_payments 
    WHERE status = 'paid'
");
$totalAmount = $stmt->fetch()['total'];

// 取得總繳費人次
$stmt = $conn->query("
    SELECT COUNT(*) as total_payments
    FROM fee_payments
    WHERE status = 'paid'
");
$totalPayments = $stmt->fetch()['total_payments'];

// 計算未繳費人數
$unpaidCount = $totalMembers - $totalPayments;

// 取得本月會費收入
$stmt = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM fee_payments 
    WHERE MONTH(payment_date) = MONTH(CURRENT_DATE) 
    AND YEAR(payment_date) = YEAR(CURRENT_DATE)
    AND status = 'paid'
");
$monthlyIncome = $stmt->fetch()['total'];

// 取得本月活動數和參與人數
$stmt = $conn->query("
    SELECT COUNT(DISTINCT a.id) as activity_count,
           COUNT(DISTINCT ap.member_id) as participant_count
    FROM activities a
    LEFT JOIN activity_participants ap ON a.id = ap.activity_id
    WHERE MONTH(a.event_date) = MONTH(CURRENT_DATE)
    AND YEAR(a.event_date) = YEAR(CURRENT_DATE)
");
$activityStats = $stmt->fetch();

// 取得幹部數量
$stmt = $conn->query("SELECT COUNT(DISTINCT member_id) as total FROM member_positions");
$staffCount = $stmt->fetch()['total'];

// 取得即將舉辦的活動
$stmt = $conn->prepare("
    SELECT 
        id,
        name,
        location,
        event_date,
        registration_deadline,
        (
            SELECT COUNT(*) 
            FROM activity_participants 
            WHERE activity_id = activities.id
        ) as participant_count,
        max_participants
    FROM activities
    WHERE event_date >= CURRENT_DATE
    ORDER BY event_date ASC
    LIMIT 3
");
$stmt->execute();
$upcomingActivities = $stmt->fetchAll();

// 取得活躍度排行
$stmt = $conn->query("
    SELECT 
        m.name,
        COUNT(ap.activity_id) as activity_count,
        (COUNT(ap.activity_id) * 100.0 / (SELECT COUNT(*) FROM activities)) as activity_score
    FROM members m
    LEFT JOIN activity_participants ap ON m.id = ap.member_id
    GROUP BY m.id, m.name
    ORDER BY activity_count DESC
    LIMIT 10
");
$activityRanking = $stmt->fetchAll();
?>

<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        text-decoration: none;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .icon-shape {
        transition: transform 0.2s;
    }
    .stat-card:hover .icon-shape {
        transform: scale(1.1);
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #2D7A6D 0%, #48B5A3 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #2E8B57 0%, #3CB371 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #0096c7 0%, #48cae4 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ff9f1c 0%, #ffbf69 100%);
    }
    .icon-shape {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-light-primary {
        background-color: rgba(45, 122, 109, 0.1);
    }
    .text-primary {
        color: #2D7A6D !important;
    }
    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
        font-size: 0.875rem;
    }
    .upcoming-activities .activity-item:not(:last-child) {
        border-bottom: 1px solid rgba(0,0,0,.05);
    }
    .avatar-initial {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: var(--primary-color);
    }
    .feature-link {
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .feature-link:hover {
        background-color: rgba(0,0,0,0.03);
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        color: inherit;
    }
    .activity-item {
        background-color: #fff;
        transition: all 0.2s ease;
        border: 1px solid rgba(0,0,0,.05);
    }
    .activity-item:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,.05);
    }
    .activity-icon {
        width: 40px;
        height: 40px;
        background-color: #f8f9fa;
    }
    .activity-icon i {
        font-size: 1.1rem;
    }
</style>

<div class="container mt-4">
    <!-- 統計卡片 -->
    <div class="row g-3">
        <div class="col-md-3">
            <a href="members/members.php" class="card bg-gradient-primary text-white h-100 stat-card">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title mb-0">總成員數</h6>
                        <h3 class="mt-3 mb-0"><?php echo $totalMembers; ?></h3>
                    </div>
                    <div class="icon-shape">
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="payments/fees.php" class="card bg-gradient-success text-white h-100 stat-card">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title mb-0">總會費收入</h6>
                        <h3 class="mt-3 mb-0">$<?php echo number_format($totalAmount); ?></h3>
                    </div>
                    <div class="icon-shape">
                        <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="payments/fees.php" class="card bg-gradient-warning text-white h-100 stat-card">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title mb-0">未繳費人數</h6>
                        <h3 class="mt-3 mb-0"><?php echo $unpaidCount; ?></h3>
                        <small class="opacity-75">本學期</small>
                    </div>
                    <div class="icon-shape">
                        <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="activities/activities.php" class="card bg-gradient-info text-white h-100 stat-card">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title mb-0">本月活動數</h6>
                        <h3 class="mt-3 mb-0"><?php echo $activityStats['activity_count']; ?></h3>
                        <small class="opacity-75"><?php echo $activityStats['participant_count']; ?> 人參與</small>
                    </div>
                    <div class="icon-shape">
                        <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- 核心功能 -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">核心功能</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="members/members.php" class="feature-link d-flex align-items-center p-3 border rounded">
                                <div class="icon-shape bg-light-primary rounded-circle me-3">
                                    <i class="fas fa-user-cog text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">成員資料管理</h6>
                                    <p class="mb-0 text-muted small">管理成員個人資料、學籍及聯絡方式</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="payments/fees.php" class="feature-link d-flex align-items-center p-3 border rounded">
                                <div class="icon-shape bg-light-success rounded-circle me-3">
                                    <i class="fas fa-dollar-sign text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">會費管理</h6>
                                    <p class="mb-0 text-muted small">管理會費繳納紀錄、產生繳費報表</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="activities/activities.php" class="feature-link d-flex align-items-center p-3 border rounded">
                                <div class="icon-shape bg-light-info rounded-circle me-3">
                                    <i class="fas fa-chart-line text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">活動管理</h6>
                                    <p class="mb-0 text-muted small">追蹤成員參與活動及貢獻度</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="exports/export_report.php" class="feature-link d-flex align-items-center p-3 border rounded">
                                <div class="icon-shape bg-light-warning rounded-circle me-3">
                                    <i class="fas fa-file-alt text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">報表功能</h6>
                                    <p class="mb-0 text-muted small">產生各類統計報表及分析圖表</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">即將舉辦活動</h5>
                        <a href="activities/activities.php" class="text-muted small">查看全部</a>
                    </div>
                    <div class="upcoming-activities">
                        <?php foreach ($upcomingActivities as $activity): ?>
                            <?php 
                                $eventDate = new DateTime($activity['event_date']);
                                $today = new DateTime();
                                $interval = $today->diff($eventDate);
                                $daysLeft = $interval->days;
                            ?>
                            <a href="activities/activities.php" class="activity-item d-flex align-items-center p-3 rounded-3 mb-2 text-decoration-none">
                                <div class="activity-icon rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
                                    <i class="fas fa-calendar text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($activity['name']); ?></h6>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <span><?php echo htmlspecialchars($activity['location']); ?></span>
                                    </div>
                                </div>
                                <div class="text-end ms-3">
                                    <div class="text-primary"><?php echo $daysLeft; ?>天後</div>
                                    <small class="text-muted"><?php echo $activity['participant_count']; ?>/<?php echo $activity['max_participants']; ?></small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        <?php if (empty($upcomingActivities)): ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-calendar-times mb-2"></i>
                                <p class="mb-0">目前沒有即將舉辦的活動</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 活躍度排行 -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">活躍度排行榜</h5>
                        <span class="badge bg-light-primary text-primary">TOP 10</span>
                    </div>
                    <div style="height: 400px; padding: 20px 10px;">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 引入 Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// 活躍度排行圖表
const activityData = <?php echo json_encode(array_map(function($item) {
    return [
        'name' => $item['name'],
        'score' => round($item['activity_score'], 1)
    ];
}, $activityRanking)); ?>;

const ctx = document.getElementById('activityChart').getContext('2d');

// 定義漸層色
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(45, 122, 109, 0.8)');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: activityData.map(item => item.name),
        datasets: [{
            label: '活動參與度',
            data: activityData.map(item => item.score),
            backgroundColor: gradient,
            borderColor: 'rgba(45, 122, 109, 0.8)',
            borderWidth: 1,
            borderRadius: {
                topLeft: 8,
                topRight: 8
            },
            borderSkipped: false,
            barThickness: 40,  // 增加柱狀圖寬度
            maxBarThickness: 100,  // 設置最大寬度
            hoverBackgroundColor: 'rgba(45, 122, 109, 0.9)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: {
                top: 20
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                grid: {
                    display: true,
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 12,
                        family: "'Segoe UI', sans-serif"
                    },
                    padding: 10,
                    callback: function(value) {
                        return value + '%';
                    }
                },
                border: {
                    display: false
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 12,
                        family: "'Segoe UI', sans-serif"
                    },
                    padding: 5,
                    maxRotation: 45,
                    minRotation: 45
                },
                border: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                titleColor: '#2D7A6D',
                titleFont: {
                    size: 14,
                    weight: 'bold',
                    family: "'Segoe UI', sans-serif"
                },
                bodyColor: '#2D7A6D',
                bodyFont: {
                    size: 13,
                    family: "'Segoe UI', sans-serif"
                },
                padding: 15,
                cornerRadius: 8,
                displayColors: false,
                borderColor: 'rgba(45, 122, 109, 0.1)',
                borderWidth: 1,
                callbacks: {
                    title: function(tooltipItems) {
                        return tooltipItems[0].label;
                    },
                    label: function(context) {
                        return '參與度：' + context.parsed.y.toFixed(1) + '%';
                    }
                }
            }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
