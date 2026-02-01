<?php
session_start();
include '../db_conn.php';


$client_id = $_SESSION['client_id'];

$query = "
    SELECT d.*, a.ref_id
    FROM documents d
    JOIN applications a ON d.application_id = a.application_id
    WHERE d.field_name = 'Proof Of Payment' AND a.client_id = $client_id
";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $receipts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    foreach ($receipts as $receipt) {
        $filePath = 'uploads/' . $receipt['file_name'];
        ?>
        <div class="card" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px 10px; margin: 10px; width: 250px;">
            <img src="<?php echo $filePath; ?>" alt="Proof Of Payment" style="max-width: 100%; height: auto; border-radius: 8px;">
        </div>
        <?php
    }
} else {
    echo "<div style='color:gray'><i class='fas fa-exclamation-triangle'></i> No data found.</div>";
}
?>
