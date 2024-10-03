<?php
session_start();
include 'db.php';  // Include your database configuration

// Check if the user is logged in and is a creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.php');
    exit();
}

$creator_id = $_SESSION['user_id'];
$message = ''; // Message to show errors/successes

// Fetch total earnings for the logged-in creator from the earnings table
$sql = "SELECT SUM(amount) AS total_earnings FROM earnings WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $creator_id);
$stmt->execute();
$result = $stmt->get_result();
$earnings = $result->fetch_assoc()['total_earnings'] ?? 0;
$stmt->close();

// Handle PayPal withdrawal form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paypal_email = $_POST['paypal_email'];
    $withdraw_amount = $_POST['amount'];

    // Validate input
    if (empty($paypal_email) || !filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid PayPal email address.";
    } elseif ($withdraw_amount > $earnings) {
        $message = "You don't have enough earnings for this withdrawal.";
    } elseif ($withdraw_amount <= 0) {
        $message = "Please enter a valid withdrawal amount.";
    } else {
        // Insert withdrawal request into withdrawals table
        $status = 'pending';
        $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, amount, paypal_email, status, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("idss", $creator_id, $withdraw_amount, $paypal_email, $status);

        if ($stmt->execute()) {
            // Update earnings in the earnings table
            $stmt_earnings = $conn->prepare("UPDATE earnings SET amount = amount - ? WHERE user_id = ?");
            $stmt_earnings->bind_param("di", $withdraw_amount, $creator_id);
            $stmt_earnings->execute();
            $stmt_earnings->close();

            $message = '<div class="bg-green-500 p-4 text-white rounded">Withdrawal request submitted successfully.</div>';
        } else {
            $message = '<div class="bg-red-500 p-4 text-white rounded">Failed to submit withdrawal request.</div>';
        }
        $stmt->close();
        // Reload page to reflect changes
        header("Refresh:2");
    }
}

// Fetch creator's withdrawal history
$sql_history = "SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $creator_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
$withdrawals = $result_history->fetch_all(MYSQLI_ASSOC);
$stmt_history->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Withdraw Rewards</title>
</head>

<body class="bg-gray-900 text-gray-200">
    <!-- Navbar -->
    <nav class="flex items-center justify-between p-4 bg-gray-800">
        <h1 class="text-white text-2xl">Withdraw Rewards</h1>
        <div class="flex items-center space-x-4">
            <a href="creator_dashboard.php" class="text-gray-400 hover:text-white">Home</a>
            <a href="live_stream.php" class="text-gray-400 hover:text-white">Live Stream</a>
            <a href="upload_video.php" class="text-gray-400 hover:text-white">Upload Video</a>
            <a href="withdraw_rewards.php" class="text-gray-400 hover:text-white">Withdraw Rewards</a>
            <a href="contact_us_creator.php" class="text-gray-400 hover:text-white">Contact Us</a>
            <a href="logout.php" class="text-red-500 hover:text-white">Logout</a>
        </div>
    </nav>

    <!-- Display Error or Success Messages -->
    <?php if (!empty($message)) : ?>
        <div class="container mx-auto mt-4">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="p-4">
        <div class="bg-gray-800 p-6 rounded-md mb-8">
            <h2 class="text-xl font-bold mb-4">Your Earnings</h2>

            <!-- Display Creator's Earnings -->
            <div class="text-2xl mb-4">
                Total Earnings: <span class="text-green-500">$<?php echo number_format($earnings, 2); ?></span>
            </div>

            <!-- Display Real-Time Day using JS -->
            <div class="mb-4">
                <strong>Current Day:</strong> <span id="currentDay"></span>
            </div>

            <!-- Withdrawal Form -->
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="paypal_email" class="block text-sm">PayPal Email:</label>
                    <input type="email" name="paypal_email" id="paypal_email" class="p-2 w-full rounded bg-gray-700 text-white" required>
                </div>

                <div class="mb-4">
                    <label for="amount" class="block text-sm">Withdraw Amount:</label>
                    <input type="number" name="amount" id="amount" min="0" max="<?php echo $earnings; ?>" step="0.01" class="p-2 w-full rounded bg-gray-700 text-white" required>
                </div>

                <button type="submit" class="bg-blue-500 text-white p-2 rounded-md">
                    Withdraw Now with PayPal
                </button>
            </form>
        </div>

        <!-- Withdrawal History Section -->
        <div class="bg-gray-800 p-6 rounded-md">
            <h2 class="text-xl font-bold mb-4">Withdrawal History</h2>

            <?php if (!empty($withdrawals)) : ?>
                <table class="min-w-full bg-gray-900 rounded-md">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-400">Amount</th>
                            <th class="px-4 py-2 text-left text-gray-400">PayPal Email</th>
                            <th class="px-4 py-2 text-left text-gray-400">Status</th>
                            <th class="px-4 py-2 text-left text-gray-400">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $withdrawal) : ?>
                            <tr class="border-b border-gray-700">
                                <td class="px-4 py-2">$<?php echo number_format($withdrawal['amount'], 2); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($withdrawal['paypal_email']); ?></td>
                                <td class="px-4 py-2">
                                    <?php
                                    if ($withdrawal['status'] == 'pending') {
                                        echo '<span class="text-yellow-500">Pending</span>';
                                    } elseif ($withdrawal['status'] == 'completed') {
                                        echo '<span class="text-green-500">Completed</span>';
                                    } else {
                                        echo '<span class="text-red-500">Failed</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-4 py-2"><?php echo date("F j, Y, g:i a", strtotime($withdrawal['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="text-gray-400">No withdrawal history found.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- JavaScript to Show Current Day Based on User's Location -->
    <script>
        document.getElementById('currentDay').innerText = new Date().toLocaleDateString('en-US', { weekday: 'long' });
    </script>
</body>

</html>
