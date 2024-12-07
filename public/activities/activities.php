<?php
require_once '../../config/database.php';

// 檢查用戶角色
session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// 檢查是否為AJAX請求
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] == 'add_participants') {
            $activity_id = $_POST['activity_id'];
            $member_ids = $_POST['member_ids'];
            
            try {
                $conn->beginTransaction();
                
                foreach ($member_ids as $member_id) {
                    // 檢查是否已經是參與者
                    $check_stmt = $conn->prepare("SELECT 1 FROM activity_participants WHERE activity_id = ? AND member_id = ?");
                    $check_stmt->execute([$activity_id, $member_id]);
                    
                    if (!$check_stmt->fetch()) {
                        // 新增參與記錄
                        $stmt = $conn->prepare("INSERT INTO activity_participants (activity_id, member_id) VALUES (?, ?)");
                        $stmt->execute([$activity_id, $member_id]);
                    }
                }
                
                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => '成功新增參與者']);
            } catch (Exception $e) {
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            exit;
        } elseif ($_POST['action'] == 'delete_participant') {
            // 刪除參與者
            $activity_id = $_POST['activity_id'];
            $member_id = $_POST['member_id'];
            
            try {
                $stmt = $conn->prepare("DELETE FROM activity_participants WHERE activity_id = ? AND member_id = ?");
                $stmt->execute([$activity_id, $member_id]);
                echo json_encode(['status' => 'success', 'message' => '成功移除參與者']);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            exit;
        } elseif ($_POST['action'] == 'delete_activity') {
            $id = $_POST['id'];
            try {
                $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => '成功刪除活動']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
    }
    exit;
}

require_once '../../includes/header.php';

// 處理一般POST請求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_activity') {
            // 新增活動
            $stmt = $conn->prepare("
                INSERT INTO activities (
                    name, 
                    description, 
                    location, 
                    event_date, 
                    max_participants
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['location'],
                $_POST['event_date'],
                $_POST['max_participants']
            ]);
            echo "<script>alert('活動已成功新增'); window.location.href = 'activities.php';</script>";
            exit;
        } elseif ($_POST['action'] == 'add_participants') {
            // 新增參與者
            $activity_id = $_POST['activity_id'];
            $member_ids = $_POST['member_ids'];
            
            try {
                $conn->beginTransaction();
                
                foreach ($member_ids as $member_id) {
                    // 檢查是否已經是參與者
                    $check_stmt = $conn->prepare("SELECT 1 FROM activity_participants WHERE activity_id = ? AND member_id = ?");
                    $check_stmt->execute([$activity_id, $member_id]);
                    
                    if (!$check_stmt->fetch()) {
                        // 新增參與記錄
                        $stmt = $conn->prepare("INSERT INTO activity_participants (activity_id, member_id) VALUES (?, ?)");
                        $stmt->execute([$activity_id, $member_id]);
                    }
                }
                
                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => '成功新增參與者']);
                exit;
            } catch (Exception $e) {
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                exit;
            }
        } elseif ($_POST['action'] == 'delete_participant') {
            // 刪除參與者
            $activity_id = $_POST['activity_id'];
            $member_id = $_POST['member_id'];
            
            try {
                $stmt = $conn->prepare("DELETE FROM activity_participants WHERE activity_id = ? AND member_id = ?");
                $stmt->execute([$activity_id, $member_id]);
                echo json_encode(['status' => 'success', 'message' => '成功移除參與者']);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            exit;
        }
    }
}

