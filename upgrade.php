<?php
session_start();
include 'includes/db_connect.php';

// Gatekeeper: Only logged-in Buyers should be running this script
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Buyer') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Add them to the Sellers table
$insert_seller = "INSERT INTO sellers (seller_id) VALUES ('$user_id')";

// 2. Update their main User profile to 'Both' so the system knows they have dual-access
$update_role = "UPDATE users SET role = 'Both' WHERE user_id = '$user_id'";

// Execute both queries
if (mysqli_query($conn, $insert_seller) && mysqli_query($conn, $update_role)) {
    
    // 3. Update the active session instantly so they don't have to log out and log back in
    $_SESSION['role'] = 'Both';
    
    // 4. Send them straight to their new Seller Dashboard!
    header("Location: seller_dashboard.php");
    exit();

} else {
    // If something goes wrong, print the error
    echo "Database Error: " . mysqli_error($conn);
    exit();
}
?>