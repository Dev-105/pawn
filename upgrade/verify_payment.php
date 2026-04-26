<?php
session_start();
header('Content-Type: application/json');

// Include database connection
include_once '../config/function.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$orderID = $input['orderID'] ?? '';
$plan = $input['plan'] ?? '';
$userId = $input['userId'] ?? '';

// Validate input
if (!$orderID || !$plan || !$userId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// PayPal configuration
// $paypalClientId = "Aai3VbBT-aiO2eXUDuAEO66ixAcTONLxlVXcPVLx1zUKn2VyijSzZGzeQllKopfzVBp0BnII-furS6iE"; // SANDBOX - DO NOT USE
// $paypalSecret = "EPN_xURA9kDJkBk6xzpnVreGztTspn3jmZL6Z3qTceI3deE0huLbMFwHnvOgy7Q-lhfdTUEVePjyGq0_"; // SANDBOX - DO NOT USE
$paypalClientId = "ATppHI17zW9G9uZehpzysnRhaXmd_9b-UraQ1rr2k-Y2P61moUosu0CpRS2R-rAz-tY-7o9xQkD-OY6l"; // LIVE PayPal Client ID
$paypalSecret = "EDOTgAH-Ekehj5h1SUnz7B75-AiB5mXkPrI0Gqq1E3yqEMoDTMe3WLm_XAJxme6DNpJyx3IijHGL4dlb"; // LIVE PayPal Secret
$paypalEnv = "live"; // LIVE MODE ACTIVATED

// Define plans
$plans = [
    'basic' => ['price' => 1, 'count' => 25, 'project' => 2, 'name' => 'Basic Plan'],
    'pro' => ['price' => 2, 'count' => 70, 'project' => 3, 'name' => 'Pro Plan'],
    'growth' => ['price' => 5, 'count' => 999999, 'project' => 10, 'name' => 'Ultimate Plan']
];

function getPayPalAccessToken($clientId, $secret, $env) {
    $url = ($env == 'sandbox')
        ? "https://api-m.sandbox.paypal.com/v1/oauth2/token"
        : "https://api-m.paypal.com/v1/oauth2/token";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$clientId}:{$secret}");
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

    $result = curl_exec($ch);
    $tokenData = json_decode($result, true);
    curl_close($ch);

    return $tokenData['access_token'] ?? null;
}

function capturePayPalOrder($orderId, $accessToken, $env) {
    $url = ($env == 'sandbox')
        ? "https://api-m.sandbox.paypal.com/v2/checkout/orders/{$orderId}/capture"
        : "https://api-m.paypal.com/v2/checkout/orders/{$orderId}/capture";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $captureData = json_decode($result, true);
    curl_close($ch);

    return $captureData;
}

function verifyPayPalOrder($orderId, $accessToken, $env) {
    $url = ($env == 'sandbox')
        ? "https://api-m.sandbox.paypal.com/v2/checkout/orders/{$orderId}"
        : "https://api-m.paypal.com/v2/checkout/orders/{$orderId}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $orderData = json_decode($result, true);
    curl_close($ch);

    return $orderData;
}

try {
    // Get access token
    $accessToken = getPayPalAccessToken($paypalClientId, $paypalSecret, $paypalEnv);
    if (!$accessToken) {
        echo json_encode(['success' => false, 'message' => 'Failed to get PayPal access token']);
        exit;
    }

    // First, capture the order
    $captureResult = capturePayPalOrder($orderID, $accessToken, $paypalEnv);
    if (!isset($captureResult['status']) || $captureResult['status'] !== 'COMPLETED') {
        echo json_encode(['success' => false, 'message' => 'Failed to capture payment', 'details' => $captureResult]);
        exit;
    }

    // Then verify the captured order
    $orderData = verifyPayPalOrder($orderID, $accessToken, $paypalEnv);
    if (!isset($orderData['status']) || $orderData['status'] !== 'COMPLETED') {
        echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
        exit;
    }

    // Validate plan and amount
    if (!isset($plans[$plan])) {
        echo json_encode(['success' => false, 'message' => 'Invalid plan']);
        exit;
    }

    $expectedAmount = $plans[$plan]['price'];
    $actualAmount = floatval($orderData['purchase_units'][0]['amount']['value'] ?? 0);

    if ($actualAmount != $expectedAmount) {
        echo json_encode(['success' => false, 'message' => 'Amount mismatch']);
        exit;
    }

    // Validate user ID (optional - you might want to check if it matches the order)
    // For now, we'll trust the user ID from the session/client

    // Update user count
    $updateSuccess = updateuserPlanUpgrade($userId, $plans[$plan]['count'], $plans[$plan]['project']);

    if ($updateSuccess) {
        // Refresh session user data
        $user = readuser($userId);
        $_SESSION['user'] = $user;

        echo json_encode([
            'success' => true,
            'message' => 'Payment successful! ' . $plans[$plan]['name'] . ' activated.',
            'new_count' => $plans[$plan]['count'],
            'new_project' => $plans[$plan]['project']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment successful but failed to update account. Please contact support.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>