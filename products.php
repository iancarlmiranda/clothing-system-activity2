<?php
require 'layout.php';
require_role('admin');

$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY id DESC");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$products = $stmt->get_result();

page_header('Products');
?>
<div class="page-head">
    <h2 class="section-title">Products</h2>
    <a href="product_form.php" class="btn btn-inline">Add product</a>
</div>

<form method="GET" class="toolbar">
    <input type="text" name="q" placeholder="Search by name or category" value="<?php echo e($q); ?>">
    <button type="submit" class="btn btn-inline">Search</button>
</form>

<?php if ($products->num_rows === 0): ?>
    <div class="info-box"><p>No products found. <a href="product_form.php">Add your first product</a>.</p></div>
<?php else: ?>
<div class="table-wrap">
<table class="data-table">
    <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
    <tbody>
    <?php while ($p = $products->fetch_assoc()): ?>
        <tr>
            <td><?php echo e($p['name']); ?></td>
            <td><?php echo e($p['category']); ?></td>
            <td>&#8369;<?php echo number_format($p['price'], 2); ?></td>
            <td>
                <?php if ($p['stock'] == 0): ?><span class="status-badge out">Sold out</span>
                <?php elseif ($p['stock'] <= 5): ?><span class="status-badge low"><?php echo (int)$p['stock']; ?></span>
                <?php else: ?><?php echo (int)$p['stock']; ?><?php endif; ?>
            </td>
            <td class="actions">
                <a href="product_form.php?id=<?php echo (int)$p['id']; ?>" class="btn-sm">Edit</a>
                <form method="POST" action="product_delete.php" onsubmit="return confirm('Delete this product? This cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                    <button type="submit" class="btn-sm danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php page_footer(); ?>
