<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Cafeteria";
$port = 8111;

$connection = mysqli_connect($servername, $username, $password, $dbname, $port);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
