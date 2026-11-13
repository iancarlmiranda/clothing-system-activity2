<?php
require 'layout.php';
require_role('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isSelf = ($id === (int)$_SESSION['user_id']);
$user = ['fullname' => '', 'email' => '', 'role' => 'buyer'];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT id, fullname, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) {
        flash('error', 'That user no longer exists.');
        header("Location: users.php");
        exit();
    }
    $user = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $user['fullname'] = trim($_POST['fullname'] ?? '');
    $user['email']    = trim($_POST['email'] ?? '');
    $role             = $_POST['role'] ?? 'buyer';
    $password         = $_POST['password'] ?? '';

    // An admin can't change their own role (prevents locking everyone out).
    $user['role'] = $isSelf ? 'admin' : (in_array($role, ['buyer', 'admin'], true) ? $role : 'buyer');

    if ($user['fullname'] === '' || strlen($user['fullname']) > 100) $errors[] = "Enter a full name (up to 100 characters).";
    if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL) || strlen($user['email']) > 100) $errors[] = "Enter a valid email address.";
    if ($id === 0 && strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($id > 0 && $password !== '' && strlen($password) < 6) $errors[] = "New password must be at least 6 characters.";

    if (!$errors) {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $chk->bind_param("si", $user['email'], $id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) $errors[] = "That email address is already registered.";
        $chk->close();
    }

    if (!$errors) {
        if ($id === 0) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $user['fullname'], $user['email'], $hash, $user['role']);
            $msg = "User added.";
        } elseif ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, role=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $user['fullname'], $user['email'], $user['role'], $hash, $id);
            $msg = "User updated and password reset.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, role=? WHERE id=?");
            $stmt->bind_param("sssi", $user['fullname'], $user['email'], $user['role'], $id);
            $msg = "User updated.";
        }
        if ($stmt->execute()) {
            flash('success', $msg);
            header("Location: users.php");
            exit();
        }
        $errors[] = "Couldn't save the user. Please try again.";
    }
}

page_header($id ? 'Edit user' : 'Add user');
?>
<h2 class="section-title"><?php echo $id ? 'Edit user' : 'Add user'; ?></h2>
<?php foreach ($errors as $err): ?><div class="alert error"><?php echo e($err); ?></div><?php endforeach; ?>

<form method="POST" class="form-card">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <div class="input-group">
        <label for="fullname">Full name</label>
        <input type="text" id="fullname" name="fullname" maxlength="100" value="<?php echo e($user['fullname']); ?>" required>
    </div>
    <div class="input-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" maxlength="100" value="<?php echo e($user['email']); ?>" required>
    </div>
    <div class="input-group">
        <label for="role">Role</label>
        <select id="role" name="role" class="input-select" <?php echo $isSelf ? 'disabled' : ''; ?>>
            <option value="buyer" <?php echo $user['role'] === 'buyer' ? 'selected' : ''; ?>>Buyer (Customer)</option>
            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
        </select>
        <?php if ($isSelf): ?><p class="hint">You can't change your own role.</p><?php endif; ?>
    </div>
    <div class="input-group">
        <label for="password"><?php echo $id ? 'New password' : 'Password'; ?></label>
        <input type="password" id="password" name="password" placeholder="<?php echo $id ? 'Leave blank to keep the current password' : 'Minimum 6 characters'; ?>" autocomplete="new-password" <?php echo $id ? '' : 'required'; ?>>
    </div>
    <button type="submit" class="btn"><?php echo $id ? 'Save changes' : 'Add user'; ?></button>
    <p class="switch-text"><a href="users.php">Cancel</a></p>
</form>

<?php page_footer(); ?>
