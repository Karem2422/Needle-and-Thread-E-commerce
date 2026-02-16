<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("My orders fetch error: " . $e->getMessage());
    $orders = [];
}

$orderItems = [];
$orderHistories = [];

if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

    try {
        $items_stmt = $pdo->prepare("SELECT oi.*, p.title, p.slug FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id IN ($placeholders)");
        $items_stmt->execute($orderIds);
        $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $orderItems[$item['order_id']][] = $item;
        }
    } catch (PDOException $e) {
        error_log("Order items fetch error: " . $e->getMessage());
    }

    foreach ($orders as $order) {
        $orderHistories[$order['id']] = getOrderStatusHistory($order['id'], $pdo);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Needle & Thread</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-soft);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-paid { background: #D1ECF1; color: #0C5460; }
        .status-shipped { background: #D4EDDA; color: #155724; }
        .status-cancelled { background: #F8D7DA; color: #721C24; }
        .order-items {
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        .order-item:last-child {
            margin-bottom: 0;
        }
        .order-history {
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        .history-entry {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            padding: 0.35rem 0;
        }
        .history-entry:not(:last-child) {
            border-bottom: 1px solid #f2f2f2;
        }
        .order-empty {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-soft);
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <h1><a href="index.php">Needle & Thread</a></h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="about.php">About</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/index.php">Admin</a></li>
                    <?php else: ?>
                        <li><a href="my_orders.php" class="active">My Orders</a></li>
                    <?php endif; ?>
                    <?php $cart_count = isset($_SESSION['cart']) ? getCartItemCount($_SESSION['cart']) : 0; ?>
                    <li>
                        <a href="cart.php" class="cart-link">
                            <span class="cart-icon">🛒</span>
                            <span class="cart-label">Cart</span>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="auth/login.php">Login</a></li>
                    <li><a href="auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="main">
        <div class="container">
            <h1>My Orders</h1>
            <p class="text-light" style="margin-bottom: 2rem;">Track your recent purchases and their delivery status.</p>

            <?php if (empty($orders)): ?>
                <div class="order-empty">
                    <p>You haven't placed any orders yet.</p>
                    <a href="products.php" class="btn btn-primary" style="margin-top: 1rem;">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <h3>Order #<?php echo e($order['id']); ?></h3>
                                <p class="text-light">Placed on <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                            </div>
                            <span class="status-badge status-<?php echo e($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div class="order-shipping">
                            <p><strong>Shipping Address:</strong><br><?php echo nl2br(e($order['address'])); ?></p>
                        </div>
                        <div class="order-items">
                            <h4>Items</h4>
                            <?php foreach ($orderItems[$order['id']] ?? [] as $item): ?>
                                <div class="order-item">
                                    <span><?php echo e($item['title'] ?? 'Product'); ?> × <?php echo e($item['quantity']); ?></span>
                                    <span><?php echo formatPrice($item['price']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="order-total" style="text-align: right;">
                            <strong>Total:</strong> <?php echo formatPrice($order['total_price']); ?>
                        </div>
                        <div class="order-history">
                            <h4>Status History</h4>
                            <?php if (!empty($orderHistories[$order['id']])): ?>
                                <?php foreach ($orderHistories[$order['id']] as $entry): ?>
                                    <div class="history-entry">
                                        <div>
                                            <strong><?php echo ucfirst($entry['new_status']); ?></strong>
                                            <span><?php echo e($entry['note']); ?></span>
                                        </div>
                                        <div>
                                            <span><?php echo $entry['admin_name'] ? e($entry['admin_name']) : 'System'; ?></span><br>
                                            <span><?php echo date('M j, Y g:i A', strtotime($entry['created_at'])); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-light">No status updates yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once 'partials/footer.php'; ?>
</body>
</html>

