<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Perks - Needle &amp; Thread</title>
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
                <p class="label">Community perks</p>
                <h1>Exclusive experiences for insiders</h1>
                <p class="lead">
                    From studio tours to styling sessions, our community gets first dibs on everything we dream up. Here’s how to plug in.
                </p>
            </div>
        </section>

        <section class="section">
            <div class="container stay-connected-grid">
                <div class="card glass-card">
                    <h2>IRL moments</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Studio visits</strong>
                            <span>Reserve a private slot to see works-in-progress and preview upcoming colorways.</span>
                        </li>
                        <li>
                            <strong>Pop-ups &amp; trunk shows</strong>
                            <span>Be the first to know about limited city takeovers and sample events.</span>
                        </li>
                        <li>
                            <strong>Craft workshops</strong>
                            <span>Hands-on evenings with our artisans covering care, stitching, and customization.</span>
                        </li>
                    </ul>
                </div>
                <div class="card glass-card">
                    <h2>Digital access</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Early product drops</strong>
                            <span>Members-only links go out 24 hours before public launches.</span>
                        </li>
                        <li>
                            <strong>Behind-the-scenes journal</strong>
                            <span>Monthly films and essays that explore the materials, makers, and muses.</span>
                        </li>
                        <li>
                            <strong>Community spotlight</strong>
                            <span>Share your looks on Instagram with #NeedleThreadClub for a chance to be featured.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container card glass-card">
                <h2>How to join</h2>
                <p>
                    Subscribe to our newsletter and follow @needleandthread.studio on Instagram. Each month we randomly select
                    community members for surprise perks—gift cards, exclusive accessories, and private styling calls.
                </p>
                <div class="hero-actions" style="margin-top:1.5rem;">
                    <a href="<?php echo SITE_URL; ?>/coming-soon.php" class="btn btn-primary btn-block">Join the waitlist</a>
                    <a href="<?php echo SITE_URL; ?>/stay-in-touch.php" class="btn btn-outline btn-block">Back to stay in touch</a>
                </div>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>


