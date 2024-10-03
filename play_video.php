<?php
session_start();

// Include database connection file
require 'db.php';

// Check if user is logged in, and handle accordingly (can redirect to login if necessary)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check for the video ID in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare and execute a query to get the video data
    $stmt = $conn->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $video = $result->fetch_assoc();
    } else {
        echo "Video not found.";
        exit;
    }

    // Fetch related videos, excluding the current video
    $related_stmt = $conn->prepare("SELECT * FROM videos WHERE id != ? LIMIT 5");
    $related_stmt->bind_param("i", $id);
    $related_stmt->execute();
    $related_result = $related_stmt->get_result();
} else {
    echo "No video ID specified.";
    exit;
}

$stmt->close();
$related_stmt->close();
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
        <h1 class="text-white text-2xl">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <div class="flex items-center space-x-4">
            <a href="creator_dashboard.php" class="text-gray-400 hover:text-white">Home</a>
            <a href="live_stream.php" class="text-gray-400 hover:text-white">Live Stream</a>
            <a href="upload_video.php" class="text-gray-400 hover:text-white">Upload Video</a>
            <a href="withdraw_rewards.php" class="text-gray-400 hover:text-white">Withdraw Rewards</a>
            <a href="contact_us_creator.php" class="text-gray-400 hover:text-white">Contact Us</a>
            <a href="logout.php" class="text-red-500 hover:text-white">Logout</a>
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
            </div>

            <!-- Related Videos Section -->
            <div>
                <h2 class="text-xl font-bold mb-4">Related Videos</h2>
                <?php
                if ($related_result->num_rows > 0) {
                    while ($related_video = $related_result->fetch_assoc()) {
                ?>
                        <div class="bg-gray-800 p-4 rounded mb-4">
                            <a href="play_video.php?id=<?php echo $related_video['id']; ?>" class="flex items-center">
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

</body>

</html>