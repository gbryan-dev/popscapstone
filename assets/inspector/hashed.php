<?php
$password = "director";

// Hash the password using bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

echo $hash;
?>
