<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

require_once 'connect.php';

if (!isset($user_id) || empty($user_id)) {
    die('No user ID found in session');
}

$query = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($myconnection, $query);

if (!$result) {
    die('Query Error: ' . mysqli_error($myconnection));
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .user-box {
            position: absolute;
            top: 20px;
            right: 20px;
            text-align: center;
        }
        .user-box img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #0d6efd;
        }
        .user-box p {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>

<div class="user-box">
    <img src="uploads/<?= htmlspecialchars($user['profile_image'] ?? 'default.jpg') ?>" alt="User Photo">
    <p><?= htmlspecialchars($user_name) ?></p>
</div>

</body>
</html>
