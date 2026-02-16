<?php
require_once '../config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = $_GET['id'];

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit;
}

// Get product images
$images = getProductImages($product_id, $pdo);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    }
    
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $tags = trim($_POST['tags'] ?? '');
    
    // Normalize slug
    if (!empty($slug)) {
        $slug = generateSlug($slug);
    }
    if (empty($slug) && !empty($title)) {
        $slug = generateSlug($title);
    }
    
    if (empty($title)) $errors[] = "Title is required";
    if (empty($slug)) $errors[] = "Slug is required";
    if (empty($description)) $errors[] = "Description is required";
    if (!isValidPrice($price)) $errors[] = "Valid price is required";
    if ($quantity < 0) $errors[] = "Valid quantity is required";
    
    // Validate slug format
    if (!empty($slug) && !preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens";
    }
    
    // Validate status
    if (!in_array($status, ['available', 'sold_out'])) {
        $status = 'available';
    }
    
    if (empty($errors)) {
        // Check if slug exists for other products
        try {
            $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $product_id]);
            if ($stmt->fetch()) {
                $errors[] = "Slug already exists";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error. Please try again.";
            error_log("Edit product error: " . $e->getMessage());
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Update product
            $stmt = $pdo->prepare("UPDATE products SET title = ?, slug = ?, description = ?, price = ?, quantity = ?, status = ?, tags = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$title, $slug, $description, $price, $quantity, $status, $tags, $product_id]);

            $quantity_diff = $quantity - $product['quantity'];
            if ($quantity_diff !== 0) {
                logInventoryChange(
                    $product_id,
                    intval($quantity_diff),
                    'manual',
                    'Manual inventory adjustment',
                    $_SESSION['user_id'] ?? null,
                    $pdo,
                    $product['title']
                );
            }

            updateProductStatus($product_id, $pdo);
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $index => $tmp_name) {
                    if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['images']['name'][$index],
                            'type' => $_FILES['images']['type'][$index],
                            'tmp_name' => $tmp_name,
                            'error' => $_FILES['images']['error'][$index],
                            'size' => $_FILES['images']['size'][$index]
                        ];
                        
                        $validation = validateImageUpload($file);
                        if ($validation === true) {
                            $filename = generateUniqueFilename($file['name']);
                            $destination = UPLOAD_PATH . $filename;
                            
                            if (move_uploaded_file($file['tmp_name'], $destination)) {
                                // Check if we need to set as primary
                                $is_primary = (count($images) == 0 && $index == 0) ? 1 : 0;
                                
                                $img_stmt = $pdo->prepare("INSERT INTO product_images (product_id, filename, is_primary) VALUES (?, ?, ?)");
                                $img_stmt->execute([$product_id, $filename, $is_primary]);
                            }
                        }
                    }
                }
            }
            
            // Handle image deletions
            if (isset($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    $stmt = $pdo->prepare("SELECT filename FROM product_images WHERE id = ?");
                    $stmt->execute([$image_id]);
                    $image = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($image) {
                        // Delete file
                        $file_path = UPLOAD_PATH . $image['filename'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                        
                        // Delete from database
                        $del_stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
                        $del_stmt->execute([$image_id]);
                    }
                }
            }
            
            // Handle primary image change
            if (isset($_POST['primary_image'])) {
                // Reset all to not primary
                $reset_stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
                $reset_stmt->execute([$product_id]);
                
                // Set selected as primary
                $primary_stmt = $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?");
                $primary_stmt->execute([$_POST['primary_image'], $product_id]);
            }
            
            $pdo->commit();
            $_SESSION['message'] = "Product updated successfully!";
            header("Location: index.php");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to update product: " . $e->getMessage();
        }
    }
}

// Refresh images after potential updates
$images = getProductImages($product_id, $pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="../assets/images/Project Icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <h1><a href="../index.php">Needle & Thread Admin</a></h1>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">View Site</a></li>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="product_details.php">Products</a></li>
                <li><a href="inventory_logs.php">Inventory Logs</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reviews.php">Reviews</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="newsletter.php">Newsletter</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="main">
        <div class="container">
            <h1>Edit Product: <?php echo e($product['title']); ?></h1>
            
            <?php if (!empty($errors)): ?>
                <div class="message error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="product-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label for="title">Product Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo e($product['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="slug">URL Slug *</label>
                    <input type="text" id="slug" name="slug" value="<?php echo e($product['slug']); ?>" required>
                    <small>Unique identifier for URLs</small>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="6" required><?php echo e($product['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo e($product['price']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" min="0" value="<?php echo e($product['quantity']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="available" <?php echo $product['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="sold_out" <?php echo $product['status'] == 'sold_out' ? 'selected' : ''; ?>>Sold Out</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" value="<?php echo e($product['tags']); ?>">
                    <small>Comma-separated tags for filtering</small>
                </div>
                
                <!-- Existing Images -->
                <?php if (!empty($images)): ?>
                <div class="form-group">
                    <label>Current Images</label>
                    <div class="existing-images">
                        <?php foreach ($images as $image): ?>
                        <div class="image-item">
                            <img src="../uploads/<?php echo e($image['filename']); ?>" alt="Product Image" style="width: 100px; height: 100px; object-fit: cover;">
                            <div class="image-actions">
                                <label>
                                    <input type="radio" name="primary_image" value="<?php echo e($image['id']); ?>" <?php echo $image['is_primary'] ? 'checked' : ''; ?>>
                                    Primary
                                </label>
                                <label>
                                    <input type="checkbox" name="delete_images[]" value="<?php echo e($image['id']); ?>">
                                    Delete
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- New Images -->
                <div class="form-group">
                    <label for="images">Add New Images</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*">
                    <small>Supported formats: JPG, PNG, GIF, WebP (max 5MB each)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <a href="delete_product.php?id=<?php echo e($product_id); ?>" class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">Delete Product</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy;<?php echo date('Y'); ?> Needle & Thread. Admin Panel</p>
        </div>
    </footer>

    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const slugField = document.getElementById('slug');
            if (!slugField.value) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)+/g, '');
                slugField.value = slug;
            }
        });
        
        // Update status based on quantity
        document.getElementById('quantity').addEventListener('change', function() {
            const statusField = document.getElementById('status');
            if (this.value <= 0) {
                statusField.value = 'sold_out';
            } else if (statusField.value === 'sold_out') {
                statusField.value = 'available';
            }
        });
    </script>
</body>
</html>