<?php
session_start();
require 'db.php';
require 'auth.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // One generic message so attackers can't discover which emails exist.
    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['role'] = $row['role'];
        header("Location: dashboard.php");
        exit();
    }
    $error = "Incorrect email or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Apparel Enterprise</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <div class="brand-badge"> APPAREL</div>
            <h2>Sign In</h2>
            <p class="subtitle">Access your clothing shop account</p>

            <?php if (!empty($error)): ?>
                <div class="alert error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="name@company.com" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <span class="toggle-password" onclick="togglePassword('password')">Show</span>
                    </div>
                </div>

                <button type="submit" class="btn">Sign In</button>
            </form>

            <p class="switch-text">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
