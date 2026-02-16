<?php
require_once '../config.php';
requireAdmin();

$errors = [];
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

$status_filter = $_GET['status'] ?? 'all';
$valid_filters = ['all', 'pending', 'published'];
if (!in_array($status_filter, $valid_filters, true)) {
    $status_filter = 'all';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    } else {
        $comment_id = null;

        if (isset($_POST['delete_comment_id'])) {
            $comment_id = intval($_POST['delete_comment_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
                $stmt->execute([$comment_id]);
                $_SESSION['message'] = "Review deleted successfully.";
                header("Location: reviews.php?status=" . $status_filter);
                exit;
            } catch (PDOException $e) {
                $errors[] = "Failed to delete review.";
                error_log("Delete review error: " . $e->getMessage());
            }
        } elseif (isset($_POST['approve_comment_id'])) {
            $comment_id = intval($_POST['approve_comment_id']);
            try {
                $stmt = $pdo->prepare("UPDATE comments SET approved = 1 WHERE id = ?");
                $stmt->execute([$comment_id]);
                $_SESSION['message'] = "Review approved and published.";
                header("Location: reviews.php?status=" . $status_filter);
                exit;
            } catch (PDOException $e) {
                $errors[] = "Failed to approve review.";
                error_log("Approve review error: " . $e->getMessage());
            }
        } elseif (isset($_POST['unapprove_comment_id'])) {
            $comment_id = intval($_POST['unapprove_comment_id']);
            try {
                $stmt = $pdo->prepare("UPDATE comments SET approved = 0 WHERE id = ?");
                $stmt->execute([$comment_id]);
                $_SESSION['message'] = "Review unpublished and hidden from the store.";
                header("Location: reviews.php?status=" . $status_filter);
                exit;
            } catch (PDOException $e) {
                $errors[] = "Failed to unpublish review.";
                error_log("Unapprove review error: " . $e->getMessage());
            }
        }
    }
}

try {
    $counts_stmt = $pdo->query("SELECT 
                                   COUNT(*) AS total,
                                   SUM(CASE WHEN approved = 0 THEN 1 ELSE 0 END) AS pending,
                                   SUM(CASE WHEN approved = 1 THEN 1 ELSE 0 END) AS published
                                FROM comments");
    $counts = $counts_stmt->fetch(PDO::FETCH_ASSOC);
    $counts = [
        'all' => intval($counts['total'] ?? 0),
        'pending' => intval($counts['pending'] ?? 0),
        'published' => intval($counts['published'] ?? 0),
    ];

    $sql = "SELECT c.*, p.title AS product_title 
            FROM comments c
            LEFT JOIN products p ON c.product_id = p.id
            WHERE 1 = 1";
    $params = [];

    if ($status_filter === 'pending') {
        $sql .= " AND c.approved = 0";
    } elseif ($status_filter === 'published') {
        $sql .= " AND c.approved = 1";
    }

    $sql .= " ORDER BY c.approved ASC, c.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Failed to load reviews.";
    $reviews = [];
    $counts = ['all' => 0, 'pending' => 0, 'published' => 0];
    error_log("Load reviews error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="../assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
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
                <li><a href="reviews.php" class="active">Reviews</a></li>
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
                    <p class="label">Feedback</p>
                    <h1>Customer Reviews</h1>
                    <p class="text-light"><?php echo $counts['all']; ?> total · <?php echo $counts['pending']; ?> pending</p>
                </div>
            </div>

            <div class="filter-pills">
                <a href="?status=all" class="filter-pill <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                    All <span><?php echo $counts['all']; ?></span>
                </a>
                <a href="?status=pending" class="filter-pill <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                    Pending <span><?php echo $counts['pending']; ?></span>
                </a>
                <a href="?status=published" class="filter-pill <?php echo $status_filter === 'published' ? 'active' : ''; ?>">
                    Published <span><?php echo $counts['published']; ?></span>
                </a>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo e($message); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="message error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <div class="message info">
                    <p>
                        <?php if ($status_filter === 'pending'): ?>
                            No reviews waiting for approval.
                        <?php elseif ($status_filter === 'published'): ?>
                            No published reviews yet.
                        <?php else: ?>
                            No customer reviews yet.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td>#<?php echo e($review['id']); ?></td>
                                <td><?php echo e($review['product_title'] ?? 'Deleted product'); ?></td>
                                <td>
                                    <strong><?php echo e($review['name']); ?></strong><br>
                                    <span class="text-light"><?php echo e($review['email']); ?></span>
                                </td>
                                <td><?php echo nl2br(e($review['comment'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $review['approved'] ? 'status-published' : 'status-pending'; ?>">
                                        <?php echo $review['approved'] ? 'Published' : 'Pending approval'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <div class="action-stack">
                                        <?php if (!$review['approved']): ?>
                                            <form method="POST">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                <input type="hidden" name="approve_comment_id" value="<?php echo e($review['id']); ?>">
                                                <button type="submit" class="btn btn-small btn-primary">Approve</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" onsubmit="return confirm('Unpublish this review?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                <input type="hidden" name="unapprove_comment_id" value="<?php echo e($review['id']); ?>">
                                                <button type="submit" class="btn btn-small btn-secondary">Unpublish</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Permanently delete this review?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                            <input type="hidden" name="delete_comment_id" value="<?php echo e($review['id']); ?>">
                                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
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

