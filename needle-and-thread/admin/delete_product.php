<?php
require_once '../config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = $_GET['id'];

// Check if product exists
$stmt = $pdo->prepare("SELECT title FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit;
}

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid security token. Please try again.";
        header('Location: index.php');
        exit;
    }
    
    if (isset($_POST['confirm_delete'])) {
        $result = deleteProductAndAssets($product_id, $_SESSION['user_id'] ?? null, $pdo);
        if ($result['success']) {
            $_SESSION['message'] = "Product '{$product['title']}' deleted successfully!";
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'] ?? "Failed to delete product.";
        }
    } else {
        // Cancelled deletion
        header('Location: edit_product.php?id=' . $product_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="assets/images/Project Icon.png">
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
            <h1>Delete Product</h1>
            
            <?php if (isset($error)): ?>
                <div class="message error">
                    <p><?php echo e($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="delete-confirmation">
                <div class="warning-message">
                    <h2>⚠️ Warning: This action cannot be undone!</h2>
                    <p>You are about to delete the product: <strong>"<?php echo e($product['title']); ?>"</strong></p>
                    
                    <div class="consequences">
                        <h3>This will permanently delete:</h3>
                        <ul>
                            <li>The product record</li>
                            <li>All product images</li>
                            <li>All associated comments and reviews</li>
                            <li>Product reference from order history (order items will remain but product details will be lost)</li>
                        </ul>
                    </div>
                    
                    <p class="warning-note">Consider marking the product as "Sold Out" instead of deleting it to preserve order history.</p>
                </div>

                <form method="POST" class="delete-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="form-actions">
                        <button type="submit" name="confirm_delete" value="1" class="btn btn-danger" 
                                onclick="return confirm('Final confirmation: Are you absolutely sure you want to delete this product?')">
                            Yes, Delete Permanently
                        </button>
                        <button type="submit" name="cancel" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
                
                <div class="alternative-actions">
                    <p>Instead of deleting, you can:</p>
                    <a href="edit_product.php?id=<?php echo e($product_id); ?>" class="btn btn-outline">Edit Product</a>
                    <a href="edit_product.php?id=<?php echo e($product_id); ?>&mark_sold_out=1" class="btn btn-outline">Mark as Sold Out</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy;<?php echo date('Y'); ?> Needle & Thread. Admin Panel</p>
        </div>
    </footer>

    <script>
        // Add confirmation for the delete button
        document.querySelector('button[name="confirm_delete"]').addEventListener('click', function(e) {
            if (!confirm('This is your final warning. This action cannot be undone. Are you absolutely sure?')) {
                e.preventDefault();
            }
        });
        
        // Handle mark as sold out
        document.querySelector('a[href*="mark_sold_out"]').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Mark this product as sold out instead of deleting?')) {
                // This would typically be handled via a separate endpoint
                // For now, redirect to edit page
                window.location.href = 'edit_product.php?id=<?php echo e($product_id); ?>';
            }
        });
    </script>
</body>
</html>