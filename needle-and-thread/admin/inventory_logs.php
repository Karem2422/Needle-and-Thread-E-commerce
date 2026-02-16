<?php
require_once '../config.php';
requireAdmin();

$type_filter = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$sql = "SELECT l.*, p.title as product_title, u.name as admin_name
        FROM inventory_logs l
        LEFT JOIN products p ON l.product_id = p.id
        LEFT JOIN users u ON l.user_id = u.id
        WHERE 1=1";
$count_sql = "SELECT COUNT(*) FROM inventory_logs l LEFT JOIN products p ON l.product_id = p.id WHERE 1=1";
$params = [];
$count_params = [];

if (!empty($type_filter)) {
    $sql .= " AND l.change_type = ?";
    $count_sql .= " AND l.change_type = ?";
    $params[] = $type_filter;
    $count_params[] = $type_filter;
}

if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR l.note LIKE ?)";
    $count_sql .= " AND (p.title LIKE ? OR l.note LIKE ?)";
    $search_term = "%{$search}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
}

$sql .= " ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_logs = $count_stmt->fetchColumn();
$total_pages = ceil($total_logs / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Logs - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="../assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
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
                <li><a href="inventory_logs.php" class="active">Inventory Logs</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reviews.php">Reviews</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="newsletter.php">Newsletter</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="main section">
        <div class="container">
            <div class="section-header">
                <div>
                    <p class="label">Audits</p>
                    <h1>Inventory Logs</h1>
                </div>
            </div>

            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Product or note" value="<?php echo e($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="type">Change Type</label>
                        <select id="type" name="type">
                            <option value="">All Types</option>
                            <option value="order" <?php echo $type_filter == 'order' ? 'selected' : ''; ?>>Order</option>
                            <option value="refund" <?php echo $type_filter == 'refund' ? 'selected' : ''; ?>>Refund</option>
                            <option value="manual" <?php echo $type_filter == 'manual' ? 'selected' : ''; ?>>Manual</option>
                            <option value="adjustment" <?php echo $type_filter == 'adjustment' ? 'selected' : ''; ?>>Adjustment</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">Apply Filters</button>
                    <a href="inventory_logs.php" class="btn btn-outline">Clear</a>
                </form>
            </div>

            <?php if (empty($logs)): ?>
                <div class="message info">
                    <p>No inventory changes recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity Change</th>
                                <th>Note</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                                <td><?php echo e($log['product_title'] ?? $log['product_name'] ?? 'Deleted Product'); ?></td>
                                <td><?php echo ucfirst($log['change_type']); ?></td>
                                <td><?php echo e($log['quantity_change']); ?></td>
                                <td><?php echo e($log['note']); ?></td>
                                <td><?php echo e($log['admin_name'] ?? 'System'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

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

    <footer class="footer">
        <div class="container">
            <p>&copy;<?php echo date('Y'); ?> Needle & Thread. Admin Panel</p>
        </div>
    </footer>
</body>
</html>

