<?php
require_once '../config.php';
requireAdmin();

// Get subscribers
$stmt = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
$subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscribers - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="../assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .subscribers-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .subscribers-table th,
        .subscribers-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #EAD5B7;
        }
        .subscribers-table th {
            background-color: #8B6D5C;
            color: white;
            font-weight: 600;
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
                <li><a href="orders.php">Orders</a></li>
                <li><a href="newsletter.php" class="active">Newsletter</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="main section">
        <div class="container">
            <div class="section-header">
                <h1>Newsletter Subscribers</h1>
            </div>
            
            <?php if (empty($subscribers)): ?>
                <div class="message info">
                    <p>No subscribers yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="subscribers-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subscribed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td>#<?php echo e($subscriber['id']); ?></td>
                                <td><?php echo e($subscriber['name']); ?></td>
                                <td><?php echo e($subscriber['email']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($subscriber['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
