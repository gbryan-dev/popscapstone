<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();


// Redirect to login page
echo '<script>window.location.href = "../";</script>';
exit;
?>
