<?php
session_start();
include '../db_conn.php';

// Function to format field names for display
function formatFieldName($field_name) {
    return ucwords(str_replace('_', ' ', $field_name));
}

function generateRefId() {
    // Get the current date in ddmmyyyy format
    $year = date('y');          // 2-digit year
    $monthDay = date('md');     // 4-digit MMDD
    $milliseconds = (int)((microtime(true) - floor(microtime(true))) * 1000); // Get current milliseconds only (0–999)
    
    // Ensure it's always 5 digits (e.g., 00042)
    $millisecondsStr = str_pad((string)$milliseconds, 5, '0', STR_PAD_LEFT);
    
    // Shuffle only the milliseconds
    $digits = str_split($millisecondsStr);
    shuffle($digits);
    $shuffledMilliseconds = implode('', $digits);

    return "MANFWD{$year}{$monthDay}{$shuffledMilliseconds}";
}


// Check if the form was submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize input data
    $client_id = $_SESSION['client_id'];
     $ref_id = generateRefId();    
     $permit_for = 'Special Permit for Fireworks Display';
    $apply_date = mysqli_real_escape_string($conn, $_POST['current_time']);

    $maplatitude = mysqli_real_escape_string($conn, $_POST['maplatitude']);
    $maplongitude = mysqli_real_escape_string($conn, $_POST['maplongitude']);
    $mapaddress = mysqli_real_escape_string($conn, $_POST['mapaddress']);

    
    
    

    // Insert application data into the database
    $sql = "INSERT INTO applications (client_id, ref_id, permit_for, apply_date, maplatitude, maplongitude, mapaddress) 
            VALUES ('$client_id', '$ref_id', '$permit_for', '$apply_date', '$maplatitude', '$maplongitude', '$mapaddress')";

    if ($conn->query($sql) === TRUE) {
        $application_id = $conn->insert_id; // Get the last inserted application_id

        // Directory for file uploads
        $upload_directory = 'uploads/'; // Make sure this folder exists and is writable

        // Define the files array from the form submission
        $files = [
            'fireworks_display_operator' => $_FILES['letter_request'],
            'dealer_license' => $_FILES['contract_copy'],
            'manufacturers_license' => $_FILES['license_copy'],
            'proof_of_payment' => $_FILES['proof_of_payment']
        ];


         foreach ($files as $field_name => $file_array) {
        if (empty($file_array['name'][0])) {
            echo 'Failed';  // If no file is uploaded for any field, return "Failed"
            return; // Halt the script
        }
    }


        // Loop through each file field in the form
        foreach ($files as $field_name => $file_array) {
            $formatted_field_name = formatFieldName($field_name); // Format field name for readability

            if (isset($file_array['name']) && is_array($file_array['name'])) {
                // Loop through each file
                for ($i = 0; $i < count($file_array['name']); $i++) {
                    $file_tmp_name = $file_array['tmp_name'][$i];
                    $file_name = $file_array['name'][$i];
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_file_name = uniqid() . '.' . $file_extension; // Generate a unique filename

                    // Validate file extension
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'jfif', 'gif', 'bmp', 'webp', 'tiff', 'heic', 'pdf', 'xlsx', 'pptx'];
                    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                        echo "Invalid file extension: " . $file_name . "<br>";
                        continue; // Skip invalid files
                    }

                    // Ensure that the file upload is successful
if (move_uploaded_file($file_tmp_name, $upload_directory . $new_file_name)) {
    $file_size = filesize($upload_directory . $new_file_name);

    // Prepare the SQL statement to insert the record for the uploaded file
    $sql = "INSERT INTO documents (application_id, field_name, file_name, file_extension, file_size) 
            VALUES (?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters to the prepared statement
        $stmt->bind_param("issss", $application_id, $formatted_field_name, $new_file_name, $file_extension, $file_size);

        // Execute the query
        if ($stmt->execute()) {
            // Success - Do something if necessary (like logging success)
            
        // Respond back to the client
        require_once 'add_notification.php';
        $message = "Your permit application {$ref_id} has been successfully submitted. We will review it shortly.";
        addNotification($client_id, $message, $ref_id);

        } else {
            // Database insertion failed - Handle accordingly (log error, notify admin, etc.)
        }

        // Close the statement
        $stmt->close();
    } else {
        // Statement preparation failed - Handle accordingly (log error, etc.)
    }
} else {
    // File upload failed - Handle accordingly (log error, etc.)
}

                }
            }
        }

        echo "Success! All files uploaded successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
