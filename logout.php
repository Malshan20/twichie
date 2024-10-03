<?php
// Start the session
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Redirect to the login page
header("Location: login.php");
die(); // End the script to ensure no further execution after redirect
?>
