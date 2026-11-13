<?php
require 'auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit();
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

flash($stmt->affected_rows > 0 ? 'success' : 'error', $stmt->affected_rows > 0 ? 'Product deleted.' : 'Product not found.');
header("Location: products.php");
exit();
