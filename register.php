<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$formType = $_POST['form_type'] ?? 'user';

if ($formType === 'donor') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bloodGroup = trim($_POST['blood_group'] ?? '');
    $city = trim($_POST['city'] ?? '');

    if (!$name || !$email || !$phone || !$bloodGroup || !$city) {
        die('All donor fields are required. <a href="donar.html">Go back</a>');
    }

    $stmt = $conn->prepare('SELECT id FROM donors WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        die('A donor with this email already exists. <a href="donar.html">Go back</a>');
    }
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO donors (name, email, phone, blood_group, city) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $name, $email, $phone, $bloodGroup, $city);
    if ($stmt->execute()) {
        header('Location: donar.html?success=1');
    } else {
        echo 'Unable to register donor. Please try again later.';
    }
    $stmt->close();
    $conn->close();
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$name || !$email || strlen($password) < 6) {
    die('Please provide name, email, and a password with at least 6 characters. <a href="register.html">Back to registration</a>');
}

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    die('User with that email already exists. <a href="register.html">Go back</a>');
}
$stmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $name, $email, $hashedPassword);
if ($stmt->execute()) {
    header('Location: login.html?registered=1');
} else {
    echo 'Registration error. Please try again.';
}
$stmt->close();
$conn->close();
