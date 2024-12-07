<?php
// 會員表單 Modal
?>
<div class="modal fade" id="memberFormModal" tabindex="-1" aria-labelledby="memberFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberFormModalLabel">新增會員</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="memberForm" method="post" autocomplete="on">
                <div class="modal-body">
                    <input type="hidden" id="memberId" name="id" autocomplete="off">
                    <input type="hidden" name="action" id="formAction" value="add" autocomplete="off">
                    
                    <div class="alert alert-danger d-none" id="formError" role="alert"></div>
                    
                    <div class="mb-3">
                        <label for="studentId" class="form-label">學號</label>
                        <input type="text" class="form-control" id="studentId" name="student_id" required autocomplete="username">
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">姓名</label>
                        <input type="text" class="form-control" id="name" name="name" required autocomplete="name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">密碼</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                        <div class="form-text password-hint">新增會員時必須設定密碼，修改時如不修改密碼可留空</div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">角色</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="member">一般會員</option>
                            <option value="admin">管理員</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label">科系</label>
                        <input type="text" class="form-control" id="department" name="department" required autocomplete="organization">
                    </div>
                    
                    <div class="mb-3">
                        <label for="class" class="form-label">班級</label>
                        <input type="text" class="form-control" id="class" name="class" required autocomplete="organization-title">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">電話</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required autocomplete="tel">
                    </div>
                    
                    <div class="mb-3">
                        <label for="entryDate" class="form-label">入會日期</label>
                        <input type="date" class="form-control" id="entryDate" name="entry_date" required autocomplete="bday">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">職位</label>
                        <div class="position-checkboxes">
                            <div id="positionsContainer" data-member-id="<?php echo isset($member) ? $member['id'] : ''; ?>">
                                <?php
                                // 獲取所有職位
                                $stmt = $conn->query("SELECT * FROM positions ORDER BY name");
                                while ($position = $stmt->fetch()) {
                                    echo '<div class="form-check">';
                                    echo '<label class="form-check-label d-flex align-items-center">';
                                    echo '<input type="checkbox" class="form-check-input position-checkbox" ';
                                    echo 'name="positions[]" value="' . $position['id'] . '" ';
                                    echo '>';
                                    echo htmlspecialchars($position['name']);
                                    if ($position['description']) {
                                        echo '<i class="ms-1 bi bi-info-circle text-muted" data-bs-toggle="tooltip" ';
                                        echo 'title="' . htmlspecialchars($position['description']) . '"></i>';
                                    }
                                    echo '</label>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">儲存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 初始化 Modal
    memberFormModal = new bootstrap.Modal(document.getElementById('memberFormModal'));
    
    // 為所有關閉按鈕添加事件監聽器
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', () => {
            memberFormModal.hide();
        });
    });

    // 表單提交處理
    document.getElementById('memberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('member_api.php?action=' + formData.get('action'), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // 顯示錯誤訊息在表單中
                const formError = document.getElementById('formError');
                formError.textContent = data.message;
                formError.classList.remove('d-none');
            } else {
                // 成功時重新載入頁面
                memberFormModal.hide();
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const formError = document.getElementById('formError');
            formError.textContent = '發生錯誤，請稍後再試';
            formError.classList.remove('d-none');
        });
    });
    
    // 新增會員按鈕點擊事件
    document.getElementById('addMemberBtn').addEventListener('click', function() {
        // 重置表單
        document.getElementById('memberForm').reset();
        document.getElementById('memberId').value = '';
        document.getElementById('formAction').value = 'add';
        document.getElementById('memberFormModalLabel').textContent = '新增會員';
        
        // 重置錯誤訊息
        const formError = document.getElementById('formError');
        formError.textContent = '';
        formError.classList.add('d-none');
        
        // 設定密碼欄位為必填
        document.getElementById('password').setAttribute('required', 'required');
        document.querySelector('.password-hint').textContent = '新增會員時必須設定密碼';
        
        // 顯示 Modal
        memberFormModal.show();
    });
});

// 編輯會員
function editMember(memberId) {
    // 發送 AJAX 請求獲取會員資料
    fetch(`member_api.php?action=get_member&id=${memberId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 設置表單為編輯模式
                document.getElementById('formAction').value = 'edit';
                // 移除密碼欄位必填屬性
                document.getElementById('password').removeAttribute('required');
                document.querySelector('.password-hint').textContent = '如不修改密碼可留空';
                // 載入會員資料到表單
                loadMemberData(data.member);
                // 顯示 Modal
                memberFormModal.show();
            } else {
                alert('無法載入會員資料');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('發生錯誤');
        });
}

// 載入會員資料到表單
function loadMemberData(member) {
    document.getElementById('memberId').value = member.id;
    document.getElementById('studentId').value = member.student_id;
    document.getElementById('name').value = member.name;
    document.getElementById('department').value = member.department;
    document.getElementById('class').value = member.class;
    document.getElementById('email').value = member.email || '';
    document.getElementById('phone').value = member.phone || '';
    document.getElementById('entryDate').value = member.entry_date;
    document.getElementById('role').value = member.role || 'member';
    document.getElementById('memberFormModalLabel').textContent = '編輯會員';
    
    // 重置所有職位選項
    document.querySelectorAll('.position-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // 設置會員的職位
    if (member.positions) {
        const positionIds = member.positions.split(',');
        positionIds.forEach(id => {
            const checkbox = document.querySelector(`input[name="positions[]"][value="${id}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
}

const positionCheckboxes = document.querySelectorAll('.position-checkbox');
const positionsContainer = document.getElementById('positionsContainer');

// 為每個checkbox添加change事件監聽器
positionCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const memberId = positionsContainer.dataset.memberId;
        // 如果是新增會員的情況，不需要即時更新職位
        if (document.getElementById('formAction').value === 'add') {
            return; // 直接返回，不執行更新
        }
        
        // 編輯會員時的職位更新邏輯
        if (!memberId) {
            console.error('會員ID不存在');
            checkbox.checked = !checkbox.checked; // 恢復原狀態
            return;
        }

        console.log('Member ID:', memberId);

        // 收集所有選中的職位
        const selectedPositions = Array.from(positionCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        console.log('Selected positions:', selectedPositions);

        // 發送AJAX請求更新職位
        fetch('update_member_positions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'memberId': memberId,
                'positions': JSON.stringify(selectedPositions)
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('網絡響應不正常');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('職位更新成功');
                // 顯示一個小提示
                const toast = document.createElement('div');
                toast.className = 'toast position-fixed bottom-0 end-0 m-3';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                toast.innerHTML = `
                    <div class="toast-header bg-success text-white">
                        <strong class="me-auto">成功</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        職位更新成功
                    </div>
                `;
                document.body.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                
                // 監聽toast隱藏事件，在隱藏後移除元素
                toast.addEventListener('hidden.bs.toast', () => {
                    toast.remove();
                });
            } else {
                console.error('更新失敗:', data.message);
                checkbox.checked = !checkbox.checked; // 恢復原狀態
                alert('更新失敗: ' + data.message);
            }
        })
        .catch(error => {
            console.error('更新出錯:', error);
            checkbox.checked = !checkbox.checked; // 恢復原狀態
            alert('更新出錯: ' + error.message);
        });
    });
});
</script>
