<?php
// db.php
$servername = "localhost";
$username = "root"; // Change this according to your database credentials
$password = "M@lshan2002"; // Change this according to your database credentials
$dbname = "twitchie"; // Change this to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
