<?php
require_once '../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single product
            $stmt = $pdo->prepare("SELECT p.*, pi.filename as image_filename 
                                 FROM products p 
                                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
                                 WHERE p.id = ?");
            $stmt->execute([$_GET['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $product['images'] = getProductImages($product['id'], $pdo);
                echo json_encode(['success' => true, 'data' => $product]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Product not found']);
            }
        } else {
            // Get all products with pagination and filters
            $search = $_GET['search'] ?? '';
            $min_price = $_GET['min_price'] ?? '';
            $max_price = $_GET['max_price'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 9);
            $offset = intval(($page - 1) * $limit);
            
            // Build query with filters
            $sql = "SELECT p.*, pi.filename as image_filename 
                    FROM products p 
                    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
                    WHERE 1=1";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
                $search_term = "%$search%";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }
            
            if (!empty($min_price)) {
                $sql .= " AND p.price >= ?";
                $params[] = $min_price;
            }
            
            if (!empty($max_price)) {
                $sql .= " AND p.price <= ?";
                $params[] = $max_price;
            }
            
            if (!empty($status)) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Build count query with same conditions
            $count_sql = "SELECT COUNT(*) as total FROM products p WHERE 1=1";
            $count_params = [];
            
            if (!empty($search)) {
                $count_sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
                $search_term = "%$search%";
                $count_params[] = $search_term;
                $count_params[] = $search_term;
                $count_params[] = $search_term;
            }
            
            if (!empty($min_price)) {
                $count_sql .= " AND p.price >= ?";
                $count_params[] = $min_price;
            }
            
            if (!empty($max_price)) {
                $count_sql .= " AND p.price <= ?";
                $count_params[] = $max_price;
            }
            
            if (!empty($status)) {
                $count_sql .= " AND p.status = ?";
                $count_params[] = $status;
            }
            
            $count_stmt = $pdo->prepare($count_sql);
            $count_stmt->execute($count_params);
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true, 
                'data' => $products,
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
        requireAdmin();
        
        $title = trim($input['title']);
        $slug = trim($input['slug']);
        $description = trim($input['description']);
        $price = floatval($input['price']);
        $quantity = intval($input['quantity']);
        $tags = trim($input['tags'] ?? '');
        
        if (empty($title) || empty($slug) || empty($description) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        try {
            $status = $quantity > 0 ? 'available' : 'sold_out';
            $stmt = $pdo->prepare("INSERT INTO products (title, slug, description, price, quantity, status, tags) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $description, $price, $quantity, $status, $tags]);
            
            $product_id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Product created', 'id' => $product_id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create product']);
        }
        break;
        
    case 'PUT':
        requireAdmin();
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit;
        }
        
        $product_id = $_GET['id'];
        $title = trim($input['title']);
        $slug = trim($input['slug']);
        $description = trim($input['description']);
        $price = floatval($input['price']);
        $quantity = intval($input['quantity']);
        $tags = trim($input['tags'] ?? '');
        $status = $input['status'] ?? 'available';
        
        try {
            $stmt = $pdo->prepare("UPDATE products SET title = ?, slug = ?, description = ?, price = ?, quantity = ?, status = ?, tags = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$title, $slug, $description, $price, $quantity, $status, $tags, $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Product updated']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update product']);
        }
        break;
        
    case 'DELETE':
        requireAdmin();
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Product deleted']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>