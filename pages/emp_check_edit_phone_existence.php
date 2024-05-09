<?php
include 'cus_db.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phoneNumber = $_POST['editEmployeePhone']; // Corrected variable name
    $id = $_POST['editEmployeeId'];

    // Check if phone number already exists in customers table for other customers
    $check_query = "SELECT COUNT(*) AS count FROM customers WHERE phone = ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("s", $phoneNumber); // Changed "si" to "s" for phone number parameter
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phoneNumber_count = $row['count'];
        if ($phoneNumber_count > 0) {
            echo 'exists';
            exit; // Exit after finding the phone number
        }
    }

    // Check if phone number already exists in suppliers table
    $check_query = "SELECT COUNT(*) AS count FROM suppliers WHERE phoneNumber = ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phoneNumber_count = $row['count'];
        if ($phoneNumber_count > 0) {
            echo 'exists';
            exit; // Exit after finding the phone number
        }
    }

    // Check if phone number already exists in employees table
    $check_query = "SELECT COUNT(*) AS count FROM employees WHERE phone_no = ? AND employee_id != ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("si", $phoneNumber, $id); // Changed to "si" for phone number and integer id
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phoneNumber_count = $row['count'];
        if ($phoneNumber_count > 0) {
            echo 'exists';
            exit; // Exit after finding the phone number
        }
    }

    // If the phone number doesn't exist in any table, it's unique
    echo 'not_exists';
}
?>
