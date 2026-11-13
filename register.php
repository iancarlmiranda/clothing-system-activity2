<?php
session_start();
require 'db.php';
require 'auth.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // SECURITY: public sign-up is always a buyer. Admin accounts can only be
    // created by an existing admin in Users > Add user.
    $role = 'buyer';

    if ($fullname === '' || strlen($fullname) > 100) {
        $error = "Enter your full name (up to 100 characters).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $error = "Enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "This email address is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $success = "Registration successful! You may now sign in.";
            } else {
                $error = "An error occurred during registration. Please try again.";
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Apparel Enterprise</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <div class="brand-badge"> APPAREL</div>
            <h2>Create an Account</h2>
            <p class="subtitle">Register as a customer</p>

            <?php if (!empty($error)): ?>
                <div class="alert error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert success"><?php echo e($success); ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST" onsubmit="return validateRegister()">
                <div class="input-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Juan Dela Cruz" maxlength="100" value="<?php echo e($_POST['fullname'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="name@company.com" maxlength="100" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <label for="reg_password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="reg_password" name="password" placeholder="Minimum 6 characters" required>
                        <span class="toggle-password" onclick="togglePassword('reg_password')">Show</span>
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">Show</span>
                    </div>
                </div>

                <button type="submit" class="btn">Register</button>
            </form>

            <p class="switch-text">Already have an account? <a href="index.php">Sign In</a></p>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
