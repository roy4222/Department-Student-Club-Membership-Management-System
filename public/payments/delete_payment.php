 <?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id'])) {
        throw new Exception('Invalid payment ID');
    }

    $stmt = $conn->prepare("DELETE FROM fee_payments WHERE id = ?");
    $result = $stmt->execute([$_POST['payment_id']]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => '繳費記錄已成功刪除']);
    } else {
        throw new Exception('Failed to delete payment');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}