<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.php');
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $creator_id = $_SESSION['user_id'];
    $streamTitle = trim($_POST['streamTitle']);
    $streamDate = $_POST['streamDate'];

    // Validate input
    if (empty($streamTitle) || empty($streamDate)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: live_stream.php');
        exit();
    }

    // Prepare SQL statement to insert scheduled stream
    $sql = "INSERT INTO live_streams (user_id, title, scheduled_time) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $creator_id, $streamTitle, $streamDate);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Live stream scheduled successfully!';
    } else {
        $_SESSION['error'] = 'Error scheduling live stream. Please try again.';
    }

    $stmt->close();
    $conn->close();
    
    // Redirect back to the live stream page
    header('Location: live_stream.php');
    exit();
}
?>
