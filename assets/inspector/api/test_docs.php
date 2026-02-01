<?php
// api/test_docs.php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'documents' => [
        'Fireworks Display Operator',
        'Dealer License', 
        'Manufacturers License',
        'Proof Of Payment'
    ],
    'test' => true
]);
?>