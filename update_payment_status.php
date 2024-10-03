<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

// Ensure the request is POST and contains the necessary data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['user_id'])) {
        $user_id = $data['user_id'];

        // Update the paid_status of the user
        $sql = "UPDATE users SET paid_status = 'paid' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID is missing']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
