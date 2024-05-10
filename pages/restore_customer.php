<?php
include 'cus_db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $deletedCustomerId = $_POST['id'];

    // Retrieve deleted customer data
    $stmt = $conn->prepare("SELECT * FROM deleted_customers WHERE id = ?");
    $stmt->bind_param("i", $deletedCustomerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $customerData = $result->fetch_assoc();

    // Restore customer to the main customers table
    $restoreSql = "INSERT INTO customers (id, firstName, lastName, phone, paymentMethod) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($restoreSql);
    $stmt->bind_param("issss", $customerData['customer_id'], $customerData['firstName'], $customerData['lastName'], $customerData['phone'], $customerData['paymentMethod']);
    if ($stmt->execute()) {
        // Remove the record from deleted_customers after restoring
        $deleteSql = "DELETE FROM deleted_customers WHERE id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $deletedCustomerId);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Customer restored successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error restoring customer: ' . $conn->error]);
    }
    $stmt->close();
    $conn->close();
}
?>
