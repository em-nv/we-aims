<?php
include 'cus_db.php';  // Ensure the database connection file is correctly included

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Retrieve the deleted employee's data
        $stmt = $conn->prepare("SELECT * FROM deleted_employees WHERE employee_id = ?");
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) throw new Exception("No record found.");
        $employeeData = $result->fetch_assoc();
        $stmt->close();

        // Restore the employee in the main employees table
        $stmt = $conn->prepare("INSERT INTO employees (employee_id, first_name, last_name, role, salary, phone_no) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $employee_id, $employeeData['first_name'], $employeeData['last_name'], $employeeData['role'], $employeeData['salary'], $employeeData['phone_no']);
        if (!$stmt->execute()) throw new Exception("Error restoring employee: " . $stmt->error);
        $stmt->close();

        // Remove the record from deleted_employees
        $stmt = $conn->prepare("DELETE FROM deleted_employees WHERE employee_id = ?");
        $stmt->bind_param("i", $employee_id);
        if (!$stmt->execute()) throw new Exception("Error removing from deleted employees: " . $stmt->error);
        $stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Employee restored successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
