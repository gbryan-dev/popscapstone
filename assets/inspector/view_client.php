<?php
require_once 'partials/auth.php';
include '../../db_conn.php';

// Get client_id from POST or SESSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
    $client_id = mysqli_real_escape_string($conn, $_POST['client_id']);
    $_SESSION['view_client_id'] = $client_id;
} elseif (isset($_SESSION['view_client_id'])) {
    $client_id = $_SESSION['view_client_id'];
} else {
    header("Location: clients.php");
    exit();
}

// Fetch client information
$clientSql = "SELECT 
    c.client_id,
    c.email,
    c.created_at,
    r.role_name,
    COALESCE(m.company_name, ret.full_name, 'N/A') as client_name,
    m.dealer_name,
    m.contact_number as m_contact,
    m.company_website,
    m.company_address as m_address,
    ret.phone,
    ret.gender,
    ret.bdate,
    ret.address as ret_address
FROM clients_acc c
LEFT JOIN roles r ON c.role_id = r.role_id
LEFT JOIN manufacturers_info m ON c.client_id = m.client_id
LEFT JOIN retailers_info ret ON c.client_id = ret.client_id
WHERE c.client_id = '$client_id'";

$clientResult = mysqli_query($conn, $clientSql);
$clientData = mysqli_fetch_assoc($clientResult);

