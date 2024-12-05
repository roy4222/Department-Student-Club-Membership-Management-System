<!-- 新增/編輯會員的模態框 -->
<div class="modal fade" id="memberFormModal" tabindex="-1" aria-labelledby="memberFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberFormModalLabel">新增會員</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="memberForm" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="id" id="memberId">
                    
                    <div class="mb-3">
                        <label for="studentId" class="form-label">學號</label>
                        <input type="text" class="form-control" id="studentId" name="student_id" 
                               required autocomplete="username">
                        <div class="invalid-feedback">請輸入學號</div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">姓名</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               required autocomplete="name">
                        <div class="invalid-feedback">請輸入姓名</div>
                    </div>

                    <div class="mb-3">
                        <label for="department" class="form-label">科系</label>
                        <input type="text" class="form-control" id="department" name="department" 
                               required autocomplete="organization">
                        <div class="invalid-feedback">請輸入科系</div>
                    </div>

                    <div class="mb-3">
                        <label for="class" class="form-label">班級</label>
                        <input type="text" class="form-control" id="class" name="class" 
                               required autocomplete="off">
                        <div class="invalid-feedback">請輸入班級</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">電子郵件</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               autocomplete="email">
                        <div class="invalid-feedback">請輸入有效的電子郵件地址</div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">電話</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               autocomplete="tel">
                        <div class="invalid-feedback">請輸入有效的電話號碼</div>
                    </div>

                    <div class="mb-3">
                        <label for="entryDate" class="form-label">入社日期</label>
                        <input type="date" class="form-control" id="entryDate" name="entry_date" 
                               required autocomplete="off">
                        <div class="invalid-feedback">請選擇入社日期</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block" id="positionsLabel">職位</label>
                        <div id="positionsContainer" role="group" aria-labelledby="positionsLabel">
                            <?php 
                            if (is_array($positions) || is_object($positions)):
                                foreach ($positions as $position): 
                                    if (is_array($position) && isset($position['id'], $position['name'])):
                                        $positionId = "position" . htmlspecialchars($position['id']); 
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="positions[]" 
                                           value="<?php echo htmlspecialchars($position['id']); ?>" 
                                           id="<?php echo $positionId; ?>"
                                           aria-labelledby="positionsLabel">
                                    <label class="form-check-label" for="<?php echo $positionId; ?>">
                                        <?php echo htmlspecialchars($position['name']); ?>
                                    </label>
                                </div>
                            <?php 
                                    endif;
                                endforeach; 
                            endif;
                            ?>
                        </div>
                    </div>

                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">取消</button>
                        <button type="button" class="btn btn-primary" onclick="saveMember()">
                            <i class="fas fa-save me-1"></i>確認儲存
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// 初始化 Modal
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('memberFormModal');
    if (modalElement) {
        window.memberFormModal = new bootstrap.Modal(modalElement);
        
        // 監聽 Modal 隱藏事件
        modalElement.addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('memberForm');
            form.reset();
            form.classList.remove('was-validated');
        });
    }
});

// 關閉 Modal 的函數
function closeModal() {
    if (window.memberFormModal) {
        window.memberFormModal.hide();
    }
}

// 修改儲存會員資料的函數
function saveMember() {
    const form = document.getElementById('memberForm');
    
    // 表單驗證
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const formData = new FormData(form);
    const id = formData.get('id');
    const url = id ? '../../public/api/update_member.php' : '../../public/api/add_member.php';

    // 顯示載入中狀態
    const saveBtn = document.querySelector('.modal-footer .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>儲存中...';
    saveBtn.disabled = true;

    // 添加調試訊息
    console.log('Sending data to:', url);
    console.log('Form data:', Object.fromEntries(formData));

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('儲存成功！');
            closeModal();
            window.location.reload();
        } else {
            throw new Error(data.message || '儲存失敗');
        }
    })
    .catch(error => {
        console.error('Error in saveMember:', error);
        alert(error.message || '發生錯誤，請稍後再試');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}
</script>
