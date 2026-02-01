<?php
require_once 'partials/auth.php';
include '../../db_conn.php';

// Get application_id from POST
$application_id = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;

if ($application_id === 0) {
    die("Invalid Application ID");
}

// Use LEFT JOIN so we still get data even if manufacturer info is missing
$sql = "
    SELECT 
        spdf.*,
        app.client_id,
        app.ref_id,
        app.permit_for,
        app.apply_date,
        app.approval_date,
        app.status,
        app.rejection_date,
        app.reason_of_rejection,
        mf.company_name,
        mf.dealer_name,
        mf.contact_number,
        mf.company_website,
        mf.company_address,
        mf.manufacturer_license_no,
        mf.manufacturer_serial_no,
        mf.manufacturer_expiry_date,
        mf.dealer_license_no,
        mf.dealer_serial_no,
        mf.dealer_expiry_date
    FROM applications app
    LEFT JOIN special_permit_display_fireworks spdf ON spdf.application_id = app.application_id
    LEFT JOIN manufacturers_info mf ON app.client_id = mf.client_id
    WHERE app.application_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error_message = "Application not found for Application ID: " . htmlspecialchars($application_id);
} else {
    $data = $result->fetch_assoc();
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <?php include('partials/head.php')?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            padding: 40px;
            text-align: center;
        }
        
        .status-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .status-icon.error {
            color: #e74c3c;
        }
        
        .status-icon.not-found {
            color: #f39c12;
        }
        
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .ref-id {
            color: #666;
            font-size: 18px;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        .message {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #e74c3c;
            border-right: 4px solid #e74c3c;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: left;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
            text-align: right;
        }
        
        .btn-home {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #D52941 0%, #D52941 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color:white;
        }
        
        .footer {
            margin-top: 25px;
            color: #999;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="status-icon not-found">⚠️</div>
            <h1>Application Not Found</h1>
            <div class="message">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="status-icon error">❌</div>
            <h1>This permit is rejected by the inspector</h1>
            <div class="ref-id"><?php echo htmlspecialchars($data['ref_id'] ?? 'N/A'); ?></div>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value" style="color: #e74c3c; font-weight: 600;">
                        <?php echo htmlspecialchars($data['status'] ?? 'N/A'); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Application Type:</span>
                    <span class="info-value"><?php echo htmlspecialchars($data['permit_for'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Apply Date:</span>
                    <span class="info-value"><?php echo htmlspecialchars($data['apply_date'] ?? 'N/A'); ?></span>
                </div>
                <?php if (!empty($data['rejection_date'])): ?>
                <div class="info-row">
                    <span class="info-label">Rejection Date:</span>
                    <span class="info-value"><?php echo htmlspecialchars($data['rejection_date']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($data['reason_of_rejection']) && $data['reason_of_rejection'] != '0'): ?>
            <div class="message">
                <strong>Reason for Rejection:</strong><br>
                <?php echo nl2br(htmlspecialchars($data['reason_of_rejection'])); ?>
            </div>
            <?php else: ?>
            <div class="message">
                No fireworks display permit found or application has not been processed yet for Application ID: <?php echo htmlspecialchars($application_id); ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <a href="index.php" class="btn-home">Home</a>
        
        <div class="footer">
            &copy; <?php echo date('Y'); ?> POPS. All rights reserved.
        </div>
    </div>
</body>
</html>