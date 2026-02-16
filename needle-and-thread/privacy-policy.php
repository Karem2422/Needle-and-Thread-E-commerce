<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Needle & Thread</title>
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
                <h1>Privacy Policy</h1>
                <p class="lead">Your privacy matters to us. This policy explains how we collect, use, and protect your personal information.</p>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="card glass-card" style="max-width: 900px; margin: 0 auto;">
                    <div style="line-height: 1.8;">
                        <p><strong>Last Updated:</strong> <?php echo date('F j, Y'); ?></p>
                        
                        <h2>1. Information We Collect</h2>
                        <p>We collect information that you provide directly to us, including:</p>
                        <ul>
                            <li><strong>Account Information:</strong> Name, email address, password, and shipping address when you create an account</li>
                            <li><strong>Order Information:</strong> Payment details, billing address, and order history</li>
                            <li><strong>Communication:</strong> Messages you send to us via email or through our website</li>
                            <li><strong>Newsletter:</strong> Email address when you subscribe to our newsletter</li>
                        </ul>

                        <h2>2. How We Use Your Information</h2>
                        <p>We use the information we collect to:</p>
                        <ul>
                            <li>Process and fulfill your orders</li>
                            <li>Send you order confirmations and shipping updates</li>
                            <li>Respond to your inquiries and provide customer support</li>
                            <li>Send you marketing communications (with your consent)</li>
                            <li>Improve our website and services</li>
                            <li>Prevent fraud and ensure security</li>
                        </ul>

                        <h2>3. Information Sharing</h2>
                        <p>We do not sell your personal information. We may share your information only in the following circumstances:</p>
                        <ul>
                            <li><strong>Service Providers:</strong> With third-party companies that help us operate our business (e.g., payment processors, shipping carriers)</li>
                            <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
                            <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                        </ul>

                        <h2>4. Data Security</h2>
                        <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure.</p>

                        <h2>5. Your Rights</h2>
                        <p>You have the right to:</p>
                        <ul>
                            <li>Access your personal information</li>
                            <li>Correct inaccurate information</li>
                            <li>Request deletion of your information</li>
                            <li>Opt-out of marketing communications</li>
                            <li>Request a copy of your data</li>
                        </ul>
                        <p>To exercise these rights, please contact us at <a href="mailto:hello@needlethread.com">hello@needlethread.com</a>.</p>

                        <h2>6. Cookies</h2>
                        <p>We use cookies to enhance your browsing experience, analyze site traffic, and personalize content. You can control cookies through your browser settings.</p>

                        <h2>7. Children's Privacy</h2>
                        <p>Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children.</p>

                        <h2>8. Changes to This Policy</h2>
                        <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.</p>

                        <h2>9. Contact Us</h2>
                        <p>If you have questions about this Privacy Policy, please contact us:</p>
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

