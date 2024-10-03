<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.php');
    exit();
}

// Fetch creator's uploaded videos
$creator_id = $_SESSION['user_id'];
$sql = "SELECT v.id, v.title, v.thumbnail_url, v.video_url, v.views, v.likes, v.created_at 
        FROM videos v 
        WHERE v.user_id = ? 
        ORDER BY v.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $creator_id);
$stmt->execute();
$result = $stmt->get_result();

$videos = [];
while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}

// Fetch comments from comments table
$sql2 = "SELECT c.content, c.created_at 
        FROM comments c 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $creator_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$comments = [];
while ($row = $result2->fetch_assoc()) {
    $comments[] = $row;
}

// Fetch followers count for the creator
$sql_followers = "SELECT COUNT(*) AS follower_count FROM followers WHERE creator_id = ?";
$stmt_followers = $conn->prepare($sql_followers);
$stmt_followers->bind_param("i", $creator_id);
$stmt_followers->execute();
$follower_count_result = $stmt_followers->get_result();
$follower_count = $follower_count_result->fetch_assoc()['follower_count'];
$stmt_followers->close();

// Handle video deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_video_id'])) {
    $video_id = $_POST['delete_video_id'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Step 1: Delete associated comments for this video
        $delete_comments_sql = "DELETE FROM comments WHERE video_id = ?";
        $delete_comments_stmt = $conn->prepare($delete_comments_sql);
        $delete_comments_stmt->bind_param("i", $video_id);
        $delete_comments_stmt->execute();

        // Step 2: Delete the video itself
        $delete_video_sql = "DELETE FROM videos WHERE id = ? AND user_id = ?";
        $delete_video_stmt = $conn->prepare($delete_video_sql);
        $delete_video_stmt->bind_param("ii", $video_id, $creator_id);
        $delete_video_stmt->execute();

        // Commit the transaction
        $conn->commit();
        header('Location: creator_dashboard.php');
        exit();
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        echo "Failed to delete video: " . $e->getMessage();
    }
}

$stmt->close();
$stmt2->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Creator Dashboard</title>
</head>
<body class="bg-gray-900 text-gray-200">
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

    <main class="p-4">

        <div>
            <h1 class="text-3xl font-bold">Followers: <?php echo $follower_count; ?></h1>
        </div>

        <h2 class="text-2xl font-bold mb-4">Your Uploaded Videos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <?php if (count($videos) > 0): ?>
                <?php foreach ($videos as $video): ?>
                    <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                        <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="text-xl font-bold text-white"><?php echo htmlspecialchars($video['title']); ?></h3>
                            <p class="text-gray-400">Views: <?php echo htmlspecialchars($video['views']); ?></p>
                            <p class="text-gray-400">Likes: <?php echo htmlspecialchars($video['likes']); ?></p>
                            <p class="text-gray-400">Uploaded on: <?php echo date('Y-m-d', strtotime($video['created_at'])); ?></p>
                        </div>
                        <div class="flex justify-between items-center px-4 pb-4">
                            <a href="play_video.php?id=<?php echo htmlspecialchars($video['id']); ?>" class="text-blue-400 hover:underline">Watch Video</a>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                <input type="hidden" name="delete_video_id" value="<?php echo htmlspecialchars($video['id']); ?>">
                                <button type="submit" class="text-red-400 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-400">No videos uploaded yet.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
