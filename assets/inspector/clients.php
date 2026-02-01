<?php require_once 'partials/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
    <head>
 <?php include('partials/head.php')?>
        
       <style>
            .search { width:60%;}

            @media (max-width: 480px) {
            .search { width:80%;}
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
    <div class="card">
        <div class="card-body">

            <!-- Title + Search -->
            <div style="display: flex; width: 100%; flex-direction: column; align-items: center; text-align: center;">
                <h5 class="card-title" style="margin: auto; font-size: 23px;">All Clients</h5>

                <div class="form-inline search" style="margin-top: 10px;">
                    <input class="form-control" id="searchInput" style="width:100%; text-align: center;"
                           type="search" placeholder="Search clients by name, email, ID.." aria-label="Search">
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive" style="margin-top: 10px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Email</th>
                            <th scope="col" style="min-width: 180px;">Client&nbsp;Name</th>
                            <th scope="col">Account&nbsp;Type</th>
                            <th scope="col">Total&nbsp;Applications</th>
                            <th scope="col">Created&nbsp;At</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody id="get_clients">
                        <!-- Rows will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation example">
                <ul class="pagination pagination-circle justify-content-center">
                    <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>

        </div>
    </div>
</div>

<!-- Delete Client Modal -->
<div class="modal fade" id="deleteClientModal" tabindex="-1" role="dialog" aria-labelledby="deleteClientModalTitle" aria-hidden="true" style="background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="deleteClientModalTitle" style="color:white;">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">delete_forever</i>
                    Confirm Deletion
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="material-icons" style="color:white;">close</i>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <p style="font-size: 15px; margin-bottom: 15px;">Are you sure you want to <strong>permanently delete</strong> this client?</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p style="margin: 5px 0;"><strong>Client ID:</strong> <span id="deleteClientId"></span></p>
                    <p style="margin: 5px 0;"><strong>Email:</strong> <span id="deleteClientEmail"></span></p>
                    <p style="margin: 5px 0;"><strong>Name:</strong> <span id="deleteClientName"></span></p>
                </div>
                
                <div class="alert alert-danger" style="margin-bottom: 0;">
                    <i class="material-icons" style="vertical-align: middle; font-size: 18px;">warning</i>
                    <strong>Warning:</strong> This action will permanently delete:
                    <ul style="margin: 10px 0 0 20px; padding-left: 0;">
                        <li>Client account</li>
                        <li>All applications and permits</li>
                        <li>All uploaded documents</li>
                        <li>Personal information</li>
                        <li>Related notifications</li>
                    </ul>
                    <strong style="color: #721c24;">This action cannot be undone!</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteClientBtn" class="btn btn-danger">
                    Yes, Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<script>

// Store the client_id for deletion
let deleteClientIdValue = null;

// Open delete client modal
function openDeleteClientModal(clientId, email, name) {
    deleteClientIdValue = clientId;
    
    // Set modal content
    document.getElementById('deleteClientId').textContent = clientId;
    document.getElementById('deleteClientEmail').textContent = email;
    document.getElementById('deleteClientName').textContent = name;
    
    // Show modal
    let modal = new bootstrap.Modal(document.getElementById('deleteClientModal'));
    window.deleteClientModal = modal;
    modal.show();
}

// Handle delete confirmation
document.getElementById("confirmDeleteClientBtn")?.addEventListener("click", function() {
    if (!deleteClientIdValue) {
        alert("No client selected for deletion.");
        return;
    }
    
    // Disable button to prevent double clicks
    const originalHTML = this.innerHTML;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
    
    let formData = new FormData();
    formData.append('client_id', deleteClientIdValue);

    fetch('api/delete_client.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        const trimmedData = data.trim();
        
        if (trimmedData === "success") {
            // Close modal
            const cancelBtn = document.querySelector("#deleteClientModal .btn-secondary");
            if (cancelBtn) cancelBtn.click();
            
            // Reload the page to refresh the table
            window.location.reload();
        } else {
            alert("Failed to delete client: " + trimmedData);
            // Re-enable button
            this.disabled = false;
            this.innerHTML = originalHTML;
        }
    })
    .catch(err => {
        console.error('Error deleting client:', err);
        alert("An error occurred while deleting the client.");
        // Re-enable button
        this.disabled = false;
        this.innerHTML = originalHTML;
    });
});

