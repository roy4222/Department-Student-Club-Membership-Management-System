<?php
require_once '../config/database.php';

// 設置header為CSV下載
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="members_fee_status_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// 創建輸出流
$output = fopen('php://output', 'w');

// 設置CSV的UTF-8 BOM，解決中文亂碼問題
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 寫入CSV標題
fputcsv($output, array('學號', '姓名', '繳費狀態', '繳費日期'));

// 查詢所有會員的繳費狀態
$stmt = $conn->prepare("
    SELECT 
        m.student_id,
        m.name,
        CASE 
            WHEN fp.status = 'paid' THEN '已繳費'
            ELSE '未繳費'
        END as payment_status,
        fp.payment_date
    FROM members m
    LEFT JOIN fee_payments fp ON m.id = fp.member_id AND fp.semester = '112-2'
    ORDER BY m.student_id
");
$stmt->execute();

// 寫入數據
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $payment_date = $row['payment_date'] ? date('Y-m-d', strtotime($row['payment_date'])) : '';
    fputcsv($output, array(
        $row['student_id'],
        $row['name'],
        $row['payment_status'],
        $payment_date
    ));
}

// 關閉輸出流
fclose($output);
exit();
