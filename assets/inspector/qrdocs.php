<?php
require_once 'partials/auth.php';
include '../../db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>POPS - Pyrotechnic Online Permitting System | CSG</title>
    <meta name="author" content="CSG - Civil Security Group">
    <meta name="description" content="POPS is a streamlined online system designed to assist LGUs and constituents in managing permit processing efficiently, transparently, and digitally.">
    <meta name="keywords" content="POPS, permitting, online processing, LGU, digital applications, CSG, governance, public service">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- FAVICON FILES -->
    <link href="../../assets/images/logo.png" rel="apple-touch-icon" sizes="144x144">
    <link href="../../assets/images/logo.png" rel="apple-touch-icon" sizes="120x120">
    <link href="../../assets/images/logo.png" rel="apple-touch-icon" sizes="76x76">
    <link href="../../assets/images/logo.png" rel="shortcut icon">

    <!-- Styles -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/plugins/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="assets/css/lime.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    
    
    <style>
        .main-container {
            max-width: 1200px;
            margin: 150px auto;
            padding: 0 50px;
        }
        .page-title {
            width: 100%;
            margin-bottom: 30px;
            text-align: center;
            color:white;
        }
        .h33 {
            font-size: 25px;
            width: 100%;
            color: white;
            margin-bottom: 10px;
        }
        .qr-item {
            transition: transform 0.3s;
            border: 1px solid #E6E6E6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
        }
        .qr-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 3px 10px rgba(62, 85, 120,.145);
        }
        .qr-canvas {
            border: 2px solid #E6E6E6;
            border-radius: 4px;
            padding: 10px;
            background: white;
            display: inline-block;
        }
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal {
            z-index: 1050;
        }
        .modal-backdrop.show {
            opacity: 0.5;
        }
        .error-text {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        .error-text.show {
            display: block;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include('partials/sidebar.php')?>
    <?php include('partials/header.php')?>
    
    <div class="main-container">
        <div class="page-title">
            <div class="h33"><i class="material-icons" style="vertical-align: middle;font-size: 30px">qr_code_2</i>&nbsp;QR&nbsp;Docs&nbsp;Manager</div>
            <p class="">Create, view, and manage QR docs</p>
        </div>
        
        <!-- Create QR Code Form -->
        <div class="card">
            <div class="card-body">
                <div style="width: 100%;display: flex;justify-content: space-between;align-items: center;">
                    <h5 class="card-title"><i class="material-icons" style="vertical-align: middle;">add_circle</i> Generate New QR Code</h5>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary btn-lg w-100" id="openAddModalBtn">
                            <i class="material-icons" style="vertical-align: middle; font-size: 16px;">add</i> Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- QR Codes List -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="material-icons" style="vertical-align: middle;">list</i> Saved QR Codes</h5>
                <div id="qrList">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="material-icons" style="vertical-align: middle;">add</i> Add New QR Code</h5>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label class="form-label">Owner: <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="addOwner" maxlength="50" required>
                            <div class="error-text" id="addOwnerError">This owner name already exists.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">License Number: <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="addLicenseNumber" maxlength="20" required>
                            <div class="error-text" id="addLicenseError">This license number already exists.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Application Type: <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="addApplicationType" maxlength="50" required>
                            
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Validity License:</label>
                            <input type="text" class="form-control" id="addValidityLicense"  style="background-color: #e9ecef;">
                            <small class="text-muted">Auto-generated: 2 years validity from today</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    
                    
                    <button type="button" class="btn btn-primary" id="saveBtn">
                        <i class="material-icons" style="vertical-align: middle; font-size: 16px;">save</i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="material-icons" style="vertical-align: middle;">edit</i> Edit</h5>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Owner: <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="editOwner" maxlength="50" required>
                            <div class="error-text" id="editOwnerError">This owner name already exists.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">License Number: <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="editLicenseNumber" maxlength="20" required>
                            <div class="error-text" id="editLicenseError">This license number already exists.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Application Type: <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="editApplicationType" maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Validity License:</label>
                            <input type="text" class="form-control" id="editValidityLicense"  style="background-color: #e9ecef;">
                            <small class="text-muted">Auto-generated: 2 years validity from today</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    
                    <button type="button" class="btn btn-primary" id="updateBtn">
                        <i class="material-icons" style="vertical-align: middle; font-size: 16px;">save</i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('partials/footer.php')?>
    
    <!-- Javascripts -->
      <script src="assets/plugins/jquery/jquery-3.2.1.min.js"></script>
    <script src="assets/plugins/bootstrap/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/js/lime.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <script>
        let addModal = null;
        let editModal = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modals
            const addModalEl = document.getElementById('addModal');
            const editModalEl = document.getElementById('editModal');
            
            if (typeof bootstrap !== 'undefined') {
                addModal = new bootstrap.Modal(addModalEl);
                editModal = new bootstrap.Modal(editModalEl);
            } else if (typeof $.fn.modal !== 'undefined') {
                addModal = {
                    show: function() { $('#addModal').modal('show'); },
                    hide: function() { $('#addModal').modal('hide'); }
                };
                editModal = {
                    show: function() { $('#editModal').modal('show'); },
                    hide: function() { $('#editModal').modal('hide'); }
                };
            }
            
            loadQRCodes();
            
            // Add real-time validation for add form
            document.getElementById('addOwner').addEventListener('blur', function() {
                checkDuplicate('owner', this.value, null, 'add');
            });
            document.getElementById('addLicenseNumber').addEventListener('blur', function() {
                checkDuplicate('license_number', this.value, null, 'add');
            });
            document.getElementById('addApplicationType').addEventListener('blur', function() {
                checkDuplicate('application_type', this.value, null, 'add');
            });
            
            // Add real-time validation for edit form
            document.getElementById('editOwner').addEventListener('blur', function() {
                const id = document.getElementById('editId').value;
                checkDuplicate('owner', this.value, id, 'edit');
            });
            document.getElementById('editLicenseNumber').addEventListener('blur', function() {
                const id = document.getElementById('editId').value;
                checkDuplicate('license_number', this.value, id, 'edit');
            });
            document.getElementById('editApplicationType').addEventListener('blur', function() {
                const id = document.getElementById('editId').value;
                checkDuplicate('application_type', this.value, id, 'edit');
            });
        });
        
        // Generate validity date (current date to 2 years from now)
        function generateValidityDate() {
            const today = new Date();
            const twoYearsLater = new Date();
            twoYearsLater.setFullYear(today.getFullYear() + 2);
            
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                          'July', 'August', 'September', 'October', 'November', 'December'];
            
            const startDate = `${months[today.getMonth()]} ${today.getDate()}, ${today.getFullYear()}`;
            const endDate = `${months[twoYearsLater.getMonth()]} ${twoYearsLater.getDate()}, ${twoYearsLater.getFullYear()}`;
            
            return `${startDate} - ${endDate}`;
        }
        
        // Check for duplicates
        function checkDuplicate(field, value, excludeId, formType) {
            if (!value) return;
            
            fetch('api/qrcheck_duplicate.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `field=${encodeURIComponent(field)}&value=${encodeURIComponent(value)}&exclude_id=${excludeId || ''}`
            })
            .then(response => response.json())
            .then(data => {
                const prefix = formType === 'add' ? 'add' : 'edit';
                let errorId, inputId;
                
                if (field === 'owner') {
                    errorId = prefix + 'OwnerError';
                    inputId = prefix + 'Owner';
                } else if (field === 'license_number') {
                    errorId = prefix + 'LicenseError';
                    inputId = prefix + 'LicenseNumber';
                } 
                
                const errorEl = document.getElementById(errorId);
                const inputEl = document.getElementById(inputId);
                
                if (data.exists) {
                    errorEl.classList.add('show');
                    inputEl.classList.add('is-invalid');
                } else {
                    errorEl.classList.remove('show');
                    inputEl.classList.remove('is-invalid');
                }
            })
            .catch(err => console.error('Error checking duplicate:', err));
        }
        
        // Open add modal
        document.getElementById('openAddModalBtn').addEventListener('click', function() {
            document.getElementById('addForm').reset();
            // Clear all error states
            document.querySelectorAll('.error-text').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
            // Set validity date
            document.getElementById('addValidityLicense').value = generateValidityDate();
            addModal.show();
        });
        
        // Save new QR Code
        document.getElementById('saveBtn').addEventListener('click', function() {
            const owner = document.getElementById('addOwner').value.trim();
            const licenseNumber = document.getElementById('addLicenseNumber').value.trim();
            const applicationType = document.getElementById('addApplicationType').value.trim();
            const validityLicense = document.getElementById('addValidityLicense').value;
            
            if(!owner || !licenseNumber || !applicationType) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Check if any errors are showing
            if (document.querySelector('#addModal .error-text.show')) {
                alert('Please fix the duplicate entries before saving');
                return;
            }
            
            fetch('api/add_qrdoc.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `owner=${encodeURIComponent(owner)}&license_number=${encodeURIComponent(licenseNumber)}&application_type=${encodeURIComponent(applicationType)}&validity_license=${encodeURIComponent(validityLicense)}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('QR Code added successfully!');
                    addModal.hide();
                    loadQRCodes();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Error adding QR code'));
        });
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        // Load all QR codes
      function loadQRCodes() {
    fetch('api/getqrdocs.php')
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            return response.text(); // Changed to text() to see raw output
        })
        .then(text => {
            console.log('Raw response:', text); // This will show you what's actually returned
            
            // Try to parse it
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                const qrList = document.getElementById('qrList');
                
                if(data.success && data.data.length > 0) {
                    let html = '<div class="row">';
                    data.data.forEach(qr => {
                        html += `
                            <div class="col-md-6 col-lg-4">
                                <div class="qr-item">
                                    <div class="qr-display-wrapper" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                        <div class="qr-display" data-value="${escapeHtml(qr.qr_code_value)}"></div>
                                    </div>
                                    <div class="mt-3">
                                        <p class="mb-1"><strong>Owner:</strong> ${escapeHtml(qr.owner)}</p>
                                        <p class="mb-1"><strong>License #:</strong> ${escapeHtml(qr.license_number)}</p>
                                        <p class="mb-1"><strong>Type:</strong> ${escapeHtml(qr.application_type)}</p>
                                        <p class="mb-2"><strong>Validity:</strong> ${escapeHtml(qr.validity_license)}</p>
                                        <div class="d-flex" style="gap:5px">
                                            <button class="btn btn-sm btn-info download-qr flex-fill"
                                                style="color:white; display:flex; justify-content:center; align-items:center; gap:5px;"
                                                data-value="${escapeHtml(qr.qr_code_value)}">
                                                <i class="material-icons" style="font-size:14px;">download</i>
                                                Download
                                            </button>
                                            <button class="btn btn-sm btn-warning edit-qr flex-fill"
                                                style="display:flex; justify-content:center; align-items:center; gap:5px;"
                                                data-id="${qr.id}" 
                                                data-owner="${escapeHtml(qr.owner)}"
                                                data-license="${escapeHtml(qr.license_number)}"
                                                data-type="${escapeHtml(qr.application_type)}"
                                                data-validity="${escapeHtml(qr.validity_license)}">
                                                <i class="material-icons" style="font-size:14px;">edit</i>
                                                Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-qr flex-fill"
                                                style="display:flex; justify-content:center; align-items:center; gap:5px;"
                                                data-id="${qr.id}">
                                                <i class="material-icons" style="font-size:14px;">delete</i>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    qrList.innerHTML = html;
                    
                    // Render QR codes
                    setTimeout(() => {
                        document.querySelectorAll('.qr-display').forEach(container => {
                            const value = container.getAttribute('data-value');
                            try {
                                new QRCode(container, {
                                    text: value,
                                    width: 200,
                                    height: 200,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                            } catch(e) {
                                console.error('Error generating QR code:', e);
                            }
                        });
                    }, 100);
                    
                    setTimeout(() => {
                        attachEventListeners();
                    }, 200);
                } else {
                    qrList.innerHTML = `
                        <div class="text-center py-5">
                            <i class="material-icons" style="font-size: 80px; color: #838D91;">qr_code_2</i>
                            <h5 class="text-muted mt-3">No QR codes yet</h5>
                            <p class="text-muted">Create your first QR code above!</p>
                        </div>
                    `;
                }
            } catch(parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Could not parse response as JSON');
                document.getElementById('qrList').innerHTML = `
                    <div class="alert alert-danger">
                        Error: Invalid response from server. Check console for details.
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Fetch Error:', err);
            document.getElementById('qrList').innerHTML = `
                <div class="alert alert-danger">Error loading QR codes: ${err.message}</div>
            `;
        });
}
        
        // Attach event listeners
        function attachEventListeners() {
            // Download buttons
            document.querySelectorAll('.download-qr').forEach(btn => {
                btn.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const tempDiv = document.createElement('div');
                    tempDiv.style.position = 'absolute';
                    tempDiv.style.left = '-9999px';
                    document.body.appendChild(tempDiv);
                    
                    new QRCode(tempDiv, {
                        text: value,
                        width: 512,
                        height: 512,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    
                    setTimeout(() => {
                        downloadQR(tempDiv, value);
                        document.body.removeChild(tempDiv);
                    }, 200);
                });
            });
            
            // Edit buttons
            document.querySelectorAll('.edit-qr').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const owner = this.getAttribute('data-owner');
                    const license = this.getAttribute('data-license');
                    const type = this.getAttribute('data-type');
                    const validity = this.getAttribute('data-validity');
                    openEditModal(id, owner, license, type, validity);
                });
            });
            
            // Delete buttons
            document.querySelectorAll('.delete-qr').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if(confirm('Delete this QR code?')) {
                        const id = this.getAttribute('data-id');
                        deleteQR(id);
                    }
                });
            });
        }
        
        // Open edit modal
        function openEditModal(id, owner, license, type, validity) {
            // Clear all error states
            document.querySelectorAll('.error-text').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
            
            document.getElementById('editId').value = id;
            document.getElementById('editOwner').value = owner;
            document.getElementById('editLicenseNumber').value = license;
            document.getElementById('editApplicationType').value = type;
            // Auto-generate new validity date
            document.getElementById('editValidityLicense').value = generateValidityDate();
            editModal.show();
        }
        
        // Update QR code
        document.getElementById('updateBtn').addEventListener('click', function() {
            const id = document.getElementById('editId').value;
            const owner = document.getElementById('editOwner').value.trim();
            const licenseNumber = document.getElementById('editLicenseNumber').value.trim();
            const applicationType = document.getElementById('editApplicationType').value.trim();
            const validityLicense = document.getElementById('editValidityLicense').value;
            
            if(!owner || !licenseNumber || !applicationType) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Check if any errors are showing
            if (document.querySelector('#editModal .error-text.show')) {
                alert('Please fix the duplicate entries before updating');
                return;
            }
            
            fetch('api/edit_qrdoc.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}&owner=${encodeURIComponent(owner)}&license_number=${encodeURIComponent(licenseNumber)}&application_type=${encodeURIComponent(applicationType)}&validity_license=${encodeURIComponent(validityLicense)}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('QR Code updated successfully!');
                    editModal.hide();
                    loadQRCodes();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Error updating QR code'));
        });
        
        // Delete QR code
        function deleteQR(id) {
            fetch('api/delete_qrdoc.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('QR Code deleted successfully!');
                    loadQRCodes();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Error deleting QR code'));
        }
        
        // Download function
        function downloadQR(canvasElement, value) {
            const img = canvasElement.querySelector('img');
            if(img) {
                const link = document.createElement('a');
                link.download = 'qrcode_' + Date.now() + '.png';
                link.href = img.src;
                link.click();
            }
        }
        
        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>