let allRows = [];
let filteredRows = [];
let currentPage = 1;
const rowsPerPage = 10;
let reloadInterval = null;

const tableBody    = document.getElementById('get_clients');
const searchInput  = document.getElementById('searchInput');
const paginationNav = document.querySelector('nav[aria-label="Page navigation example"]');
const paginationUl  = paginationNav.querySelector('ul.pagination');

// Load clients
function loadClients() {
    fetch('api/get_clients.php')
        .then(r => r.text())
        .then(html => {
            const cleaned = html.trim();
            if (!cleaned) {
                allRows = [];
            } else {
                const tempTable = document.createElement('table');
                tempTable.innerHTML = cleaned;
                allRows = Array.from(tempTable.querySelectorAll('tr'))
                                .map(tr => tr.outerHTML);
            }
            
            // Reapply current search filter
            applySearchFilter();
            
            // Re-render current page
            renderTable();
            renderPagination();
        })
        .catch(err => {
            console.error(err);
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load clients</td></tr>';
        });
}

// Apply search filter
function applySearchFilter() {
    const term = searchInput.value.trim().toLowerCase();
    
    filteredRows = term === ''
        ? [...allRows]
        : allRows.filter(rowHtml => {
            const text = rowHtml.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ');
            return text.toLowerCase().includes(term);
        });
}

// Render table rows for current page
function renderTable() {
    const start = (currentPage - 1) * rowsPerPage;
    const end   = start + rowsPerPage;
    const pageRows = filteredRows.slice(start, end);

    tableBody.innerHTML = pageRows.length
        ? pageRows.join('')
        : '<tr><td colspan="7" class="text-center">No clients found.</td></tr>';
}

// Render pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

    if (totalPages <= 1 || filteredRows.length === 0) {
        paginationNav.style.display = 'none';
        return;
    }
    paginationNav.style.display = 'block';

    let html = '';

    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="prev">Previous</a></li>`;

    let startPage = currentPage;
    let endPage = Math.min(currentPage + 2, totalPages);

    if (endPage - startPage < 2) {
        startPage = Math.max(1, endPage - 2);
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
    }

    while ((endPage - startPage + 1) < 3 && endPage < totalPages) {
        endPage++;
        html += `<li class="page-item">
                    <a class="page-link" href="#" data-page="${endPage}">${endPage}</a></li>`;
    }

    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="next">Next</a></li>`;

    paginationUl.innerHTML = html;

    paginationUl.querySelectorAll('a[data-page]').forEach(link => {
        link.onclick = e => {
            e.preventDefault();
            const val = link.dataset.page;
            let target = val === 'prev' ? currentPage - 1 :
                        val === 'next' ? currentPage + 1 :
                        parseInt(val);

            if (target >= 1 && target <= totalPages && target !== currentPage) {
                goToPage(target);
            }
        };
    });
}

// Go to page
function goToPage(page) {
    currentPage = page;
    renderTable();
    renderPagination();

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Live Search with debounce
let searchTimeout;
searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applySearchFilter();
        goToPage(1);
    }, 250);
});

// Start auto-reload every 5 seconds
function startAutoReload() {
    if (reloadInterval) {
        clearInterval(reloadInterval);
    }
    
    reloadInterval = setInterval(() => {
        loadClients();
    }, 5000);
}

// Initial load and start auto-reload
loadClients();
startAutoReload();

// Stop auto-reload when user leaves the page
window.addEventListener('beforeunload', () => {
    if (reloadInterval) {
        clearInterval(reloadInterval);
    }
});
</script>
</div>

                </div>
            </div>
            <?php include('partials/footer.php')?>
        </div>
        
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