<?php
session_start();
if (isset($_SESSION['user_name'])) {
    header("Location: index.php"); // Redirect to dashboard if logged in
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Login</title>
</head>
<body class="bg-gray-900 text-gray-200">
    <div class="flex items-center justify-center h-screen">
        <form action="login_process.php" method="POST" class="bg-gray-800 p-8 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold mb-4">Login</h1>
            <div class="mb-4">
                <label for="email" class="block mb-2">Email:</label>
                <input type="email" name="email" id="email" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
            </div>
            <div class="mb-4">
                <label for="password" class="block mb-2">Password:</label>
                <input type="password" name="password" id="password" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
            </div>
            <div class="mb-4">
                <button type="submit" class="bg-blue-500 text-white p-2 rounded-md w-full">Login as Creator</button>
            </div>
            <div class="mb-4">
                <button type="submit" name="fan_login" class="bg-green-500 text-white p-2 rounded-md w-full">Login as Fan</button>
            </div>
            <div class="mb-4">
                <a href="forgot_password.php" class="text-blue-400">Forgot Password?</a>
            </div>
            <div>
                <p class="text-gray-400">Don't have an account? <a href="signup.php" class="text-blue-400">Sign Up</a></p>
            </div>
        </form>
    </div>
</body>
</html>
