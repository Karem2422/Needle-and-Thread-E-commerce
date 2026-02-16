<?php
/**
 * Helper script to create a test user
 * Run this once to create a test user, then delete this file for security
 */
require_once 'config.php';

// Only allow this in development (comment out in production)
// Uncomment the line below to enable this script
// define('ALLOW_USER_CREATION', true);

if (!defined('ALLOW_USER_CREATION') && !isset($_GET['force'])) {
    die('This script is disabled for security. To enable, uncomment ALLOW_USER_CREATION in this file.');
}

$email = $_GET['email'] ?? 'ka@gmail.com';
$password = $_GET['password'] ?? 'karem123';
$name = $_GET['name'] ?? 'Kareem';
$is_admin = isset($_GET['admin']) ? 0 : 1;

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, name = ?, is_admin = ? WHERE email = ?");
        $stmt->execute([$password_hash, $name, $is_admin, $email]);
        echo "✓ User password updated successfully!<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
    } else {
        // Create new user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, is_admin) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password_hash, $is_admin]);
        echo "✓ User created successfully!<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
    }
    
    // Verify the password
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        echo "<br>✓ Password verification successful!<br>";
        echo "<br><a href='auth/login.php'>Go to Login Page</a>";
    } else {
        echo "<br>✗ Password verification failed!<br>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

