<?php 
$id = $_GET["userid"]; 
include_once "../connect.php"; 
$sql = " delete from users where user_id = $id"; 
mysqli_query( $myconnection, $sql); 
header("location: users.php");