<?php
include '../../../db_conn.php';

// Function to generate control number
function generateControlNumber($prefix = 'EX-AU') {
    $datePart = date('md'); // Example: 0813
    $randomPart = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    return $prefix . '-' . $datePart . '-' . $randomPart;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = $_POST['application_id'];
    $display_datetime = $_POST['display_datetime'];
    $display_location = $_POST['display_location'];
    $display_purpose = $_POST['display_purpose'];
    $pyro_technician = $_POST['pyro_technician'];
    $fdo_license_number = $_POST['fdo_license_number'];
    $partner_police_station = $_POST['partner_police_station'];
    $reference_number = $_POST['reference_number'];
    $amount_paid = $_POST['amount_paid'];
    $pay_date = $_POST['pay_date'];
    
    // Check if record already exists for this application_id
    $check_sql = "SELECT application_id, control_number FROM special_permit_display_fireworks WHERE application_id = ?";
    
    if ($check_stmt = $conn->prepare($check_sql)) {
        $check_stmt->bind_param("s", $application_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            // Record exists, update it
            $check_stmt->bind_result($existing_app_id, $existing_control_number);
            $check_stmt->fetch();
            $check_stmt->close();
            
            // Update existing record (keep existing control number)
            $update_sql = "UPDATE special_permit_display_fireworks 
                          SET display_datetime = ?, 
                              display_purpose = ?, 
                              display_location = ?, 
                              pyro_technician = ?, 
                              fdo_licence_number = ?, 
                              partner_police_station = ?, 
                              receipt_reference_number = ?, 
                              amount_paid = ?, 
                              pay_date = ?
                          WHERE application_id = ?";
            
            if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param(
                    "ssssssssss", 
                    $display_datetime, 
                    $display_purpose, 
                    $display_location,
                    $pyro_technician, 
                    $fdo_license_number, 
                    $partner_police_station, 
                    $reference_number,
                    $amount_paid, 
                    $pay_date,
                    $application_id
                );
                
                if ($update_stmt->execute()) {
                    echo "Record updated successfully!";
                } else {
                    echo "Error updating record: " . $update_stmt->error;
                }
                $update_stmt->close();
            } else {
                echo "Error preparing update statement: " . $conn->error;
            }
            
        } else {
            // Record doesn't exist, proceed with insert
            $check_stmt->close();
            
            // Generate control number
            $control_number = generateControlNumber();
            
            // Insert into special_permit_display_fireworks
            $sql = "INSERT INTO special_permit_display_fireworks 
                    (application_id, display_datetime, display_purpose, display_location, pyro_technician, 
                     fdo_licence_number, control_number, partner_police_station, receipt_reference_number, amount_paid, pay_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param(
                    "sssssssssss", 
                    $application_id, 
                    $display_datetime, 
                    $display_purpose, 
                    $display_location,
                    $pyro_technician, 
                    $fdo_license_number, 
                    $control_number, 
                    $partner_police_station, 
                    $reference_number,
                    $amount_paid, 
                    $pay_date
                );
                
                if ($stmt->execute()) {
                    // Update status in applications table
                    $update_sql = "UPDATE applications SET status = 'Drafting Permit' WHERE application_id = ?";
                    if ($update_stmt = $conn->prepare($update_sql)) {
                        $update_stmt->bind_param("s", $application_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                    echo "Data inserted successfully!";
                } else {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
            }
        }
    } else {
        echo "Error checking for existing record: " . $conn->error;
    }
    
    $conn->close();
}
?>