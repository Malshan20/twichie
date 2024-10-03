<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.php');
    exit();
}

// Fetch previous live streams for the creator
$creator_id = $_SESSION['user_id'];
$sql = "SELECT * FROM live_streams WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $creator_id);
$stmt->execute();
$result = $stmt->get_result();

$streams = [];
while ($row = $result->fetch_assoc()) {
    $streams[] = $row;
}

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Live Stream</title>
</head>
<body class="bg-gray-900 text-gray-200">
    <nav class="flex items-center justify-between p-4 bg-gray-800">
        <h1 class="text-white text-2xl">Live Stream</h1>
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
        <div class="mb-6">
            <h2 class="text-xl font-bold">Start Live Streaming</h2>
            <div class="mt-4">
                <video id="liveVideo" class="w-full h-64 bg-gray-700" controls></video>
                <button id="startStream" class="mt-2 bg-green-500 text-white p-2 rounded-md">Start Streaming</button>
                <button id="stopStream" class="mt-2 bg-red-500 text-white p-2 rounded-md hidden">Stop Streaming</button>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-bold">Previous Live Streams</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php if (count($streams) > 0): ?>
                    <?php foreach ($streams as $stream): ?>
                        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                            <video class="w-full" controls>
                                <source src="<?php echo htmlspecialchars($stream['video_url']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="p-4">
                                <h3 class="text-lg font-bold text-white"><?php echo htmlspecialchars($stream['title']); ?></h3>
                                <p class="text-gray-400">Date: <?php echo date('Y-m-d H:i:s', strtotime($stream['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-400">No previous streams found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-bold">Schedule a Live Stream</h2>
            <form action="schedule_stream.php" method="POST" class="mt-4">
                <div class="mb-4">
                    <label for="streamTitle" class="block mb-2">Stream Title:</label>
                    <input type="text" name="streamTitle" id="streamTitle" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
                </div>
                <div class="mb-4">
                    <label for="streamDate" class="block mb-2">Stream Date and Time:</label>
                    <input type="datetime-local" name="streamDate" id="streamDate" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
                </div>
                <button type="submit" class="bg-blue-500 text-white p-2 rounded-md">Schedule Stream</button>
            </form>
        </div>
    </main>

    <script>
        let mediaRecorder;
        let liveStream;

        document.getElementById('startStream').addEventListener('click', async () => {
            liveStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            document.getElementById('liveVideo').srcObject = liveStream;

            mediaRecorder = new MediaRecorder(liveStream);
            mediaRecorder.start();

            document.getElementById('startStream').classList.add('hidden');
            document.getElementById('stopStream').classList.remove('hidden');
        });

        document.getElementById('stopStream').addEventListener('click', () => {
            mediaRecorder.stop();
            liveStream.getTracks().forEach(track => track.stop());

            document.getElementById('startStream').classList.remove('hidden');
            document.getElementById('stopStream').classList.add('hidden');
        });
    </script>
</body>
</html>
