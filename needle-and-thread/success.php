<?php
require_once 'config.php';
requireLogin();

if (!isset($_SESSION['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['order_id'];

// Get order details
$stmt = $pdo->prepare("SELECT o.*, oi.quantity, oi.price, p.title 
                      FROM orders o 
                      JOIN order_items oi ON o.id = oi.order_id 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE o.id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($order_items)) {
    header('Location: index.php');
    exit;
}

$order = $order_items[0]; // First item contains order info
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Needle & Thread</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/images/Project Icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <h1><a href="index.php">Needle & Thread</a></h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
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

    <main class="main">
        <div class="container">
            <div class="success-message">
                <h1>Order Confirmed!</h1>
                <p class="success-icon">✓</p>
                <p>Thank you for your purchase, <?php echo e($order['name']); ?>!</p>
                <p>Your order has been received and is being processed.</p>
            </div>

            <div class="order-details">
                <h2>Order Details</h2>
                <div class="order-info">
                    <div class="info-row">
                        <span>Order Number:</span>
                        <span>#<?php echo e($order_id); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Order Date:</span>
                        <span><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Customer Name:</span>
                        <span><?php echo e($order['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Email:</span>
                        <span><?php echo e($order['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Shipping Address:</span>
                        <span><?php echo nl2br(e($order['address'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Order Status:</span>
                        <span class="status pending"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                </div>

                <h3>Order Items</h3>
                <div class="order-items">
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <div class="item-details">
                            <h4><?php echo e($item['title']); ?></h4>
                            <p>Quantity: <?php echo e($item['quantity']); ?></p>
                        </div>
                        <div class="item-price">
                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-total">
                    <div class="total-row">
                        <span>Total:</span>
                        <span><?php echo formatPrice($order['total_price']); ?></span>
                    </div>
                </div>
            </div>

            <div class="success-actions">
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                <button onclick="window.print()" class="btn btn-secondary">Print Receipt</button>
            </div>
        </div>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>