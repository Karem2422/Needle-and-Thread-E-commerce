<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Needle & Thread - Handcrafted Bags</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <h1><a href="index.php">Needle & Thread</a></h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="about.php">About</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/index.php">Admin</a></li>
                    <?php else: ?>
                        <li><a href="my_orders.php"<?php echo basename($_SERVER['PHP_SELF']) === 'my_orders.php' ? ' class="active"' : ''; ?>>My Orders</a></li>
                    <?php endif; ?>
                    <?php $cart_count = isset($_SESSION['cart']) ? getCartItemCount($_SESSION['cart']) : 0; ?>
                    <li>
                        <a href="cart.php" class="cart-link">
                            <span class="cart-icon">🛒</span>
                            <span class="cart-label">Cart</span>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="auth/login.php">Login</a></li>
                    <li><a href="auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Precision Crafted Handmade Bags</h2>
            <p>Each piece tells a story of tradition, quality, and exceptional craftsmanship</p>
            <a href="products.php" class="btn btn-primary">Shop Collection</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2>Featured Collection</h2>
            <div class="products-grid">
                <?php
                try {
                    $stmt = $pdo->query("SELECT p.*, pi.filename as image_filename 
                                       FROM products p 
                                       LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
                                       WHERE p.status = 'available' 
                                       ORDER BY p.created_at DESC 
                                       LIMIT 3");
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    error_log("Home page products error: " . $e->getMessage());
                    $products = [];
                }
                
                if (empty($products)):
                ?>
                    <p>No featured products available at the moment.</p>
                <?php else:
                    foreach ($products as $product):
                ?>
                <div class="product-card">
                    <a href="product.php?id=<?php echo e($product['id']); ?>" class="product-card-link">
                        <div class="product-image">
                            <img src="uploads/<?php echo e($product['image_filename'] ?? 'placeholder.png'); ?>" alt="<?php echo e($product['title']); ?>">
                            <?php if ($product['status'] == 'sold_out'): ?>
                                <span class="sold-out-badge">Sold Out</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo e($product['title']); ?></h3>
                            <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                        </div>
                    </a>
                    <div class="product-actions">
                        <a href="product.php?id=<?php echo e($product['id']); ?>" class="btn btn-secondary btn-block">View Details</a>
                    </div>
                </div>
                <?php 
                    endforeach;
                endif; 
                ?>
            </div>
        </div>
    </section>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>