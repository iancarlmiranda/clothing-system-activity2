<?php
require_once __DIR__ . '/auth.php';

function page_header(string $title): void
{
    $role = $_SESSION['role'];
    $current = basename($_SERVER['PHP_SELF']);
    $links = [
        'dashboard.php' => 'Dashboard',
        'shop.php'      => 'Shop',
    ];
    if ($role === 'admin') {
        $links['products.php'] = 'Products';
        $links['users.php']    = 'Users';
    }
    $links['profile.php'] = 'My Profile';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> | Apparel Enterprise</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
    <nav class="navbar">
        <div class="nav-brand"> APPAREL <span class="role-tag"><?php echo e(strtoupper($role)); ?></span></div>
        <div class="nav-links">
            <?php foreach ($links as $file => $label): ?>
                <a href="<?php echo e($file); ?>" class="<?php echo $current === $file ? 'active' : ''; ?>"><?php echo e($label); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="nav-user">
            <span><?php echo e($_SESSION['fullname']); ?></span>
            <a href="logout.php" class="logout-link">Sign Out</a>
        </div>
    </nav>
    <div class="dashboard-container">
        <?php show_flash(); ?>
<?php
}

function page_footer(): void
{
    ?>
    </div>
    <script src="script.js"></script>
</body>
</html>
<?php
}
