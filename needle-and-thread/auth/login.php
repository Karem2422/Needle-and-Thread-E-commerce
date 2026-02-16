<?php
require_once '../config.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ../admin/index.php');
    } else {
        header('Location: ../index.php');
    }
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    }
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $errors[] = "Email and password are required";
    }
    
    if (empty($errors)) {
        try {
            // Use LOWER() for case-insensitive email comparison
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $errors[] = "Invalid email or password";
            } elseif (empty($user['password_hash'])) {
                $errors[] = "Account error. Please contact support.";
                error_log("User {$user['id']} has no password hash");
            } elseif (!password_verify($password, $user['password_hash'])) {
                $errors[] = "Invalid email or password";
            } else {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = (bool)$user['is_admin'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['message'] = "Welcome back, " . $user['name'] . "!";
                
                // Redirect admins to the dashboard, regular users to homepage
                if (isAdmin()) {
                    header('Location: ../admin/index.php');
                } else {
                    header('Location: ../index.php');
                }
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Database error. Please try again later.";
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Needle & Thread</title>
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
                <h2>Login to Your Account</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo e($error); ?></p>
                        <?php endforeach; ?>
                        <p style="font-size: 0.9rem; margin-top: 0.5rem; opacity: 0.8;">
                            <strong>Tip:</strong> Make sure you're using the correct email and password. 
                            If you just registered, try logging in with the credentials you used.
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message success"><?php echo e($_SESSION['message']); unset($_SESSION['message']); ?></div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo e($_POST['email'] ?? ''); ?>" required autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                
                <p class="auth-link">
                    <a href="forgot_password.php">Forgot your password?</a><br>
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