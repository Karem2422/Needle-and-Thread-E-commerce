<?php
require_once '../config.php';
requireAdmin();

$bulk_message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete_products'])) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid security token. Please try again.";
        header('Location: product_details.php');
        exit;
    }

    $product_ids = array_map('intval', $_POST['product_ids'] ?? []);

    if (empty($product_ids)) {
        $_SESSION['message'] = "Please select at least one product to delete.";
        header('Location: product_details.php');
        exit;
    }

    $deleted = 0;
    foreach ($product_ids as $pid) {
        $result = deleteProductAndAssets($pid, $_SESSION['user_id'] ?? null, $pdo);
        if ($result['success']) {
            $deleted++;
        }
    }

    $_SESSION['message'] = $deleted > 0 ? "{$deleted} product(s) deleted successfully." : "No products were deleted.";
    header('Location: product_details.php');
    exit;
}

try {
    $stmt = $pdo->query("SELECT p.*, COALESCE(pi.filename, 'placeholder.png') AS image_filename
                         FROM products p
                         LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                         ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin products error: " . $e->getMessage());
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="../assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
    </style>
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
                <li><a href="product_details.php" class="active">Products</a></li>
                <li><a href="inventory_logs.php">Inventory Logs</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reviews.php">Reviews</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="newsletter.php">Newsletter</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="main section">
        <div class="container">
            <div class="section-header">
                <div>
                    <p class="label">Inventory</p>
                    <h1>All Products</h1>
                </div>
                <a href="add_product.php" class="btn btn-primary">Add Product</a>
            </div>

            <?php if (empty($products)): ?>
                <div class="message info">
                    <p>No products found.</p>
                </div>
            <?php else: ?>
                <?php if ($bulk_message): ?>
                    <div class="message success">
                        <p><?php echo e($bulk_message); ?></p>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="bulk-actions">
                        <div>
                            <label>
                                <input type="checkbox" id="select-all-products">
                                Select All
                            </label>
                        </div>
                        <button type="submit" name="bulk_delete_products" value="1" class="btn btn-danger" onclick="return confirm('Delete selected products? This cannot be undone.');">
                            Delete Selected
                        </button>
                    </div>
                    <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="product_ids[]" value="<?php echo e($product['id']); ?>" class="product-checkbox">
                                </td>
                                <td>#<?php echo e($product['id']); ?></td>
                                <td>
                                    <div class="product-row">
                                        <img src="../uploads/<?php echo e($product['image_filename']); ?>" alt="<?php echo e($product['title']); ?>" onerror="this.src='../uploads/placeholder.png';">
                                        <div>
                                            <strong><?php echo e($product['title']); ?></strong>
                                            <p class="text-light"><?php echo e(substr($product['description'], 0, 60)); ?>...</p>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatPrice($product['price']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo e($product['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $product['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo e($product['quantity']); ?></td>
                                <td>
                                    <div class="stock-actions">
                                        <a href="edit_product.php?id=<?php echo e($product['id']); ?>" class="btn btn-small btn-secondary">Edit</a>
                                        <a href="delete_product.php?id=<?php echo e($product['id']); ?>" class="btn btn-small btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy;<?php echo date('Y'); ?> Needle & Thread. Admin Panel</p>
        </div>
    </footer>
</body>
<script>
    const selectAllProducts = document.getElementById('select-all-products');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');

    if (selectAllProducts) {
        selectAllProducts.addEventListener('change', function () {
            productCheckboxes.forEach(cb => cb.checked = this.checked);
        });
    }
</script>
</html>

