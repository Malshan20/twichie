<?php
include 'db.php';

// Fetch all messages from the contacts table
$sql = "SELECT id, name, message, admin_response FROM contacts ORDER BY created_at ASC";
$result = $conn->query($sql);

$messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($messages);
?>
