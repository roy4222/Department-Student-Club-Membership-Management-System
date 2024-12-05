<?php
require_once '../../config/database.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start();

    // 檢查是否為管理員
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }

    $semester = $_POST['semester'] ?? 'all';
    
    // 準備查詢 - 獲取所有會員
    $membersQuery = "SELECT id, student_id, name FROM members ORDER BY student_id";
    $membersStmt = $conn->query($membersQuery);
    $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 獲取已繳費記錄
    $paymentsQuery = "SELECT member_id, amount, payment_date 
                     FROM fee_payments 
                     WHERE status = 'paid'";
    if ($semester !== 'all') {
        $paymentsQuery .= " AND semester = :semester";
    }
    
    $paymentsStmt = $conn->prepare($paymentsQuery);
    if ($semester !== 'all') {
        $paymentsStmt->bindParam(':semester', $semester);
    }
    $paymentsStmt->execute();
    $payments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 建立繳費查找表
    $paidMembers = [];
    foreach ($payments as $payment) {
        $paidMembers[$payment['member_id']] = $payment;
    }
    
    // 分類已繳費和未繳費會員
    $paid = [];
    $unpaid = [];
    foreach ($members as $member) {
        if (isset($paidMembers[$member['id']])) {
            $paid[] = [
                'student_id' => $member['student_id'],
                'name' => $member['name'],
                'payment_date' => $paidMembers[$member['id']]['payment_date'],
                'amount' => $paidMembers[$member['id']]['amount']
            ];
        } else {
            $unpaid[] = [
                'student_id' => $member['student_id'],
                'name' => $member['name']
            ];
        }
    }

    $format = $_POST['format'] ?? 'csv';
    if ($format === 'csv') {
        // CSV 匯出
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="會費繳納統計表_' . date('Ymd') . '.csv"');
        
        // 開啟輸出緩衝區
        $output = fopen('php://output', 'w');
        
        // 寫入 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // 寫入標題
        fputcsv($output, ['會費繳納統計表']);
        fputcsv($output, ['統計日期：' . date('Y-m-d')]);
        fputcsv($output, ['學期：' . ($semester == 'all' ? '全部' : $semester)]);
        fputcsv($output, []);
        
        // 寫入統計資訊
        fputcsv($output, ['已繳費人數：' . count($paid) . ' 人']);
        fputcsv($output, ['未繳費人數：' . count($unpaid) . ' 人']);
        fputcsv($output, []);
        
        // 寫入已繳費名單
        fputcsv($output, ['已繳費名單']);
        fputcsv($output, ['學號', '姓名', '繳費日期', '金額']);
        foreach ($paid as $row) {
            fputcsv($output, [
                $row['student_id'],
                $row['name'],
                $row['payment_date'],
                $row['amount']
            ]);
        }
        fputcsv($output, []);
        
        // 寫入未繳費名單
        fputcsv($output, ['未繳費名單']);
        fputcsv($output, ['學號', '姓名']);
        foreach ($unpaid as $row) {
            fputcsv($output, [
                $row['student_id'],
                $row['name']
            ]);
        }
        
        fclose($output);
        exit();
        
    } else {
        // Excel 匯出
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 設定標題
        $sheet->setCellValue('A1', '會費繳納統計表');
        $sheet->setCellValue('A2', '統計日期：' . date('Y-m-d'));
        $sheet->setCellValue('A3', '學期：' . ($semester == 'all' ? '全部' : $semester));
        
        // 設定統計資訊
        $sheet->setCellValue('A5', '已繳費人數：' . count($paid) . ' 人');
        $sheet->setCellValue('A6', '未繳費人數：' . count($unpaid) . ' 人');
        
        // 設定已繳費名單
        $sheet->setCellValue('A8', '已繳費名單');
        $sheet->setCellValue('A9', '學號');
        $sheet->setCellValue('B9', '姓名');
        $sheet->setCellValue('C9', '繳費日期');
        $sheet->setCellValue('D9', '金額');
        
        $row = 10;
        foreach ($paid as $member) {
            $sheet->setCellValue('A'.$row, $member['student_id']);
            $sheet->setCellValue('B'.$row, $member['name']);
            $sheet->setCellValue('C'.$row, $member['payment_date']);
            $sheet->setCellValue('D'.$row, $member['amount']);
            $row++;
        }
        
        // 設定未繳費名單
        $row += 2;
        $sheet->setCellValue('A'.$row, '未繳費名單');
        $row++;
        $sheet->setCellValue('A'.$row, '學號');
        $sheet->setCellValue('B'.$row, '姓名');
        $row++;
        
        foreach ($unpaid as $member) {
            $sheet->setCellValue('A'.$row, $member['student_id']);
            $sheet->setCellValue('B'.$row, $member['name']);
            $row++;
        }
        
        // 設定樣式
        $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A8:D8')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A9:D9')->getFont()->setBold(true);
        
        // 設定欄寬
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        
        // 輸出 Excel 檔案
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="會費繳納統計表_' . date('Ymd') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
} else {
    // 如果不是 POST 請求，顯示匯出表單
    require_once '../../includes/header.php';
    
    // 檢查是否為管理員
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
?>

<div class="container mt-4">
    <!-- 頁面標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-file-export text-primary me-2"></i>
            <span>匯出會費統計表</span>
        </h2>
    </div>

    <!-- 匯出選項卡片 -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="export_report.php" method="post" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="semester" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>學期
                            </label>
                            <select name="semester" id="semester" class="form-select form-select-lg">
                                <option value="all">全部</option>
                                <option value="112-2">112-2</option>
                                <option value="112-1">112-1</option>
                                <option value="111-2">111-2</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="format" class="form-label fw-bold">
                                <i class="fas fa-file-alt text-primary me-2"></i>匯出格式
                            </label>
                            <select name="format" id="format" class="form-select form-select-lg">
                                <option value="csv">CSV 格式</option>
                                <option value="excel">Excel 格式</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-download me-2"></i>匯出統計表
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 匯出說明 -->
            <div class="card mt-4 shadow-sm border-info">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-info me-2"></i>匯出說明
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            CSV 格式適合用於資料分析和匯入其他系統
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Excel 格式包含完整的格式設定，適合列印和查看
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
    require_once '../../includes/footer.php';
}
?>