<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

// 處理活動相關操作
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_activity') {
            // 新增活動
            $stmt = $conn->prepare("INSERT INTO activities (name, description, event_date) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['event_date']
            ]);
            echo "<script>alert('活動已成功新增'); window.location.href = 'activities.php';</script>";
            exit;
        } elseif ($_POST['action'] == 'add_participants') {
            // 新增參與者
            $activity_id = $_POST['activity_id'];
            $student_ids = explode("\n", trim($_POST['student_ids']));
            
            try {
                $conn->beginTransaction();
                
                // 準備查詢語句
                $member_stmt = $conn->prepare("SELECT id FROM members WHERE student_id = ?");
                $participant_stmt = $conn->prepare("INSERT INTO activity_participants (activity_id, member_id) VALUES (?, ?)");
                
                foreach ($student_ids as $student_id) {
                    $student_id = trim($student_id);
                    if (empty($student_id)) continue;
                    
                    // 查找會員ID
                    $member_stmt->execute([$student_id]);
                    $member = $member_stmt->fetch();
                    
                    if ($member) {
                        // 新增參與記錄
                        $participant_stmt->execute([$activity_id, $member['id']]);
                    }
                }
                
                $conn->commit();
                echo "<script>alert('參與者已成功新增'); window.location.href = 'activities.php';</script>";
                exit;
            } catch (Exception $e) {
                $conn->rollBack();
                echo "<script>alert('錯誤：" . $e->getMessage() . "');</script>";
            }
        }
    }
}

// 獲取活動列表
$activities = $conn->query("
    SELECT 
        a.*,
        COUNT(DISTINCT ap.member_id) as participant_count,
        MIN(ap.created_at) as first_registration,
        MAX(ap.created_at) as last_registration
    FROM activities a
    LEFT JOIN activity_participants ap ON a.id = ap.activity_id
    GROUP BY a.id
    ORDER BY a.event_date DESC
")->fetchAll();
?>

<div class="container mt-4">
    <!-- 頁面標題和新增按鈕 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-calendar-alt"></i> 活動管理
            <small class="text-muted fs-6">（共 <?php echo count($activities); ?> 個活動）</small>
        </h2>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addActivityModal">
            <i class="fas fa-plus"></i> 新增活動
        </button>
    </div>

    <!-- 活動列表 -->
    <div class="row">
        <?php foreach ($activities as $activity): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-primary mb-0">
                            <?php echo htmlspecialchars($activity['name']); ?>
                        </h5>
                        <span class="badge bg-<?php echo strtotime($activity['event_date']) > time() ? 'success' : 'secondary'; ?>">
                            <?php echo strtotime($activity['event_date']) > time() ? '即將舉行' : '已結束'; ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        <?php echo nl2br(htmlspecialchars($activity['description'])); ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted small">
                            <i class="far fa-calendar"></i> 
                            <?php echo date('Y/m/d', strtotime($activity['event_date'])); ?>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-users"></i> 
                            <?php echo $activity['participant_count']; ?> 位參與者
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo ($activity['participant_count'] / 50) * 100; ?>%"
                             aria-valuenow="<?php echo $activity['participant_count']; ?>" 
                             aria-valuemin="0" aria-valuemax="50">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-primary btn-sm" 
                                onclick="viewParticipants(<?php echo $activity['id']; ?>, '<?php echo htmlspecialchars($activity['name']); ?>')">
                            <i class="fas fa-list"></i> 查看參與者
                        </button>
                        <button class="btn btn-outline-success btn-sm" 
                                onclick="setActivityForParticipants(<?php echo $activity['id']; ?>, '<?php echo htmlspecialchars($activity['name']); ?>')">
                            <i class="fas fa-user-plus"></i> 新增參與者
                        </button>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 text-muted small">
                    <i class="fas fa-clock"></i> 最後更新：<?php echo date('Y/m/d H:i', strtotime($activity['updated_at'])); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 新增活動Modal -->
    <div class="modal fade" id="addActivityModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle"></i> 新增活動
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_activity">
                        <div class="form-group mb-3">
                            <label>活動名稱</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>描述</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label>日期</label>
                            <input type="date" class="form-control" name="event_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 新增參與者Modal -->
    <div class="modal fade" id="addParticipantsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i> 新增參與者
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_participants">
                        <input type="hidden" name="activity_id" id="activity_id">
                        <h6 class="text-muted mb-3" id="activity_name_display"></h6>
                        <div class="form-group">
                            <label>學號（每行一個）</label>
                            <textarea class="form-control" name="student_ids" rows="5" required 
                                    placeholder="請輸入學號，每行一個&#10;例如：&#10;B12345678&#10;B12345679"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 查看參與者Modal -->
    <div class="modal fade" id="viewParticipantsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users"></i> 活動參與者
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6 class="text-muted mb-3" id="view_activity_name"></h6>
                    <div id="participantsList">
                        <!-- 參與者列表將通過AJAX載入 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
    border: none;
}

.card:hover {
    transform: translateY(-5px);
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
}

.progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.modal-content {
    border: none;
    border-radius: 15px;
}

.modal-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.modal-footer {
    border-top: 1px solid rgba(0,0,0,0.1);
}

.btn-outline-primary:hover, .btn-outline-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}
</style>

<script>
function setActivityForParticipants(id, name) {
    document.getElementById('activity_id').value = id;
    document.getElementById('activity_name_display').textContent = '活動：' + name;
    $('#addParticipantsModal').modal('show');
}

function viewParticipants(id, name) {
    document.getElementById('view_activity_name').textContent = '活動：' + name;
    $.get('get_participants.php?activity_id=' + id, function(data) {
        $('#participantsList').html(data);
        $('#viewParticipantsModal').modal('show');
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>
