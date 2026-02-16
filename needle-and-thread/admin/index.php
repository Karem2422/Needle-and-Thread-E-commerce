<?php
require_once '../config.php';
requireAdmin();

// Get stats
$products_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
// Calculate revenue the same way as orders.php - all orders except cancelled
$revenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?? 0;

// Get recent orders
$recent_orders = $pdo->query("SELECT o.*, u.name as customer_name 
                             FROM orders o 
                             LEFT JOIN users u ON o.user_id = u.id 
                             ORDER BY o.created_at DESC 
                             LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Get low stock products
$low_stock = $pdo->query("SELECT * FROM products WHERE quantity <= 5 AND status = 'available' ORDER BY quantity ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Needle & Thread</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                <li><a href="index.php" class="active">Dashboard</a></li>
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

    <main class="main section">
        <div class="container">
            <div class="section-header">
                <h1>Admin Dashboard</h1>
            </div>
            
            <div class="admin-stats">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $products_count; ?></span>
                    <span class="stat-label">Products</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $orders_count; ?></span>
                    <span class="stat-label">Orders</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $users_count; ?></span>
                    <span class="stat-label">Users</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo formatPrice($revenue); ?></span>
                    <span class="stat-label">Total Revenue</span>
                </div>
            </div>

            <div class="admin-layout">
                <div class="admin-section">
                    <h2>Recent Orders</h2>
                    <?php if (empty($recent_orders)): ?>
                        <p>No orders yet.</p>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($recent_orders as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <strong>Order #<?php echo e($order['id']); ?></strong>
                                    <span><?php echo e($order['customer_name'] ?? 'Guest'); ?></span>
                                    <span><?php echo formatPrice($order['total_price']); ?></span>
                                    <span class="status-badge status-<?php echo e($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span>
                                </div>
                                <a href="orders.php?view=<?php echo e($order['id']); ?>" class="btn btn-small">View</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                    <?php endif; ?>
                </div>

                <div class="admin-section">
                    <h2>Low Stock Alert</h2>
                    <?php if (empty($low_stock)): ?>
                        <p>All products have sufficient stock.</p>
                    <?php else: ?>
                        <div class="low-stock-list">
                            <?php foreach ($low_stock as $product): ?>
                            <div class="stock-item">
                                <div class="stock-info">
                                    <strong><?php echo e($product['title']); ?></strong>
                                    <span class="stock-count <?php echo $product['quantity'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                        <?php echo e($product['quantity']); ?> left
                                    </span>
                                </div>
                                <div class="stock-actions">
                                    <a href="edit_product.php?id=<?php echo e($product['id']); ?>" class="btn btn-small btn-secondary">Edit</a>
                                    <a href="delete_product.php?id=<?php echo e($product['id']); ?>" class="btn btn-small btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-actions">
                <a href="add_product.php" class="btn btn-primary">Add New Product</a>
                <a href="orders.php" class="btn btn-secondary">Manage Orders</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy;<?php echo date('Y'); ?> Needle & Thread. Admin Panel</p>
        </div>
    </footer>
</body>
</html>