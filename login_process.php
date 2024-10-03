<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if logging in as a fan
    if (isset($_POST['fan_login'])) {
        $sql = "SELECT * FROM users WHERE email = ? AND role = 'fan'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
    } else {
        $sql = "SELECT * FROM users WHERE email = ? AND role = 'creator'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'creator') {
                header('Location: creator_dashboard.php');
            } else if ($user['role'] === 'fan') {
                header('Location: index.php');
            }
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found!";
    }

    $stmt->close();
}
$conn->close();
?>