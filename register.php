<?php
// 1. BACKEND LOGIC (PHP)
include 'includes/db_connect.php'; 

$error_message = ""; 

// Initialize variables so the form starts blank
$first_name = "";
$last_name = "";
$email = "";
$shipping_address = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Save and sanitize the submitted data
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $shipping_address = mysqli_real_escape_string($conn, trim($_POST['shipping_address']));
    
    // EVERYONE is a buyer by default!
    $role = 'Buyer'; 
    
    $password_raw = $_POST['password'];
    $password_confirm = $_POST['confirm_password'];

    if ($password_raw !== $password_confirm) {
        $error_message = "Error: Passwords do not match!";
    } 
    else if (strlen($password_raw) < 8 || !preg_match("#[0-9]+#", $password_raw)) {
        $error_message = "Error: Password must be at least 8 characters long and contain at least one number.";
    } 
    else {
        // Check if email already exists
        $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if(mysqli_num_rows($check_email) > 0) {
            $error_message = "Error: An account with that email already exists. Please log in.";
        } else {
            // Secure it and save to the database
            $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

            // 1. Insert into Users table
            $sql_user = "INSERT INTO users (first_name, last_name, email, password_hash, role) 
                         VALUES ('$first_name', '$last_name', '$email', '$password_hash', '$role')";

            if (mysqli_query($conn, $sql_user)) {
                $new_user_id = mysqli_insert_id($conn);

                // 2. Automatically insert them into the Buyers table with their address
                mysqli_query($conn, "INSERT INTO buyers (buyer_id, shipping_address) VALUES ('$new_user_id', '$shipping_address')");

                // Success! Redirect to login
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Database Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TradeBridge SA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4 fw-bold text-primary">Create an Account</h2>
                    
                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted fw-bold">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted fw-bold">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Password</label>
                            <input type="password" id="pass1" name="password" class="form-control" required>
                            <small class="text-muted">Must be at least 8 characters and contain a number.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Confirm Password</label>
                            <input type="password" id="pass2" name="confirm_password" class="form-control" required>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword()">
                            <label class="form-check-label text-muted" for="showPassword">
                                Show Passwords
                            </label>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold">Shipping Address</label>
                            <textarea name="shipping_address" class="form-control" rows="2" placeholder="Enter full address for faster checkout..." required><?php echo htmlspecialchars($shipping_address); ?></textarea>
                            <div class="form-text">We need this to process your future orders.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fs-5 fw-bold">Create Account</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="login.php" class="text-decoration-none fw-bold">Already have an account? Login here.</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var p1 = document.getElementById("pass1");
    var p2 = document.getElementById("pass2");
    
    if (p1.type === "password") {
        p1.type = "text";
        p2.type = "text";
    } else {
        p1.type = "password";
        p2.type = "password";
    }
}
</script>

</body>
</html>