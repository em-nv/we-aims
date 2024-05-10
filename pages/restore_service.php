<?php
include 'cus_db.php'; // Include your database connection file

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['serviceId'])) {
    $serviceId = $_POST['serviceId'];

    // Start transaction
    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    try {
        // Retrieve the deleted service data
        $selectSql = "SELECT * FROM deleted_services WHERE serviceId = ?";
        $selectStmt = $conn->prepare($selectSql);
        if (false === $selectStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $selectStmt->bind_param("i", $serviceId);
        $selectStmt->execute();
        $deletedService = $selectStmt->get_result()->fetch_assoc();
        $selectStmt->close();

        if (!$deletedService) {
            throw new Exception("Service not found in deleted services.");
        }

        // Insert the data back into the services table
        $insertSql = "INSERT INTO services (serviceId, serviceName, employee_id, employee_name, employee_role, timeRequired, servicePrice) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        if (false === $insertStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $insertStmt->bind_param("isisssd", $deletedService['serviceId'], $deletedService['serviceName'], $deletedService['employee_id'], $deletedService['employee_name'], $deletedService['employee_role'], $deletedService['timeRequired'], $deletedService['servicePrice']);
        $insertStmt->execute();
        $insertStmt->close();

        // Optionally, delete the record from the deleted_services table
        $deleteSql = "DELETE FROM deleted_services WHERE serviceId = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (false === $deleteStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $deleteStmt->bind_param("i", $serviceId);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Commit the transaction
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Service restored successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
