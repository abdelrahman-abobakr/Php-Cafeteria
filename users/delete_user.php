<?php 
session_start();
if($_SESSION['user_role']!="admin")
{
    header("Location: ../unauth.php"); 
    exit();
}
$id = $_GET["userid"]; 
include_once "../connect.php"; 
$sql = " delete from users where user_id = $id"; 
mysqli_query( $connection, $sql); 
header("location: users.php");