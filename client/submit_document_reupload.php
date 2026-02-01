<?php
session_start();
include '../db_conn.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    if (!isset($_SESSION['client_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    if (!isset($_POST['log_id']) || !isset($_POST['application_id'])) {
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }

    $log_id = intval($_POST['log_id']);
    $application_id = intval($_POST['application_id']);
    $client_id = $_SESSION['client_id'];
    $current_time = $_POST['current_time'] ?? date('l, F j, Y \a\t h:i:s A');

    // Verify ownership
    $stmt = $conn->prepare("SELECT application_id FROM applications WHERE application_id = ? AND client_id = ?");
    $stmt->bind_param("is", $application_id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }
    $stmt->close();

    // Upload directory
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $uploaded_files = [];
    $errors = [];

    // Process each file field
    foreach ($_FILES as $field_name => $file_data) {
        // Convert underscore to space for matching with documents table
        $normalized_field_name = str_replace('_', ' ', $field_name);
        
        // Check if multiple files
        if (is_array($file_data['name'])) {
            $file_count = count($file_data['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($file_data['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $file_data['tmp_name'][$i];
                    $original_name = basename($file_data['name'][$i]);
                    $file_size = $file_data['size'][$i];
                    
                    // Generate unique filename
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $ext;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($tmp_name, $destination)) {
                        // Insert into document_reuploads table
                        $stmt = $conn->prepare("
                            INSERT INTO document_reuploads 
                            (log_id, application_id, field_name, file_name, file_extension, file_size, uploaded_at, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
                        ");
                        $stmt->bind_param(
                            "iisssss",
                            $log_id,
                            $application_id,
                            $field_name,
                            $new_filename,
                            $ext,
                            $file_size,
                            $current_time
                        );
                        
                        if ($stmt->execute()) {
                            $uploaded_files[] = [
                                'field_name' => $field_name,
                                'file_name' => $new_filename
                            ];
                        } else {
                            $errors[] = "Database error for file: $original_name";
                        }
                        $stmt->close();
                        
                        // UPDATE the documents table with the new file
                        // Use normalized field name (with spaces) for matching
                        $stmt = $conn->prepare("
                            UPDATE documents 
                            SET file_name = ?, 
                                file_extension = ?, 
                                file_size = ? 
                            WHERE application_id = ? 
                            AND field_name = ?
                        ");
                        $stmt->bind_param(
                            "sssis",
                            $new_filename,
                            $ext,
                            $file_size,
                            $application_id,
                            $normalized_field_name
                        );
                        
                        $stmt->execute();
                        $affected_rows = $stmt->affected_rows;
                        $stmt->close();
                        
                        // If no rows were affected, log it for debugging
                        if ($affected_rows === 0) {
                            error_log("UPDATE documents: No rows affected for application_id=$application_id, field_name=$normalized_field_name");
                        }
                        
                    } else {
                        $errors[] = "Failed to move file: $original_name";
                    }
                }
            }
        }
    }

    // Update review_logs
    $stmt = $conn->prepare("UPDATE review_logs SET isdone = 'yes' WHERE log_id = ?");
    $stmt->bind_param("i", $log_id);
    $stmt->execute();
    $stmt->close();

    // Update applications
    $stmt = $conn->prepare("UPDATE applications SET status = 'Replied' WHERE application_id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->close();

    // Close connection ONCE
    $conn->close();

    if (count($errors) > 0) {
        echo json_encode([
            'success' => false,
            'errors' => $errors,
            'uploaded' => $uploaded_files
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Documents re-uploaded successfully',
            'uploaded' => $uploaded_files
        ]);
    }

} catch (Exception $e) {
    error_log("Exception in submit_document_reupload.php: " . $e->getMessage());
    echo json_encode(['error' => 'Upload error: ' . $e->getMessage()]);
}
?>