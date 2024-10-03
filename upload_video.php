<?php
session_start();
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in and is a creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.php');
    exit();
}

// Fetch categories from the database
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
$categories = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $creator_id = $_SESSION['user_id'];
    $videoTitle = trim($_POST['videoTitle']);
    $videoDescription = trim($_POST['videoDescription']);
    $videoCategory = $_POST['videoCategory'];
    $videoFile = $_FILES['videoFile'];
    $thumbnailFile = $_FILES['thumbnailFile']; // Added thumbnail file handling
    
    // Add this line to get the status of the video
    $videoStatus = $_POST['videoStatus'] ?? 'free'; // Default to 'free' if not set

    // Validate input
    if (empty($videoTitle) || empty($videoDescription) || empty($videoCategory) || empty($videoFile['name']) || empty($thumbnailFile['name'])) {
        $_SESSION['error'] = 'Please fill in all fields, select a video file, and upload a thumbnail.';
        header('Location: upload_video.php');
        exit();
    }

    // Validate category input
    if (empty($videoCategory) || !is_numeric($videoCategory)) {
        $_SESSION['error'] = 'Please select a valid category.';
        header('Location: upload_video.php');
        exit();
    }

    // Define upload directory and file path for video
    $videoTargetDir = "uploads/";
    if (!is_dir($videoTargetDir)) {
        $_SESSION['error'] = 'Video upload directory does not exist.';
        header('Location: upload_video.php');
        exit();
    }

    if (!is_writable($videoTargetDir)) {
        $_SESSION['error'] = 'Video upload directory is not writable.';
        header('Location: upload_video.php');
        exit();
    }

    $videoTargetFile = $videoTargetDir . basename($videoFile["name"]);
    $videoFileType = strtolower(pathinfo($videoTargetFile, PATHINFO_EXTENSION));

    // Check video file type
    if (!in_array($videoFileType, ['mp4', 'avi', 'mov', 'wmv'])) {
        $_SESSION['error'] = 'Invalid video file type. Only MP4, AVI, MOV, and WMV files are allowed.';
        header('Location: upload_video.php');
        exit();
    }

    // Define upload directory and file path for thumbnail
    $thumbnailTargetDir = "uploads/thumbnails/";
    if (!is_dir($thumbnailTargetDir)) {
        $_SESSION['error'] = 'Thumbnail upload directory does not exist.';
        header('Location: upload_video.php');
        exit();
    }

    if (!is_writable($thumbnailTargetDir)) {
        $_SESSION['error'] = 'Thumbnail upload directory is not writable.';
        header('Location: upload_video.php');
        exit();
    }

    $thumbnailTargetFile = $thumbnailTargetDir . basename($thumbnailFile["name"]);
    $thumbnailFileType = strtolower(pathinfo($thumbnailTargetFile, PATHINFO_EXTENSION));

    // Check thumbnail file type
    if (!in_array($thumbnailFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $_SESSION['error'] = 'Invalid thumbnail file type. Only JPG, JPEG, PNG, and GIF files are allowed.';
        header('Location: upload_video.php');
        exit();
    }

    // Move uploaded video file to the server
    if (move_uploaded_file($videoFile["tmp_name"], $videoTargetFile)) {
        // Move uploaded thumbnail file to the server
        if (move_uploaded_file($thumbnailFile["tmp_name"], $thumbnailTargetFile)) {
            // Prepare SQL statement to insert video information
            $sql = "INSERT INTO videos (user_id, title, description, category, video_url, thumbnail_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // Check for SQL errors
            if ($stmt === false) {
                $_SESSION['error'] = 'SQL Error: ' . $conn->error;
                header('Location: upload_video.php');
                exit();
            }

            // Bind parameters (assuming category is an integer)
            $stmt->bind_param("ississs", $creator_id, $videoTitle, $videoDescription, $videoCategory, $videoTargetFile, $thumbnailTargetFile, $videoStatus);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Video uploaded successfully!';
            } else {
                $_SESSION['error'] = 'Error uploading video. Please try again.';
            }

            $stmt->close();
        } else {
            $_SESSION['error'] = 'Error moving uploaded thumbnail file.';
        }
    } else {
        $_SESSION['error'] = 'Error moving uploaded video file.';
    }

    $conn->close();

    // Redirect back to the upload page
    header('Location: upload_video.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Upload Video</title>
</head>

<body class="bg-gray-900 text-gray-200">
    <nav class="flex items-center justify-between p-4 bg-gray-800">
        <h1 class="text-white text-2xl">Upload Video</h1>
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
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-500 text-white p-4 mb-4 rounded-md"><?php echo $_SESSION['success']; ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="bg-red-500 text-white p-4 mb-4 rounded-md"><?php echo $_SESSION['error']; ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h2 class="text-xl font-bold mb-4">Upload a New Video</h2>
        <form action="upload_video.php" method="POST" enctype="multipart/form-data" class="bg-gray-800 p-6 rounded-md">
            <div class="mb-4">
                <label for="videoTitle" class="block mb-2">Video Title:</label>
                <input type="text" name="videoTitle" id="videoTitle" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
            </div>
            <div class="mb-4">
                <label for="videoDescription" class="block mb-2">Video Description:</label>
                <textarea name="videoDescription" id="videoDescription" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full" rows="4"></textarea>
            </div>
            <div class="mb-4">
                <label for="videoCategory" class="block mb-2">Video Category:</label>
                <select name="videoCategory" id="videoCategory" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Video Status:</label>
                <div class="flex items-center">
                    <label class="mr-4">
                        <input type="radio" name="videoStatus" value="free" checked class="mr-1">
                        Free
                    </label>
                    <label>
                        <input type="radio" name="videoStatus" value="premium" class="mr-1">
                        Premium
                    </label>
                </div>
            </div>
            <div class="mb-4">
                <label for="videoFile" class="block mb-2">Upload Video File:</label>
                <input type="file" name="videoFile" id="videoFile" accept="video/*" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
            </div>
            <div class="mb-4">
                <label for="thumbnailFile" class="block mb-2">Upload Thumbnail File:</label>
                <input type="file" name="thumbnailFile" id="thumbnailFile" accept="" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
            </div>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded-md">Upload Video</button>
        </form>
    </main>
</body>

</html>