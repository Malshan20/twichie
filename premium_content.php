<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user details to check for paid status
$user_id = $_SESSION['user_id'];
$sql = "SELECT paid_status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If the user is not a paid user, redirect them
if ($user['paid_status'] !== 'paid') {
    header('Location: more_video.php'); // Redirect to a payment/upgrade page
    exit();
}

// Fetch premium videos
$sql_videos = "SELECT id, title, thumbnail_url, video_url, views, likes, created_at 
               FROM videos 
               WHERE status = 'premium' 
               ORDER BY created_at DESC";
$result_videos = $conn->query($sql_videos);

// Store videos in an array
$premium_videos = [];
while ($row = $result_videos->fetch_assoc()) {
    $premium_videos[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Content - Twitchie</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <style>
        /* Custom styles to make the video cards cute and stylish */
        .video-card {
            transition: transform 0.3s ease;
        }

        .video-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
    <script>
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        // Disable key combinations to open developer tools
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === 'u' || e.key === 'i' || e.key === 'j' || e.key === 's' || e.key === 'c')) {
                e.preventDefault();
            }
        });

        // Disable F12 (Inspect Element)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12') {
                e.preventDefault();
            }
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
            <a href="premium_content.php" class="text-gray-400 hover:text-white mr-2">More Videos |</a>
            <?php if (isset($_SESSION['user_name'])): ?>
                <span class="text-gray-400">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a href="logout.php" class="text-red-400 hover:text-white">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-400 hover:text-white">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="p-6">
        <h2 class="text-3xl font-bold mb-6">Exclusive Premium Videos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (count($premium_videos) > 0): ?>
                <?php foreach ($premium_videos as $video): ?>
                    <div class="video-card bg-gray-800 rounded-lg overflow-hidden shadow-lg animate__animated animate__fadeInUp">
                        <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="text-xl font-bold text-white truncate"><?php echo htmlspecialchars($video['title']); ?></h3>
                            <p class="text-gray-400 mt-2">Views: <?php echo htmlspecialchars($video['views']); ?></p>
                            <p class="text-gray-400">Likes: <?php echo htmlspecialchars($video['likes']); ?></p>
                            <p class="text-gray-400">Uploaded on: <?php echo date('Y-m-d', strtotime($video['created_at'])); ?></p>
                            <a href="play_fvideo.php?id=<?php echo htmlspecialchars($video['id']); ?>" class="mt-3 block text-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded transition">Watch Video</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-400">No premium videos available yet.</p>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>