<?php
require_once __DIR__ . '/layout.php';
page_header('Access denied');
?>
<div class="welcome-card">
    <h1>Access denied</h1>
    <p>Your account doesn't have permission to open this page.</p>
</div>
<a href="dashboard.php" class="btn btn-inline">Back to dashboard</a>
<?php page_footer(); ?>
