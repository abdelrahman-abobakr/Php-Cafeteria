<?php
include_once "connect.php";
$name = "maria_samuel";
$email = "admin@gmail.com";
$password = "admin123"; 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, password, role)
        VALUES ('$name', '$email', '$hashed_password', 'admin')";
if (mysqli_query($connection, $sql)) {
    echo "Admin inserted successfully.";
} else {
    echo "Error: " . mysqli_error($connection);
}
mysqli_close($connection);
?>