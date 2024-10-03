<?php
session_start();
include 'db.php'; // Include your DB connection here

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Help & Support</title>
    <script>
        // Submit the help form via AJAX
        async function submitHelpForm(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('help-form'));

            const response = await fetch('submit_help.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.text();

            if (result === 'success') {
                alert('Your request has been submitted!');
                document.getElementById('help-form').reset();
                loadChatMessages(); // Load updated chat messages
            } else {
                alert('There was an error submitting your request.');
            }
        }

        // Load chat messages via AJAX
        async function loadChatMessages() {
            const response = await fetch('load_chat.php');
            const messages = await response.json();
            const chatContainer = document.getElementById('chat-container');
            chatContainer.innerHTML = ''; // Clear chat

            messages.forEach(message => {
                const messageElement = document.createElement('div');
                messageElement.className = 'p-2 bg-gray-800 rounded-lg my-2';

                messageElement.innerHTML = `
                    <div>
                        <strong class="text-blue-400">${message.name}</strong>
                        <p class="text-white">${message.message}</p>
                        ${message.admin_response ? `<p class="text-green-400">Support: ${message.admin_response}</p>` : ''}
                    </div>
                `;
                chatContainer.appendChild(messageElement);
            });
        }

        // Load chat messages every 5 seconds for real-time updates
        setInterval(loadChatMessages, 5000);

        // Load chat messages when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            loadChatMessages();
        });
    </script>
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
            <a href="premium_content.php" class="text-gray-400 hover:text-white mr-2">More Videos </a>
            <a href="help.php" class="text-gray-400 hover:text-white mr-2">Help |</a>

            <?php if (isset($_SESSION['user_name'])): ?>
                <span class="text-gray-400">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a href="logout.php" class="text-red-400 hover:text-white">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-400 hover:text-white">Login</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-white">Help & Support</h1>

        <!-- Help Form -->
        <form id="help-form" class="mb-6" onsubmit="submitHelpForm(event)">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-400">Your Name</label>
                <input type="text" id="name" name="name" required class="mt-1 p-2 w-full bg-gray-800 text-white rounded-lg border border-gray-700">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-400">Your Email</label>
                <input type="email" id="email" name="email" required class="mt-1 p-2 w-full bg-gray-800 text-white rounded-lg border border-gray-700">
            </div>
            <div class="mb-4">
                <label for="message" class="block text-sm font-medium text-gray-400">Your Message</label>
                <textarea id="message" name="message" rows="4" required class="mt-1 p-2 w-full bg-gray-800 text-white rounded-lg border border-gray-700"></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Submit</button>
        </form>

        <!-- Live Chat Area -->
        <h2 class="text-2xl font-bold mb-4 text-white">Live Chat</h2>
        <div id="chat-container" class="p-4 bg-gray-700 rounded-lg h-64 overflow-y-scroll">
            <!-- Chat messages will be dynamically loaded here -->
        </div>
    </div>
</body>

</html>