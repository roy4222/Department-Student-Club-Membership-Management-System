<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

// JavaScript functions
?>
<script>
// 初始化 Modal
let memberFormModal;

// 刪除會員
function deleteMember(memberId) {
    // 檢查是否要刪除自己的帳號
    if (memberId == <?php echo $_SESSION['user_id']; ?>) {
        alert('不能刪除自己的帳號！');
        return;
    }
    
    if (confirm('確定要刪除此會員嗎？')) {
        const formData = new FormData();
        formData.append('id', memberId);
        
        fetch('member_api.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
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

// 載入會員資料到表單
function loadMemberData(member) {
    try {
        console.log('Loading member data:', member); // 調試用
        
        // 設置表單標題
        document.getElementById('memberFormModalLabel').textContent = '編輯會員';
        
        // 設置表單欄位
        document.getElementById('memberId').value = member.id || '';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('studentId').value = member.student_id || '';
        document.getElementById('name').value = member.name || '';
        document.getElementById('department').value = member.department || '';
        document.getElementById('class').value = member.class || '';
        document.getElementById('email').value = member.email || '';
        document.getElementById('phone').value = member.phone || '';
        document.getElementById('entryDate').value = member.entry_date || '';
        document.getElementById('role').value = member.role || 'member';
        
        // 設置密碼提示
        document.querySelector('.password-hint').textContent = '如不修改密碼可留空';
        
        // 設置職位容器的會員ID
        const positionsContainer = document.getElementById('positionsContainer');
        if (positionsContainer) {
            positionsContainer.dataset.memberId = member.id;
        }
        
        // 設置職位
        if (member.position_ids) {
            const positionIds = member.position_ids.split(',');
            // 重置所有checkbox
            document.querySelectorAll('.position-checkbox').forEach(checkbox => {
                checkbox.checked = positionIds.includes(checkbox.value);
            });
        } else {
            // 如果沒有職位，取消所有選取
            document.querySelectorAll('.position-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
        
        // 打開Modal
        memberFormModal.show();
    } catch (error) {
        console.error('Error loading member data:', error);
        alert('載入會員資料時發生錯誤');
    }
}

// 重置並顯示會員表單
function resetMemberForm() {
    const form = document.getElementById('memberForm');
    form.reset();
    document.getElementById('memberId').value = '';
    
    // 重設學號欄位為可編輯
    const studentIdField = document.getElementById('studentId');
    studentIdField.readOnly = false;
    
    document.getElementById('memberFormModalLabel').textContent = '新增會員';
    document.getElementById('formAction').value = 'add';
    
    // 設定密碼欄位為必填
    document.getElementById('password').setAttribute('required', 'required');
    document.querySelector('.password-hint').textContent = '新���員時必須設定密碼';
    
    // 清除所有職位選擇
    const checkboxes = document.querySelectorAll('input[name="positions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    
    // 清除錯誤訊息
    const formError = document.getElementById('formError');
    formError.textContent = '';
    formError.classList.add('d-none');
    
    // 顯示 Modal
    memberFormModal.show();
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
        button.addEventListener('click', function() {
            const memberId = this.getAttribute('data-member-id');
            deleteMember(memberId);
        });
    });
    
    // Add event listener to the "Add Member" button
    document.getElementById('addMemberBtn')?.addEventListener('click', resetMemberForm);
});
</script>

<?php
// 檢查是否為管理員
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$currentUserId = $_SESSION['user_id'];

// 處理新增/編輯會員
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 檢查是否為管理員
    if (!$isAdmin) {
        echo '<div class="alert alert-danger" role="alert">您沒有權限執行此操作！</div>';
        exit;
    }
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            // 檢查學號是否已存在
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM members WHERE student_id = ?");
            $check_stmt->execute([$_POST['student_id']]);
            if ($check_stmt->fetchColumn() > 0) {
                echo '<div class="alert alert-danger" role="alert">此學號已經存在！</div>';
            } else {
                // 新增會員基本資料
                $stmt = $conn->prepare("INSERT INTO members (student_id, name, department, class, email, phone, entry_date, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['student_id'],
                    $_POST['name'],
                    $_POST['department'],
                    $_POST['class'],
                    $_POST['email'] ?? null,
                    $_POST['phone'] ?? null,
                    $_POST['entry_date'],
                    md5($_POST['password']), // 使用 MD5 加密
                    'member' // 預設角色
                ]);
                
                // 如果有選擇職位，新增職位記錄
                if (!empty($_POST['position_id'])) {
                    $member_id = $conn->lastInsertId();
                    $stmt = $conn->prepare("INSERT INTO member_positions (member_id, position_id, start_date) VALUES (?, ?, CURDATE())");
                    $stmt->execute([$member_id, $_POST['position_id']]);
                }
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

// 獲取會員列表，包含活動參與次數和職位
$query = "
    SELECT 
        m.*,
        COUNT(DISTINCT ap.activity_id) as activity_count,
        GROUP_CONCAT(
            DISTINCT 
            CONCAT(p.name, ' (', DATE_FORMAT(mp.created_at, '%Y-%m-%d'), ')')
            ORDER BY mp.created_at DESC
            SEPARATOR '||'
        ) as positions,
        GROUP_CONCAT(
            DISTINCT mp.position_id
            ORDER BY mp.created_at DESC
        ) as position_ids
    FROM 
        members m
        LEFT JOIN activity_participants ap ON m.id = ap.member_id
        LEFT JOIN member_positions mp ON m.id = mp.member_id
        LEFT JOIN positions p ON mp.position_id = p.id
    GROUP BY 
        m.id
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
        <?php if ($isAdmin): ?>
        <button type="button" class="btn btn-primary btn-add-member" id="addMemberBtn">
            <i class="fas fa-plus me-2"></i>新增會員
        </button>
        <?php endif; ?>
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
                            <th>個人資料</th>
                            <?php if ($isAdmin): ?>
                            <th>操作</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="<?php echo $isAdmin ? '7' : '6'; ?>" class="text-center py-4 text-muted">
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
                                    $positions = explode('||', $member['positions']);
                                    foreach ($positions as $position) {
                                        // 從職位字串中提取名稱和日期
                                        if (preg_match('/(.+?) \((\d{4}-\d{2}-\d{2})\)/', $position, $matches)) {
                                            $posName = $matches[1];
                                            $posDate = $matches[2];
                                            echo '<div class="badge bg-secondary mb-1">';
                                            echo htmlspecialchars($posName);
                                            echo ' <small class="text-white-50">(' . htmlspecialchars($posDate) . ')</small>';
                                            echo '</div><br>';
                                        }
                                    }
                                } else {
                                    echo '<span class="text-muted">無</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="member_activities.php?id=<?php echo $member['id']; ?>" 
                                   class="btn btn-primary btn-sm position-relative d-inline-flex align-items-center"
                                   style="transition: all 0.2s ease-in-out;"
                                   onmouseover="this.style.transform='translateY(-1px)'"
                                   onmouseout="this.style.transform='translateY(0)'">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    活動記錄
                                    <?php if($member['activity_count'] > 0): ?>
                                    <span class="badge bg-white text-primary rounded-pill ms-2">
                                        <?php echo $member['activity_count']; ?>
                                    </span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <?php if ($isAdmin): ?>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm me-1 btn-edit-member" 
                                        data-member='<?php echo htmlspecialchars(json_encode([
                                            "id" => $member["id"],
                                            "student_id" => $member["student_id"],
                                            "name" => $member["name"],
                                            "department" => $member["department"],
                                            "class" => $member["class"],
                                            "email" => $member["email"],
                                            "phone" => $member["phone"],
                                            "entry_date" => $member["entry_date"],
                                            "role" => $member["role"],
                                            "position_ids" => isset($member["position_ids"]) ? $member["position_ids"] : ""
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT)); ?>'>
                                    <i class="fas fa-edit me-1"></i>編輯
                                </button>
                                <?php if ($member['id'] != $_SESSION['user_id']): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-member" 
                                        data-member-id="<?php echo $member['id']; ?>">
                                    <i class="fas fa-trash me-1"></i>刪除
                                </button>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
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

<!-- 編輯會員Modal -->
<?php include __DIR__ . '/edit_member_modal.php'; ?>

<?php require_once '../../includes/footer.php'; ?>
