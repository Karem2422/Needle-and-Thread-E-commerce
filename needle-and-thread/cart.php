<?php
require_once 'config.php';
requireLogin();

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid security token. Please try again.";
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['update_cart']) && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $index => $quantity) {
            $quantity = intval($quantity);
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$index]);
            } else {
                if (isset($_SESSION['cart'][$index])) {
                    $_SESSION['cart'][$index]['quantity'] = $quantity;
                }
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
        $_SESSION['message'] = "Cart updated successfully!";
    } elseif (isset($_POST['remove_item']) && isset($_POST['item_index'])) {
        $index = intval($_POST['item_index']);
        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
            $_SESSION['message'] = "Item removed from cart!";
        }
    } elseif (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $_SESSION['message'] = "Cart cleared!";
    }
    
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];

// Clean up invalid cart items
if (is_array($cart)) {
    $cart = array_filter($cart, function($item) {
        return isset($item['product_id']) && 
               isset($item['quantity']) && 
               isset($item['price']) && 
               isset($item['title']) &&
               $item['quantity'] > 0 &&
               $item['price'] > 0;
    });
    $_SESSION['cart'] = array_values($cart); // Reindex
}

$subtotal = getCartTotal($cart);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Needle & Thread</title>
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
                        <a href="cart.php" class="cart-link active">
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
            <h1>Shopping Cart</h1>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success"><?php echo e($_SESSION['message']); unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <?php if (empty($cart)): ?>
                <div class="empty-cart">
                    <p>Your cart is empty</p>
                    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            <?php else: ?>
                <form method="POST" class="cart-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="cart-items">
                        <?php 
                        $subtotal = 0;
                        foreach ($cart as $index => $item): 
                            // Validate cart item
                            if (!isset($item['price']) || !isset($item['quantity']) || !isset($item['title'])) {
                                continue;
                            }
                            $item_total = floatval($item['price']) * intval($item['quantity']);
                            $subtotal += $item_total;
                            $item_image = isset($item['image']) ? $item['image'] : 'placeholder.png';
                        ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="uploads/<?php echo e($item_image); ?>" alt="<?php echo e($item['title']); ?>" 
                                     onerror="this.src='uploads/placeholder.png'">
                            </div>
                            <div class="item-details">
                                <h3><?php echo e($item['title']); ?></h3>
                                <p class="item-price"><?php echo formatPrice($item['price']); ?></p>
                            </div>
                            <div class="item-quantity">
                                <input type="number" name="quantities[<?php echo $index; ?>]" 
                                       value="<?php echo e($item['quantity']); ?>" min="1" max="10" required>
                            </div>
                            <div class="item-total">
                                <?php echo formatPrice($item_total); ?>
                            </div>
                            <div class="item-actions">
                                <button type="submit" name="remove_item" value="1" class="btn btn-danger btn-small" 
                                        onclick="return confirm('Remove this item from cart?')">Remove</button>
                                <input type="hidden" name="item_index" value="<?php echo $index; ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>$10.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span><?php echo formatPrice($subtotal + 10); ?></span>
                        </div>
                    </div>

                    <div class="cart-actions">
                        <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                        <button type="submit" name="clear_cart" class="btn btn-danger" 
                                onclick="return confirm('Clear entire cart?')">Clear Cart</button>
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>