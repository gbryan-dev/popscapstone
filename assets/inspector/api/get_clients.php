<?php
include '../../../db_conn.php';

$sql = "SELECT 
    c.client_id,
    c.email,
    c.created_at,
    r.role_name,
    COALESCE(m.dealer_name, ret.full_name, 'N/A') as client_name,
    COUNT(a.application_id) as total_applications
FROM clients_acc c
LEFT JOIN roles r ON c.role_id = r.role_id
LEFT JOIN manufacturers_info m ON c.client_id = m.client_id
LEFT JOIN retailers_info ret ON c.client_id = ret.client_id
LEFT JOIN applications a ON c.client_id = a.client_id
GROUP BY c.client_id, c.email, c.created_at, r.role_name, client_name
ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $clientId = htmlspecialchars($row['client_id']);
        $email = htmlspecialchars($row['email']);
        $clientName = htmlspecialchars($row['client_name']);
        $accountType = htmlspecialchars($row['role_name']);
        $totalApps = htmlspecialchars($row['total_applications']);
        $createdAt = htmlspecialchars($row['created_at']);
        
        echo "<tr>
            <td>{$email}</td>
            <td style='text-transform: capitalize;'>{$clientName}</td>
            <td>{$accountType}</td>
            <td>{$totalApps}</td>
            <td>{$createdAt}</td>
            <td style='display:flex;gap:5px;'>
                <form method='POST' action='view_client' style='display: flex'>
                    <input type='hidden' name='client_id' value='{$clientId}'>
                    <button type='submit' style='font-weight:400;font-size:12px !important;border:none;outline:none' class='badge bg-success text-white' style='display:flex' title='View Details'>
                        View
                    </button>
                </form>
                <button  style='display:flex' onclick='openDeleteClientModal(\"{$clientId}\", \"{$email}\", \"{$clientName}\")' 
                        class='btn btn-danger btn-sm' title='Delete Client'>
                    Delete
                </button>
            </td>
        </tr>";
    }
} else {
    echo "";
}

mysqli_close($conn);
?>