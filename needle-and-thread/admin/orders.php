<?php
require_once '../config.php';
requireAdmin();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid security token. Please try again.";
        header("Location: orders.php");
        exit;
    }
    
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    // Validate status
    $valid_statuses = ['pending', 'paid', 'shipped', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['message'] = "Invalid status.";
        header("Location: orders.php");
        exit;
    }
    
    $result = updateOrderStatusWithHistory($order_id, $status, $pdo, $_SESSION['user_id'] ?? null);
    $_SESSION['message'] = $result['success'] ? "Order status updated successfully!" : $result['message'];
    header("Location: orders.php");
    exit;
}


// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1)); // Ensure page is at least 1
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT o.*, u.name as customer_name 
       FROM orders o 
       LEFT JOIN users u ON o.user_id = u.id 
       WHERE 1=1";
$count_sql = "SELECT COUNT(*) FROM orders o WHERE 1=1";
$params = [];
$count_params = [];

if (!empty($status_filter)) {
    $sql .= " AND o.status = ?";
    $count_sql .= " AND o.status = ?";
    $params[] = $status_filter;
    $count_params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (o.name LIKE ? OR o.email LIKE ? OR o.id = ?)";
    $count_sql .= " AND (o.name LIKE ? OR o.email LIKE ? OR o.id = ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search;
}

// LIMIT and OFFSET must be integers, not bound parameters
$limit = intval($limit);
$offset = intval($offset);
$sql .= " ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_orders = $count_stmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Get order statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(total_price) as total_revenue
    FROM orders
    WHERE status != 'cancelled'
");
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="../assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .orders-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #F5EFE6;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #8B6D5C;
            display: block;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #EAD5B7;
        }
        .orders-table th {
            background-color: #8B6D5C;
            color: white;
            font-weight: 600;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-paid { background: #D1ECF1; color: #0C5460; }
        .status-shipped { background: #D4EDDA; color: #155724; }
        .status-cancelled { background: #F8D7DA; color: #721C24; }
        .order-actions {
            display: flex;
            gap: 5px;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <h1><a href="../index.php">Needle & Thread Admin</a></h1>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">View Site</a></li>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="product_details.php">Products</a></li>
                <li><a href="inventory_logs.php">Inventory Logs</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reviews.php">Reviews</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="newsletter.php">Newsletter</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="main">
        <div class="container">
            <h1>Order Management</h1>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success"><?php echo e($_SESSION['message']); unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <!-- Order Statistics -->
            <div class="orders-stats">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['total_orders']; ?></span>
                    <span class="stat-label">Total Orders</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo formatPrice($stats['total_revenue'] ?? 0); ?></span>
                    <span class="stat-label">Total Revenue</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['pending_orders']; ?></span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['paid_orders']; ?></span>
                    <span class="stat-label">Paid</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['shipped_orders']; ?></span>
                    <span class="stat-label">Shipped</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="search">Search Orders</label>
                        <input type="text" id="search" name="search" placeholder="Name, Email, or Order ID" value="<?php echo e($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="status">Status Filter</label>
                        <select id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $status_filter == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">Apply Filters</button>
                    <a href="orders.php" class="btn btn-outline">Clear</a>
                </form>
            </div>

            <!-- Orders Table -->
            <?php if (empty($orders)): ?>
                <div class="message info">
                    <p>No orders found matching your criteria.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo e($order['id']); ?></td>
                                <td><?php echo e($order['name']); ?></td>
                                <td><?php echo e($order['email']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo e($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="order-actions">
                                        <button type="button" class="btn btn-small" 
                                                onclick="viewOrder(<?php echo e($order['id']); ?>)">View</button>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                            <input type="hidden" name="order_id" value="<?php echo e($order['id']); ?>">
                                            <select name="status" onchange="this.form.submit()" class="status-select">
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="paid" <?php echo $order['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancel</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="orderDetails"></div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy;<?php echo date('Y'); ?> Needle & Thread. Admin Panel</p>
        </div>
    </footer>

    <script>
        // View order details
        function viewOrder(orderId) {
            fetch(`../api/orders.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.data;
                        const modal = document.getElementById('orderModal');
                        const details = document.getElementById('orderDetails');
                        const history = order.status_history || [];
                        const historyHtml = history.length
                            ? history.map(entry => `
                                <div class="history-entry">
                                    <div>
                                        <strong>${entry.new_status.charAt(0).toUpperCase() + entry.new_status.slice(1)}</strong>
                                        <span>${entry.note ?? ''}</span>
                                    </div>
                                    <div>
                                        <span>${entry.admin_name ?? 'System'}</span>
                                        <span>${new Date(entry.created_at).toLocaleString()}</span>
                                    </div>
                                </div>
                              `).join('')
                            : '<p>No status changes recorded.</p>';
                        
                        details.innerHTML = `
                            <h2>Order #${order.id}</h2>
                            <div class="order-info">
                                <p><strong>Customer:</strong> ${order.name}</p>
                                <p><strong>Email:</strong> ${order.email}</p>
                                <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                                <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status}</span></p>
                                <p><strong>Address:</strong> ${order.address.replace(/\n/g, '<br>')}</p>
                            </div>
                            <h3>Order Items</h3>
                            <div class="order-items">
                                ${order.items.map(item => `
                                    <div class="order-item" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                                        <div>
                                            <strong>${item.title}</strong>
                                            <br>Qty: ${item.quantity} × ${formatPrice(item.price)}
                                        </div>
                                        <div>${formatPrice(item.price * item.quantity)}</div>
                                    </div>
                                `).join('')}
                            </div>
                            <div class="order-total" style="text-align: right; margin-top: 20px; font-size: 1.2em; font-weight: bold;">
                                Total: ${formatPrice(order.total_price)}
                            </div>
                            <div class="order-history">
                                <h3>Status History</h3>
                                ${historyHtml}
                            </div>
                        `;
                        
                        modal.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    alert('Error loading order details');
                });
        }

        // Close modal
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('orderModal').style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Price formatting helper
        function formatPrice(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        }

        // Add CSS for modal
        const style = document.createElement('style');
        style.textContent = `
            .modal {
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }
            .modal-content {
                background-color: white;
                margin: 5% auto;
                padding: 20px;
                border-radius: 8px;
                width: 80%;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
                position: relative;
            }
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            .close:hover {
                color: black;
            }
            .status-select {
                padding: 4px 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: white;
            }
            .order-history {
                margin-top: 20px;
                border-top: 1px solid #eee;
                padding-top: 15px;
            }
            .order-history h3 {
                margin-bottom: 10px;
            }
            .history-entry {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid #f0f0f0;
                font-size: 0.9rem;
            }
            .history-entry:last-child {
                border-bottom: none;
            }
            .history-entry strong {
                display: inline-block;
                margin-right: 8px;
                text-transform: capitalize;
            }
        `;
        document.head.appendChild(style);

    </script>
</body>
</html>