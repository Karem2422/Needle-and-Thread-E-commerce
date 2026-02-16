<?php
require_once '../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single order
            $order_id = intval($_GET['id']);
            
            $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name 
                                 FROM orders o 
                                 LEFT JOIN users u ON o.user_id = u.id 
                                 WHERE o.id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                exit;
            }
            
            // Check permissions: admins can view all orders, regular users only their own
            if (!isAdmin()) {
                if (!isLoggedIn() || $order['user_id'] != $_SESSION['user_id']) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Access denied']);
                    exit;
                }
            }
            
            // Get order items
            $stmt = $pdo->prepare("SELECT oi.*, p.title, p.slug, pi.filename as image_filename 
                                 FROM order_items oi 
                                 JOIN products p ON oi.product_id = p.id 
                                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
                                 WHERE oi.order_id = ?");
            $stmt->execute([$order_id]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Include status history for admin visibility
            if (isAdmin()) {
                $order['status_history'] = getOrderStatusHistory($order_id, $pdo);
            }
            
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            // Get orders list
            if (isAdmin()) {
                // Admin can see all orders
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 20;
                $offset = ($page - 1) * $limit;
                $status = $_GET['status'] ?? '';
                
                $sql = "SELECT o.*, u.name as customer_name 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id";
                $count_sql = "SELECT COUNT(*) FROM orders o";
                $params = [];
                
                if (!empty($status)) {
                    $sql .= " WHERE o.status = ?";
                    $count_sql .= " WHERE o.status = ?";
                    $params[] = $status;
                }
                
                // LIMIT and OFFSET must be integers, not bound parameters
                $limit = intval($limit);
                $offset = intval($offset);
                $sql .= " ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Execute count query with only status parameter (no LIMIT/OFFSET)
                $count_params = [];
                if (!empty($status)) {
                    $count_params[] = $status;
                }
                $count_stmt = $pdo->prepare($count_sql);
                $count_stmt->execute($count_params);
                $total = $count_stmt->fetchColumn();
                
            } else {
                // Regular users can only see their own orders
                requireLogin();
                
                $page = intval($_GET['page'] ?? 1);
                $limit = intval($_GET['limit'] ?? 10);
                $offset = intval(($page - 1) * $limit);
                
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
                $stmt->execute([$_SESSION['user_id']]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $total = $stmt->fetchColumn();
            }
            
            echo json_encode([
                'success' => true, 
                'data' => $orders,
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
        requireLogin();
        
        // Create new order
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $address = trim($input['address'] ?? '');
        $items = $input['items'] ?? [];
        $total_price = floatval($input['total_price'] ?? 0);
        
        if (empty($name) || empty($email) || empty($address) || empty($items) || $total_price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid order data']);
            exit;
        }
        
        try {
            $pdo->beginTransaction();
            
            // Create order
            $user_id = $_SESSION['user_id'];
            $status = $input['status'] ?? 'pending';
            // Validate status if provided
            if (!in_array($status, ['pending', 'paid'])) {
                $status = 'pending';
            }
            
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, name, email, address, total_price, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, $address, $total_price, $status]);
            $order_id = $pdo->lastInsertId();
            
            // Add order items and update product quantities
            foreach ($items as $item) {
                $product_id = $item['product_id'];
                $quantity = intval($item['quantity']);
                $price = floatval($item['price']);
                
                // Verify product exists and has sufficient quantity
                $product_stmt = $pdo->prepare("SELECT quantity, title FROM products WHERE id = ?");
                $product_stmt->execute([$product_id]);
                $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    throw new Exception("Product not found: {$product_id}");
                }
                
                if ($product['quantity'] < $quantity) {
                    throw new Exception("Insufficient quantity for: {$product['title']}");
                }
                
                // Add order item
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $product_id, $quantity, $price]);
                
                // Update product quantity
                $update_stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $update_stmt->execute([$quantity, $product_id]);
                
                // Update product status if needed
                updateProductStatus($product_id, $pdo);
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Order created successfully',
                'order_id' => $order_id
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        requireAdmin();
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            exit;
        }
        
        $order_id = $_GET['id'];
        $status = $input['status'] ?? '';
        
        if (!in_array($status, ['pending', 'paid', 'shipped', 'cancelled'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        $result = updateOrderStatusWithHistory($order_id, $status, $pdo, $_SESSION['user_id'] ?? null);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Order updated']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Failed to update order']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>