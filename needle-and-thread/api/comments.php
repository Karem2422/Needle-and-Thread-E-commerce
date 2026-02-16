<?php
require_once '../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['product_id'])) {
            // Get comments for a product
            $product_id = $_GET['product_id'];
            $approved_only = !isset($_GET['all']) || !isAdmin();
            
            $sql = "SELECT c.*, u.name as user_name 
                   FROM comments c 
                   LEFT JOIN users u ON c.user_id = u.id 
                   WHERE c.product_id = ?";
            
            $params = [$product_id];
            
            if ($approved_only) {
                $sql .= " AND c.approved = 1";
            }
            
            $sql .= " ORDER BY c.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $comments]);
        } else {
            // Get all comments (admin only)
            requireAdmin();
            
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $offset = intval(($page - 1) * $limit);
            
            $sql = "SELECT c.*, p.title as product_title, u.name as user_name 
                   FROM comments c 
                   LEFT JOIN products p ON c.product_id = p.id 
                   LEFT JOIN users u ON c.user_id = u.id 
                   ORDER BY c.created_at DESC 
                   LIMIT $limit OFFSET $offset";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
            
            echo json_encode([
                'success' => true, 
                'data' => $comments,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Add new comment
        $product_id = $input['product_id'] ?? null;
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $comment = trim($input['comment'] ?? '');
        
        if (empty($product_id) || empty($name) || empty($email) || empty($comment)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        // Validate product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
        $approved = isAdmin() ? 1 : 0; // Auto-approve admin comments
        
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (product_id, user_id, name, email, comment, approved) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $user_id, $name, $email, $comment, $approved]);
            
            $comment_id = $pdo->lastInsertId();
            
            // Get the created comment with user info
            $stmt = $pdo->prepare("SELECT c.*, u.name as user_name 
                                 FROM comments c 
                                 LEFT JOIN users u ON c.user_id = u.id 
                                 WHERE c.id = ?");
            $stmt->execute([$comment_id]);
            $new_comment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'message' => $approved ? 'Comment added successfully' : 'Comment submitted for approval',
                'data' => $new_comment
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
        }
        break;
        
    case 'PUT':
        requireAdmin();
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Comment ID required']);
            exit;
        }
        
        $comment_id = $_GET['id'];
        $approved = $input['approved'] ?? 0;
        $comment_text = trim($input['comment'] ?? '');
        
        try {
            if (!empty($comment_text)) {
                $stmt = $pdo->prepare("UPDATE comments SET comment = ?, approved = ? WHERE id = ?");
                $stmt->execute([$comment_text, $approved, $comment_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE comments SET approved = ? WHERE id = ?");
                $stmt->execute([$approved, $comment_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Comment updated']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update comment']);
        }
        break;
        
    case 'DELETE':
        requireAdmin();
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Comment ID required']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Comment deleted']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>