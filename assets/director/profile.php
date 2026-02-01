<?php
require_once 'partials/auth.php';
include '../../db_conn.php';

// Fetch Director (Director) information
$systemAdminSql = "SELECT 
    o.id,
    o.username,
    o.email,
    o.created_at,
    r.role_name
FROM officials_acc o
LEFT JOIN roles r ON o.role_id = r.role_id
WHERE o.role_id = 1
LIMIT 1";

$systemAdminResult = mysqli_query($conn, $systemAdminSql);
$systemAdminData = mysqli_fetch_assoc($systemAdminResult);

// Fetch Admin (Inspector) information
$adminSql = "SELECT 
    o.id,
    o.username,
    o.email,
    o.created_at,
    r.role_name
FROM officials_acc o
LEFT JOIN roles r ON o.role_id = r.role_id
WHERE o.role_id = 2
LIMIT 1";

$adminResult = mysqli_query($conn, $adminSql);
$adminData = mysqli_fetch_assoc($adminResult);
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

            /* Success Popup Styles */
            .success-popup {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                z-index: 9999;
                justify-content: center;
                align-items: center;
            }
            
            .success-popup.show {
                display: flex;
            }
            
            .success-popup-content {
                background: white;
                padding: 40px;
                border-radius: 15px;
                text-align: center;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                animation: popIn 0.3s ease-out;
                position: relative;
                z-index: 10000;
            }
            
            @keyframes popIn {
                0% {
                    transform: scale(0.8);
                    opacity: 0;
                }
                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }
            
            .success-icon {
                font-size: 80px;
                color: #28a745;
                margin-bottom: 20px;
            }
            
            .success-title {
                font-size: 28px;
                font-weight: bold;
                color: #333;
                margin-bottom: 10px;
            }
            
            .success-message {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }
            
            .success-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s;
            }
            
            .success-btn:hover {
                transform: translateY(-2px);
            }
            
            /* Confetti Canvas */
            #confetti-canvas {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 9998;
            }

            @media (max-width: 480px) {
                .search { width:80%;}
                .info-row {
                    flex-direction: column;
                }
                .info-label {
                    width: 100%;
                    margin-bottom: 5px;
                }
                .success-popup-content {
                    margin: 20px;
                    padding: 30px 20px;
                }
                .success-icon {
                    font-size: 60px;
                }
                .success-title {
                    font-size: 22px;
                }
            }
       </style>
    </head>
    <body>
        
        <canvas id="canvas"></canvas>
        <canvas id="confetti-canvas"></canvas>
        
        <!-- Success Popup -->
        <div class="success-popup" id="successPopup">
            <div class="success-popup-content">
                <div class="success-icon">✓</div>
                <div class="success-title">Success!</div>
                <div class="success-message" id="successMessage">Information updated successfully!</div>
                <button class="success-btn" onclick="closeSuccessPopup()">Continue</button>
            </div>
        </div>
        
        <?php include('partials/sidebar.php')?>
        <?php include('partials/header.php')?>

        <div class="lime-container" >
            <div class="lime-body">
                <div class="container">

                    <!-- Director Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                        <h5 class="card-title" style="font-size: 23px; margin: 0;">
                                            Director Information
                                        </h5>
                                        <?php if ($systemAdminData): ?>
                                        <button type="button" class="btn btn-primary" onclick="openEditSystemAdminModal()">
                                            <i class="material-icons" style="vertical-align: middle; font-size: 18px;">edit</i>
                                            Edit
                                        </button>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($systemAdminData): ?>
                                    <div class="info-section">
                                        <h6 style="margin-bottom: 15px; color: #007bff;">Account Details</h6>
                                        
                                        <div class="info-row">
                                            <div class="info-label">Username:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($systemAdminData['username']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Email:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($systemAdminData['email']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Role:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($systemAdminData['role_name']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Created At:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($systemAdminData['created_at']); ?></div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-warning">Director not found.</div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Section -->
                    <div class="row" style="margin-top: 30px;">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                        <h5 class="card-title" style="font-size: 23px; margin: 0;">
                                            Inspector Information
                                        </h5>
                                        <?php if ($adminData): ?>
                                        <button type="button" class="btn btn-primary" onclick="openEditAdminModal()">
                                            <i class="material-icons" style="vertical-align: middle; font-size: 18px;">edit</i>
                                            Edit
                                        </button>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($adminData): ?>
                                    <div class="info-section">
                                        <h6 style="margin-bottom: 15px; color: #007bff;">Account Details</h6>
                                        
                                        <div class="info-row">
                                            <div class="info-label">Username:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($adminData['username']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Email:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($adminData['email']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Role:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($adminData['role_name']); ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Created At:</div>
                                            <div class="info-value"><?php echo htmlspecialchars($adminData['created_at']); ?></div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-warning">Inspector not found.</div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('partials/footer.php')?>
        </div>

        <!-- Edit Director Modal -->
        <div class="modal fade" id="editSystemAdminModal" tabindex="-1" role="dialog" aria-labelledby="editSystemAdminModalTitle" aria-hidden="true" style="background: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title" id="editSystemAdminModalTitle" style="color:white;">
                            <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">edit</i>
                            Edit Director Information
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="material-icons" style="color:white;">close</i>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 25px;">
                        <form id="editSystemAdminForm">
                            <input type="hidden" name="admin_id" value="<?php echo $systemAdminData['id'] ?? ''; ?>">
                            
                            <div class="form-group">
                                <label for="system_username">Username</label>
                                <input type="text" class="form-control" id="system_username" name="username" 
                                       value="<?php echo htmlspecialchars($systemAdminData['username'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="system_email">Email</label>
                                <input type="email" class="form-control" id="system_email" name="email" 
                                       value="<?php echo htmlspecialchars($systemAdminData['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="system_password">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="system_password" name="password" 
                                       placeholder="Enter new password or leave blank">
                            </div>
                            
                            <div class="form-group">
                                <label for="system_confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="system_confirm_password" name="confirm_password" 
                                       placeholder="Confirm new password">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            
                            Cancel
                        </button>
                        <button type="button" id="saveSystemAdminBtn" class="btn btn-primary">
                            <i class="material-icons" style="vertical-align: middle; font-size: 16px;">save</i>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Admin Modal -->
        <div class="modal fade" id="editAdminModal" tabindex="-1" role="dialog" aria-labelledby="editAdminModalTitle" aria-hidden="true" style="background: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title" id="editAdminModalTitle" style="color:white;">
                            <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">edit</i>
                            Edit Inspector Information
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="material-icons" style="color:white;">close</i>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 25px;">
                        <form id="editAdminForm">
                            <input type="hidden" name="admin_id" value="<?php echo $adminData['id'] ?? ''; ?>">
                            
                            <div class="form-group">
                                <label for="admin_username">Username</label>
                                <input type="text" class="form-control" id="admin_username" name="username" 
                                       value="<?php echo htmlspecialchars($adminData['username'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_email">Email</label>
                                <input type="email" class="form-control" id="admin_email" name="email" 
                                       value="<?php echo htmlspecialchars($adminData['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_password">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="admin_password" name="password" 
                                       placeholder="Enter new password or leave blank">
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="admin_confirm_password" name="confirm_password" 
                                       placeholder="Confirm new password">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            
                            Cancel
                        </button>
                        <button type="button" id="saveAdminBtn" class="btn btn-primary">
                            <i class="material-icons" style="vertical-align: middle; font-size: 16px;">save</i>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Director Modal
        function openEditSystemAdminModal() {
            let modal = new bootstrap.Modal(document.getElementById('editSystemAdminModal'));
            modal.show();
        }

        document.getElementById("saveSystemAdminBtn")?.addEventListener("click", function() {
            const password = document.getElementById('system_password').value;
            const confirmPassword = document.getElementById('system_confirm_password').value;
            
            if (password && password !== confirmPassword) {
                alert("Passwords do not match!");
                return;
            }
            
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            const formData = new FormData(document.getElementById('editSystemAdminForm'));

            fetch('api/update_director.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const trimmedData = data.trim();
                
                if (trimmedData === "success") {
                    alert("Updated successfully!");
                    window.location.reload();
                } else {
                    alert("Failed to update: " + trimmedData);
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert("An error occurred while updating.");
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });

        // Admin Modal
        function openEditAdminModal() {
            let modal = new bootstrap.Modal(document.getElementById('editAdminModal'));
            modal.show();
        }

        document.getElementById("saveAdminBtn")?.addEventListener("click", function() {
            const password = document.getElementById('admin_password').value;
            const confirmPassword = document.getElementById('admin_confirm_password').value;
            
            if (password && password !== confirmPassword) {
                alert("Passwords do not match!");
                return;
            }
            
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            const formData = new FormData(document.getElementById('editAdminForm'));

            fetch('api/update_inspector.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const trimmedData = data.trim();
                
                if (trimmedData === "success") {
                    alert("Updated successfully!");
                    window.location.reload();
                } else {
                    alert("Failed to update: " + trimmedData);
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert("An error occurred while updating.");
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });
        </script>
        
        <!-- Javascripts -->
        <script src="assets/plugins/bootstrap/popper.min.js"></script>
        <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <script src="assets/js/lime.min.js"></script>
        <script src="assets/js/fireworks_anim.js"></script>
    </body>
</html>
<?php mysqli_close($conn); ?>