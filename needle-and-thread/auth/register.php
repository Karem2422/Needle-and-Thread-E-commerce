<?php
require_once '../config.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    }
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !isValidEmail($email)) {
        $errors[] = "Valid email is required";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        // Check if email exists
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email already registered";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
                if ($stmt->execute([$name, $email, $password_hash])) {
                    $_SESSION['message'] = "Registration successful! Please login.";
                    header('Location: login.php');
                    exit;
                } else {
                    $errors[] = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error. Please try again later.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Needle & Thread</title>
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
                <h2>Create Account</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo e($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?php echo e($_POST['name'] ?? ''); ?>" required autocomplete="name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo e($_POST['email'] ?? ''); ?>" required autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter password (min 6 characters)" required autocomplete="new-password" minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password" minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
                
                <p class="auth-link">
                    Already have an account? <a href="login.php">Login here</a>
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