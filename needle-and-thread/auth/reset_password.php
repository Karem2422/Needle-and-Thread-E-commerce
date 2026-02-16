<?php
require_once '../config.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$success = false;
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_id = null;

// Validate token
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_reset_tokens WHERE token = ? AND used = 0");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($token_data) {
            // Check if token is expired
            if (strtotime($token_data['expires_at']) > time()) {
                $valid_token = true;
                $user_id = $token_data['user_id'];
            } else {
                $errors[] = "This reset link has expired. Please request a new one.";
            }
        } else {
            $errors[] = "Invalid or already used reset link.";
        }
    } catch (PDOException $e) {
        $errors[] = "An error occurred. Please try again later.";
        error_log("Token validation error: " . $e->getMessage());
    }
} else {
    $errors[] = "No reset token provided.";
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    }
    
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if (empty($confirm_password)) {
        $errors[] = "Please confirm your password";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors) && $user_id) {
        try {
            $pdo->beginTransaction();
            
            // Update password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update_stmt->execute([$password_hash, $user_id]);
            
            // Mark token as used
            $mark_stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
            $mark_stmt->execute([$token]);
            
            $pdo->commit();
            
            $success = true;
        } catch (PDOException $e) {
            $pdo->rollBack();
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
    <title>Reset Password - Needle & Thread</title>
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
                <h2>Reset Password</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo e($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message success">
                        <p>Your password has been reset successfully!</p>
                        <p style="margin-top: 1rem;">
                            <a href="login.php" class="btn btn-primary">Login Now</a>
                        </p>
                    </div>
                <?php elseif ($valid_token): ?>
                    <p style="margin-bottom: 1.5rem; color: #666;">
                        Please enter your new password below.
                    </p>

                    <form method="POST" action="" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter new password (min. 6 characters)" required autocomplete="new-password" minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required autocomplete="new-password" minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                    </form>
                <?php else: ?>
                    <div class="message error">
                        <p>Invalid or expired reset link.</p>
                        <p style="margin-top: 1rem;">
                            <a href="forgot_password.php" class="btn btn-secondary">Request New Reset Link</a>
                        </p>
                    </div>
                <?php endif; ?>
                
                <p class="auth-link">
                    <a href="login.php">Back to Login</a>
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

