<?php
require 'layout.php';
require_role('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$product = ['name' => '', 'category' => '', 'description' => '', 'price' => '', 'stock' => '0'];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) {
        flash('error', 'That product no longer exists.');
        header("Location: products.php");
        exit();
    }
    $product = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $product['name']        = trim($_POST['name'] ?? '');
    $product['category']    = trim($_POST['category'] ?? '');
    $product['description'] = trim($_POST['description'] ?? '');
    $product['price']       = trim($_POST['price'] ?? '');
    $product['stock']       = trim($_POST['stock'] ?? '');

    if ($product['name'] === '' || strlen($product['name']) > 100) $errors[] = "Enter a product name (up to 100 characters).";
    if ($product['category'] === '' || strlen($product['category']) > 50) $errors[] = "Enter a category (up to 50 characters).";
    if (!is_numeric($product['price']) || $product['price'] < 0 || $product['price'] > 99999999) $errors[] = "Enter a valid price of 0 or more.";
    if (filter_var($product['stock'], FILTER_VALIDATE_INT) === false || (int)$product['stock'] < 0) $errors[] = "Enter a whole-number stock of 0 or more.";

    if (!$errors) {
        $price = round((float)$product['price'], 2);
        $stock = (int)$product['stock'];
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, description=?, price=?, stock=? WHERE id=?");
            $stmt->bind_param("sssdii", $product['name'], $product['category'], $product['description'], $price, $stock, $id);
            $msg = "Product updated.";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, category, description, price, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdi", $product['name'], $product['category'], $product['description'], $price, $stock);
            $msg = "Product added.";
        }
        if ($stmt->execute()) {
            flash('success', $msg);
            header("Location: products.php");
            exit();
        }
        $errors[] = "Couldn't save the product. Please try again.";
    }
}

page_header($id ? 'Edit product' : 'Add product');
?>
<h2 class="section-title"><?php echo $id ? 'Edit product' : 'Add product'; ?></h2>
<?php foreach ($errors as $err): ?><div class="alert error"><?php echo e($err); ?></div><?php endforeach; ?>

<form method="POST" class="form-card">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <div class="input-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" maxlength="100" value="<?php echo e($product['name']); ?>" required>
    </div>
    <div class="input-group">
        <label for="category">Category</label>
        <input type="text" id="category" name="category" maxlength="50" placeholder="Tops, Bottoms, Outerwear..." value="<?php echo e($product['category']); ?>" required>
    </div>
    <div class="input-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"><?php echo e($product['description']); ?></textarea>
    </div>
    <div class="row-2">
        <div class="input-group">
            <label for="price">Price (&#8369;)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo e($product['price']); ?>" required>
        </div>
        <div class="input-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" step="1" min="0" value="<?php echo e($product['stock']); ?>" required>
        </div>
    </div>
    <button type="submit" class="btn"><?php echo $id ? 'Save changes' : 'Add product'; ?></button>
    <p class="switch-text"><a href="products.php">Cancel</a></p>
</form>

<?php page_footer(); ?>
