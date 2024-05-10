<?php
include 'cus_db.php';  // Ensure this is the correct path to your database connection script

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transactionId'])) {
    $transactionId = $_POST['transactionId'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Retrieve the deleted transaction's data
        $stmt = $conn->prepare("SELECT * FROM deleted_transactions WHERE transactionId = ?");
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("No record found to restore.");
        }
        $transactionData = $result->fetch_assoc();
        $stmt->close();

        // Restore the transaction in the main transactionspro table
        $restoreSql = "INSERT INTO transactionspro (transactionId, productId, customerId, date, productName, retailPrice, quantity, totalRetailPrice, customerName, paymentMethod) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($restoreSql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iiisssidss", $transactionData['transactionId'], $transactionData['productId'], $transactionData['customerId'], $transactionData['date'], $transactionData['productName'], $transactionData['retailPrice'], $transactionData['quantity'], $transactionData['totalRetailPrice'], $transactionData['customerName'], $transactionData['paymentMethod']);
        if (!$stmt->execute()) {
            throw new Exception("Error restoring transaction: " . $stmt->error);
        }
        $stmt->close();

        // Remove the record from deleted_transactions
        $deleteSql = "DELETE FROM deleted_transactions WHERE transactionId = ?";
        $stmt = $conn->prepare($deleteSql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $transactionId);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting record from deleted transactions: " . $stmt->error);
        }
        $stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Transaction restored successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
