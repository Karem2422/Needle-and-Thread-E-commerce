<?php
require_once '../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'login':
                    $email = trim($input['email'] ?? '');
                    $password = $input['password'] ?? '';
                    
                    if (empty($email) || empty($password)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
                        exit;
                    }
                    
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user && password_verify($password, $user['password_hash'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['is_admin'] = (bool)$user['is_admin'];
                        
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Login successful',
                            'user' => [
                                'id' => $user['id'],
                                'name' => $user['name'],
                                'email' => $user['email'],
                                'is_admin' => (bool)$user['is_admin']
                            ]
                        ]);
                    } else {
                        http_response_code(401);
                        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
                    }
                    break;
                    
                case 'register':
                    $name = trim($input['name'] ?? '');
                    $email = trim($input['email'] ?? '');
                    $password = $input['password'] ?? '';
                    $confirm_password = $input['confirm_password'] ?? '';
                    
                    $errors = [];
                    
                    if (empty($name)) $errors[] = "Name is required";
                    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
                    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
                    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
                    
                    // Check if email exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $errors[] = "Email already registered";
                    }
                    
                    if (!empty($errors)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                        exit;
                    }
                    
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    try {
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $email, $password_hash]);
                        
                        $user_id = $pdo->lastInsertId();
                        
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Registration successful',
                            'user_id' => $user_id
                        ]);
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(['success' => false, 'message' => 'Registration failed']);
                    }
                    break;
                    
                case 'logout':
                    session_destroy();
                    echo json_encode(['success' => true, 'message' => 'Logout successful']);
                    break;
                    
                case 'check':
                    echo json_encode([
                        'success' => true,
                        'logged_in' => isLoggedIn(),
                        'user' => isLoggedIn() ? [
                            'id' => $_SESSION['user_id'],
                            'name' => $_SESSION['user_name'],
                            'email' => $_SESSION['user_email'],
                            'is_admin' => $_SESSION['is_admin']
                        ] : null
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action parameter required']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>