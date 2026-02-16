<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping &amp; Delivery - Needle &amp; Thread</title>
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
                <h1><a href="<?php echo SITE_URL; ?>/index.php">Needle &amp; Thread</a></h1>
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>/products.php">Products</a></li>
                <li><a href="<?php echo SITE_URL; ?>/about.php">About</a></li>
                <li><a href="<?php echo SITE_URL; ?>/faqs.php">FAQs</a></li>
            </ul>
        </nav>
    </header>

    <main class="main">
        <section class="hero stay-connected-hero">
            <div class="container hero-content">
                <p class="label">Customer care</p>
                <h1>Shipping &amp; delivery</h1>
                <p class="lead">
                    We hand-pack every order within 2 business days and provide transparent tracking from our studio to your doorstep.
                </p>
            </div>
        </section>

        <section class="section">
            <div class="container stay-connected-grid">
                <div class="card glass-card">
                    <h2>Processing &amp; transit times</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Processing</strong>
                            <span>Orders ship Monday–Friday. Please allow up to 48 hours for packing and quality checks.</span>
                        </li>
                        <li>
                            <strong>Domestic shipping</strong>
                            <span>Standard (5–7 days) and expedited (2–3 days) options via UPS and USPS.</span>
                        </li>
                        <li>
                            <strong>International shipping</strong>
                            <span>Available to select regions with delivery in 7–14 days depending on customs clearance.</span>
                        </li>
                    </ul>
                </div>
                <div class="card glass-card">
                    <h2>Tracking &amp; delivery</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Tracking emails</strong>
                            <span>Sent automatically when your label is created—check spam if you don’t see it.</span>
                        </li>
                        <li>
                            <strong>Signature requests</strong>
                            <span>Available on request for orders over $500 for added peace of mind.</span>
                        </li>
                        <li>
                            <strong>Lost or delayed</strong>
                            <span>Reach out to hello@needlethread.com within 7 days so we can investigate with the carrier.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container card glass-card">
                <h2>Customs, duties &amp; fees</h2>
                <p>
                    International customers are responsible for any import duties and local taxes. We provide accurate customs
                    documentation with the actual purchase value and HS codes for leather accessories.
                </p>
                <p>
                    Need a special shipping arrangement? Email hello@needlethread.com or call +1 (967) 71652111 before placing your order
                    and we’ll find the best carrier for your timeline.
                </p>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>


