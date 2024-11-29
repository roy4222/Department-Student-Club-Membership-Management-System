<!-- 新增/編輯會員的模態框 -->
<div class="modal fade" id="memberFormModal" tabindex="-1" aria-labelledby="memberFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberFormModalLabel">新增會員</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="memberForm" method="post">
                    <input type="hidden" name="id" id="memberId">
                    
                    <div class="mb-3">
                        <label for="studentId" class="form-label">學號</label>
                        <input type="text" class="form-control" id="studentId" name="student_id" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">姓名</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="department" class="form-label">科系</label>
                        <input type="text" class="form-control" id="department" name="department" required>
                    </div>

                    <div class="mb-3">
                        <label for="class" class="form-label">班級</label>
                        <input type="text" class="form-control" id="class" name="class" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">電子郵件</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">電話</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>

                    <div class="mb-3">
                        <label for="entryDate" class="form-label">入社日期</label>
                        <input type="date" class="form-control" id="entryDate" name="entry_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">職位</label>
                        <div id="positionsContainer">
                            <?php foreach ($positions as $position): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="positions[]" 
                                       value="<?php echo $position['id']; ?>" 
                                       id="position<?php echo $position['id']; ?>">
                                <label class="form-check-label" for="position<?php echo $position['id']; ?>">
                                    <?php echo htmlspecialchars($position['name']); ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="saveMember()">儲存</button>
            </div>
        </div>
    </div>
</div>

<script>
// 清空表單
function resetMemberForm() {
    console.log('Resetting form'); // 添加調試信息
    document.getElementById('memberForm').reset();
    document.getElementById('memberId').value = '';
    document.getElementById('memberFormModalLabel').textContent = '新增會員';
    // 重置所有職位選項
    const checkboxes = document.querySelectorAll('input[name="positions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

// 載入會員資料到表單
function loadMemberData(member) {
    console.log('Loading member data:', member); // 添加調試信息
    try {
        document.getElementById('memberFormModalLabel').textContent = '編輯會員';
        document.getElementById('memberId').value = member.id;
        document.getElementById('studentId').value = member.student_id;
        document.getElementById('name').value = member.name;
        document.getElementById('department').value = member.department;
        document.getElementById('class').value = member.class;
        document.getElementById('email').value = member.email || '';
        document.getElementById('phone').value = member.phone || '';
        document.getElementById('entryDate').value = member.entry_date;

        // 設置職位
        const positions = member.positions ? member.positions.split(',') : [];
        console.log('Positions:', positions); // 添加調試信息
        const checkboxes = document.querySelectorAll('input[name="positions[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = positions.includes(checkbox.value);
        });
    } catch (error) {
        console.error('Error in loadMemberData:', error); // 添加錯誤處理
        throw error;
    }
}

// 儲存會員資料
function saveMember() {
    const form = document.getElementById('memberForm');
    const formData = new FormData(form);
    const id = formData.get('id');
    const url = id ? 'api/update_member.php' : 'api/add_member.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 關閉模態框並重新載入頁面
            const modal = bootstrap.Modal.getInstance(document.getElementById('memberFormModal'));
            modal.hide();
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

// 當模態框關閉時重置表單
document.getElementById('memberFormModal').addEventListener('hidden.bs.modal', function () {
    resetMemberForm();
});
</script>
