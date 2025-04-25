<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Cafeteria";
$port = 3307;

$myconnection = mysqli_connect($servername, $username, $password, $dbname, $port);

if (!$myconnection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
