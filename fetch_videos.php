<?php
session_start();
include 'db.php'; // Make sure to include your database connection

// Fetch videos where status is 'free'
$sql = "SELECT id, title, description, thumbnail_url, views, likes FROM videos WHERE status = 'free' ORDER BY created_at DESC";
$result = $conn->query($sql);

$videos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($videos);
?>
