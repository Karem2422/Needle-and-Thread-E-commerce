<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Needle & Thread</title>
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
                <li><a href="about.php" class="active">About</a></li>
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
        <section class="about-hero">
            <div class="container">
                <p class="label">Our Story</p>
                <h1>Crafted in the desert, carried around the world.</h1>
                <p>Needle &amp; Thread was born from a love of traditional craftsmanship and modern, functional design. Each bag is imagined in the studio, cut by hand, and stitched with intention.</p>
            </div>
        </section>

        <section class="about-values">
            <div class="container">
                <div class="value-card">
                    <h3>Timeless Craft</h3>
                    <p>We partner with artisans who learned their craft from generations before them. Every stitch reflects hours of training and an eye for detail.</p>
                </div>
                <div class="value-card">
                    <h3>Honest Materials</h3>
                    <p>Only full-grain leathers and natural dyes make the cut. Materials are sourced responsibly to ensure longevity and a rich patina over time.</p>
                </div>
                <div class="value-card">
                    <h3>Intentional Design</h3>
                    <p>We obsess over proportion, functionality, and weight. Your bag is meant to accompany you everywhere and look even better with age.</p>
                </div>
            </div>
        </section>

        <section class="about-process">
            <div class="container">
                <div class="process-grid">
                    <div>
                        <h2>Made slowly, shared widely</h2>
                        <p>From sketch to final stitch, each Needle &amp; Thread piece passes through a small team of craftspeople. Limited batches mean we can keep quality high and waste low.</p>
                        <ul>
                            <li>Small-batch production</li>
                            <li>Hand-selected hides and textiles</li>
                            <li>Lifetime repair guarantees</li>
                        </ul>
                    </div>
                    <div class="process-card">
                        <h4>Studio Hours</h4>
                        <p> Sat – Wed, 9am – 6pm</p>
                        <h4>Visit us</h4>
                        <p>Liu University, 50th Street<br>Building B, 301B </p>
                        <h4>Contact</h4>
                        <p><a href="mailto:hello@needlethread.com">hello@needlethread.com</a><br>(967) 716552111</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>

