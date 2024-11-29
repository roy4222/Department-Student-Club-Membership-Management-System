<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// 處理新增/編輯會員
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            // 新增會員基本資料
            $stmt = $conn->prepare("INSERT INTO members (student_id, name, department, class, email, phone, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['student_id'],
                $_POST['name'],
                $_POST['department'],
                $_POST['class'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['entry_date']
            ]);
            
            // 如果有選擇職位，新增職位記錄
            if (!empty($_POST['position_id'])) {
                $member_id = $conn->lastInsertId();
                $stmt = $conn->prepare("INSERT INTO member_positions (member_id, position_id, start_date) VALUES (?, ?, CURDATE())");
                $stmt->execute([$member_id, $_POST['position_id']]);
            }
        } elseif ($_POST['action'] == 'edit') {
            // 更新會員基本資料
            $stmt = $conn->prepare("UPDATE members SET name = ?, department = ?, class = ?, email = ?, phone = ?, entry_date = ? WHERE student_id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['department'],
                $_POST['class'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['entry_date'],
                $_POST['student_id']
            ]);

            // 更新職位記錄
            if (!empty($_POST['position_id'])) {
                // 先取得會員 ID
                $stmt = $conn->prepare("SELECT id FROM members WHERE student_id = ?");
                $stmt->execute([$_POST['student_id']]);
                $member = $stmt->fetch();

                // 結束現有的職位（如果有的話）
                $stmt = $conn->prepare("UPDATE member_positions SET end_date = CURDATE() WHERE member_id = ? AND end_date IS NULL");
                $stmt->execute([$member['id']]);
                
                // 新增新的職位記錄
                $stmt = $conn->prepare("INSERT INTO member_positions (member_id, position_id, start_date) VALUES (?, ?, CURDATE())");
                $stmt->execute([$member['id'], $_POST['position_id']]);
            }
        } elseif ($_POST['action'] == 'delete') {
            try {
                // 開始交易
                $conn->beginTransaction();

                // 先刪除相關的職位記錄
                $stmt = $conn->prepare("DELETE FROM member_positions WHERE member_id IN (SELECT id FROM members WHERE student_id = ?)");
                $stmt->execute([$_POST['student_id']]);

                // 再刪除會員記錄
                $stmt = $conn->prepare("DELETE FROM members WHERE student_id = ?");
                $stmt->execute([$_POST['student_id']]);

                // 提交交易
                $conn->commit();
                
                // 輸出 JavaScript alert 然後重新導向
                echo "<script>alert('會員已成功刪除'); window.location.href = 'members.php';</script>";
                exit;
            } catch (Exception $e) {
                // 發生錯誤時回滾交易
                $conn->rollBack();
                $error = '刪除失敗：' . $e->getMessage();
                echo "<script>alert('$error');</script>";
            }
        }
    }
}

// 處理排序參數
$orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'student_id';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';
$validColumns = ['student_id', 'name', 'department', 'activity_count'];
$orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'student_id';
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';

// 獲取會員列表，包含活動參與次數
$query = "
    SELECT 
        m.*,
        COUNT(DISTINCT ap.activity_id) as activity_count,
        GROUP_CONCAT(DISTINCT p.name) as positions
    FROM 
        members m
        LEFT JOIN activity_participants ap ON m.id = ap.member_id
        LEFT JOIN member_positions mp ON m.id = mp.member_id
        LEFT JOIN positions p ON mp.position_id = p.id
    GROUP BY 
        m.id
";

// 根據排序參數添加ORDER BY子句
if ($orderBy === 'activity_count') {
    $query .= " ORDER BY activity_count " . $order;
} else {
    $query .= " ORDER BY " . $orderBy . " " . $order;
}

$stmt = $conn->query($query);
$members = $stmt->fetchAll();

// 獲取所有職位
$stmt = $conn->query("SELECT * FROM positions ORDER BY name");
$positions = $stmt->fetchAll();

