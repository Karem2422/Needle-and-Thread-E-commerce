<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Needle & Thread</title>
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
        <section class="hero stay-connected-hero">
            <div class="container hero-content">
                <p class="label">Legal</p>
                <h1>Terms of Service</h1>
                <p class="lead">Please read these terms carefully before using our website and services.</p>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="card glass-card" style="max-width: 900px; margin: 0 auto;">
                    <div style="line-height: 1.8;">
                        <p><strong>Last Updated:</strong> <?php echo date('F j, Y'); ?></p>
                        
                        <h2>1. Acceptance of Terms</h2>
                        <p>By accessing and using the Needle & Thread website, you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our website.</p>

                        <h2>2. Use of Website</h2>
                        <p>You agree to use our website only for lawful purposes and in a way that does not infringe the rights of others or restrict their use of the website. Prohibited activities include:</p>
                        <ul>
                            <li>Attempting to gain unauthorized access to our systems</li>
                            <li>Transmitting viruses or malicious code</li>
                            <li>Interfering with the website's functionality</li>
                            <li>Using automated systems to scrape or collect data</li>
                        </ul>

                        <h2>3. Products and Pricing</h2>
                        <p>We strive to provide accurate product descriptions and pricing. However, we reserve the right to:</p>
                        <ul>
                            <li>Correct any errors in pricing or product information</li>
                            <li>Limit quantities of products purchased</li>
                            <li>Refuse or cancel orders at our discretion</li>
                            <li>Discontinue products without notice</li>
                        </ul>
                        <p>All prices are in USD and are subject to change without notice.</p>

                        <h2>4. Orders and Payment</h2>
                        <p>When you place an order, you agree to provide accurate and complete information. We reserve the right to refuse or cancel any order for any reason, including:</p>
                        <ul>
                            <li>Product availability</li>
                            <li>Errors in pricing or product information</li>
                            <li>Fraudulent or suspicious activity</li>
                            <li>Violation of these terms</li>
                        </ul>
                        <p>Payment must be received before we process and ship your order.</p>

                        <h2>5. Shipping and Delivery</h2>
                        <p>Shipping terms, delivery times, and costs are detailed on our <a href="shipping-delivery.php">Shipping & Delivery</a> page. We are not responsible for delays caused by shipping carriers or customs.</p>

                        <h2>6. Returns and Refunds</h2>
                        <p>Our return and refund policy is detailed on our <a href="returns-exchanges.php">Returns & Exchanges</a> page. All returns must comply with our stated return policy.</p>

                        <h2>7. Intellectual Property</h2>
                        <p>All content on this website, including text, graphics, logos, images, and software, is the property of Needle & Thread and protected by copyright and trademark laws. You may not reproduce, distribute, or create derivative works without our written permission.</p>

                        <h2>8. User Accounts</h2>
                        <p>You are responsible for:</p>
                        <ul>
                            <li>Maintaining the confidentiality of your account credentials</li>
                            <li>All activities that occur under your account</li>
                            <li>Providing accurate and current information</li>
                            <li>Notifying us immediately of any unauthorized use</li>
                        </ul>

                        <h2>9. Limitation of Liability</h2>
                        <p>To the fullest extent permitted by law, Needle & Thread shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of our website or products.</p>

                        <h2>10. Indemnification</h2>
                        <p>You agree to indemnify and hold harmless Needle & Thread from any claims, damages, losses, or expenses arising from your use of our website or violation of these terms.</p>

                        <h2>11. Modifications to Terms</h2>
                        <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of the website constitutes acceptance of the modified terms.</p>

                        <h2>12. Governing Law</h2>
                        <p>These terms shall be governed by and construed in accordance with the laws of the jurisdiction in which Needle & Thread operates, without regard to conflict of law principles.</p>

                        <h2>13. Contact Information</h2>
                        <p>If you have questions about these Terms of Service, please contact us:</p>
                        <p>
                            <strong>Needle & Thread</strong><br>
                            Email: <a href="mailto:hello@needlethread.com">hello@needlethread.com</a><br>
                            Address: Liu University, 50th Street, Building B, 301B
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>

