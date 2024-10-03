<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Twitchie</title>
    <script>
        // Fetch videos on page load
        async function fetchVideos() {
            const response = await fetch('fetch_videos.php');
            const videos = await response.json();
            const videosContainer = document.getElementById('videos-container');

            // Limit to the 5 latest videos
            const latestVideos = videos.slice(0, 5);

            latestVideos.forEach(video => {
                const videoElement = document.createElement('div');
                videoElement.className = 'group relative p-4';

                videoElement.innerHTML = `
                    <a href="play_fvideo.php?id=${video.id}" class="block">
                        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg transform transition duration-500 group-hover:scale-105" style="height: 220px; width: 100%;">
                            <img src="${video.thumbnail_url}" alt="${video.title}" class="w-full h-full object-cover">
                            <div class="p-4 absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-90">
                                <h2 class="text-lg font-bold text-white truncate">${video.title}</h2>
                                <div class="flex items-center justify-between text-sm text-gray-300 mt-2">
                                    <span><i class="fas fa-eye mr-1"></i> ${video.views} views</span>
                                    <span><i class="fas fa-heart mr-1"></i> ${video.likes} likes</span>
                                </div>
                                <p class="text-gray-400 mt-1 truncate">${video.description}</p>
                            </div>
                        </div>
                    </a>
                `;
                videosContainer.appendChild(videoElement);
            });
        }

        // Fetch notifications on page load
        async function fetchNotifications() {
            const response = await fetch('fetch_notifications.php');
            const notifications = await response.json();
            const notificationsContainer = document.getElementById('notifications-container');

            if (notifications.length === 0) {
                notificationsContainer.innerHTML = '<p class="text-gray-400">No new notifications</p>';
            } else {
                notifications.forEach(notification => {
                    const notificationElement = document.createElement('div');
                    notificationElement.className = 'p-2 border-b border-gray-700';
                    notificationElement.innerHTML = `
                        <p class="text-white">${notification.message}</p>
                        <small class="text-gray-500">${new Date(notification.created_at).toLocaleString()}</small>
                    `;
                    notificationsContainer.appendChild(notificationElement);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchVideos();
            fetchNotifications();
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

    <!-- Main content -->
    <main class="p-6">
        <h2 class="text-3xl font-bold mb-6 text-white">Featured Videos</h2>

        <!-- Video Container -->
        <div id="videos-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <!-- Videos will be loaded here dynamically -->
        </div>
    </main>
</body>

</html>