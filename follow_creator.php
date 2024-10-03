<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['action'], $data['creator_id'], $data['user_id'])) {
    $action = $data['action'];
    $creator_id = $data['creator_id'];
    $user_id = $data['user_id'];

    if ($action === 'follow') {
        // Insert into followers table
        $stmt = $conn->prepare("INSERT INTO followers (user_id, creator_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $user_id, $creator_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to follow creator.']);
        }
    } elseif ($action === 'unfollow') {
        // Remove from followers table
        $stmt = $conn->prepare("DELETE FROM followers WHERE user_id = ? AND creator_id = ?");
        $stmt->bind_param("ii", $user_id, $creator_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to unfollow creator.']);
        }
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>
