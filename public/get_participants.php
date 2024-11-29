<?php
require_once '../config/database.php';

if (isset($_GET['activity_id'])) {
    $activity_id = $_GET['activity_id'];
    
    // 獲取活動資訊
    $stmt = $conn->prepare("SELECT * FROM activities WHERE id = ?");
    $stmt->execute([$activity_id]);
    $activity = $stmt->fetch();
    
    // 獲取參與者列表
    $stmt = $conn->prepare("
        SELECT 
            m.student_id,
            m.name,
            m.department,
            m.class,
            ap.attendance_status,
            ap.created_at as registered_at
        FROM activity_participants ap
        JOIN members m ON ap.member_id = m.id
        WHERE ap.activity_id = ?
        ORDER BY m.department, m.class, m.name
    ");
    $stmt->execute([$activity_id]);
    $participants = $stmt->fetchAll();
    
    if ($activity) {
        echo "<h4>" . htmlspecialchars($activity['name']) . "</h4>";
        echo "<p class='text-muted'>活動日期：" . $activity['event_date'] . "</p>";
        
        if (count($participants) > 0) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped'>";
            echo "<thead><tr>
                    <th>學號</th>
                    <th>姓名</th>
                    <th>系所</th>
                    <th>班級</th>
                    <th>狀態</th>
                    <th>報名時間</th>
                  </tr></thead>";
            echo "<tbody>";
            
            foreach ($participants as $p) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($p['student_id']) . "</td>";
                echo "<td>" . htmlspecialchars($p['name']) . "</td>";
                echo "<td>" . htmlspecialchars($p['department']) . "</td>";
                echo "<td>" . htmlspecialchars($p['class']) . "</td>";
                echo "<td>" . htmlspecialchars($p['attendance_status']) . "</td>";
                echo "<td>" . $p['registered_at'] . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table></div>";
        } else {
            echo "<p class='text-center'>目前沒有參與者</p>";
        }
    } else {
        echo "<p class='text-center'>找不到此活動</p>";
    }
} else {
    echo "<p class='text-center'>無效的請求</p>";
}
?>
