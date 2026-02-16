<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns &amp; Exchanges - Needle &amp; Thread</title>
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
                <h1>Returns &amp; exchanges</h1>
                <p class="lead">
                    We want every piece to feel perfect. If something isn’t quite right, we’re happy to help with a quick exchange or a return.
                </p>
            </div>
        </section>

        <section class="section">
            <div class="container stay-connected-grid">
                <div class="card glass-card">
                    <h2>Return window &amp; eligibility</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Timeline</strong>
                            <span>Returns accepted within 30 days of delivery; holiday purchases extended to January 15.</span>
                        </li>
                        <li>
                            <strong>Condition</strong>
                            <span>Items must be unused with original tags, dust bag, and protective wrapping.</span>
                        </li>
                        <li>
                            <strong>Non-returnable</strong>
                            <span>Final sale and monogrammed items can be exchanged only if defective.</span>
                        </li>
                    </ul>
                </div>
                <div class="card glass-card">
                    <h2>How to start an exchange</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Request form</strong>
                            <span>Email hello@needlethread.com with your order number and reason.</span>
                        </li>
                        <li>
                            <strong>Prepaid labels</strong>
                            <span>We cover domestic return shipping for exchanges; refunds incur an $8 restocking fee.</span>
                        </li>
                        <li>
                            <strong>Processing</strong>
                            <span>Once your item is received, allow 3 business days for inspection and confirmation.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container card glass-card">
                <h2>Warranty &amp; repairs</h2>
                <p>
                    All Needle &amp; Thread goods include a one-year craftsmanship warranty. If you notice a defect within that period,
                    we’ll repair or replace the item at no charge. Beyond a year, we offer paid repairs handled by the same artisans who created your piece.
                </p>
                <p>
                    Need help? Reach out to hello@needlethread.com or call +1 (967) 71652111 and we’ll send shipping instructions with care tips to protect your item in transit.
                </p>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>


