<?php
include 'cus_db.php';  // Ensure the database connection file is correctly included

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $supplier_id = $_POST['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Retrieve the deleted supplier
        $stmt = $conn->prepare("SELECT * FROM deleted_suppliers WHERE supplier_id = ?");
        $stmt->bind_param("i", $supplier_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) throw new Exception("No record found.");
        $supplierData = $result->fetch_assoc();
        $stmt->close();

        // Restore the supplier in the main suppliers table
        $stmt = $conn->prepare("INSERT INTO suppliers (Sup_Id, companyName, province, city, zipCode, phoneNumber) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $supplier_id, $supplierData['companyName'], $supplierData['province'], $supplierData['city'], $supplierData['zipCode'], $supplierData['phoneNumber']);
        if (!$stmt->execute()) throw new Exception("Error restoring supplier: " . $stmt->error);
        $stmt->close();

        // Remove the record from deleted_suppliers
        $stmt = $conn->prepare("DELETE FROM deleted_suppliers WHERE supplier_id = ?");
        $stmt->bind_param("i", $supplier_id);
        if (!$stmt->execute()) throw new Exception("Error removing from deleted suppliers: " . $stmt->error);
        $stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Supplier restored successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
