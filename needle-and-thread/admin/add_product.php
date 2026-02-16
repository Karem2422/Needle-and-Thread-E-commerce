<?php
require_once '../config.php';
requireAdmin();

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
    $tags = trim($_POST['tags'] ?? '');
    
    // Auto-generate slug if empty
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
    
    if (empty($errors)) {
        // Check if slug exists
        try {
            $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $errors[] = "Slug already exists";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error. Please try again.";
            error_log("Add product error: " . $e->getMessage());
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert product
            $status = $quantity > 0 ? 'available' : 'sold_out';
            $stmt = $pdo->prepare("INSERT INTO products (title, slug, description, price, quantity, status, tags) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $description, $price, $quantity, $status, $tags]);
            $product_id = $pdo->lastInsertId();

            if ($quantity > 0) {
                logInventoryChange(
                    $product_id,
                    intval($quantity),
                    'manual',
                    'Initial stock added',
                    $_SESSION['user_id'] ?? null,
                    $pdo,
                    $title
                );
            }
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $primary_set = false;
                
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
                                $is_primary = !$primary_set ? 1 : 0;
                                $primary_set = true;
                                
                                $img_stmt = $pdo->prepare("INSERT INTO product_images (product_id, filename, is_primary) VALUES (?, ?, ?)");
                                $img_stmt->execute([$product_id, $filename, $is_primary]);
                            }
                        }
                    }
                }
            }
            
            $pdo->commit();
            $_SESSION['message'] = "Product added successfully!";
            header('Location: index.php');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to add product: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="../assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            <h1>Add New Product</h1>
            
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
                    <input type="text" id="title" name="title" value="<?php echo e($_POST['title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="slug">URL Slug *</label>
                    <input type="text" id="slug" name="slug" value="<?php echo e($_POST['slug'] ?? ''); ?>" required>
                    <small>Unique identifier for URLs (e.g., "desert-nomad-tote")</small>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="6" required><?php echo e($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo e($_POST['price'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" min="0" value="<?php echo e($_POST['quantity'] ?? '0'); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" value="<?php echo e($_POST['tags'] ?? ''); ?>">
                    <small>Comma-separated tags for filtering (e.g., "tote,leather,everyday")</small>
                </div>
                
                <div class="form-group">
                    <label for="images">Product Images</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*">
                    <small>First image will be set as primary. Supported formats: JPG, PNG, GIF, WebP (max 5MB each)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Product</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
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
    </script>
</body>
</html>