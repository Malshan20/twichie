<?php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$video_id = $data['video_id'];

$stmt = $conn->prepare("UPDATE videos SET likes = likes + 1 WHERE id = ?");
$stmt->bind_param("i", $video_id);
$stmt->execute();

$stmt->close();
$conn->close();

// Return the updated number of likes
echo json_encode(['success' => true, 'likes' => $likes]);
?>