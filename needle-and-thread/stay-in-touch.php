<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stay in Touch - Needle & Thread</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/images/Project Icon.png">
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
                <li><a href="faqs.php">FAQs</a></li>
            </ul>
        </nav>
    </header>

    <main class="main">
        <section class="hero stay-connected-hero">
            <div class="container hero-content">
                <p class="label">Stay in touch</p>
                <h1>Join the Needle & Thread circle</h1>
                <p class="lead">
                    Get first dibs on limited runs, behind-the-scenes studio moments,
                    and care guides for keeping your pieces beautiful for years to come.
                </p>
                <div class="hero-actions">
                    <a href="<?php echo SITE_URL; ?>/coming-soon.php" class="btn btn-primary">Subscribe to updates</a>
                    <a href="<?php echo SITE_URL; ?>/community-perks.php" class="btn btn-outline">Explore community perks</a>
                </div>
            </div>
        </section>

        <section id="newsletter" class="section">
            <div class="container stay-connected-grid">
                <div class="card glass-card">
                    <?php
                    $message = '';
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
                        $name = sanitizeInput($_POST['name'] ?? '');
                        $email = sanitizeInput($_POST['email'] ?? '');

                        if (empty($name) || empty($email)) {
                            $message = '<div class="alert alert-danger">Please fill in all fields.</div>';
                        } elseif (!isValidEmail($email)) {
                            $message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
                        } else {
                            try {
                                $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (name, email) VALUES (?, ?)");
                                $stmt->execute([$name, $email]);
                                $message = '<div class="alert alert-success">Thank you for subscribing! We will be in touch soon.</div>';
                            } catch (PDOException $e) {
                                $message = '<div class="alert alert-danger">Something went wrong. Please try again later.</div>';
                            }
                        }
                    }
                    ?>
                    <h2>Newsletter signup</h2>
                    <p>
                        Expect thoughtful emails no more than twice a month. We share new releases,
                        studio stories, styling inspiration, and exclusive offers reserved for
                        subscribers.
                    </p>
                    <?php echo $message; ?>
                    <form class="newsletter-form" method="POST" action="">
                        <div class="form-group">
                            <label for="newsletter-name">Full Name</label>
                            <input type="text" id="newsletter-name" name="name" placeholder="Your name" required>
                        </div>
                        <div class="form-group">
                            <label for="newsletter-email">Email Address</label>
                            <input type="email" id="newsletter-email" name="email" placeholder="your@email.com" required>
                        </div>
                        <button type="submit" name="subscribe" class="btn btn-primary btn-block">Join the list</button>
                        <small class="text-light">
                            By subscribing you agree to receive Needle & Thread emails.
                            You can unsubscribe anytime.
                        </small>
                    </form>
                </div>

                <div class="card glass-card">
                    <h2>What you’ll receive</h2>
                    <ul class="benefits-list">
                        <li>
                            <strong>Launch alerts</strong>
                            <span>Be the first to shop limited, small-batch releases.</span>
                        </li>
                        <li>
                            <strong>Care knowledge</strong>
                            <span>Tips from our artisans for conditioning, storing, and repairing your pieces.</span>
                        </li>
                        <li>
                            <strong>Studio stories</strong>
                            <span>Go behind the scenes with material sourcing and design sketches.</span>
                        </li>
                        <li>
                            <strong>Community perks</strong>
                            <span>Priority access to sample sales, warehouse events, and styling sessions.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="community" class="section">
            <div class="container">
                <div class="two-column">
                    <div>
                        <p class="label">Join the conversation</p>
                        <h2>Let’s connect beyond the inbox</h2>
                        <p>
                            From styling reels to long-form journal entries, each social platform lets us
                            showcase a different side of the Needle & Thread world. Pick your favorite space,
                            say hello, and tag us when your bag joins an adventure—we often feature community looks.
                        </p>
                        <div class="social-pill-group">
                            <a href="https://www.instagram.com/needle_and_thread.ye?igsh=YnNnMnB3Nnl5aHk3&utm_source=qr" class="social-pill" target="_blank">Instagram</a>
                            <a href="https://pin.it/6mO8SNOUO" class="social-pill" target="_blank">Pinterest</a>
                            <a href="https://www.facebook.com/share/1D3dkN3rqF/?mibextid=wwXIfr" class="social-pill" target="_blank">Facebook</a>
                        </div>
                    </div>
                    <div class="card glass-card">
                        <h3>Visit the studio</h3>
                        <p>
                        Liu University, 50th Street,<br>
                         Building B, 301B
                        </p>
                        <p class="text-light">
                            Appointments available Sat–Wed, 9am–6pm.
                        </p>
                        <a href="<?php echo SITE_URL; ?>/book-visit.php" class="btn btn-outline btn-block">Book a visit</a>
                        <a href="tel:+16025551234" class="btn btn-secondary btn-block">Call the studio</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>

