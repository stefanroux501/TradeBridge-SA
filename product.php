<?php
include 'includes/header.php';
include 'includes/db_connect.php';

// 1. Check if an ID was passed in the URL
if (!isset($_GET['id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Product not found.</div></div>";
    include 'includes/footer.php';
    exit();
}

// 2. Clean the ID for security and fetch the item
$listing_id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT listings.*, users.first_name, users.last_name 
        FROM listings 
        JOIN users ON listings.seller_id = users.user_id 
        WHERE listing_id = '$listing_id'";
$result = mysqli_query($conn, $sql);

// 3. Check if the item actually exists in the database
if (mysqli_num_rows($result) > 0) {
    $item = mysqli_fetch_assoc($result);
} else {
    echo "<div class='container mt-5'><div class='alert alert-danger'>This item no longer exists.</div></div>";
    exit();
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <?php 
                // SAFETY CHECK: Use the real image if it exists, otherwise use the fallback placeholder
                $image_path = !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'assets/images/placeholder.png'; 
            ?>
            <img src="<?php echo $image_path; ?>" class="img-fluid rounded shadow-sm" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width: 100%; height: 400px; object-fit: cover;">
        </div>

        <div class="col-md-6">
            <h1 class="display-5 text-dark fw-bold"><?php echo htmlspecialchars($item['title']); ?></h1>
            <h2 class="text-success mb-4">R <?php echo htmlspecialchars($item['price']); ?></h2>
            
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body bg-light">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                </div>
            </div>

            <p class="text-muted">
                <strong>Seller:</strong> <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?><br>
                <strong>Status:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($item['status']); ?></span>
            </p>

            <?php if(isset($_SESSION['user_id']) && ($_SESSION['role'] == 'Buyer' || $_SESSION['role'] == 'Both')): ?>
                
                <?php if($_SESSION['user_id'] != $item['seller_id']): ?>
                    <a href="checkout.php?id=<?php echo $item['listing_id']; ?>" class="btn btn-primary btn-lg w-100 mt-3 shadow-sm">Buy Now</a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100 mt-3 shadow-sm" disabled>You cannot buy your own item</button>
                <?php endif; ?>

            <?php elseif(!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-secondary btn-lg w-100 mt-3">Log in to Purchase</a>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>