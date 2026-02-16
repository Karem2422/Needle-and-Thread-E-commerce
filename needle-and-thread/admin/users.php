<?php
require_once '../config.php';
requireAdmin();

$errors = [];
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    } else {
        $user_id = intval($_POST['delete_user_id']);
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception("User not found.");
            }

            if ($user['is_admin']) {
                throw new Exception("Cannot delete admin accounts.");
            }

            $updateOrders = $pdo->prepare("UPDATE orders SET user_id = NULL WHERE user_id = ?");
            $updateOrders->execute([$user_id]);

            $updateComments = $pdo->prepare("UPDATE comments SET user_id = NULL WHERE user_id = ?");
            $updateComments->execute([$user_id]);

            $deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $deleteUser->execute([$user_id]);

            $pdo->commit();
            $_SESSION['message'] = "User deleted successfully.";
            header("Location: users.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

try {
    $stmt = $pdo->query("SELECT id, name, email, is_admin, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin users error: " . $e->getMessage());
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="../assets/images/Project Icon.png">
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
                <li><a href="inventory_logs.php">Inventory Logs</a></li>
                <li><a href="users.php" class="active">Users</a></li>
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
                    <p class="label">Community</p>
                    <h1>Registered Users</h1>
                </div>
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

            <?php if (empty($users)): ?>
                <div class="message info">
                    <p>No users found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo e($user['id']); ?></td>
                                <td><?php echo e($user['name']); ?></td>
                                <td><?php echo e($user['email']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $user['is_admin'] ? 'status-paid' : 'status-pending'; ?>">
                                        <?php echo $user['is_admin'] ? 'Admin' : 'Customer'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if (!$user['is_admin']): ?>
                                    <form method="POST" onsubmit="return confirm('Delete this user?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="delete_user_id" value="<?php echo e($user['id']); ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                    </form>
                                    <?php else: ?>
                                        <span class="text-light">Protected</span>
                                    <?php endif; ?>
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