// 生成排序URL的輔助函數
function getSortUrl($column, $currentOrderBy, $currentOrder) {
    $newOrder = ($currentOrderBy === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
    return "?sort=" . $column . "&order=" . $newOrder;
}

// 生成排序圖標的輔助函數
function getSortIcon($column, $currentOrderBy, $currentOrder) {
    if ($currentOrderBy !== $column) {
        return '<i class="fas fa-sort text-muted ms-1"></i>';
    }
    return $currentOrder === 'asc' 
        ? '<i class="fas fa-sort-up ms-1"></i>' 
        : '<i class="fas fa-sort-down ms-1"></i>';
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-users text-primary me-2"></i>會員管理
        </h2>
        <button type="button" id="addMemberBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="fas fa-user-plus me-2"></i>新增會員
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">會員列表</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>
                                <a href="<?php echo getSortUrl('student_id', $orderBy, $order); ?>" 
                                   class="text-dark text-decoration-none">
                                    學號
                                    <?php echo getSortIcon('student_id', $orderBy, $order); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortUrl('name', $orderBy, $order); ?>" 
                                   class="text-dark text-decoration-none">
                                    姓名
                                    <?php echo getSortIcon('name', $orderBy, $order); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortUrl('department', $orderBy, $order); ?>" 
                                   class="text-dark text-decoration-none">
                                    科系
                                    <?php echo getSortIcon('department', $orderBy, $order); ?>
                                </a>
                            </th>
                            <th>職位</th>
                            <th>
                                <a href="<?php echo getSortUrl('activity_count', $orderBy, $order); ?>" 
                                   class="text-dark text-decoration-none">
                                    活動參與
                                    <?php echo getSortIcon('activity_count', $orderBy, $order); ?>
                                </a>
                            </th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                目前沒有會員資料
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['department']); ?></td>
                                <td>
                                    <?php 
                                    $positionArray = $member['positions'] ? explode(',', $member['positions']) : [];
                                    foreach ($positionArray as $position) {
                                        echo '<span class="badge bg-info me-1">' . htmlspecialchars($position) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <span class="badge bg-primary rounded-pill">
                                                <?php echo $member['activity_count']; ?>
                                            </span>
                                        </div>
                                        <a href="member_activities.php?id=<?php echo $member['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-history me-1"></i>
                                            詳細記錄
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editMember(<?php echo $member['id']; ?>)">
                                            <i class="fas fa-edit me-1"></i>編輯
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteMember(<?php echo $member['id']; ?>)">
                                            <i class="fas fa-trash-alt me-1"></i>刪除
                                        </button>
                                    </div>
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

<!-- 會員表單Modal -->
<?php include __DIR__ . '/member_form_modal.php'; ?>

<script>
let currentModal = null;

// 編輯會員
function editMember(memberId) {
    console.log('Editing member:', memberId);
    fetch(`member_api.php?action=get&id=${memberId}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                loadMemberData(data.member);
                const modalElement = document.getElementById('memberFormModal');
                currentModal = new bootstrap.Modal(modalElement);
                currentModal.show();
            } else {
                alert(data.message || '載入會員資料失敗');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('發生錯誤，請稍後再試');
        });
}

// 刪除會員
function deleteMember(memberId) {
    if (confirm('確定要刪除這位會員嗎？此操作無法復原。')) {
        fetch('member_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&id=${memberId}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '刪除失敗');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('發生錯誤，請稍後再試');
        });
    }
}

// 儲存會員資料
function saveMember() {
    const form = document.getElementById('memberForm');
    const formData = new FormData(form);
    const id = formData.get('id');
    formData.append('action', id ? 'update' : 'add');

    fetch('member_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (currentModal) {
                currentModal.hide();
            }
            location.reload();
        } else {
            alert(data.message || '儲存失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('發生錯誤，請稍後再試');
    });
}

// 排序功能
function sortTable(column) {
    const currentOrder = new URLSearchParams(window.location.search).get('order') || 'asc';
    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
    window.location.href = `members.php?sort=${column}&order=${newOrder}`;
}

// 等待 DOM 完全加載後再添加事件監聽器
document.addEventListener('DOMContentLoaded', function() {
    const addMemberBtn = document.getElementById('addMemberBtn');
    if (addMemberBtn) {
        addMemberBtn.addEventListener('click', function() {
            resetMemberForm();
            const modalElement = document.getElementById('memberFormModal');
            currentModal = new bootstrap.Modal(modalElement);
            currentModal.show();
        });
    } else {
        console.error('Add member button not found');
    }

    // 監聽模態框隱藏事件
    const modalElement = document.getElementById('memberFormModal');
    modalElement.addEventListener('hidden.bs.modal', function () {
        currentModal = null;
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
