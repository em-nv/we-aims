<?php
include 'cus_db.php';  // Ensure this is the correct path to your database connection script

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['productId'])) {
    $productId = $_POST['productId'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Retrieve the deleted product's data
        $stmt = $conn->prepare("SELECT * FROM deleted_products WHERE productId = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("No record found to restore.");
        }
        $productData = $result->fetch_assoc();
        $stmt->close();

        // Restore the product in the main products table including totalCostPrice and totalRetailPrice
        $restoreSql = "INSERT INTO products (productId, Sup_Id, companyName, productName, costPrice, retailPrice, quantity, totalCostPrice, totalRetailPrice) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($restoreSql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iisssdddd", $productData['productId'], $productData['Sup_Id'], $productData['companyName'], $productData['productName'], $productData['costPrice'], $productData['retailPrice'], $productData['quantity'], $productData['totalCostPrice'], $productData['totalRetailPrice']);
        if (!$stmt->execute()) {
            throw new Exception("Error restoring product: " . $stmt->error);
        }
        $stmt->close();

        // Remove the record from deleted_products
        $deleteSql = "DELETE FROM deleted_products WHERE productId = ?";
        $stmt = $conn->prepare($deleteSql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $productId);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting record from deleted products: " . $stmt->error);
        }
        $stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Product restored successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
