<?php
// 0. Start the session FIRST so PHP remembers who is logged in!
session_start();

// 1. Connect to the database
include 'includes/db_connect.php';

// Gatekeeper: Ensure they are logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Bring in the navigation bar
include 'includes/header.php';

// Check if the form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Grab what the seller typed in the boxes
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    // 2. Grab the logged-in seller's ID
    $seller_id = $_SESSION['user_id'];

    // 3. Set the default status
    $status = 'Active';

    // --- NEW IMAGE UPLOAD LOGIC ---
    $image_url = 'assets/images/placeholder.png'; // Fallback

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/';
        
        $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        
        // Create a unique file name
        $new_file_name = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $new_file_name;

        // Security check
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($file_extension, $allowed_types)) {
            // Move to your assets/images folder
            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file; 
            }
        }
    }
    // ------------------------------

    // 4. The SQL Query
    $sql = "INSERT INTO listings (seller_id, category_id, title, description, price, status, image_url) 
            VALUES ('$seller_id', '$category_id', '$title', '$description', '$price', '$status', '$image_url')";

    // 5. Execute the query
    if (mysqli_query($conn, $sql)) {
        header("Location: seller_dashboard.php");
        exit();
    } else {
        echo "<div class='container mt-3'><div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div></div>";
    }
}
?>

<div class="container mt-5 mb-5">
    <h2>Add a New Product</h2>
    <div class="row mt-4">
        <div class="col-md-8 mx-auto"> 
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    
                    <form method="POST" action="add_product.php" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Product Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., Samsung Galaxy S21" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Describe the item's condition, features, etc." required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted fw-bold">Price (R)</label>
                                <input type="number" name="price" step="0.01" class="form-control" placeholder="0.00" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted fw-bold">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="1">Electronics</option>
                                    <option value="2">Clothing & Apparel</option>
                                    <option value="3">Home & Garden</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold">Product Image</label>
                            <input type="file" name="product_image" class="form-control" accept="image/*" required>
                            <div class="form-text">Accepted formats: JPG, PNG, GIF, WEBP.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Post Item for Sale</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>