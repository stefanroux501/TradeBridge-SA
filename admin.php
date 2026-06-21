<?php
// 1. Pull in your standard layout and database connection
include 'includes/header.php';
include 'includes/db_connect.php';

// 2. The RBAC Gatekeeper Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    // If they aren't logged in, OR if their role is anything other than 'Admin', kick them out
    header("Location: index.php");
    exit();
}

// --- ADMINISTRATIVE ACTIONS HANDLER ---
// Check if an admin clicked an action button that passed parameters into the URL
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $target_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // ACTION 1: Verify a Seller's Identity
    if ($action == 'verify_seller') {
        $sql_verify = "UPDATE sellers SET is_verified = 1 WHERE seller_id = '$target_id'";
        mysqli_query($conn, $sql_verify);
    }
    
    // ACTION 2: Release Escrow Funds (Resolve Dispute / Payout)
    else if ($action == 'release_escrow') {
        $sql_release = "UPDATE transactions SET escrow_status = 'Funds Released' WHERE transaction_id = '$target_id'";
        mysqli_query($conn, $sql_release);
        
        // Optionally update the linked listing status to 'Sold'
        // Find the listing ID for this transaction first
        $tx_query = mysqli_query($conn, "SELECT listing_id FROM transactions WHERE transaction_id = '$target_id'");
        if ($tx_row = mysqli_fetch_assoc($tx_query)) {
            $sold_listing_id = $tx_row['listing_id'];
            mysqli_query($conn, "UPDATE listings SET status = 'Sold' WHERE listing_id = '$sold_listing_id'");
        }
    }

    // ACTION 3: Delete a Rule-Breaking Listing
    else if ($action == 'delete_listing') {
        // Because the Admin has ultimate power, this wipes the listing completely
        $sql_delete_listing = "DELETE FROM listings WHERE listing_id = '$target_id'";
        mysqli_query($conn, $sql_delete_listing);
    }

    // ACTION 4: Ban a Problematic User
    else if ($action == 'ban_user') {
        $sql_ban = "UPDATE users SET account_status = 'Banned' WHERE user_id = '$target_id'";
        mysqli_query($conn, $sql_ban);
    }
    
    // ACTION 5: Forgive / Unban a User
    else if ($action == 'unban_user') {
        $sql_unban = "UPDATE users SET account_status = 'Active' WHERE user_id = '$target_id'";
        mysqli_query($conn, $sql_unban);
    }

    // Redirect back to clean the URL parameters so refreshing doesn't trigger the action twice
    header("Location: admin.php");
    exit();
}
// --------------------------------------

// 1. Fetch all registered users alongside their verification and account status
$sql_users = "SELECT users.user_id, users.first_name, users.last_name, users.email, users.role, users.account_status, sellers.is_verified 
              FROM users 
              LEFT JOIN sellers ON users.user_id = sellers.seller_id 
              ORDER BY users.created_at DESC";
$result_users = mysqli_query($conn, $sql_users);

// 2. Fetch all listings alongside the seller's name using a JOIN
$sql_listings = "SELECT listings.listing_id, listings.title, listings.price, listings.status, users.first_name, users.last_name 
                 FROM listings 
                 JOIN users ON listings.seller_id = users.user_id 
                 ORDER BY listings.created_at DESC";
$result_listings = mysqli_query($conn, $sql_listings);

// 3. Fetch all transactions to monitor the Escrow system
$sql_transactions = "SELECT transactions.transaction_id, transactions.amount, transactions.escrow_status, listings.title, users.first_name AS buyer_name
                     FROM transactions 
                     JOIN listings ON transactions.listing_id = listings.listing_id
                     JOIN users ON transactions.buyer_id = users.user_id
                     ORDER BY transactions.transaction_date DESC";
$result_transactions = mysqli_query($conn, $sql_transactions);
?>

<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">System Administrator Dashboard</h1>
            <p class="text-muted">Monitor platform activity, manage user accounts, and oversee secure escrow flows.</p>
        </div>
    </div>

   <div class="card border-0 shadow-sm mb-5 bg-light">
        <div class="card-body p-4">
            <h4 class="card-title mb-3 border-bottom pb-2">Registered Users</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Verification Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($result_users)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                
                                <td>
                                    <?php if ($user['role'] == 'Seller' || $user['role'] == 'Both'): ?>
                                        <?php echo $user['is_verified'] ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-warning text-dark">Pending Review</span>'; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <?php if (($user['role'] == 'Seller' || $user['role'] == 'Both') && $user['is_verified'] == 0): ?>
                                        <a href="admin.php?action=verify_seller&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-success me-1">Verify</a>
                                    <?php endif; ?>
                                    
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <?php if ($user['account_status'] == 'Active'): ?>
                                            <a href="admin.php?action=ban_user&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to ban this user?');">Ban</a>
                                        <?php else: ?>
                                            <a href="admin.php?action=unban_user&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-secondary">Unban</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5 bg-light">
        <div class="card-body p-4">
            <h4 class="card-title mb-3 border-bottom pb-2">All Platform Listings</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Posted By (Seller)</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($listing = mysqli_fetch_assoc($result_listings)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($listing['listing_id']); ?></td>
                                <td><?php echo htmlspecialchars($listing['title']); ?></td>
                                <td><?php echo htmlspecialchars($listing['first_name'] . ' ' . $listing['last_name']); ?></td>
                                <td>R <?php echo htmlspecialchars($listing['price']); ?></td>
                                <td>
                                    <?php 
                                        $status_class = 'bg-primary';
                                        if ($listing['status'] == 'Active') $status_class = 'bg-success';
                                        if ($listing['status'] == 'Pending') $status_class = 'bg-warning text-dark';
                                        if ($listing['status'] == 'Sold') $status_class = 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($listing['status']); ?></span>
                                </td>
                                <td>
                                    <a href="admin.php?action=delete_listing&id=<?php echo $listing['listing_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to completely delete this rule-breaking listing?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5 bg-light">
        <div class="card-body p-4">
            <h4 class="card-title mb-3 border-bottom pb-2">Escrow Payment Tracking</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Tx ID</th>
                            <th>Item Title</th>
                            <th>Purchased By (Buyer)</th>
                            <th>Amount</th>
                            <th>Escrow Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($tx = mysqli_fetch_assoc($result_transactions)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tx['transaction_id']); ?></td>
                                <td><?php echo htmlspecialchars($tx['title']); ?></td>
                                <td><?php echo htmlspecialchars($tx['buyer_name']); ?></td>
                                <td>R <?php echo htmlspecialchars($tx['amount']); ?></td>
                                <td>
                                    <?php 
                                        $escrow_badge = 'bg-info text-dark';
                                        if ($tx['escrow_status'] == 'Funds Released') $escrow_badge = 'bg-success';
                                    ?>
                                    <span class="badge <?php echo $escrow_badge; ?>"><?php echo htmlspecialchars($tx['escrow_status']); ?></span>
                                </td>
                                <td>
                                    <?php if ($tx['escrow_status'] !== 'Funds Released'): ?>
                                        <a href="admin.php?action=release_escrow&id=<?php echo $tx['transaction_id']; ?>" class="btn btn-sm btn-primary">Release Funds</a>
                                    <?php else: ?>
                                        <span class="text-muted fw-bold">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>