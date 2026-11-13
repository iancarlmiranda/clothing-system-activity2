<?php
require 'layout.php';
require_login();
$role = $_SESSION['role'];

page_header('Dashboard');

if ($role === 'admin'):
    $users    = (int)$conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
    $admins   = (int)$conn->query("SELECT COUNT(*) c FROM users WHERE role='admin'")->fetch_assoc()['c'];
    $products = (int)$conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
    $low      = (int)$conn->query("SELECT COUNT(*) c FROM products WHERE stock <= 5")->fetch_assoc()['c'];
?>
    <div class="welcome-card admin-header">
        <h1>Administrator Dashboard</h1>
        <p>Manage products and user accounts.</p>
    </div>

    <div class="info-grid">
        <div class="info-box stat">
            <h3>Users</h3>
            <p class="stat-number"><?php echo $users; ?></p>
            <p><?php echo $admins; ?> admin<?php echo $admins === 1 ? '' : 's'; ?>, <?php echo $users - $admins; ?> buyer<?php echo ($users - $admins) === 1 ? '' : 's'; ?></p>
            <p><a href="users.php">Manage users</a></p>
        </div>
        <div class="info-box stat">
            <h3>Products</h3>
            <p class="stat-number"><?php echo $products; ?></p>
            <p><?php echo $low; ?> low or out of stock</p>
            <p><a href="products.php">Manage products</a></p>
        </div>
    </div>

<?php else:
    $res = $conn->query("SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 3");
?>
    <div class="welcome-card">
        <h1>Welcome, <?php echo e($_SESSION['fullname']); ?></h1>
        <p>Browse our latest collections and manage your account details.</p>
    </div>

    <h2 class="section-title">New arrivals</h2>
    <?php if ($res->num_rows === 0): ?>
        <div class="info-box"><p>No products are available yet. Check back soon.</p></div>
    <?php else: ?>
        <div class="product-grid">
            <?php while ($p = $res->fetch_assoc()): ?>
                <div class="product-card">
                    <h4><?php echo e($p['name']); ?></h4>
                    <div class="category"><?php echo e($p['category']); ?></div>
                    <div class="price">&#8369;<?php echo number_format($p['price'], 2); ?></div>
                </div>
            <?php endwhile; ?>
        </div>
        <p><a href="shop.php" class="btn btn-inline">View all products</a></p>
    <?php endif; ?>
<?php endif; ?>

<?php page_footer(); ?>
