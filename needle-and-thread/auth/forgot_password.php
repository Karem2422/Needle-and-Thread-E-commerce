<?php
require_once '../config.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    }
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!isValidEmail($email)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($errors)) {
        try {
            // Check if user exists and is not an admin
            $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE LOWER(email) = LOWER(?) AND is_admin = 0");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete any existing tokens for this user
                $delete_stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                $delete_stmt->execute([$user['id']]);
                
                // Insert new token
                $insert_stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $insert_stmt->execute([$user['id'], $token, $expires_at]);
                
                // Generate reset link
                $reset_link = SITE_URL . "/auth/reset_password.php?token=" . $token;
                
                // In a real application, you would send an email here
                // For now, we'll show the link (in production, remove this and send email)
                $success = true;
                $_SESSION['reset_token_display'] = $reset_link;
                $_SESSION['reset_email'] = $user['email'];
                
                // Note: In production, send email with reset link instead of displaying it
                // mail($user['email'], 'Password Reset Request', "Click here to reset your password: $reset_link");
            } else {
                // Don't reveal if email exists for security
                $success = true; // Show success message even if user doesn't exist
            }
        } catch (PDOException $e) {
            $errors[] = "An error occurred. Please try again later.";
            error_log("Password reset error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Needle & Thread</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="../assets/images/Project Icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <h1><a href="../index.php">Needle & Thread</a></h1>
            </div>
        </nav>
    </header>

    <main class="main auth-main">
        <div class="auth-container">
            <div class="auth-form">
                <h2>Forgot Password</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo e($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message success">
                        <p>If an account with that email exists, a password reset link has been generated.</p>
                        <?php if (isset($_SESSION['reset_token_display'])): ?>
                            <p style="margin-top: 1rem; font-size: 0.9rem;">
                                <strong>Reset Link (for testing):</strong><br>
                                <a href="<?php echo e($_SESSION['reset_token_display']); ?>" style="word-break: break-all; color: #007bff;">
                                    <?php echo e($_SESSION['reset_token_display']); ?>
                                </a>
                            </p>
                            <p style="margin-top: 0.5rem; font-size: 0.85rem; opacity: 0.8;">
                                <em>Note: In production, this link would be sent via email.</em>
                            </p>
                            <?php 
                            unset($_SESSION['reset_token_display']);
                            unset($_SESSION['reset_email']);
                            ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="margin-bottom: 1.5rem; color: #666;">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>

                    <form method="POST" action="" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo e($_POST['email'] ?? ''); ?>" required autocomplete="email">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                    </form>
                <?php endif; ?>
                
                <p class="auth-link">
                    Remember your password? <a href="login.php">Login here</a><br>
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Needle & Thread. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

