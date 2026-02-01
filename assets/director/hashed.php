<?php
$password = "gbryandev";

// Hash the password using bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

echo $hash;
?>
