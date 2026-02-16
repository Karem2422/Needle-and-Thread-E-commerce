<?php
require_once 'config.php';
requireLogin();

if (empty($_SESSION['cart']) && !isset($_GET['product_id'])) {
    header('Location: cart.php');
    exit;
}

// Handle direct buy now
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $quantity = $_GET['quantity'] ?? 1;
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product && $product['status'] == 'available' && $quantity <= $product['quantity']) {
        $_SESSION['direct_checkout'] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product['price'],
            'title' => $product['title'],
            'image' => getPrimaryProductImage($product_id, $pdo)
        ];
    } else {
        header('Location: products.php');
        exit;
    }
}

$direct_checkout = $_SESSION['direct_checkout'] ?? null;
$cart = $direct_checkout ? [$direct_checkout] : ($_SESSION['cart'] ?? []);

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 10.00;
$total = $subtotal + $shipping;

// Handle order submission
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    }
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !isValidEmail($email)) $errors[] = "Valid email is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($phone)) $errors[] = "Phone is required";
    
    // Validate phone format - must be 7 digits starting with 77, 78, 71, 73, or 70
    if (!empty($phone)) {
        // Remove any spaces, dashes, or other characters
        $phone_clean = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's exactly 7 digits
        if (strlen($phone_clean) !== 9) {
            $errors[] = "Phone number must be exactly 9 digits";
        } else {
            // Check if it starts with valid prefix
            $valid_prefixes = ['77', '78', '71', '73', '70'];
            $prefix = substr($phone_clean, 0, 2);
            if (!in_array($prefix, $valid_prefixes)) {
                $errors[] = "Phone number must start with 77, 78, 71, 73, or 70";
            }
        }
    }
    
    if (empty($errors) && !empty($cart)) {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $user_id = $_SESSION['user_id'];
            $status = 'pending';
            if (isset($_POST['payment_status']) && $_POST['payment_status'] === 'paid') {
                $status = 'paid';
            }
            
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, name, email, address, total_price, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, $address, $total, $status]);
            $order_id = $pdo->lastInsertId();

            // Record initial status history
            recordOrderStatusHistory($order_id, null, 'pending', 'Order created', $user_id, $pdo);
            
            // Add order items and update product quantities
            foreach ($cart as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                    throw new Exception("Invalid cart item data");
                }
                
                // Verify product is still available
                if (!isProductAvailable($item['product_id'], $item['quantity'], $pdo)) {
                    throw new Exception("Product '{$item['title']}' is no longer available in the requested quantity");
                }
                
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update product quantity
                $update_stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $update_stmt->execute([$item['quantity'], $item['product_id']]);
                
                // Update product status if needed
                updateProductStatus($item['product_id'], $pdo);

                // Log inventory change
                logInventoryChange(
                    $item['product_id'],
                    -intval($item['quantity']),
                    'order',
                    "Order #{$order_id} placed",
                    $user_id,
                    $pdo,
                    $item['title'] ?? null
                );
            }
            
            $pdo->commit();
            
            // Clear cart and direct checkout
            if ($direct_checkout) {
                unset($_SESSION['direct_checkout']);
            } else {
                $_SESSION['cart'] = [];
            }
            
            $_SESSION['order_id'] = $order_id;
            header('Location: success.php');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Order failed. Please try again.";
            error_log("Order error: " . $e->getMessage());
        }
    } elseif (empty($cart)) {
        $errors[] = "Your cart is empty. Please add items before checkout.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Needle & Thread</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/images/Project Icon.png">
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
            <h1>Checkout</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="message error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="checkout-layout">
                <div class="checkout-form">
                    <h2>Shipping Information</h2>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Full Name" 
                                   value="<?php echo e($_POST['name'] ?? ($_SESSION['user_name'] ?? '')); ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Email Address" 
                                   value="<?php echo e($_POST['email'] ?? ($_SESSION['user_email'] ?? '')); ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="phone" placeholder="Phone Number" 
                                   value="<?php echo e($_POST['phone'] ?? ($_SESSION['user_phone'] ?? '')); ?>" required>
                        </div>
                        <div class="form-group">
                            <textarea name="address" placeholder="Shipping Address" rows="4" required><?php echo e($_POST['address'] ?? ($_SESSION['user_address'] ?? '')); ?></textarea>
                        </div>
                        
                        <h2>Payment Method</h2>
                        <div class="payment-method">
                            <div class="payment-option">
                                <input type="radio" id="paypal" name="payment_method" value="paypal" checked>
                                <label for="paypal">PayPal</label>
                            </div>
                            <p class="payment-note">You will be redirected to PayPal to complete your payment.</p>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary btn-block" id="place-order-btn">Place Order</button>
                        
                        <!-- PayPal Button Container -->
                        <div id="paypal-button-container" style="display: none; margin-top: 1rem;"></div>
                    </form>
                </div>

                <!-- PayPal SDK -->
                <?php require_once 'paypal-proxy/paypal_config.php'; ?>
                <script>
                    const ORDER_TOTAL = <?php echo number_format($total, 2, '.', ''); ?>;
                </script>
                <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>

                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-items">
                        <?php foreach ($cart as $item): 
                            if (!isset($item['title']) || !isset($item['quantity']) || !isset($item['price'])) {
                                continue;
                            }
                            $item_image = isset($item['image']) ? $item['image'] : 'placeholder.png';
                        ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <img src="uploads/<?php echo e($item_image); ?>" alt="<?php echo e($item['title']); ?>"
                                     onerror="this.src='uploads/placeholder.png'">
                            </div>
                            <div class="item-info">
                                <h4><?php echo e($item['title']); ?></h4>
                                <p>Qty: <?php echo e($item['quantity']); ?></p>
                                <p><?php echo formatPrice($item['price']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span><?php echo formatPrice($shipping); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>