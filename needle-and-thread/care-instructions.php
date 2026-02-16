<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Care Instructions - Needle &amp; Thread</title>
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
                <p class="label">Care guide</p>
                <h1>Care instructions</h1>
                <p class="lead">
                    Thoughtful care keeps your leather goods looking rich and resilient for decades. Follow these studio-tested tips.
                </p>
            </div>
        </section>

        <section class="section">
            <div class="container stay-connected-grid">
                <div class="card glass-card">
                    <h2>Daily care</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Storage</strong>
                            <span>Keep pieces in their dust bag and stuff larger totes with tissue to maintain shape.</span>
                        </li>
                        <li>
                            <strong>Cleaning</strong>
                            <span>Wipe with a soft, dry cloth. Avoid baby wipes or alcohol-based cleaners.</span>
                        </li>
                        <li>
                            <strong>Moisture</strong>
                            <span>If your bag gets wet, blot gently and allow it to air dry away from direct heat or sun.</span>
                        </li>
                    </ul>
                </div>
                <div class="card glass-card">
                    <h2>Long-term conditioning</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Conditioner</strong>
                            <span>Apply a neutral leather balm every 6 months using a microfiber cloth.</span>
                        </li>
                        <li>
                            <strong>Hardware</strong>
                            <span>Polish brass accents with a jewelry cloth only—chemical cleaners can strip finishes.</span>
                        </li>
                        <li>
                            <strong>Professional refresh</strong>
                            <span>Ship your bag back to us for spa service; we’ll clean, recondition, and re-wrap it for a small fee.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container card glass-card">
                <h2>Stain removal</h2>
                <p>
                    For oil-based marks, sprinkle talc or cornstarch immediately and let it sit overnight before brushing away.
                    For ink, dab (don’t rub) with a colorless leather cleaner. When in doubt, send us photos and we’ll recommend the safest approach.
                </p>
                <p>
                    Email hello@needlethread.com with “Care help” in the subject line or call +1 (967) 71652111 to schedule a virtual care consultation.
                </p>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>


