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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: donar.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$blood_group = trim($_POST['blood_group'] ?? '');
$city = trim($_POST['city'] ?? '');

if (!$name || !$email || !$phone || !$blood_group || !$city) {
    die('All fields are required. <a href="donar.html">Go back</a>');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('A valid email is required. <a href="donar.html">Go back</a>');
}

$phoneDigits = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
    die('Please provide a valid phone number. <a href="donar.html">Go back</a>');
}

$allowedGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
if (!in_array($blood_group, $allowedGroups, true)) {
    die('Please select a valid blood group. <a href="donar.html">Go back</a>');
}

$stmt = $conn->prepare('SELECT id FROM donors WHERE email = ? OR phone = ?');
if (!$stmt) {
    die('Database error: ' . $conn->error);
}
$stmt->bind_param('ss', $email, $phoneDigits);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    die('A donor with this email or phone already exists. <a href="donar.html">Go back</a>');
}
$stmt->close();

$stmt = $conn->prepare('INSERT INTO donors (name, email, phone, blood_group, city) VALUES (?, ?, ?, ?, ?)');
if (!$stmt) {
    die('Database error: ' . $conn->error);
}
$stmt->bind_param('sssss', $name, $email, $phoneDigits, $blood_group, $city);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    die('Unable to register donor at this time. Please try again later.');
}
$stmt->close();

$smsMessage = "New Donor Registered:\n"
    . "Name: {$name}\n"
    . "Blood: {$blood_group}\n"
    . "City: {$city}\n"
    . "Phone: {$phoneDigits}";

$notificationNumber = '+918121395284';
$smsResult = sendTwilioSms($notificationNumber, $smsMessage);

$conn->close();

if ($smsResult['success']) {
    header('Location: dashboard.html?donor=success');
    exit;
}

$errorText = urlencode('donor_sms_error');
header("Location: dashboard.html?donor=success&sms={$errorText}");
exit;
