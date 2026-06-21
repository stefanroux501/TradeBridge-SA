<?php
session_start();
include 'includes/db_connect.php';

// Gatekeeper
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// --- BACKEND LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    
    // NEW: Sanitize the single shipping address input
    $shipping_address = mysqli_real_escape_string($conn, trim($_POST['shipping_address']));
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Update the profile information AND the single address column
    $update_sql = "UPDATE users 
                   SET first_name='$first_name', 
                       last_name='$last_name', 
                       email='$email',
                       shipping_address='$shipping_address'
                   WHERE user_id='$user_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['first_name'] = $first_name;
        $success_message = "Account details and address updated successfully.";

        // Password logic
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $pass_sql = "UPDATE users SET password_hash='$hashed_password' WHERE user_id='$user_id'";
                if (mysqli_query($conn, $pass_sql)) {
                    $success_message = "Account details, address, and password updated successfully.";
                } else {
                    $error_message = "Error updating password. Please try again.";
                }
            } else {
                $error_message = "Passwords do not match. Profile details were saved, but password was not changed.";
            }
        }
    } else {
        $error_message = "Error updating profile details: " . mysqli_error($conn);
    }
}
// ----------------------------------------------

// Fetch user data
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="index.php" class="btn btn-outline-secondary mb-3">&larr; Back to Dashboard</a>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white p-4">
                    <h3 class="mb-0">Account Settings</h3>
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="settings.php">
                        <h5 class="border-bottom pb-2 mb-3">Personal Details</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3 mt-5">Shipping Address</h5>
                        <div class="mb-3">
                            <label class="form-label">Full Delivery Address</label>
                            <textarea name="shipping_address" class="form-control" rows="3" placeholder="Enter your full street address, city, and postal code"><?php echo htmlspecialchars($user['shipping_address'] ?? ''); ?></textarea>
                            <div class="form-text">This will automatically fill in during checkout.</div>
                        </div>
                        <h5 class="border-bottom pb-2 mb-3 mt-5">Change Password</h5>
                        <p class="text-muted small mb-3">Leave these blank if you do not want to change your password.</p>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Save Changes</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>