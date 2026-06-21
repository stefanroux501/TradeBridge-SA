<?php
// 1. Define the database credentials
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "tradebridge_sa";

// 2. Create the connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// 3. Check if the connection actually works
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>