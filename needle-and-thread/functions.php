<?php
// Security: Escape output
function e($string) {
    if ($string === null) return '';
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Sanitize input
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Generate URL-friendly slug
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        $loginUrl = SITE_URL . '/auth/login.php';
        header('Location: ' . $loginUrl);
        exit;
    }
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $loginUrl = SITE_URL . '/auth/login.php';
        header('Location: ' . $loginUrl);
        exit;
    }
}

// Generate CSRF token
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// File upload validation
function validateImageUpload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "Upload error: " . $file['error'];
    }
    
    if ($file['size'] > $max_size) {
        return "File too large. Maximum size is 5MB.";
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
    }
    
    return true;
}

// Generate unique filename
function generateUniqueFilename($original_name) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Get product images
function getProductImages($product_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get product images error: " . $e->getMessage());
        return [];
    }
}

// Get primary product image
function getPrimaryProductImage($product_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT filename FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['filename'] : 'placeholder.png';
    } catch (PDOException $e) {
        error_log("Get primary image error: " . $e->getMessage());
        return 'placeholder.png';
    }
}

// Update product status based on quantity
function updateProductStatus($product_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $status = $product['quantity'] > 0 ? 'available' : 'sold_out';
            $update_stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
            $update_stmt->execute([$status, $product_id]);
        }
    } catch (PDOException $e) {
        error_log("Update product status error: " . $e->getMessage());
    }
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate price
function isValidPrice($price) {
    return is_numeric($price) && $price > 0;
}

// Get cart total
function getCartTotal($cart) {
    $total = 0;
    if (is_array($cart)) {
        foreach ($cart as $item) {
            if (isset($item['price']) && isset($item['quantity'])) {
                $total += floatval($item['price']) * intval($item['quantity']);
            }
        }
    }
    return $total;
}

// Get total item count in cart
function getCartItemCount($cart) {
    $count = 0;
    if (is_array($cart)) {
        foreach ($cart as $item) {
            if (isset($item['quantity'])) {
                $count += intval($item['quantity']);
            }
        }
    }
    return $count;
}

// Check if product exists and is available
function isProductAvailable($product_id, $quantity, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT quantity, status FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $product && 
               $product['status'] === 'available' && 
               $product['quantity'] >= $quantity;
    } catch (PDOException $e) {
        error_log("Check product availability error: " . $e->getMessage());
        return false;
    }
}

// Record order status history
function recordOrderStatusHistory($order_id, $old_status, $new_status, $note, $user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $user_id, $old_status, $new_status, $note]);
    } catch (PDOException $e) {
        error_log("Order status history error: " . $e->getMessage());
    }
}

// Fetch order status history
function getOrderStatusHistory($order_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT h.*, u.name as admin_name 
                               FROM order_status_history h 
                               LEFT JOIN users u ON h.changed_by = u.id 
                               WHERE h.order_id = ?
                               ORDER BY h.created_at DESC");
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get order status history error: " . $e->getMessage());
        return [];
    }
}

// Log inventory changes
function logInventoryChange($product_id, $quantity_change, $change_type, $note, $user_id, $pdo, $product_name = null) {
    try {
        if ($product_id && $product_name === null) {
            $name_stmt = $pdo->prepare("SELECT title FROM products WHERE id = ?");
            $name_stmt->execute([$product_id]);
            $product = $name_stmt->fetch(PDO::FETCH_ASSOC);
            $product_name = $product['title'] ?? null;
        }

        $stmt = $pdo->prepare("INSERT INTO inventory_logs (product_id, product_name, user_id, change_type, quantity_change, note) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $product_name, $user_id, $change_type, $quantity_change, $note]);
    } catch (PDOException $e) {
        error_log("Inventory log error: " . $e->getMessage());
    }
}

// Delete product helper (used for bulk deletes)
function deleteProductAndAssets($product_id, $user_id, $pdo) {
    try {
        $pdo->beginTransaction();

        $product_stmt = $pdo->prepare("SELECT title, quantity FROM products WHERE id = ?");
        $product_stmt->execute([$product_id]);
        $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $pdo->rollBack();
            return ['success' => false, 'message' => "Product #{$product_id} not found."];
        }

        // Fetch images before deleting
        $image_stmt = $pdo->prepare("SELECT filename FROM product_images WHERE product_id = ?");
        $image_stmt->execute([$product_id]);
        $images = $image_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Remove order item references
        $orderItemStmt = $pdo->prepare("DELETE FROM order_items WHERE product_id = ?");
        $orderItemStmt->execute([$product_id]);

        // Log inventory removal
        if ((int)$product['quantity'] !== 0) {
            logInventoryChange(
                $product_id,
                -intval($product['quantity']),
                'manual',
                'Product deleted',
                $user_id,
                $pdo,
                $product['title']
            );
        }

        // Delete product record (images cascade)
        $delete_stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt->execute([$product_id]);

        $pdo->commit();

        // Delete image files after commit
        foreach ($images as $image) {
            $file_path = UPLOAD_PATH . $image['filename'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        return ['success' => true];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Delete product helper error: " . $e->getMessage());
        return ['success' => false, 'message' => "Failed to delete product #{$product_id}."];
    }
}

// Update order status helper used across admin/APIs
function updateOrderStatusWithHistory($order_id, $new_status, $pdo, $actor_user_id = null) {
    $admin_id = $actor_user_id ?? ($_SESSION['user_id'] ?? null);
    try {
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return ['success' => false, 'message' => "Order #{$order_id} not found."];
        }

        $old_status = $order['status'];

        if ($old_status === $new_status) {
            return ['success' => false, 'message' => "Order #{$order_id} is already {$new_status}."];
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);

        recordOrderStatusHistory($order_id, $old_status, $new_status, 'Status updated', $admin_id, $pdo);

        if ($old_status !== 'cancelled' && $new_status === 'cancelled') {
            $item_stmt = $pdo->prepare("SELECT oi.product_id, oi.quantity, p.title 
                                        FROM order_items oi 
                                        LEFT JOIN products p ON oi.product_id = p.id 
                                        WHERE oi.order_id = ?");
            $item_stmt->execute([$order_id]);
            $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $update_stmt = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
                $update_stmt->execute([$item['quantity'], $item['product_id']]);

                logInventoryChange(
                    $item['product_id'],
                    intval($item['quantity']),
                    'refund',
                    "Order #{$order_id} cancelled - restocked",
                    $admin_id,
                    $pdo,
                    $item['title'] ?? null
                );

                updateProductStatus($item['product_id'], $pdo);
            }
        }

        $pdo->commit();

        return ['success' => true];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Order status update helper error: " . $e->getMessage());
        return ['success' => false, 'message' => "Failed to update Order #{$order_id}."];
    }
}
?>