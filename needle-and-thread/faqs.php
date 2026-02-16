<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - Needle & Thread</title>
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
                <li><a href="stay-in-touch.php">Stay in touch</a></li>
            </ul>
        </nav>
    </header>

    <main class="main">
        <section class="hero faq-hero">
            <div class="container">
                <p class="label">FAQ</p>
                <h1>Your questions, answered</h1>
                <p class="lead">
                    From shipping timelines to leather care, we pulled together the most common questions
                    we hear from the Needle & Thread community.
                </p>
                <div class="hero-actions">
                    <a href="#shipping" class="btn btn-secondary">Shipping</a>
                    <a href="#care" class="btn btn-outline">Care & Materials</a>
                    <a href="#orders" class="btn btn-outline">Orders & Returns</a>
                </div>
            </div>
        </section>

        <section class="section" id="shipping">
            <div class="container">
                <h2>Shipping & fulfillment</h2>
                <div class="accordion">
                    <article class="accordion-item">
                        <h3>When will my order ship?</h3>
                        <p>
                            Orders ship within 2–3 business days. During limited releases please allow up to
                            5 business days while we finish quality checks on each piece.
                        </p>
                    </article>
                    <article class="accordion-item">
                        <h3>Do you offer expedited shipping?</h3>
                        <p>
                            Yes. Select expedited options at checkout for 2-day or overnight delivery. Orders
                            placed before 12pm MST ship the same day.
                        </p>
                    </article>
                    <article class="accordion-item">
                        <h3>Can I track my shipment?</h3>
                        <p>
                            Absolutely. As soon as your order leaves the studio, you’ll receive a tracking email.
                            You can also check status anytime from the Orders page when logged in.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section sand-bg" id="care">
            <div class="container">
                <h2>Care & materials</h2>
                <div class="accordion">
                    <article class="accordion-item">
                        <h3>What type of leather do you use?</h3>
                        <p>
                            We work with vegetable-tanned Italian hides sourced from family-owned tanneries.
                            Each hide is inspected for tone and grain before cutting.
                        </p>
                    </article>
                    <article class="accordion-item">
                        <h3>How should I care for my bag?</h3>
                        <p>
                            Condition every 6 months with a neutral balm, store upright in its dust bag, and keep
                            away from direct sunlight. Need a refresher? Book a care appointment from the Stay in touch page.
                        </p>
                    </article>
                    <article class="accordion-item">
                        <h3>Do you offer repairs?</h3>
                        <p>
                            Yes. We stand behind each piece and offer complimentary repairs for manufacturing defects
                            for two years. Reach us at hello@needlethread.com with photos to get started.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section" id="orders">
            <div class="container">
                <h2>Orders, returns & gifting</h2>
                <div class="accordion">
                    <article class="accordion-item">
                        <h3>What is your return policy?</h3>
                        <p>
                            Returns are accepted within 14 days of delivery on unused items with original packaging.
                            Start a return from your account dashboard or email <a href="mailto:support@needlethread.com">support@needlethread.com</a> for customer support assistance.
                        </p>
                    </article>
                    <article class="accordion-item">
                        <h3>Can I send a gift?</h3>
                        <p>
                            Definitely. Select “Mark as gift” at checkout to include a hand-written note and hide prices on the receipt.
                        </p>
                    </article>
                    <article class="accordion-item">
                        <h3>Do you offer custom work?</h3>
                        <p>
                            We release a few custom spots each season. Join the Stay in touch list to hear about openings or
                            email <a href="mailto:customs@needlethread.com">customs@needlethread.com</a> with your vision for custom orders.
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>

