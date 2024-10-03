<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.php');
    exit();
}

$creator_id = $_SESSION['user_id'];

// Handle form submission for new ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $question = $_POST['question'];

    if (!empty($subject) && !empty($question)) {
        $sql = "INSERT INTO tickets (user_id, subject, question) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $creator_id, $subject, $question);
        if ($stmt->execute()) {
            $success_message = "Your ticket has been submitted successfully!";
        } else {
            $error_message = "Error submitting the ticket. Please try again.";
        }
        $stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}

// Fetch the last 5 tickets submitted by the creator
$sql = "SELECT subject, question, created_at FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $creator_id);
$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Contact Us - Creator</title>
</head>
<body class="bg-gray-900 text-gray-200">
    <!-- Navbar -->
    <nav class="flex items-center justify-between p-4 bg-gray-800">
        <h1 class="text-white text-2xl">Contact Us</h1>
        <div class="flex items-center space-x-4">
            <a href="creator_dashboard.php" class="text-gray-400 hover:text-white">Home</a>
            <a href="live_stream.php" class="text-gray-400 hover:text-white">Live Stream</a>
            <a href="upload_video.php" class="text-gray-400 hover:text-white">Upload Video</a>
            <a href="withdraw_rewards.php" class="text-gray-400 hover:text-white">Withdraw Rewards</a>
            <a href="contact_us_creator.php" class="text-gray-400 hover:text-white">Contact Us</a>
            <a href="logout.php" class="text-red-500 hover:text-white">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="p-6">
        <div class="bg-gray-800 p-6 rounded-md">
            <h2 class="text-xl font-bold mb-4">Submit a New Ticket</h2>

            <!-- Error or Success Message -->
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-500 text-white p-2 mb-4 rounded">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-500 text-white p-2 mb-4 rounded">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Ticket Submission Form -->
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="subject" class="block text-sm font-medium">Subject</label>
                    <input type="text" name="subject" id="subject" class="mt-1 p-2 bg-gray-700 rounded w-full" required>
                </div>
                <div>
                    <label for="question" class="block text-sm font-medium">Question</label>
                    <textarea name="question" id="question" rows="4" class="mt-1 p-2 bg-gray-700 rounded w-full" required></textarea>
                </div>
                <button type="submit" class="bg-blue-500 text-white p-2 rounded">Submit Ticket</button>
            </form>
        </div>

        <!-- Last 5 Tickets -->
        <div class="bg-gray-800 p-6 mt-8 rounded-md">
            <h2 class="text-xl font-bold mb-4">Your Recent Tickets</h2>

            <?php if (count($tickets) > 0): ?>
                <ul class="space-y-4">
                    <?php foreach ($tickets as $ticket): ?>
                        <li class="bg-gray-700 p-4 rounded">
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                            <p class="text-sm"><?php echo htmlspecialchars($ticket['question']); ?></p>
                            <p class="text-gray-400 text-sm">Submitted on: <?php echo date('F j, Y, g:i a', strtotime($ticket['created_at'])); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-400">You have not submitted any tickets yet.</p>
            <?php endif; ?>
        </div>
    </main>
</body>



</html>
