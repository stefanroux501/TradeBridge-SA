<?php
// 1. Include header and database connection
include 'includes/header.php';
include 'includes/db_connect.php';

// 2. THE BOUNCER (Role-Based Access Control)
// If they aren't logged in, or if their role is NOT 'Seller', kick them to the homepage
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Seller' && $_SESSION['role'] !== 'Both')) {
    header("Location: index.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// --- SELLER ACTIONS HANDLER ---
// Check if the seller clicked an action button
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $target_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Security: Grab the logged-in seller's ID so they can't delete someone else's items!
    $current_seller_id = $_SESSION['user_id'];

    // ACTION: Delete a listing
    if ($action == 'delete') {
        // We add AND status = 'Active' so they physically cannot delete items tied to transactions
        $sql_delete = "DELETE FROM listings WHERE listing_id = '$target_id' AND seller_id = '$current_seller_id' AND status = 'Active'";
        mysqli_query($conn, $sql_delete);
    }

    // Redirect back to clean the URL
    header("Location: seller_dashboard.php");
    exit();
}
// ------------------------------
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Inventory Dashboard</h2>
        <a href="add_product.php" class="btn btn-success">+ Add New Item</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Item Title</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 3. Fetch only THIS seller's items from the database
                    $sql = "SELECT * FROM listings WHERE seller_id = '$seller_id' ORDER BY created_at DESC";
                    $result = mysqli_query($conn, $sql);

                    // 4. Check if they have any items
                    if (mysqli_num_rows($result) > 0) {
                        // Loop through each item and create a table row
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($row['title']) . "</strong></td>";
                            echo "<td>R " . htmlspecialchars($row['price']) . "</td>";
                            
                            // Color-code the status badge
                            $badge_color = ($row['status'] == 'Active') ? 'bg-success' : 'bg-secondary';
                            echo "<td><span class='badge {$badge_color}'>" . htmlspecialchars($row['status']) . "</span></td>";
                            
                            echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                            // Action Buttons: Edit and Delete
                            // Action Buttons: Edit and Delete
                            echo "<td>";
                            echo "<a href='edit_product.php?id=" . $row['listing_id'] . "' class='btn btn-sm btn-outline-primary me-2'>Edit</a> ";
                            
                            // Only show the Delete button if the item is still 'Active'
                            if ($row['status'] == 'Active') {
                                echo "<a href='seller_dashboard.php?action=delete&id=" . $row['listing_id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to completely delete this listing?\");'>Delete</a>";
                            } else {
                                // If it is Pending or Sold, just show a disabled badge
                                echo "<span class='badge bg-light text-muted border'>Locked</span>";
                            }
                            echo "</td>";
                        }
                    } else {
                        // If they haven't uploaded anything yet
                        echo "<tr><td colspan='5' class='text-center py-4'>You haven't listed any items for sale yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>