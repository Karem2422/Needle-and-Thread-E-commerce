<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = $_GET['id'];

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get product images
$images = getProductImages($product_id, $pdo);
$primary_image = getPrimaryProductImage($product_id, $pdo);

// Get comments
$comments_stmt = $pdo->prepare("SELECT c.* FROM comments c WHERE c.product_id = ? AND c.approved = 1 ORDER BY c.created_at DESC");
$comments_stmt->execute([$product_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related products
$related_stmt = $pdo->prepare("SELECT p.*, pi.filename as image_filename 
                              FROM products p 
                              LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
                              WHERE p.id != ? AND p.status = 'available' 
                              ORDER BY RAND() LIMIT 3");
$related_stmt->execute([$product_id]);
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid security token. Please try again.";
        header("Location: product.php?id=$product_id");
        exit;
    }
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    if (!empty($name) && !empty($email) && isValidEmail($email) && !empty($comment)) {
        try {
            $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
            $approved = isAdmin() ? 1 : 0; // Auto-approve admin comments
            
            $insert_stmt = $pdo->prepare("INSERT INTO comments (product_id, user_id, name, email, comment, approved) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->execute([$product_id, $user_id, $name, $email, $comment, $approved]);
            
            $_SESSION['message'] = $approved ? "Comment added successfully!" : "Comment submitted for approval!";
            header("Location: product.php?id=$product_id");
            exit;
        } catch (PDOException $e) {
            $_SESSION['message'] = "Failed to add comment. Please try again.";
            error_log("Comment error: " . $e->getMessage());
            header("Location: product.php?id=$product_id");
            exit;
        }
    } else {
        $_SESSION['message'] = "Please fill in all fields correctly.";
        header("Location: product.php?id=$product_id");
        exit;
    }
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid security token. Please try again.";
        header("Location: product.php?id=$product_id");
        exit;
    }
    
    if (!isLoggedIn()) {
        $_SESSION['message'] = "Please login to add items to cart.";
        header('Location: auth/login.php');
        exit;
    }
    
    $quantity = intval($_POST['quantity'] ?? 1);
    
    // Validate product availability
    if (!isProductAvailable($product_id, $quantity, $pdo)) {
        $_SESSION['message'] = "Product is not available in the requested quantity.";
        header("Location: product.php?id=$product_id");
        exit;
    }
    
    if ($quantity > 0 && $quantity <= $product['quantity']) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $cart_item = [
            'product_id' => intval($product_id),
            'quantity' => intval($quantity),
            'price' => floatval($product['price']),
            'title' => sanitizeInput($product['title']),
            'image' => sanitizeInput($primary_image)
        ];
        
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if (isset($item['product_id']) && $item['product_id'] == $product_id) {
                $new_quantity = $item['quantity'] + $quantity;
                if ($new_quantity <= $product['quantity']) {
                    $item['quantity'] = $new_quantity;
                    $found = true;
                    break;
                } else {
                    $_SESSION['message'] = "Cannot add more. Only " . $product['quantity'] . " items available.";
                    header("Location: product.php?id=$product_id");
                    exit;
                }
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = $cart_item;
        }
        
        $_SESSION['message'] = "Product added to cart!";
        header("Location: product.php?id=$product_id");
        exit;
    } else {
        $_SESSION['message'] = "Invalid quantity. Please select a valid amount.";
        header("Location: product.php?id=$product_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($product['title']); ?> - Needle & Thread</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success"><?php echo e($_SESSION['message']); unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <div class="product-detail">
                <div class="product-gallery">
                    <div class="main-image">
                        <img src="uploads/<?php echo e($primary_image); ?>" alt="<?php echo e($product['title']); ?>" id="main-product-image">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="image-thumbnails">
                        <?php foreach ($images as $image): ?>
                            <img src="uploads/<?php echo e($image['filename']); ?>" 
                                 alt="<?php echo e($product['title']); ?>" 
                                 class="thumbnail <?php echo $image['is_primary'] ? 'active' : ''; ?>"
                                 onclick="document.getElementById('main-product-image').src = this.src">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <h1><?php echo e($product['title']); ?></h1>
                    <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                    <p class="product-status <?php echo e($product['status']); ?>">
                        <?php echo ucfirst($product['status']); ?> (<?php echo e($product['quantity']); ?> left)
                    </p>
                    
                    <div class="product-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(e($product['description'])); ?></p>
                    </div>

                    <?php if ($product['status'] == 'available'): ?>
                    <form method="POST" class="add-to-cart-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="qty-controls">
                                <button type="button" class="qty-btn qty-minus" aria-label="Decrease quantity">−</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo e($product['quantity']); ?>">
                                <button type="button" class="qty-btn qty-plus" aria-label="Increase quantity">+</button>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button type="submit" name="add_to_cart" class="btn btn-secondary">Add to Cart</button>
                            <a href="checkout.php?product_id=<?php echo e($product_id); ?>&quantity=1" class="btn btn-primary">Buy Now</a>
                        </div>
                    </form>
                    <?php else: ?>
                        <p class="sold-out-message">This product is currently sold out.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comments Section -->
            <section class="comments-section" data-product-id="<?php echo e($product_id); ?>">
                <h2>Customer Reviews</h2>
                
                <!-- Comment Form -->
                <div class="comment-form">
                    <h3>Add a Review</h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Your Name" required 
                                   value="<?php echo isLoggedIn() ? e($_SESSION['user_name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Your Email" required
                                   value="<?php echo isLoggedIn() ? e($_SESSION['user_email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <textarea name="comment" placeholder="Your Review" rows="5" required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>

                <!-- Comments List -->
                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <p>No reviews yet. Be the first to review this product!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <strong><?php echo e($comment['name']); ?></strong>
                                    <span class="comment-date"><?php echo date('F j, Y', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <p><?php echo nl2br(e($comment['comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
            <section class="related-products">
                <h2>You Might Also Like</h2>
                <div class="products-grid">
                    <?php foreach ($related_products as $related): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?php echo e($related['id']); ?>" class="product-card-link">
                            <div class="product-image">
                                <img src="uploads/<?php echo e($related['image_filename'] ?? 'placeholder.png'); ?>" alt="<?php echo e($related['title']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo e($related['title']); ?></h3>
                                <p class="product-price"><?php echo formatPrice($related['price']); ?></p>
                            </div>
                        </a>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo e($related['id']); ?>" class="btn btn-secondary btn-block">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>