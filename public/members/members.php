<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

// JavaScript functions
?>
<script>
// 初始化 Modal
let memberFormModal;

// 刪除會員
function deleteMember(studentId) {
    if (confirm('確定要刪除此會員嗎？')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const studentIdInput = document.createElement('input');
        studentIdInput.type = 'hidden';
        studentIdInput.name = 'student_id';
        studentIdInput.value = studentId;
        
        form.appendChild(actionInput);
        form.appendChild(studentIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// 載入會員資料到表單
function loadMemberData(member) {
    try {
        console.log('Loading member data:', member); // 調試用
        
        // 設置表單標題
        document.getElementById('memberFormModalLabel').textContent = '編輯會員';
        
        // 設置表單欄位
        document.getElementById('memberId').value = member.id || '';
        document.getElementById('studentId').value = member.student_id || '';
        document.getElementById('name').value = member.name || '';
        document.getElementById('department').value = member.department || '';
        document.getElementById('class').value = member.class || '';
        document.getElementById('email').value = member.email || '';
        document.getElementById('phone').value = member.phone || '';
        document.getElementById('entryDate').value = member.entry_date || '';
        
        // 設置學號欄位為唯讀
        document.getElementById('studentId').readOnly = true;
        
        // 設置職位
        const positions = member.positions ? member.positions.split(',') : [];
        console.log('Positions:', positions); // 調試用
        const checkboxes = document.querySelectorAll('input[name="positions[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = positions.includes(checkbox.value);
        });

        // 顯示 Modal
        memberFormModal.show();
    } catch (error) {
        console.error('Error in loadMemberData:', error);
        alert('載入會員資料時發生錯誤');
    }
}

// 重置並顯示會員表單
function resetMemberForm() {
    const form = document.getElementById('memberForm');
    form.reset();
    document.getElementById('memberId').value = '';
    document.getElementById('studentId').readOnly = false;
    document.getElementById('memberFormModalLabel').textContent = '新增會員';
    
    // 清除所有職位選擇
    const checkboxes = document.querySelectorAll('input[name="positions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    
    // 顯示 Modal
    const modal = new bootstrap.Modal(document.getElementById('memberFormModal'));
    modal.show();
}

// 儲存會員資料
function saveMember() {
    const form = document.getElementById('memberForm');
    if (form.checkValidity()) {
        form.submit();
    } else {
        form.classList.add('was-validated');
    }
}

// 當文檔加載完成後設置事件監聽器
document.addEventListener('DOMContentLoaded', function() {
    // 初始化 Modal
    memberFormModal = new bootstrap.Modal(document.getElementById('memberFormModal'));
    
    // 為新增會員按鈕添加事件監聽器
    document.querySelector('.btn-add-member')?.addEventListener('click', resetMemberForm);
    
    // 為編輯按鈕添加事件監聽器
    document.querySelectorAll('.btn-edit-member').forEach(button => {
        button.addEventListener('click', function() {
            try {
                const memberData = this.getAttribute('data-member');
                console.log('Member data:', memberData); // 調試用
                const member = JSON.parse(memberData);
                loadMemberData(member);
            } catch (error) {
                console.error('Error handling edit button click:', error);
                alert('載入會員資料時發生錯誤');
            }
        });
    });
    
    // 為所有刪除按鈕添加事件監聽器
    document.querySelectorAll('.btn-delete-member').forEach(button => {
        button.addEventListener('click', () => {
            const studentId = button.getAttribute('data-student-id');
            deleteMember(studentId);
        });
    });
});
</script>

<?php
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
        GROUP_CONCAT(
            DISTINCT 
            CONCAT(p.name, ' (', DATE_FORMAT(mp.created_at, '%Y-%m-%d'), ')')
            ORDER BY mp.created_at DESC
        ) as positions
    FROM 
        members m
        LEFT JOIN activity_participants ap ON m.id = ap.member_id
        LEFT JOIN member_positions mp ON m.id = mp.member_id
        LEFT JOIN positions p ON mp.position_id = p.id
    GROUP BY 
        m.id, m.student_id, m.name, m.department, m.class, m.email, m.phone, m.entry_date
    ORDER BY 
        $orderBy $order";

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
        <button type="button" class="btn btn-primary btn-add-member">
            <i class="fas fa-plus me-1"></i>新增會員
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
                                <a href="<?php echo getSortUrl('student_id', $orderBy, $order); ?>" class="text-decoration-none text-dark">
                                    學號 <?php echo getSortIcon('student_id', $orderBy, $order); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortUrl('name', $orderBy, $order); ?>" class="text-decoration-none text-dark">
                                    姓名 <?php echo getSortIcon('name', $orderBy, $order); ?>
                                </a>
                            </th>
                            <th>聯繫方式</th>
                            <th>入學時間</th>
                            <th>曾任職位</th>
                            <th>
                                <a href="<?php echo getSortUrl('activity_count', $orderBy, $order); ?>" class="text-decoration-none text-dark">
                                    活動參與 <?php echo getSortIcon('activity_count', $orderBy, $order); ?>
                                </a>
                            </th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                目前有會員資料
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                            <td>
                                <div><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($member['email']); ?></div>
                                <div><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($member['phone']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($member['entry_date']); ?></td>
                            <td>
                                <?php 
                                if ($member['positions']) {
                                    $positions = explode(',', $member['positions']);
                                    foreach ($positions as $position) {
                                        echo "<div class='badge bg-secondary mb-1'>" . htmlspecialchars($position) . "</div><br>";
                                    }
                                } else {
                                    echo "<span class='text-muted'>無</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="member_activities.php?id=<?php echo $member['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    <?php echo $member['activity_count']; ?> 次
                                </a>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-member" data-member="<?php echo htmlspecialchars(json_encode($member)); ?>">
                                        <i class="fas fa-edit me-1"></i>編輯
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-member" data-student-id="<?php echo $member['student_id']; ?>">
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

<?php require_once '../../includes/footer.php'; ?>
