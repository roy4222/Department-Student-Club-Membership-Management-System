<?php
// 編輯會員 Modal
?>
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMemberModalLabel">編輯會員</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMemberForm" method="post">
                    <input type="hidden" id="editMemberId" name="id">
                    <input type="hidden" name="action" value="edit">
                    
                    <div class="alert alert-danger d-none" id="editFormError" role="alert"></div>
                    
                    <div class="mb-3">
                        <label for="editStudentId" class="form-label">學號</label>
                        <input type="text" class="form-control" id="editStudentId" name="student_id" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editName" class="form-label">姓名</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editPassword" class="form-label">密碼</label>
                        <input type="password" class="form-control" id="editPassword" name="password">
                        <div class="form-text">如不修改密碼可留空</div>
                    </div>

                    <div class="mb-3">
                        <label for="editRole" class="form-label">角色</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <option value="member">一般會員</option>
                            <option value="admin">管理員</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDepartment" class="form-label">科系</label>
                        <input type="text" class="form-control" id="editDepartment" name="department" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editClass" class="form-label">班級</label>
                        <input type="text" class="form-control" id="editClass" name="class" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editPhone" class="form-label">電話</label>
                        <input type="tel" class="form-control" id="editPhone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editEntryDate" class="form-label">入會日期</label>
                        <input type="date" class="form-control" id="editEntryDate" name="entry_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">職位</label>
                        <div id="editPositionsContainer">
                            <?php
                            // 獲取所有職位
                            $stmt = $conn->query("SELECT * FROM positions ORDER BY name");
                            while ($position = $stmt->fetch()) {
                                echo '<div class="form-check">';
                                echo '<input type="checkbox" class="form-check-input edit-position-checkbox" ';
                                echo 'name="positions[]" value="' . $position['id'] . '" ';
                                echo 'id="editPosition' . $position['id'] . '">';
                                echo '<label class="form-check-label" for="editPosition' . $position['id'] . '">';
                                echo htmlspecialchars($position['name']);
                                echo '</label>';
                                echo '</div>';
                            }
                            ?>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 初始化編輯 Modal
    const editMemberModal = new bootstrap.Modal(document.getElementById('editMemberModal'));
    
    // 為編輯按鈕添加事件監聽器
    document.querySelectorAll('.btn-edit-member').forEach(button => {
        button.addEventListener('click', function() {
            const memberData = this.getAttribute('data-member');
            try {
                const member = JSON.parse(memberData);
                loadEditMemberData(member);
                editMemberModal.show();
            } catch (error) {
                console.error('Error parsing member data:', error);
                alert('載入會員資料時發生錯誤');
            }
        });
    });

    // 編輯表單提交處理
    document.getElementById('editMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // 收集所有選中的職位
        const selectedPositions = [];
        document.querySelectorAll('.edit-position-checkbox:checked').forEach(checkbox => {
            selectedPositions.push(checkbox.value);
        });
        
        // 將職位資料添加到 formData
        selectedPositions.forEach(positionId => {
            formData.append('positions[]', positionId);
        });
        
        fetch('member_api.php?action=edit', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                const formError = document.getElementById('editFormError');
                formError.textContent = data.message;
                formError.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const formError = document.getElementById('editFormError');
            formError.textContent = '發生錯誤，請稍後再試';
            formError.classList.remove('d-none');
        });
    });
});

// 載入會員資料到編輯表單
function loadEditMemberData(member) {
    document.getElementById('editMemberId').value = member.id;
    document.getElementById('editStudentId').value = member.student_id;
    document.getElementById('editName').value = member.name;
    document.getElementById('editDepartment').value = member.department;
    document.getElementById('editClass').value = member.class;
    document.getElementById('editEmail').value = member.email || '';
    document.getElementById('editPhone').value = member.phone || '';
    document.getElementById('editEntryDate').value = member.entry_date;
    document.getElementById('editRole').value = member.role || 'member';
    
    // 重置所有職位選項
    document.querySelectorAll('.edit-position-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // 設置會員的職位
    if (member.positions) {
        const positionIds = member.positions.split(',');
        positionIds.forEach(id => {
            const checkbox = document.querySelector(`#editPosition${id}`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
}
</script>
