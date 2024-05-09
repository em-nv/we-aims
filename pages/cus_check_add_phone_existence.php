<?php
include 'cus_db.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['customerPhone'];
    
    // Check if phone number already exists in customers table
    $check_query = "SELECT COUNT(*) AS count FROM customers WHERE phone = ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phone_count = $row['count'];
        if ($phone_count > 0) {
            echo 'exists';
            exit; // Exit after finding the phone number
        }
    }

    // Check if phone number already exists in suppliers table
    $check_query = "SELECT COUNT(*) AS count FROM suppliers WHERE phoneNumber = ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phone_count = $row['count'];
        if ($phone_count > 0) {
            echo 'exists';
            exit; // Exit after finding the phone number
        }
    }

    // Check if phone number already exists in employees table
    $check_query = "SELECT COUNT(*) AS count FROM employees WHERE phone_no = ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phone_count = $row['count'];
        if ($phone_count > 0) {
            echo 'exists';
            exit; // Exit after finding the phone number
        }
    }

    // If the phone number doesn't exist in any table, it's unique
    echo 'not_exists';
}
?>
