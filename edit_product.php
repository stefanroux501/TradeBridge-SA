<?php
// 1. Include header and database connection
include 'includes/header.php';
include 'includes/db_connect.php';

// 2. THE BOUNCER (Role-Based Access Control)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Seller' && $_SESSION['role'] !== 'Both')) {
    header("Location: index.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// 3. FETCH THE ITEM DATA
// Check if an ID was passed in the URL (e.g., edit_product.php?id=1)
if (isset($_GET['id'])) {
    $listing_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Security: Fetch the item, but ONLY if the seller_id matches the logged-in user!
    $sql_fetch = "SELECT * FROM listings WHERE listing_id = '$listing_id' AND seller_id = '$seller_id'";
    $result = mysqli_query($conn, $sql_fetch);

    // If the item exists and belongs to them, save it to the $item array
    if (mysqli_num_rows($result) > 0) {
        $item = mysqli_fetch_assoc($result);
    } else {
        // If they try to edit someone else's item or a fake ID, kick them out
        header("Location: seller_dashboard.php");
        exit();
    }
} else {
    // If there is no ID in the URL at all, kick them out
    header("Location: seller_dashboard.php");
    exit();
}
// 4. HANDLE THE FORM SUBMISSION (Save Changes)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Grab the newly typed data from the form
    $new_title = mysqli_real_escape_string($conn, $_POST['title']);
    $new_desc = mysqli_real_escape_string($conn, $_POST['description']);
    $new_price = $_POST['price'];
    $new_status = $_POST['status'];

    // Update the database (Safety check: AND seller_id guarantees they own it)
    $sql_update = "UPDATE listings 
                   SET title = '$new_title', description = '$new_desc', price = '$new_price', status = '$new_status' 
                   WHERE listing_id = '$listing_id' AND seller_id = '$seller_id'";
    
    mysqli_query($conn, $sql_update);

    // Redirect back to the dashboard to see the updated item
    header("Location: seller_dashboard.php");
    exit();
}
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">Edit Listing</h3>
                    
                    <form method="POST" action="edit_product.php?id=<?php echo $item['listing_id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Item Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (R)</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($item['price']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Active" <?php if($item['status'] == 'Active') echo 'selected'; ?>>Active (Visible to buyers)</option>
                                <option value="Inactive" <?php if($item['status'] == 'Inactive') echo 'selected'; ?>>Inactive (Hidden from marketplace)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">Save Changes</button>
                        <a href="seller_dashboard.php" class="btn btn-outline-secondary w-100">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>