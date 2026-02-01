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
                <h5 class="card-title" style="margin: auto; font-size: 23px;">All Application Permits</h5>

                <div class="form-inline search" style="margin-top: 10px;">
                    <input class="form-control" id="searchInput" style="width:100%; text-align: center;"
                           type="search" placeholder="Search all application permits.." aria-label="Search">
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive" style="margin-top: 10px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Status</th>
                            <th scope="col">Reference&nbsp;ID</th>
                            <th scope="col"  style="min-width: 210px; max-width: 210px;">Permit&nbsp;For</th>
                            <th scope="col" style="min-width: 210px; max-width: 210px;">Apply Date</th>
                            <th scope="col">Client&nbsp;Name</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody id="get_applications">
                        <!-- Rows will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination (optional – you can keep server-side pagination if you want) -->
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

<script>
let allRows = [];
let filteredRows = [];
let currentPage = 1;
const rowsPerPage = 10;
let reloadInterval = null;

const tableBody    = document.getElementById('get_applications');
const searchInput  = document.getElementById('searchInput');
const paginationNav = document.querySelector('nav[aria-label="Page navigation example"]');
const paginationUl  = paginationNav.querySelector('ul.pagination');

// ------------------------------------------------------------------
// 1. Load applications
function loadApplications() {
    fetch('api/getallapps.php')
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
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load permits</td></tr>';
        });
}

// ------------------------------------------------------------------
// 2. Apply search filter
function applySearchFilter() {
    const term = searchInput.value.trim().toLowerCase();
    
    filteredRows = term === ''
        ? [...allRows]
        : allRows.filter(rowHtml => {
            const text = rowHtml.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ');
            return text.toLowerCase().includes(term);
        });
}

// ------------------------------------------------------------------
// 3. Render table rows for current page
function renderTable() {
    const start = (currentPage - 1) * rowsPerPage;
    const end   = start + rowsPerPage;
    const pageRows = filteredRows.slice(start, end);

    tableBody.innerHTML = pageRows.length
        ? pageRows.join('')
        : '<tr><td colspan="6" class="text-center">No permits found.</td></tr>';
}

// ------------------------------------------------------------------
// 4. Render pagination – ALWAYS exactly 4 buttons: Prev + 3 pages + Next
//     Current page is ALWAYS the first number after "Previous"
function renderPagination() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

    // Hide if no pagination needed
    if (totalPages <= 1 || filteredRows.length === 0) {
        paginationNav.style.display = 'none';
        return;
    }
    paginationNav.style.display = 'block';

    let html = '';

    // Previous button
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="prev">Previous</a></li>`;

    // Start showing from current page
    let startPage = currentPage;
    let endPage = Math.min(currentPage + 2, totalPages);

    // If we're near the end, shift left to always show 3 numbers
    if (endPage - startPage < 2) {
        startPage = Math.max(1, endPage - 2);
    }

    // Add exactly 3 page number buttons
    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
    }

    // If we have less than 3 (shouldn't happen, but safety)
    while ((endPage - startPage + 1) < 3 && endPage < totalPages) {
        endPage++;
        html += `<li class="page-item">
                    <a class="page-link" href="#" data-page="${endPage}">${endPage}</a></li>`;
    }

    // Next button
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="next">Next</a></li>`;

    paginationUl.innerHTML = html;

    // Click handlers
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

// ------------------------------------------------------------------
// 5. Go to page
function goToPage(page) {
    currentPage = page;
    renderTable();
    renderPagination();

    // Smooth scroll all the way to the top of the page
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// ------------------------------------------------------------------
// 6. Live Search with debounce
let searchTimeout;
searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applySearchFilter();
        goToPage(1);
    }, 250);
});

// ------------------------------------------------------------------
// 7. Start auto-reload every 5 seconds
function startAutoReload() {
    // Clear any existing interval
    if (reloadInterval) {
        clearInterval(reloadInterval);
    }
    
    // Set up new interval
    reloadInterval = setInterval(() => {
        loadApplications();
    }, 5000); // 5000ms = 5 seconds
}

// ------------------------------------------------------------------
// 8. Initial load and start auto-reload
loadApplications();
startAutoReload();

// Optional: Stop auto-reload when user leaves the page
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