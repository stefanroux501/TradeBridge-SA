<?php
include 'includes/header.php';
include 'includes/db_connect.php';

// Gatekeeper: Only Buyers can access checkout
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== "Buyer" && $_SESSION['role'] !== "Both")) {
    header("Location: index.php");
    exit();
}

// Ensure an item ID was actually passed in the URL
if (!isset($_GET['id'])){
    echo "<div class='container mt-5'><div class='alert alert-danger'>Error: No item selected for checkout.</div></div>";
    include 'includes/footer.php';
    exit();
}

// Secure the ID
$display_id = mysqli_real_escape_string($conn, $_GET['id']);

// --- BACKEND LOGIC: PROCESS PAYMENT ---
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $buyer_id = $_SESSION['user_id'];
    
    // NEW: Look up the actual price of the item before making the receipt
    $price_query = mysqli_query($conn, "SELECT price FROM listings WHERE listing_id = '$display_id'");
    $price_row = mysqli_fetch_assoc($price_query);
    $actual_price = $price_row['price'];
    
    // 1. Create the transaction record (Now explicitly saving the amount)
    $sql_transaction = "INSERT INTO transactions (listing_id, buyer_id, amount, escrow_status) VALUES ('$display_id', '$buyer_id', '$actual_price', 'Funds Held')";
    
    // 2. Mark the item as 'Pending' so no one else can buy it
    $sql_update = "UPDATE listings SET status = 'Pending' WHERE listing_id = '$display_id'";
    
    mysqli_query($conn, $sql_transaction);
    mysqli_query($conn, $sql_update);
    
    // Redirect back home after purchase
    header("Location: index.php");
    exit();
}
// --------------------------------------

// Fetch the item details to display the title and price
$sql_fetch = "SELECT * FROM listings WHERE listing_id = '$display_id'";
$result = mysqli_query($conn, $sql_fetch);
$item = mysqli_fetch_assoc($result);

// Fetch the buyer's shipping address (Matching the 'users' table from settings.php)
$current_buyer = $_SESSION['user_id'];
$sql_buyer = "SELECT shipping_address FROM buyers WHERE buyer_id = '$current_buyer'";
$result_buyer = mysqli_query($conn, $sql_buyer);
$buyer = mysqli_fetch_assoc($result_buyer);

?>

<div class="container mt-5 mb-5">
    
    <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($item['title']); ?></h1>
    <h2 class="text-success mb-4">R <?php echo htmlspecialchars($item['price']); ?></h2>
    
    <form method="POST" action="checkout.php?id=<?php echo $display_id; ?>">
        <div class="card border-0 shadow-sm p-4 bg-light">
            
            <h5 class="mb-3 border-bottom pb-2">1. Shipping Details</h5>
            
            <textarea name="shipping_address" class="form-control mb-4" rows="3" placeholder="Enter your full shipping address here..." required><?php echo htmlspecialchars($buyer['shipping_address'] ?? ''); ?></textarea>
            
            <h5 class="mb-3 border-bottom pb-2">2. Payment Information</h5>
            
            <div class="mb-3">
                <label class="form-label text-muted small">Name on Card</label>
                <input type="text" class="form-control" placeholder="e.g. John Doe" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-muted small">Card Number</label>
                <input type="text" class="form-control" placeholder="0000 0000 0000 0000" required>
            </div>
            
            <div class="row mb-4">
                <div class="col-6">
                    <label class="form-label text-muted small">Expiry Date</label>
                    <input type="text" class="form-control" placeholder="MM/YY" required>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted small">CVV</label>
                    <input type="text" class="form-control" placeholder="123" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold">Confirm Payment</button>
        </div>
    </form>

</div>

<?php include 'includes/footer.php'; ?>