// 獲取活動列表
$activities = $conn->query("
    SELECT 
        a.*,
        COUNT(DISTINCT ap.member_id) as participant_count,
        (SELECT COUNT(*) FROM members) as total_members,
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
        <?php if ($isAdmin): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">
            <i class="fas fa-plus"></i> 新增活動
        </button>
        <?php endif; ?>
    </div>

    <!-- 活動列表 -->
    <div class="row">
        <?php foreach ($activities as $activity): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm activity-card" style="cursor: pointer;" 
                 onclick="viewActivityDetails(<?php echo $activity['id']; ?>)">
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
                            <i class="fas fa-users"></i> <?php echo $activity['participant_count']; ?> 位參與者
                            
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo ($activity['participant_count'] / $activity['total_members']) * 100; ?>%"
                             aria-valuenow="<?php echo $activity['participant_count']; ?>" 
                             aria-valuemin="0" aria-valuemax="<?php echo $activity['total_members']; ?>">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-primary btn-sm" 
                                onclick="event.stopPropagation(); viewParticipants(<?php echo $activity['id']; ?>, '<?php echo htmlspecialchars($activity['name']); ?>')">
                            <i class="fas fa-list"></i> 查看參與者
                        </button>
                        <?php if ($isAdmin): ?>
                        <button class="btn btn-outline-success btn-sm" 
                                onclick="event.stopPropagation(); addParticipant(<?php echo $activity['id']; ?>, '<?php echo htmlspecialchars($activity['name']); ?>')">
                            <i class="fas fa-user-plus"></i> 新增參與者
                        </button>
                        <?php endif; ?>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <label>地點</label>
                            <input type="text" class="form-control" name="location" required 
                                   placeholder="請輸入活動地點">
                        </div>
                        <div class="form-group mb-3">
                            <label>日期</label>
                            <input type="date" class="form-control" name="event_date" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>參與人數上限</label>
                            <input type="number" class="form-control" name="max_participants" 
                                   min="1" step="1" value="50" required>
                            <small class="form-text text-muted">請設定活動可容納的最大參與人數</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
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
                        <i class="fas fa-users"></i> <span id="view_activity_name"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="participantsList"></div>
                </div>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-muted mb-3" id="add_activity_name"></h6>
                    <form id="addParticipantsForm">
                        <input type="hidden" name="action" value="add_participants">
                        <input type="hidden" name="activity_id" id="activity_id">
                        <div class="form-group">
                            <label>選擇會員：</label>
                            <div id="members-checkbox-list" class="border p-3" style="max-height: 300px; overflow-y: auto;">
                                <!-- Checkboxes will be dynamically added here -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-primary">新增</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 活動詳細資訊Modal -->
    <div class="modal fade" id="activityDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt"></i> 活動詳細資訊
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 id="detail_activity_name" class="text-primary mb-4"></h3>
                            
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">活動說明</h6>
                                    <p id="detail_activity_description" class="mb-0"></p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="text-muted mb-2">活動地點</h6>
                                            <p id="detail_activity_location" class="mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="text-muted mb-2">活動日期</h6>
                                            <p id="detail_activity_date" class="mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">參與人數上限</h6>
                                    <p id="detail_max_participants" class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">活動管理</h6>
                                    <button class="btn btn-outline-primary btn-block mb-2" onclick="viewParticipantsList()">
                                        <i class="fas fa-users"></i> 管理參與者
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <button class="btn btn-outline-success btn-block mb-2" onclick="addNewParticipant()">
                                        <i class="fas fa-user-plus"></i> 新增參與者
                                    </button>
                                    <button class="btn btn-outline-danger btn-block" onclick="deleteActivity()">
                                        <i class="fas fa-trash"></i> 刪除活動
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">目前參與人數</h6>
                                    <div class="d-flex align-items-center">
                                        <h3 class="mb-0 mr-2" id="detail_current_participants">0</h3>
                                        <small class="text-muted" id="detail_participants_limit"></small>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-success" id="detail_participants_progress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Pass PHP isAdmin variable to JavaScript
    const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    let currentActivityId = null;

    function viewActivityDetails(id) {
        currentActivityId = id;
        // 使用 AJAX 獲取活動詳細資訊
        $.get('get_activity_details.php?id=' + id, function(activity) {
            $('#detail_activity_name').text(activity.name);
            $('#detail_activity_description').html(activity.description.replace(/\n/g, '<br>'));
            $('#detail_activity_location').text(activity.location || '未指定地點');
            $('#detail_activity_date').text(new Date(activity.event_date).toLocaleDateString('zh-TW'));
            $('#detail_max_participants').text(activity.max_participants + ' 人');
            $('#detail_current_participants').text(activity.participant_count);
            $('#detail_participants_limit').text('/ ' + activity.max_participants + ' 人');
            
            // 更新進度條
            const percentage = (activity.participant_count / activity.max_participants) * 100;
            $('#detail_participants_progress').css('width', percentage + '%');
            
            $('#activityDetailsModal').modal('show');
        });
    }

    function deleteActivity() {
        if (!currentActivityId) return;
        
        if (confirm('確定要刪除這個活動嗎？此操作無法復原。')) {
            $.ajax({
                url: 'activities.php',
                method: 'POST',
                data: {
                    action: 'delete_activity',
                    id: currentActivityId
                },
                success: function(response) {
                    response = typeof response === 'string' ? JSON.parse(response) : response;
                    if (response.success) {
                        $('#activityDetailsModal').modal('hide');
                        location.reload(); // 重新載入頁面以更新活動列表
                    } else {
                        alert(response.message || '刪除活動失敗');
                    }
                },
                error: function() {
                    alert('發生錯誤，請稍後再試');
                }
            });
        }
    }

    function viewParticipantsList() {
        if (!currentActivityId) return;
        $('#activityDetailsModal').modal('hide');
        viewParticipants(currentActivityId, $('#detail_activity_name').text());
    }

    function addNewParticipant() {
        if (!currentActivityId) return;
        $('#activityDetailsModal').modal('hide');
        addParticipants(currentActivityId, $('#detail_activity_name').text());
    }

    function viewParticipants(activityId, activityName) {
        $('#view_activity_name').text('活動：' + activityName);
        
        // 載入參與者列表
        $.get('get_participants.php', { activity_id: activityId }, function(response) {
            $('#participantsList').html(response);
            $('#viewParticipantsModal').modal('show');
        }).fail(function() {
            alert('載入參與者列表失敗');
        });
    }

    function deleteParticipant(activityId, memberId, memberName) {
        if (!isAdmin) {
            alert('您沒有權限執行此操作');
            return;
        }
        if (confirm(`確定要移除參與者 ${memberName} 嗎？`)) {
            $.ajax({
                url: 'activities.php',
                method: 'POST',
                data: {
                    action: 'delete_participant',
                    activity_id: activityId,
                    member_id: memberId
                },
                success: function(response) {
                    response = typeof response === 'string' ? JSON.parse(response) : response;
                    if (response.status === 'success') {
                        // 重新載入參與者列表
                        viewParticipants(activityId, $('#view_activity_name').text().replace('活動：', ''));
                    } else {
                        alert(response.message || '移除參與者失敗');
                    }
                },
                error: function() {
                    alert('發生錯誤，請稍後再試');
                }
            });
        }
    }

    function addParticipants(id, name) {
        $('#activityDetailsModal').modal('hide');  // 確保詳細資訊modal已關閉
        document.getElementById('add_activity_name').textContent = '活動：' + name;
        document.getElementById('activity_id').value = id;
        
        // 載入可選的會員
        fetch('get_available_members.php?activity_id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                const checkboxContainer = document.getElementById('members-checkbox-list');
                checkboxContainer.innerHTML = '';
                
                if (data.length === 0) {
                    checkboxContainer.innerHTML = '<p class="text-muted">沒有可新增的會員</p>';
                    return;
                }
                
                data.forEach(member => {
                    const checkbox = `
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" 
                                   id="member_${member.id}" 
                                   name="member_ids[]" 
                                   value="${member.id}">
                            <label class="custom-control-label" for="member_${member.id}">
                                ${member.name} (${member.student_id})
                            </label>
                        </div>
                    `;
                    checkboxContainer.innerHTML += checkbox;
                });
                
                $('#addParticipantsModal').modal('show');
            })
            .catch(error => {
                alert('載入會員列表失敗：' + error.message);
            });
    }

    function addParticipant(id, name) {
        // 載入可選的會員
        fetch('get_available_members.php?activity_id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                const checkboxContainer = document.getElementById('members-checkbox-list');
                checkboxContainer.innerHTML = '';
                
                if (data.length === 0) {
                    checkboxContainer.innerHTML = '<p class="text-muted">沒有可新增的會員</p>';
                    return;
                }
                
                data.forEach(member => {
                    const checkbox = `
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" 
                                   id="member_${member.id}" 
                                   name="member_ids[]" 
                                   value="${member.id}">
                            <label class="custom-control-label" for="member_${member.id}">
                                ${member.name} (${member.student_id})
                            </label>
                        </div>
                    `;
                    checkboxContainer.innerHTML += checkbox;
                });
                
                // Set the activity name and ID in the modal
                document.getElementById('add_activity_name').textContent = '活動：' + name;
                document.getElementById('activity_id').value = id;
                
                // Show the modal
                $('#addParticipantsModal').modal('show');
            })
            .catch(error => {
                alert('載入會員列表失敗：' + error.message);
            });
    }

    // 處理新增參與者表單提交
    document.getElementById('addParticipantsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('activities.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            alert(data.message);
            if (data.status === 'success') {
                location.reload();
            }
        })
        .catch(error => {
            alert('新增參與者時發生錯誤：' + error.message);
        });
    });

    // 確保modal關閉時重置表單
    $('#addParticipantsModal').on('hidden.bs.modal', function () {
        document.getElementById('addParticipantsForm').reset();
    });
    </script>

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

<?php require_once '../../includes/footer.php'; ?>
