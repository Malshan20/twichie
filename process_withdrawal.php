<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.php');
    exit();
}

$creator_id = $_SESSION['user_id'];

// Fetch creator's total earnings from the earnings table
$sql = "SELECT SUM(amount) AS total_earnings FROM earnings WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $creator_id);
$stmt->execute();
$result = $stmt->get_result();
$earnings_row = $result->fetch_assoc();
$earnings = $earnings_row['total_earnings'] ?? 0;

if ($earnings > 0) {
    // Insert a new record in the withdrawals table
    $sql = "INSERT INTO withdrawals (user_id, amount, status) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $creator_id, $earnings);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Your withdrawal request has been submitted. PayPal will process it soon.';
    } else {
        $_SESSION['error'] = 'Error processing your withdrawal request. Please try again.';
    }
    $stmt->close();
} else {
    $_SESSION['error'] = 'You do not have enough earnings to withdraw.';
}

$conn->close();
header('Location: withdraw_rewards.php');
exit();
?>