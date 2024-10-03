<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

// You can modify this to fetch more premium videos if needed
$premium_video_title = "Premium Video Access";
$premium_video_price = 5.00;  // The price for premium videos

// PayPal client ID (sandbox or live, depending on your environment)
$paypalClientID = "AXMh5RGo61jXruCVBBnIqJHSRKZ1OYtPCZm5YOpBDeWngJSo1aj6DsbErckJxQIhIkVG3cToEYBUEiS2";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Video Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <style>
        .premium-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-200">
    <!-- Navigation Bar -->
    <nav class="flex items-center justify-between p-4 bg-gray-800">
        <div class="flex items-center space-x-4">
            <h1 class="text-white text-2xl">Twitchie</h1>
            <a href="index.php" class="text-gray-400 hover:text-white">Home</a>
            <div class="relative">
                <input type="text" placeholder="Search..." class="bg-gray-700 text-gray-200 p-2 rounded-md">
            </div>
            <div class="relative">
                <button class="text-gray-400 hover:text-white" onclick="document.getElementById('notification-dropdown').classList.toggle('hidden')">
                    Notifications
                </button>
                <div id="notification-dropdown" class="absolute right-0 bg-gray-800 rounded-md p-4 shadow-lg hidden" style="width: 250px;">
                    <div id="notifications-container">
                        <!-- Notifications will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
        <div>
            <a href="premium_content.php" class="text-gray-400 hover:text-white mr-2">More Videos |</a>
            <?php if (isset($_SESSION['user_name'])): ?>
                <span class="text-gray-400">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a href="logout.php" class="text-red-400 hover:text-white">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-400 hover:text-white">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Display Error or Success Messages -->
    <?php if (!empty($message)) : ?>
        <div class="container mx-auto mt-4">
            <div class="bg-red-500 text-white p-4 rounded-md">
                <?php echo $message; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-8">
        <div class="premium-card p-10 rounded-lg text-center shadow-lg">
            <h2 class="text-3xl font-bold mb-4 text-white"><?php echo $premium_video_title; ?></h2>
            <p class="text-lg mb-6 text-gray-100">
                Access our premium content for just $<?php echo number_format($premium_video_price, 2); ?>.
                Unlock exclusive videos!
            </p>

            <!-- PayPal Button Container -->
            <div id="paypal-button-container" class="my-8"></div>

            <div class="mt-6">
                <a href="index.php" class="text-blue-500 hover:underline">Go Back to Home</a>
            </div>
        </div>
    </main>

    <!-- PayPal SDK Script -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalClientID; ?>&currency=USD"></script>

    <script>
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'blue',
                layout: 'vertical',
                label: 'pay',
            },
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $premium_video_price; ?>'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Transaction successful
                    alert('Transaction completed by ' + details.payer.name.given_name);

                    // Send AJAX request to update user's paid status
                    fetch('update_payment_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({user_id: '<?php echo $_SESSION['user_id']; ?>'})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = "premium_content.php";
                        } else {
                            alert('There was an error updating your payment status.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            },
            onError: function(err) {
                // Error during the transaction
                console.error("An error occurred:", err);
                alert('An error occurred during the transaction.');
            }
        }).render('#paypal-button-container');
    </script>
</body>

</html>
