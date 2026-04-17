<?php
require_once 'config.php';

function sendTwilioSms($to, $message)
{
    $accountSid = 'AC0410ee6d69e28352e86dc469039c66a8';
    $authToken = '19ba550e994d1152a1a6dbcec17738dc';
    $fromNumber = '+15703564609';
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

    $postData = [
        'From' => $fromNumber,
        'To' => $to,
        'Body' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $accountSid . ':' . $authToken);

    $response = curl_exec($ch);
    if ($response === false) {
        $errorMessage = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'error' => $errorMessage];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'response' => $response,
        'http_code' => $httpCode
    ];
}

function ensureRequestTable($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS requests (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        blood_group VARCHAR(5) NOT NULL,
        city VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$conn->query($sql)) {
        die('Unable to create requests table: ' . $conn->error);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: request.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$blood_group = trim($_POST['blood_group'] ?? '');
$city = trim($_POST['city'] ?? '');

if (!$name || !$phone || !$blood_group || !$city) {
    die('All fields are required. <a href="request.html">Go back</a>');
}

$phoneDigits = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
    die('Please provide a valid phone number. <a href="request.html">Go back</a>');
}

$allowedGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
if (!in_array($blood_group, $allowedGroups, true)) {
    die('Please select a valid blood group. <a href="request.html">Go back</a>');
}

ensureRequestTable($conn);

$stmt = $conn->prepare('INSERT INTO requests (name, phone, blood_group, city) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    die('Database error: ' . $conn->error);
}
$stmt->bind_param('ssss', $name, $phoneDigits, $blood_group, $city);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    die('Unable to save your request at this time. Please try again later.');
}
$stmt->close();

$smsMessage = "New Blood Request:\n"
    . "Name: {$name}\n"
    . "Blood: {$blood_group}\n"
    . "City: {$city}\n"
    . "Contact: {$phoneDigits}";

$notificationNumber = '+918121395284';
$smsResult = sendTwilioSms($notificationNumber, $smsMessage);

$conn->close();

if ($smsResult['success']) {
    header('Location: dashboard.html?request=success');
    exit;
}

$errorText = urlencode('request_sms_error');
header("Location: dashboard.html?request=success&sms={$errorText}");
exit;
