<?php
require 'auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit();
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);

if ($id === (int)$_SESSION['user_id']) {
    flash('error', "You can't delete your own account.");
} else {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) flash('success', 'User deleted.');
    else flash('error', 'User not found.');
}
header("Location: users.php");
exit();