if (!$clientData) {
    header("Location: clients.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
 <?php include('partials/head.php')?>
        
       <style>
            .info-section {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            .info-row {
                display: flex;
                padding: 10px 0;
                border-bottom: 1px solid #dee2e6;
            }
            .info-row:last-child {
                border-bottom: none;
            }
            .info-label {
                font-weight: bold;
                width: 200px;
                color: #495057;
            }
            .info-value {
                flex: 1;
                color: #212529;
            }
            .search { width:60%;}

            @media (max-width: 480px) {
                .search { width:80%;}
                .info-row {
                    flex-direction: column;
                }
                .info-label {
                    width: 100%;
                    margin-bottom: 5px;
                }
            }
       </style>
    </head>
    <body>
        
        <canvas id="canvas"></canvas>
        <?php include('partials/sidebar.php')?>
        <?php include('partials/header.php')?>

        <div class="lime-container" >
            <div class="lime-body">
                <div class="container">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <a href="clients" class="btn btn-secondary mb-3" onclick="backtoclients()">
                                <i class="material-icons" style="vertical-align: middle;">arrow_back</i>
                                Back to Clients
                            </a>
                        </div>
                    </div>

                    <script>
                    function backtoclients() {
                        window.location.href='clients'
                    }
                    </script>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title" style="font-size: 23px; margin-bottom: 20px;">
                                        Client Information
                                    </h5>

                                    <div class="info-section">
                                        <h6 style="margin-bottom: 15px; color: #007bff;">Account Details</h6>
                                        <div class="info-row">
                                            <div class="info-label">Client ID:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['client_id']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Email:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['email']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Account Type:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['role_name']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Created At:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['created_at']); ?></div>
                                        </div>
                                    </div>

                                    <?php if ($clientData['role_name'] === 'Manufacturer'): ?>
                                    <div class="info-section">
                                        <h6 style="margin-bottom: 15px; color: #007bff;">Company Information</h6>
                                        <div class="info-row">
                                            <div class="info-label">Company Name:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['client_name']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Dealer Name:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['dealer_name'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Contact Number:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['m_contact'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Website:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['company_website'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Address:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['m_address'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($clientData['role_name'] === 'Retailer'): ?>
                                    <div class="info-section">
                                        <h6 style="margin-bottom: 15px; color: #007bff;">Personal Information</h6>
                                        <div class="info-row">
                                            <div class="info-label">Full Name:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['client_name']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Phone:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['phone'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Gender:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['gender'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Birthdate:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['bdate'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Address:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($clientData['ret_address'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 30px;">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title" style="font-size: 23px; margin-bottom: 20px;">
                                        Applications & Permits
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Reference ID</th>
                                                    <th>Permit For</th>
                                                    <th>Apply Date</th>
                                                    <th>Approval Date</th>
                                                    <th>Valid Until</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $appSql = "SELECT * FROM applications WHERE client_id = '$client_id' ORDER BY apply_date DESC";
                                                $appResult = mysqli_query($conn, $appSql);
                                                
                                                if (mysqli_num_rows($appResult) > 0) {
                                                    while ($app = mysqli_fetch_assoc($appResult)) {
                                                        $status = $app['status'];
                                                        $refId = htmlspecialchars($app['ref_id']);
                                                        $permitFor = htmlspecialchars($app['permit_for']);
                                                        $application_id = $app['application_id'];
                                                        
                                                        // Status badge
                                                        $badge = '';
                                                        switch ($status) {
                                                            case 'Pending':
                                                                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-warning text-white">Pending</span>';
                                                                break;
                                                            case 'Replied':
                                                                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-primary text-white">Replied to Remark</span>';
                                                                break;
                                                            case 'Under Review':
                                                                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-primary text-white">Under Review</span>';
                                                                break;
                                                            case 'Drafting Permit':
                                                                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-primary text-white">Drafting Permit</span>';
                                                                break;
                                                            case 'Permit Issued':
                                                                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-success text-white">Permit Issued</span>';
                                                                break;
                                                            case 'Rejected':
                                                                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-danger text-white">Rejected</span>';
                                                                break;
                                                            case 'Expired':
                                                                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-danger text-white">Expired</span>';
                                                                break;
                                                            default:
                                                                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-primary text-white">' . htmlspecialchars($status) . '</span>';
                                                        }
                                                        
                                                        echo "<tr>
                                                            <td>{$badge}</td>
                                                            <td>{$refId}</td>
                                                            <td>{$permitFor}</td>
                                                            <td>" . htmlspecialchars($app['apply_date']) . "</td>
                                                            <td>" . htmlspecialchars($app['approval_date'] ?? 'N/A') . "</td>
                                                            <td>" . htmlspecialchars($app['valid_until'] ?? 'N/A') . "</td>
                                                            <td style='display:flex'>";
                                                        
                                                        // View button logic
                                                        if ($status == 'Pending' || $status == 'Replied') {
                                                            echo '<form method="POST" action="api/underreview_and_view" style="display: inline-block;">
                                                                    <input type="hidden" name="ref_id" value="' . $refId . '">
                                                                    <input type="hidden" name="client_id" value="' . $client_id . '">
                                                                    <button type="submit" class="btn bg-success btn-primary btn-sm">View</button>
                                                                </form>';
                                                        }
                                                        
                                                        if ($status != 'Pending' && $status != 'Replied' && $status != 'Permit Issued') {
                                                            echo '<form method="POST" action="view_application" style="display: inline-block;">
                                                                    <input type="hidden" name="ref_id" value="' . $refId . '">
                                                                    <button type="submit" class="btn bg-success btn-primary btn-sm">View</button>
                                                                </form>';
                                                        }
                                                        
                                                        if ($status == 'Permit Issued') {
                                                            echo '<form method="POST" action="spdf" target="_blank"   style="display: inline-block;">
                                                                      <input type="hidden" name="application_id" value="' . $application_id . '">
                                                                    <button type="submit" class="btn bg-success btn-primary btn-sm">View</button>
                                                                </form>';
                                                        }
                                                        
                                                        // Delete button
                                                        echo '<button type="button" class="btn btn-danger btn-sm" style="margin-left: 5px;"
                                                            onclick="openDeleteApplicationModal(\'' . $refId . '\', \'' . addslashes($permitFor) . '\', \'' . addslashes($clientData['client_name']) . '\')">
                                                        Delete
                                                      </button>';
                                                        
                                                        echo "</td></tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center'>No applications found for this client.</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('partials/footer.php')?>
        </div>

        <!-- Delete Application Modal -->
<div class="modal fade" id="deleteApplicationModal" tabindex="-1" role="dialog" aria-labelledby="deleteApplicationModalTitle" aria-hidden="true" style="background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="deleteApplicationModalTitle" style="color:white;">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">delete_forever</i>
                    Confirm Deletion
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="material-icons" style="color:white;">close</i>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <p style="font-size: 15px; margin-bottom: 15px;">Are you sure you want to <strong>permanently delete</strong> this application?</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p style="margin: 5px 0;"><strong>Reference ID:</strong> <span id="deleteRefId"></span></p>
                    <p style="margin: 5px 0;"><strong>Permit Type:</strong> <span id="deletePermitFor"></span></p>
                    <p style="margin: 5px 0;"><strong>Client Name:</strong> <span id="deleteClientName"></span></p>
                </div>
                
                <div class="alert alert-danger" style="margin-bottom: 0;">
                    <i class="material-icons" style="vertical-align: middle; font-size: 18px;">warning</i>
                    <strong>Warning:</strong> This action will permanently delete:
                    <ul style="margin: 10px 0 0 20px; padding-left: 0;">
                        <li>Application record</li>
                        <li>All uploaded documents</li>
                        <li>All reuploaded documents</li>
                        <li>All review logs and remarks</li>
                        <li>Permit details (if issued)</li>
                        <li>Related notifications</li>
                    </ul>
                    <strong style="color: #721c24;">This action cannot be undone!</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="material-icons" style="vertical-align: middle; font-size: 16px;">cancel</i>
                    Cancel
                </button>
                <button type="button" id="confirmDeleteApplicationBtn" class="btn btn-danger">
                    <i class="material-icons" style="vertical-align: middle; font-size: 16px;">delete_forever</i>
                    Yes, Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Store the ref_id for deletion
let deleteApplicationRefId = null;

// Open delete application modal
function openDeleteApplicationModal(refId, permitFor, clientName) {
    deleteApplicationRefId = refId;
    
    // Set modal content
    document.getElementById('deleteRefId').textContent = refId;
    document.getElementById('deletePermitFor').textContent = permitFor;
    document.getElementById('deleteClientName').textContent = clientName;
    
    // Show modal
    let modal = new bootstrap.Modal(document.getElementById('deleteApplicationModal'));
    window.deleteApplicationModal = modal;
    modal.show();
}

// Handle delete confirmation
document.getElementById("confirmDeleteApplicationBtn")?.addEventListener("click", function() {
    if (!deleteApplicationRefId) {
        alert("No application selected for deletion.");
        return;
    }
    
    // Disable button to prevent double clicks
    const originalHTML = this.innerHTML;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
    
    let formData = new FormData();
    formData.append('ref_id', deleteApplicationRefId);

    fetch('api/delete_application.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        const trimmedData = data.trim();
        
        if (trimmedData === "success") {
            // Close modal
            const cancelBtn = document.querySelector("#deleteApplicationModal .btn-secondary");
            if (cancelBtn) cancelBtn.click();
            
            // Reload the page to refresh the table
            window.location.reload();
        } else {
            alert("Failed to delete application: " + trimmedData);
            // Re-enable button
            this.disabled = false;
            this.innerHTML = originalHTML;
        }
    })
    .catch(err => {
        console.error('Error deleting application:', err);
        alert("An error occurred while deleting the application.");
        // Re-enable button
        this.disabled = false;
        this.innerHTML = originalHTML;
    });
});
</script>
        
        <!-- Javascripts -->
        <script src="assets/plugins/bootstrap/popper.min.js"></script>
        <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <script src="assets/plugins/chartjs/chart.min.js"></script>
        <script src="assets/plugins/apexcharts/dist/apexcharts.min.js"></script>
        <script src="assets/js/lime.min.js"></script>
        <script src="assets/js/pages/dashboard.js"></script>
        <script src="assets/js/fireworks_anim.js"></script>
        <script src="assets/js/pages/charts.js"></script>
    </body>
</html>
<?php mysqli_close($conn); ?>