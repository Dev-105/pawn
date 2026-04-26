<?php
// paypal_webhook.php - Handle PayPal IPN messages
include_once '../config/function.php';

// Log incoming webhook
$logFile = 'paypal_webhook_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook received\n", FILE_APPEND);
file_put_contents($logFile, file_get_contents('php://input') . "\n", FILE_APPEND);

// Verify that the request came from PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// Read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}

// Determine if we're in sandbox or live mode
$isSandbox = false; // LIVE MODE
$paypal_url = $isSandbox 
    ? "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr" 
    : "https://ipnpb.paypal.com/cgi-bin/webscr";

// Post back to PayPal system to validate
$ch = curl_init($paypal_url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$res = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

// Log the validation result
file_put_contents($logFile, date('Y-m-d H:i:s') . " - IPN Validation: " . $res . "\n", FILE_APPEND);
if ($curlError) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - CURL Error: " . $curlError . "\n", FILE_APPEND);
}

if (strcmp($res, "VERIFIED") == 0) {
    // Payment is verified
    $payment_status = $_POST['payment_status'];
    $custom = $_POST['custom'] ?? '';
    $txn_id = $_POST['txn_id'] ?? '';
    $receiver_email = $_POST['receiver_email'] ?? '';
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - VERIFIED - Payment Status: $payment_status, Custom: $custom\n", FILE_APPEND);
    
    if ($payment_status == 'Completed') {
        // Extract user ID and plan from custom field
        if (preg_match('/user_(\d+)_plan_(\w+)/', $custom, $matches)) {
            $userId = $matches[1];
            $plan = $matches[2];
            
            $plans = [
                'basic' => ['count' => 25, 'project' => 2],
                'pro' => ['count' => 70, 'project' => 3],
                'growth' => ['count' => 999999, 'project' => 10]
            ];
            
            if (isset($plans[$plan])) {
                // Update user limit (no payment_logs table needed)
                $updateSuccess = updateuserPlanUpgrade($userId, $plans[$plan]['count'], $plans[$plan]['project']);
                
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - User $userId upgraded to $plan - Update success: " . ($updateSuccess ? 'Yes' : 'No') . "\n", FILE_APPEND);
            }
        }
    }
} else {
    // Invalid payment
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - INVALID PayPal IPN\n", FILE_APPEND);
}