<?php
require 'layout.php';
require_login();

$uid = (int)$_SESSION['user_id'];
$errors = [];

$stmt = $conn->prepare("SELECT fullname, email, password FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';

    if ($fullname === '' || strlen($fullname) > 100) $errors[] = "Enter your full name (up to 100 characters).";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) $errors[] = "Enter a valid email address.";

    if (!$errors) {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $chk->bind_param("si", $email, $uid);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) $errors[] = "That email address is already used by another account.";
        $chk->close();
    }

    $newHash = null;
    if ($new !== '') {
        if (!password_verify($current, $me['password'])) $errors[] = "Current password is incorrect.";
        elseif (strlen($new) < 6) $errors[] = "New password must be at least 6 characters.";
        else $newHash = password_hash($new, PASSWORD_DEFAULT);
    }

    if (!$errors) {
        if ($newHash) {
            $u = $conn->prepare("UPDATE users SET fullname=?, email=?, password=? WHERE id=?");
            $u->bind_param("sssi", $fullname, $email, $newHash, $uid);
        } else {
            $u = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
            $u->bind_param("ssi", $fullname, $email, $uid);
        }
        $u->execute();
        $u->close();
        flash('success', 'Profile updated.');
        header("Location: profile.php");
        exit();
    }
    $me['fullname'] = $fullname;
    $me['email'] = $email;
}

page_header('My Profile');
?>
<h2 class="section-title">My Profile</h2>
<?php foreach ($errors as $err): ?><div class="alert error"><?php echo e($err); ?></div><?php endforeach; ?>

<form method="POST" class="form-card">
    <?php echo csrf_field(); ?>
    <div class="input-group">
        <label for="fullname">Full name</label>
        <input type="text" id="fullname" name="fullname" maxlength="100" value="<?php echo e($me['fullname']); ?>" required>
    </div>
    <div class="input-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" maxlength="100" value="<?php echo e($me['email']); ?>" required>
    </div>
    <hr class="divider">
    <p class="hint">To change your password, fill in both fields below. Otherwise leave them blank.</p>
    <div class="input-group">
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password">
    </div>
    <div class="input-group">
        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" placeholder="Minimum 6 characters" autocomplete="new-password">
    </div>
    <button type="submit" class="btn">Save changes</button>
</form>

<?php page_footer(); ?>
