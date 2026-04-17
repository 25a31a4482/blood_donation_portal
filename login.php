<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    die('Email and password are required. <a href="login.html">Go back</a>');
}

$stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    die('Invalid credentials. <a href="login.html">Try again</a>');
}
$stmt->bind_result($id, $name, $hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($password, $hash)) {
    die('Invalid credentials. <a href="login.html">Try again</a>');
}

$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $name;

header('Location: dashboard.html');
$conn->close();
exit;
