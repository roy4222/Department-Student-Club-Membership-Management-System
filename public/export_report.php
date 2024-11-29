<?php
require_once '../config/database.php';
require '../vendor/autoload.php';

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

    $semester = $_POST['semester'] ?? '';
    
    // 準備查詢
    $sql = "SELECT m.student_id, m.name, fp.semester, fp.amount, fp.payment_date, fp.status 
            FROM members m 
            LEFT JOIN fee_payments fp ON m.id = fp.member_id 
            WHERE 1=1";
    
    if ($semester) {
        $sql .= " AND fp.semester = :semester";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($semester) {
        $stmt->bindParam(':semester', $semester);
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 整理資料：分成已繳費和未繳費
    $paid = [];
    $unpaid = [];
    foreach ($results as $row) {
        if ($row['status'] == 'paid') {
            $paid[] = $row;
        } else {
            $unpaid[] = $row;
        }
    }

    $format = $_POST['format'] ?? 'csv';
    if ($format === 'csv') {
        // CSV 匯出
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="會費繳納統計表_' . date('Ymd') . '.csv"');
        
        // 開啟輸出緩衝區
        $output = fopen('php://temp', 'w+');
        
        // 寫入 UTF-8 BOM
        fwrite($output, "\xEF\xBB\xBF");
        
        // 寫入標題
        fputcsv($output, ['會費繳納統計表']);
        fputcsv($output, ['統計日期：' . date('Y-m-d')]);
        fputcsv($output, ['學期：' . ($semester ?: '全部')]);
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
        
        // 將指針移到開頭
        rewind($output);
        
        // 輸出並關閉
        echo stream_get_contents($output);
        fclose($output);
        exit();
    } else {
        // Excel 匯出
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 設定工作表標題
        $sheet->setTitle('會費繳納統計表');
        
        // 設定標題
        $sheet->setCellValue('A1', '會費繳納統計表');
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A2', '統計日期：' . date('Y-m-d'));
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A3', '學期：' . ($semester ?: '全部'));
        $sheet->mergeCells('A3:D3');

        // 設定標題樣式
        $sheet->getStyle('A1:D3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        // 寫入統計資訊
        $sheet->setCellValue('A5', '已繳費人數：' . count($paid) . ' 人');
        $sheet->mergeCells('A5:D5');
        $sheet->setCellValue('A6', '未繳費人數：' . count($unpaid) . ' 人');
        $sheet->mergeCells('A6:D6');

        // 設定統計資訊樣式
        $sheet->getStyle('A5:D6')->applyFromArray([
            'font' => ['bold' => true]
        ]);

        // 寫入已繳費名單
        $row = 8;
        $sheet->setCellValue('A' . $row, '已繳費名單');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '90EE90']  // 淺綠色
            ]
        ]);

        // 已繳費表頭
        $row++;
        $headers = ['學號', '姓名', '繳費日期', '金額'];
        foreach ($headers as $idx => $header) {
            $col = chr(65 + $idx);
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ]);
        }

        // 寫入已繳費資料
        foreach ($paid as $data) {
            $row++;
            $sheet->setCellValue('A' . $row, $data['student_id']);
            $sheet->setCellValue('B' . $row, $data['name']);
            $sheet->setCellValue('C' . $row, $data['payment_date']);
            $sheet->setCellValue('D' . $row, $data['amount']);
        }

        // 設定已繳費區域的框線和對齊
        $lastPaidRow = $row;
        $sheet->getStyle('A9:D' . $lastPaidRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // 寫入未繳費名單
        $row += 2;
        $unpaidStartRow = $row;
        $sheet->setCellValue('A' . $row, '未繳費名單');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFB6C1']  // 淺紅色
            ]
        ]);

        // 未繳費表頭
        $row++;
        $headers = ['學號', '姓名'];
        foreach ($headers as $idx => $header) {
            $col = chr(65 + $idx);
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ]);
        }

        // 寫入未繳費資料
        foreach ($unpaid as $data) {
            $row++;
            $sheet->setCellValue('A' . $row, $data['student_id']);
            $sheet->setCellValue('B' . $row, $data['name']);
        }

        // 設定未繳費區域的框線和對齊
        $sheet->getStyle('A' . $unpaidStartRow . ':B' . $row)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // 調整欄寬
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // 設定檔案標頭
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="會費繳納統計表_' . date('Ymd') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // 輸出 Excel 檔案
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
} else {
    // 如果不是 POST 請求，顯示匯出表單
    require_once '../includes/header.php';

    // 檢查是否為管理員
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
    ?>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-file-export"></i> 匯出會費統計表</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>學期</label>
                                <select name="semester" class="form-control">
                                    <option value="">全部</option>
                                    <option value="112-1">112-1</option>
                                    <option value="112-2">112-2</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>匯出格式</label>
                                <select name="format" class="form-control">
                                    <option value="csv">CSV 格式</option>
                                    <option value="excel">Excel 格式</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-download"></i> 匯出統計表
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    require_once '../includes/footer.php';
}
?>