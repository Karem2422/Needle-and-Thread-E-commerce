<?php
require_once 'config.php';

// Handle filters and search
$search = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
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

$sql .= " ORDER BY p.created_at DESC";

// Pagination
$products_per_page = 9;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $products_per_page;

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
$total_products = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $products_per_page);

$sql .= " LIMIT $products_per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Needle & Thread</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="assets/images/Project Icon.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <h1><a href="index.php">Needle & Thread</a></h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php" class="active">Products</a></li>
                <li><a href="about.php">About</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/index.php">Admin</a></li>
                    <?php else: ?>
                        <li><a href="my_orders.php"<?php echo basename($_SERVER['PHP_SELF']) === 'my_orders.php' ? ' class="active"' : ''; ?>>My Orders</a></li>
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
            <h1>Our Collection</h1>
            
            <!-- Filters -->
            <div class="filters">
                <form method="GET" class="filter-form" id="filter-form">
                    <div class="filter-group">
                        <input type="text" name="search" id="search-input" placeholder="Search products..." value="<?php echo e($search); ?>">
                    </div>
                    <div class="filter-group">
                        <input type="number" name="min_price" id="min-price" placeholder="Min price" value="<?php echo e($min_price); ?>">
                        <input type="number" name="max_price" id="max-price" placeholder="Max price" value="<?php echo e($max_price); ?>">
                    </div>
                    <div class="filter-group">
                        <select name="status" id="status-select">
                            <option value="">All Status</option>
                            <option value="available" <?php echo $status == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="sold_out" <?php echo $status == 'sold_out' ? 'selected' : ''; ?>>Sold Out</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">Apply Filters</button>
                    <a href="products.php" class="btn btn-outline">Clear</a>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <p class="no-products">No products found matching your criteria.</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?php echo e($product['id']); ?>" class="product-card-link">
                            <div class="product-image">
                                <img src="uploads/<?php echo e($product['image_filename'] ?? 'placeholder.png'); ?>" alt="<?php echo e($product['title']); ?>">
                                <?php if ($product['status'] == 'sold_out'): ?>
                                    <span class="sold-out-badge">Sold Out</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?php echo e($product['title']); ?></h3>
                                <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                                <p class="product-status <?php echo e($product['status']); ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </p>
                            </div>
                        </a>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo e($product['id']); ?>" class="btn btn-secondary btn-block">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
        </div>
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>