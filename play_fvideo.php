<?php
session_start();

// Include database connection file
require 'db.php';

// Check if user is logged in, and handle accordingly
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check for the video ID in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare and execute a query to get the video data along with creator info
    $stmt = $conn->prepare("SELECT videos.*, users.username AS creator_name, users.id AS user_id 
                            FROM videos 
                            JOIN users ON videos.user_id = users.id 
                            WHERE videos.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $video = $result->fetch_assoc();
        $creator_id = $video['user_id'];
    } else {
        echo "Video not found.";
        exit;
    }

    // Fetch related videos, excluding the current video
    $related_stmt = $conn->prepare("SELECT * FROM videos WHERE id != ? LIMIT 5");
    $related_stmt->bind_param("i", $id);
    $related_stmt->execute();
    $related_result = $related_stmt->get_result();

    // Check if the current user is already following the creator
    $follow_stmt = $conn->prepare("SELECT * FROM followers WHERE user_id = ? AND creator_id = ?");
    $follow_stmt->bind_param("ii", $_SESSION['user_id'], $creator_id);
    $follow_stmt->execute();
    $is_following = $follow_stmt->get_result()->num_rows > 0;

} else {
    echo "No video ID specified.";
    exit;
}

$stmt->close();
$related_stmt->close();
$follow_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
</head>

<body class="bg-gray-900 text-white">

    <!-- Include Navbar -->
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
            <?php else: ?>
                <a href="login.php" class="text-gray-400 hover:text-white">Login</a>
            <?php endif; ?>
            <a href="logout.php" class="text-red-400 hover:text-white">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto my-10">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Video Section -->
            <div class="lg:col-span-3">
                <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($video['title']); ?></h1>
                <video controls class="w-full rounded" style="height: 500px;">
                    <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <p class="mt-4"><?php echo htmlspecialchars($video['description']); ?></p>

                <!-- Creator Name and Follow Button -->
                <div class="mt-4 flex items-center">
                    <span class="text-lg font-semibold">Created by: <?php echo htmlspecialchars($video['creator_name']); ?></span>
                    <button id="follow-btn" class="ml-4 px-4 py-2 text-white rounded-lg <?php echo $is_following ? 'bg-green-500' : 'bg-blue-500'; ?>">
                        <?php echo $is_following ? 'Following' : 'Follow'; ?>
                    </button>
                </div>
            </div>

            <!-- Related Videos Section -->
            <div>
                <h2 class="text-xl font-bold mb-4">Related Videos</h2>
                <?php
                if ($related_result->num_rows > 0) {
                    while ($related_video = $related_result->fetch_assoc()) {
                ?>
                        <div class="bg-gray-800 p-4 rounded mb-4">
                            <a href="play_fvideo.php?id=<?php echo $related_video['id']; ?>" class="flex items-center">
                                <img src="<?php echo htmlspecialchars($related_video['thumbnail_url']); ?>" alt="Thumbnail" class="w-16 h-16 object-cover rounded mr-4">
                                <div>
                                    <h3 class="text-lg"><?php echo htmlspecialchars($related_video['title']); ?></h3>
                                    <p class="text-sm text-gray-400">Views: <?php echo $related_video['views']; ?></p>
                                </div>
                            </a>
                        </div>
                <?php
                    }
                } else {
                    echo "<p>No related videos found.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        const followBtn = document.getElementById('follow-btn');

        followBtn.addEventListener('click', function() {
            const isFollowing = followBtn.textContent.trim() === 'Following';
            const action = isFollowing ? 'unfollow' : 'follow';

            // Send AJAX request to follow or unfollow the creator
            fetch('follow_creator.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    creator_id: <?php echo $creator_id; ?>,
                    user_id: <?php echo $_SESSION['user_id']; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (action === 'follow') {
                        followBtn.textContent = 'Following';
                        followBtn.classList.remove('bg-blue-500');
                        followBtn.classList.add('bg-green-500');
                    } else {
                        followBtn.textContent = 'Follow';
                        followBtn.classList.remove('bg-green-500');
                        followBtn.classList.add('bg-blue-500');
                    }
                } else {
                    alert('An error occurred: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>

</html>
