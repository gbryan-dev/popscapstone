    <?php
    // Include database connection
    session_start();
    include '../db_conn.php';

    // Fetch ref_id values from the applications table based on client_id
    $client_id = $_SESSION['client_id'];
    $sql = "SELECT DISTINCT ref_id FROM applications WHERE client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all the ref_ids
    $ref_ids = [];
    while ($row = $result->fetch_assoc()) {
        $ref_ids[] = $row['ref_id'];
    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ref ID Cards</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <h2 class="text-center mb-4">Ref ID Cards</h2>
    <p class="text-center mb-4">Click on a Reference ID below to view the related documents.</p>


    <!-- Card Container for Ref IDs -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">
        <?php foreach ($ref_ids as $ref_id): ?>
            <div class="col">
                <div class="card h-100 shadow-sm" style="cursor: pointer;" onclick="showDocuments('<?php echo $ref_id; ?>')">
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <h5 class="card-title"><?php echo $ref_id; ?></h5>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bootstrap Modal for displaying documents -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style="position: sticky;">
                <h5 class="modal-title" id="documentModalLabel">Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="documentList" style="justify-content: center;">
                <!-- Document content will be injected here -->
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS & Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
<script>
// JavaScript to handle modal display and fetching documents
function showDocuments(refId) {
    // Fetch documents based on the ref_id
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_documents.php?ref_id=" + refId, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const documents = JSON.parse(xhr.responseText);
            const documentList = document.getElementById("documentList");
            documentList.innerHTML = '';

            // Set the container to display flex and center the images
            documentList.style.display = 'flex';
            documentList.style.flexWrap = 'wrap';
            documentList.style.justifyContent = 'center';
            documentList.style.alignItems = 'center';

            // Display documents (limit the size to 300px)
            documents.forEach(function (doc) {
                const img = document.createElement("img");
                img.src = "uploads/" + doc.file_name;  // Assuming files are in "uploads" folder
                
                // Set image style to limit width/height to 300px
                img.classList.add("img-fluid", "m-2");
                img.style.maxWidth = "300px";  // Limiting width to 300px
                img.style.maxHeight = "300px"; // Limiting height to 300px

                // Append the image to the container
                documentList.appendChild(img);
            });

            // Show the modal
            const documentModal = new bootstrap.Modal(document.getElementById('documentModal'));
            documentModal.show();
        }
    };
    xhr.send();
}
</script>


</body>
</html>
