<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Sign Up</title>
</head>
<body class="bg-gray-900 text-gray-200">
    <div class="flex items-center justify-center h-screen">
        <form action="signup_process.php" method="POST" class="bg-gray-800 p-8 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold mb-4">Sign Up</h1>
            <div class="mb-4">
                <label for="username" class="block mb-2">Username:</label>
                <input type="text" name="username" id="username" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
            </div>
            <div class="mb-4">
                <label for="email" class="block mb-2">Email:</label>
                <input type="email" name="email" id="email" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
            </div>
            <div class="mb-4">
                <label for="password" class="block mb-2">Password:</label>
                <input type="password" name="password" id="password" required class="bg-gray-700 text-gray-200 p-2 rounded-md w-full">
            </div>
            <div class="mb-4">
                <label class="block mb-2">Sign Up As:</label>
                <div class="flex space-x-4">
                    <label>
                        <input type="radio" name="role" value="creator" required class="mr-2"> Creator
                    </label>
                    <label>
                        <input type="radio" name="role" value="fan" required class="mr-2"> Fan
                    </label>
                </div>
            </div>
            <div class="mb-4">
                <button type="submit" class="bg-blue-500 text-white p-2 rounded-md w-full">Sign Up</button>
            </div>
            <div>
                <p class="text-gray-400">Already have an account? <a href="login.php" class="text-blue-400">Login</a></p>
            </div>
        </form>
    </div>
</body>
</html>
