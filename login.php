<?php
// 1. BACKEND LOGIC
// Start the session IMMEDIATELY so we can save the user's login state
session_start();
include 'includes/db_connect.php';

$error_message = ""; // Variable to hold login errors

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Find the user by their email
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    // If the email exists in the database
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify the password against the hash
        // Verify password
    if (password_verify($password, $user['password_hash'])) {
        
        // --- NEW BAN CHECK ---
        if ($user['account_status'] == 'Banned') {
            // If they are banned, reject the login and throw an error
            $error_message = "Your account has been suspended by an administrator.";
        } else {
            // If they are 'Active', proceed with logging them in
            // If they are 'Active', proceed with logging them in
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name']; // <-- WE ADDED HER NAME!

            // ROLE-BASED ACCESS CONTROL (RBAC) ROUTING
            if ($user['role'] == 'Admin') {
                header("Location: admin.php");
            } else if ($user['role'] == 'Seller') {
                header("Location: seller_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }
        // ---------------------
        
    } else {
        $error_message = "Incorrect email or password.";
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TradeBridge SA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Welcome Back</h2>
                    
                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="register.php" class="text-decoration-none">Don't have an account? Register here.</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>