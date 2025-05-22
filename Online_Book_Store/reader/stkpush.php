<?php
// stkpush.php
header('Content-Type: application/json');
date_default_timezone_set('Africa/Nairobi');
require_once 'accessToken.php'; // must set $access_token

// Safaricom credentials
$BusinessShortCode = '174379'; // or your actual Shortcode
$Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'; // from Daraja portal
$Timestamp = date('YmdHis');
$Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);

// Get POST data
$phone = $_POST['phone'] ?? '';
$amount = $_POST['amount'] ?? '';

// Validate
if (!$phone || !$amount || !preg_match('/^2547\d{8}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number or amount']);
    exit;
}

// Callback URL (should be public - use Ngrok or live server)
$callback_url = 'https://51ef-102-0-14-100.ngrok-free.app'; // change this!

// Prepare STK Push request
$stkpush_payload = [
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => (int)$amount,
    'PartyA' => $phone,
    'PartyB' => $BusinessShortCode,
    'PhoneNumber' => $phone,
    'CallBackURL' => $callback_url,
    'AccountReference' => 'Bookstore',
    'TransactionDesc' => 'Book Purchase'
];

// Send request to Safaricom API
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($stkpush_payload)
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

// Handle response
if ($err) {
    echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . $err]);
    exit;
}

$res = json_decode($response, true);

if (isset($res['ResponseCode']) && $res['ResponseCode'] === '0') {
    echo json_encode(['status' => 'success', 'message' => 'STK Push initiated. Check your phone to complete the payment.']);
} else {
    $errorMessage = $res['errorMessage'] ?? 'Failed to initiate payment';
    echo json_encode(['status' => 'error', 'message' => $errorMessage]);
}
