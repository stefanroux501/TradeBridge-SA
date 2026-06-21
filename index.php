<?php 
// 1. Include the header (this injects your navbar and starts the session)
include 'includes/header.php'; 

// 2. Connect to the database so we can fetch products
include 'includes/db_connect.php';
?>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-4 text-primary"><strong>Welcome to TradeBridge SA</strong></h1>
        <p class="lead">The most trusted marketplace for the township economy.</p>
    </div>

    <div class="row mb-4 justify-content-center">
        <div class="col-md-6">
            <form method="GET" action="index.php" class="d-flex">   
                <input class="form-control me-2" type="search" name="search" placeholder="Search for items..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                
                <select name="category_id" class="form-select me-2" style="max-width: 200px;">
                    <option value="">All Categories</option>
                    <?php
                    // Dynamically fetch categories from your Categories table
                    $cat_query = "SELECT * FROM categories";
                    $cat_result = mysqli_query($conn, $cat_query);
                    
                    if (mysqli_num_rows($cat_result) > 0) {
                        while($cat = mysqli_fetch_assoc($cat_result)) {
                            // Check if this category is currently selected to keep it visible after search
                            $selected = (isset($_GET['category_id']) && $_GET['category_id'] == $cat['category_id']) ? 'selected' : '';
                            echo "<option value='" . $cat['category_id'] . "' $selected>" . htmlspecialchars($cat['category_name']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="row">
        <?php
        // --- SEARCH & FILTER LOGIC ---
        // Start with the base rule: Only show Active items
        $sql = "SELECT * FROM listings WHERE status = 'Active'";

        // 1. Check for a typed search word
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $search_term = mysqli_real_escape_string($conn, trim($_GET['search']));
            $sql .= " AND title LIKE '%$search_term%'";
        }

        // 2. Check for a selected category_id from the dropdown
        if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
            $category_id = mysqli_real_escape_string($conn, $_GET['category_id']);
            $sql .= " AND category_id = '$category_id'"; 
        }

        // 3. Finally, order the results by newest first
        $sql .= " ORDER BY created_at DESC";

        $result = mysqli_query($conn, $sql);
        // -----------------------------

$result = mysqli_query($conn, $sql);
// -----------------------------

        // Check if there are any products to show
        if (mysqli_num_rows($result) > 0) {
            // Loop through each product and create a Bootstrap Card
            while($row = mysqli_fetch_assoc($result)) {
                
                // SAFETY CHECK: If they have an image, use it. If not (old products), use a placeholder.
                $image_path = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'uploads/products/placeholder.png';

                echo '
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        
                        <img src="' . $image_path . '" class="card-img-top" alt="' . htmlspecialchars($row['title']) . '" style="height: 250px; object-fit: cover;">
                        
                        <div class="card-body">
                            <h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>
                            <p class="card-text text-truncate">' . htmlspecialchars($row['description']) . '</p>
                            <h4 class="text-success">R ' . htmlspecialchars($row['price']) . '</h4>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="product.php?id=' . $row['listing_id'] . '" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>';
            }
        } else {
            // If the database is empty, show this alert
            echo '
            <div class="col-12 text-center mt-4">
                <div class="alert alert-warning shadow-sm" role="alert">
                    There are no active listings on the marketplace right now. Check back later!
                </div>
            </div>';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>