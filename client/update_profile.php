<?php
session_start();
include '../db_conn.php';

if (!isset($_SESSION['client_id'])) {
    echo "Unauthorized access.";
    exit();
}

$client_id = $_SESSION['client_id'];
$role = $_POST['role'];

if ($role == 3) {
    // Update Retailer
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $bdate = $_POST['bdate'];
    $address = $_POST['address'];
    
    $stmt = $conn->prepare("UPDATE retailers_info SET full_name = ?, phone = ?, gender = ?, bdate = ?, address = ? WHERE client_id = ?");
    $stmt->bind_param("ssssss", $full_name, $phone, $gender, $bdate, $address, $client_id);
    
    if ($stmt->execute()) {
        echo "Profile updated successfully!";
    } else {
        echo "Failed to update profile.";
    }
    
    $stmt->close();
    
} elseif ($role == 4) {
    // Update Manufacturer
    $company_name = $_POST['company_name'];
    $dealer_name = $_POST['dealer_name'];
    $contact_number = $_POST['contact_number'];
    $company_website = $_POST['company_website'];
    $company_address = $_POST['company_address'];
    
    $stmt = $conn->prepare("UPDATE manufacturers_info SET company_name = ?, dealer_name = ?, contact_number = ?, company_website = ?, company_address = ? WHERE client_id = ?");
    $stmt->bind_param("ssssss", $company_name, $dealer_name, $contact_number, $company_website, $company_address, $client_id);
    
    if ($stmt->execute()) {
        echo "Profile updated successfully!";
    } else {
        echo "Failed to update profile.";
    }
    
    $stmt->close();
    
} else {
    echo "Invalid role.";
}

$conn->close();
?>