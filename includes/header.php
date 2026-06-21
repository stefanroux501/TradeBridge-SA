<?php
// Start the session only if one hasn't already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradeBridge SA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">

        <a class="navbar-brand" href="index.php">TradeBridge SA</a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto">

                <?php if (isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <span class="nav-link text-light">
                            Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
                        </span>
                    </li>

                    <?php if ($_SESSION['role'] == 'Seller' || $_SESSION['role'] == 'Both'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="seller_dashboard.php">My Dashboard</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="add_product.php">Sell an Item</a>
                        </li>
                    
                    <?php elseif ($_SESSION['role'] == 'Buyer'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold" href="upgrade.php">Become a Seller</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Admin Panel</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">Account Settings</a>
                    </li>

                    <li class="nav-item d-flex align-items-center ms-3 me-3">
                        <button id="darkModeToggle"
                                type="button"
                                class="btn btn-sm btn-outline-light">
                            🌙 Dark Mode
                        </button>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white ms-2 px-3"
                           href="logout.php">
                            Logout
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white ms-2 px-3"
                           href="register.php">
                            Register
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/script.js"></script>
</body>
</html>