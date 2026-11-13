<?php
require 'layout.php';
require_role('admin');

$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$stmt = $conn->prepare("SELECT id, fullname, email, role, created_at FROM users WHERE fullname LIKE ? OR email LIKE ? ORDER BY id");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$users = $stmt->get_result();

page_header('Users');
?>
<div class="page-head">
    <h2 class="section-title">Users</h2>
    <a href="user_form.php" class="btn btn-inline">Add user</a>
</div>

<form method="GET" class="toolbar">
    <input type="text" name="q" placeholder="Search by name or email" value="<?php echo e($q); ?>">
    <button type="submit" class="btn btn-inline">Search</button>
</form>

<div class="table-wrap">
<table class="data-table">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php while ($u = $users->fetch_assoc()): $self = ((int)$u['id'] === (int)$_SESSION['user_id']); ?>
        <tr>
            <td><?php echo e($u['fullname']); ?><?php echo $self ? ' <span class="status-badge">You</span>' : ''; ?></td>
            <td><?php echo e($u['email']); ?></td>
            <td><span class="role-tag"><?php echo e(strtoupper($u['role'])); ?></span></td>
            <td><?php echo e(date('M j, Y', strtotime($u['created_at']))); ?></td>
            <td class="actions">
                <a href="user_form.php?id=<?php echo (int)$u['id']; ?>" class="btn-sm">Edit</a>
                <?php if (!$self): ?>
                <form method="POST" action="user_delete.php" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                    <button type="submit" class="btn-sm danger">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>

<?php page_footer(); ?>
