<?php
require_once('../../config/database.php');

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['member_id'])) {
    echo '<div class="alert alert-danger">缺少必要參數</div>';
    exit;
}

$member_id = intval($_GET['member_id']);

try {
    // 獲取會員參與的所有活動
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.title,
            a.activity_date,
            a.location,
            ap.attendance_status,
            ap.registration_date
        FROM 
            activities a
            INNER JOIN activity_participants ap ON a.id = ap.activity_id
        WHERE 
            ap.member_id = ?
        ORDER BY 
            a.activity_date DESC
    ");
    
    $stmt->execute([$member_id]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($activities)) {
        echo '<div class="alert alert-info">此會員尚未參加任何活動</div>';
    } else {
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover">';
        echo '<thead class="thead-light">';
        echo '<tr>';
        echo '<th>活動名稱</th>';
        echo '<th>活動日期</th>';
        echo '<th>地點</th>';
        echo '<th>報名時間</th>';
        echo '<th>出席狀態</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($activities as $activity) {
            $statusClass = '';
            $statusText = '';
            
            switch ($activity['attendance_status']) {
                case 'present':
                    $statusClass = 'success';
                    $statusText = '已出席';
                    break;
                case 'absent':
                    $statusClass = 'danger';
                    $statusText = '缺席';
                    break;
                case 'registered':
                    $statusClass = 'info';
                    $statusText = '已報名';
                    break;
                default:
                    $statusClass = 'secondary';
                    $statusText = '未知';
            }
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($activity['title']) . '</td>';
            echo '<td>' . date('Y/m/d', strtotime($activity['activity_date'])) . '</td>';
            echo '<td>' . htmlspecialchars($activity['location']) . '</td>';
            echo '<td>' . date('Y/m/d H:i', strtotime($activity['registration_date'])) . '</td>';
            echo '<td><span class="badge badge-' . $statusClass . '">' . $statusText . '</span></td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';

        // 顯示統計資訊
        $totalActivities = count($activities);
        $presentCount = array_reduce($activities, function($carry, $item) {
            return $carry + ($item['attendance_status'] == 'present' ? 1 : 0);
        }, 0);
        
        echo '<div class="card mt-3">';
        echo '<div class="card-body">';
        echo '<h6 class="card-title">參與統計</h6>';
        echo '<div class="row">';
        echo '<div class="col-md-4">';
        echo '<p class="mb-1">總活動數：<span class="badge badge-primary">' . $totalActivities . '</span></p>';
        echo '</div>';
        echo '<div class="col-md-4">';
        echo '<p class="mb-1">出席次數：<span class="badge badge-success">' . $presentCount . '</span></p>';
        echo '</div>';
        echo '<div class="col-md-4">';
        echo '<p class="mb-1">出席率：<span class="badge badge-info">' . 
             round(($presentCount / $totalActivities) * 100, 1) . '%</span></p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">獲取活動記錄時發生錯誤</div>';
}
?>
