<?php
require 'layout.php';
require_login(); // buyers and admins can browse

$q   = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');
$like = "%$q%";

if ($cat !== '') {
    $stmt = $conn->prepare("SELECT * FROM products WHERE (name LIKE ? OR description LIKE ?) AND category = ? ORDER BY name");
    $stmt->bind_param("sss", $like, $like, $cat);
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY name");
    $stmt->bind_param("ss", $like, $like);
}
$stmt->execute();
$products = $stmt->get_result();
$cats = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");

page_header('Shop');
?>
<h2 class="section-title">Shop</h2>

<form method="GET" class="toolbar">
    <input type="text" name="q" placeholder="Search products" value="<?php echo e($q); ?>">
    <select name="category" class="input-select">
        <option value="">All categories</option>
        <?php while ($c = $cats->fetch_assoc()): ?>
            <option value="<?php echo e($c['category']); ?>" <?php echo $cat === $c['category'] ? 'selected' : ''; ?>><?php echo e($c['category']); ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-inline">Search</button>
</form>

<?php if ($products->num_rows === 0): ?>
    <div class="info-box"><p>No products match your search.</p></div>
<?php else: ?>
    <div class="product-grid">
        <?php while ($p = $products->fetch_assoc()): ?>
            <div class="product-card">
                <h4><?php echo e($p['name']); ?></h4>
                <div class="category"><?php echo e($p['category']); ?></div>
                <?php if ($p['description']): ?><p class="desc"><?php echo e($p['description']); ?></p><?php endif; ?>
                <div class="price">&#8369;<?php echo number_format($p['price'], 2); ?></div>
                <?php if ($p['stock'] == 0): ?>
                    <span class="status-badge out">Sold out</span>
                <?php elseif ($p['stock'] <= 5): ?>
                    <span class="status-badge low">Only <?php echo (int)$p['stock']; ?> left</span>
                <?php else: ?>
                    <span class="status-badge">In stock</span>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php page_footer(); ?>
