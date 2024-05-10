<?php
include 'cus_db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transactionServiceId'])) {

    $transactionServiceId = intval($_POST['transactionServiceId']);

    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);  

    try {
        $selectSql = "SELECT * FROM deleted_transactionsser WHERE transactionServiceId = ?";
        $selectStmt = $conn->prepare($selectSql);
        if (!$selectStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $selectStmt->bind_param("i", $transactionServiceId);
        $selectStmt->execute();
        $deletedTransactionData = $selectStmt->get_result()->fetch_assoc();
        $selectStmt->close();

        if (!$deletedTransactionData) {
            throw new Exception("Deleted transaction service not found.");
        }

        $insertSql = "INSERT INTO transactionsser (transactionServiceId, serviceId, customerId, employeeId, date, serviceName, servicePrice, customerName, paymentMethod, employeeName, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        if (!$insertStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $insertStmt->bind_param("iiiisssssss",
            $deletedTransactionData['transactionServiceId'],
            $deletedTransactionData['serviceId'],
            $deletedTransactionData['customerId'],
            $deletedTransactionData['employeeId'],
            $deletedTransactionData['date'],
            $deletedTransactionData['serviceName'],
            $deletedTransactionData['servicePrice'],
            $deletedTransactionData['customerName'],
            $deletedTransactionData['paymentMethod'],
            $deletedTransactionData['employeeName'],
            $deletedTransactionData['role']
        );
        $insertStmt->execute();
        $insertStmt->close();

        $deleteSql = "DELETE FROM deleted_transactionsser WHERE transactionServiceId = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (!$deleteStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $deleteStmt->bind_param("i", $transactionServiceId);
        $deleteStmt->execute();
        $deleteStmt->close();
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Transaction service restored successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error restoring transaction service: ' . $e->getMessage()]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
