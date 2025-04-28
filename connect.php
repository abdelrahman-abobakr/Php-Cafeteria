<?php
    $serverName = "localhost";
    $username = "root";
    $pass = "";
    $databaseName = "cafeteria";

    $connection = mysqli_connect($serverName, $username, $pass,$databaseName);

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>
