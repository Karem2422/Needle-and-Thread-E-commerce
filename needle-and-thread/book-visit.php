<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Visit - Needle & Thread</title>
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
                <h1><a href="<?php echo SITE_URL; ?>/index.php">Needle & Thread</a></h1>
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
                <p class="label">Book a visit</p>
                <h1>Studio bookings coming soon</h1>
                <p class="lead">
                    We’re putting the finishing touches on our appointment scheduler. Check back soon
                    or reach out via hello@needlethread.com for bespoke requests in the meantime.
                </p>
                <div class="hero-actions">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-primary">Return home</a>
                </div>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>


