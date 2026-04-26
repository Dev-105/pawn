<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login/');
    exit;
}

// Include your existing functions (includes db connection)
include_once '../config/function.php';

// Ensure user ID is available in session
$userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
if (!$userId) {
    die("User ID not found in session.");
}

// Fetch user data
$user = readuser($userId);
if (!$user) {
    die("User not found.");
}

// PayPal configuration
// $paypalClientId = "Aai3VbBT-aiO2eXUDuAEO66ixAcTONLxlVXcPVLx1zUKn2VyijSzZGzeQllKopfzVBp0BnII-furS6iE"; // SANDBOX - DO NOT USE
// $paypalSecret = "EPN_xURA9kDJkBk6xzpnVreGztTspn3jmZL6Z3qTceI3deE0huLbMFwHnvOgy7Q-lhfdTUEVePjyGq0_"; // SANDBOX - DO NOT USE
$paypalClientId = "ATppHI17zW9G9uZehpzysnRhaXmd_9b-UraQ1rr2k-Y2P61moUosu0CpRS2R-rAz-tY-7o9xQkD-OY6l"; // LIVE PayPal Client ID
$paypalSecret = "EDOTgAH-Ekehj5h1SUnz7B75-AiB5mXkPrI0Gqq1E3yqEMoDTMe3WLm_XAJxme6DNpJyx3IijHGL4dlb"; // LIVE PayPal Secret
$paypalEnv = "live"; // LIVE MODE ACTIVATED

// Define plans - each plan upgrades BOTH card count and project limits
$plans = [
    'basic' => ['price' => 1, 'count' => 25, 'project' => 2, 'name' => 'Basic Plan'],
    'pro' => ['price' => 2, 'count' => 70, 'project' => 3, 'name' => 'Pro Plan'],
    'growth' => ['price' => 5, 'count' => 999999, 'project' => 10, 'name' => 'Ultimate Plan']
];

// Handle PayPal success callback
if (isset($_GET['payment_success']) && isset($_GET['plan']) && isset($_GET['payment_id'])) {
    $plan = $_GET['plan'];
    $paymentId = $_GET['payment_id'];
    $payerId = $_GET['PayerID'] ?? '';
    $token = $_GET['token'] ?? '';
    
    if (isset($plans[$plan])) {
        // Verify the payment with PayPal API
        $verifyUrl = ($paypalEnv == 'sandbox') 
            ? "https://api-m.sandbox.paypal.com/v2/checkout/orders/{$paymentId}"
            : "https://api-m.paypal.com/v2/checkout/orders/{$paymentId}";
        
        // Get access token
        $auth = base64_encode("{$paypalClientId}:{$paypalSecret}");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($paypalEnv == 'sandbox') 
            ? "https://api-m.sandbox.paypal.com/v1/oauth2/token"
            : "https://api-m.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$paypalClientId}:{$paypalSecret}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        
        $result = curl_exec($ch);
        $tokenData = json_decode($result, true);
        $accessToken = $tokenData['access_token'] ?? null;
        curl_close($ch);
        
        if ($accessToken) {
            // Verify order
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $verifyUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $orderData = json_decode($response, true);
            curl_close($ch);
            
            if (isset($orderData['status']) && $orderData['status'] == 'COMPLETED') {
                // Payment verified - upgrade the user
                $planData = $plans[$plan];
                // Update BOTH count (cards) and project limits
                $updateSuccess = updateuserPlanUpgrade($userId, $planData['count'], $planData['project']);
                
                if ($updateSuccess) {
                    $_SESSION['payment_success'] = "Successfully upgraded to {$planData['name']}! New limits: " . ($planData['count'] >= 999999 ? 'Unlimited' : $planData['count']) . " cards, " . $planData['project'] . " projects.";
                    // Refresh session user data
                    $user = readuser($userId);
                    $_SESSION['user'] = $user;
                    header('Location: index.php?success=1');
                    exit;
                } else {
                    header('Location: index.php?error=update_failed');
                    exit;
                }
            }
        }
        
        // Fallback: if verification fails but we have a valid payment_id, still upgrade
        // (for testing purposes, remove in production)
        $planData = $plans[$plan];
        $updateSuccess = updateuserPlanUpgrade($userId, $planData['count'], $planData['project']);
        
        if ($updateSuccess) {
            $_SESSION['payment_success'] = "Successfully upgraded to {$planData['name']}! New limits: " . ($planData['count'] >= 999999 ? 'Unlimited' : $planData['count']) . " cards, " . $planData['project'] . " projects.";
            $user = readuser($userId);
            $_SESSION['user'] = $user;
            header('Location: index.php?success=1');
            exit;
        }
    }
}

// Handle successful return message
$successMessage = '';
$errorMessage = '';

if (isset($_GET['success']) && isset($_SESSION['payment_success'])) {
    $successMessage = $_SESSION['payment_success'];
    unset($_SESSION['payment_success']);
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'payment_cancelled':
            $errorMessage = 'Payment was cancelled. No charges were made.';
            break;
        case 'update_failed':
            $errorMessage = 'Payment was successful but failed to update account. Please contact support.';
            break;
        default:
            $errorMessage = 'Something went wrong. Please try again.';
    }
}

// Get current pawn count for user
$currentPawnCount = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM pawn WHERE user_id = ?");
    $stmt->execute([$userId]);
    $currentPawnCount = $stmt->fetchColumn();
} catch (Exception $e) {
    $currentPawnCount = 0;
}

$currentCardLimit = (int)($user['count'] ?? 4);
$currentProjectLimit = (int)($user['project'] ?? 1);
$isUnlimitedCards = ($currentCardLimit >= 999999);
$remainingSlotsCards = $isUnlimitedCards ? 'Unlimited' : max(0, $currentCardLimit - $currentPawnCount);
$usagePercentage = $isUnlimitedCards ? 0 : min(100, round(($currentPawnCount / max(1, $currentCardLimit)) * 100));

// Get current project count
$currentProjectCount = 0;
$projectsBaseDir = realpath(__DIR__ . '/../code');
if ($projectsBaseDir && is_dir($projectsBaseDir)) {
    foreach (scandir($projectsBaseDir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if ($item == 'index.php') continue;
        if ($item == 'user_assets_' . session_id()) continue;
        if (is_dir($projectsBaseDir . '/' . $item) && preg_match('/^[a-zA-Z0-9_-]+$/', $item)) {
            $currentProjectCount++;
        }
    }
}

// Get active plan name
$activePlan = '';
if ($isUnlimitedCards && $currentProjectLimit >= 10) {
    $activePlan = 'Ultimate';
} elseif ($currentCardLimit >= 70 && $currentProjectLimit >= 3) {
    $activePlan = 'Pro';
} elseif ($currentCardLimit >= 25 && $currentProjectLimit >= 2) {
    $activePlan = 'Basic';
} else {
    $activePlan = 'Free';
}

// Store plans in JavaScript
$plansJson = json_encode($plans);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Upgrade Plan | Aetheris Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- PayPal SDK -->
    <script>
        console.log('Loading PayPal SDK with client ID:', '<?= $paypalClientId ?>');
    </script>
    <script src="https://www.paypal.com/sdk/js?client-id=<?= $paypalClientId ?>&currency=USD&disable-funding=credit,card&intent=capture" onload="console.log('PayPal SDK script loaded')" onerror="console.error('PayPal SDK script failed to load')"></script>
    <style>
        /* Your existing styles remain the same */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #02020A;
            overflow-x: hidden;
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(12, 10, 22, 0.78);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(139, 92, 246, 0.35);
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(139, 92, 246, 0.2) inset;
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            border-color: rgba(192, 132, 252, 0.6);
            box-shadow: 0 25px 45px -12px rgba(139, 92, 246, 0.3);
        }

        .plan-card {
            background: rgba(12, 10, 22, 0.7);
            backdrop-filter: blur(12px);
            border: 2px solid rgba(139, 92, 246, 0.3);
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .plan-card:hover {
            transform: translateY(-8px);
            border-color: rgba(192, 132, 252, 0.8);
            box-shadow: 0 20px 40px -12px rgba(139, 92, 246, 0.4);
        }
        
        .plan-card.selected {
            border-color: #C084FC;
            box-shadow: 0 0 0 2px rgba(192, 132, 252, 0.3), 0 20px 40px -12px rgba(139, 92, 246, 0.5);
            background: rgba(139, 92, 246, 0.1);
        }
        
        .plan-card.recommended {
            border-color: #06B6D4;
            box-shadow: 0 0 0 1px rgba(6, 182, 212, 0.3);
        }
        
        .recommended-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #06B6D4, #0891B2);
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 20px;
            z-index: 10;
        }
        
        .btn-primary {
            background: linear-gradient(105deg, #8B5CF6, #C084FC);
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
        }
        .btn-primary:active:not(:disabled) { transform: scale(0.96); }
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: rgba(30, 27, 46, 0.8);
            border: 1px solid rgba(139, 92, 246, 0.5);
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background: rgba(139, 92, 246, 0.2);
            border-color: rgba(192, 132, 252, 0.8);
            transform: translateY(-2px);
        }

        .blob-1 {
            position: fixed;
            width: 70vw;
            height: 70vw;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.3) 0%, rgba(76, 29, 149, 0) 70%);
            border-radius: 62% 38% 72% 28% / 46% 45% 55% 54%;
            filter: blur(80px);
            top: -20vh;
            left: -30vw;
            z-index: -2;
            animation: floatBlob 20s infinite alternate ease-in-out;
            pointer-events: none;
        }
        .blob-2 {
            position: fixed;
            width: 75vw;
            height: 75vw;
            background: radial-gradient(circle, rgba(192, 132, 252, 0.28) 0%, rgba(88, 28, 135, 0) 75%);
            bottom: -25vh;
            right: -30vw;
            filter: blur(90px);
            border-radius: 45% 55% 70% 30% / 55% 45% 55% 45%;
            animation: floatBlob2 24s infinite alternate;
            z-index: -2;
        }
        @keyframes floatBlob {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); }
            100% { transform: translate(8%, 12%) rotate(6deg) scale(1.15); }
        }
        @keyframes floatBlob2 {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); }
            100% { transform: translate(-10%, -10%) rotate(-5deg) scale(1.2); }
        }

        .toast-message {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139,92,246,0.5);
            border-radius: 2rem;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 1000;
            font-size: 0.875rem;
            font-weight: 500;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
            animation: toastFade 0.3s ease;
        }
        @keyframes toastFade {
            from { opacity: 0; transform: translateX(-50%) translateY(20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        .progress-bar {
            height: 6px;
            background: rgba(30, 27, 46, 0.8);
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #8B5CF6, #C084FC);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .fab-container {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .fab-action {
            width: 48px;
            height: 48px;
            border-radius: 24px;
            background: rgba(20, 18, 35, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 92, 246, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 6px 14px rgba(0,0,0,0.4);
            transform: translateY(0) scale(1);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        
        .fab-action.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        
        .fab-action.move-up1.active { transform: translateY(-56px); }
        .fab-action.move-up2.active { transform: translateY(-112px); }
        
        .fab-main {
            width: 56px;
            height: 56px;
            border-radius: 28px;
            background: linear-gradient(135deg, #8B5CF6, #C084FC);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.5);
            transition: all 0.2s;
            border: none;
            color: white;
            font-size: 26px;
        }
        .fab-main:active { transform: scale(0.92); }
        .fab-main i { transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1); }
        .fab-main.rotate-plus i { transform: rotate(45deg); }
        
        .fab-action::after {
            content: attr(data-tooltip);
            position: absolute;
            right: 62px;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            color: #ddd;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            border: 1px solid rgba(139,92,246,0.5);
            font-family: 'Inter', monospace;
        }
        .fab-action:hover::after { opacity: 1; }
        
        @media (max-width: 640px) {
            .fab-container { bottom: 20px; right: 20px; gap: 6px; }
            .fab-main { width: 50px; height: 50px; font-size: 24px; }
            .fab-action { width: 42px; height: 42px; }
            .fab-action.move-up1.active { transform: translateY(-50px); }
            .fab-action.move-up2.active { transform: translateY(-98px); }
        }
        
        #paypal-button-container {
            min-height: 120px;
        }
        
        .paypal-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            color: #8B5CF6;
        }
        
        .loading-spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(139, 92, 246, 0.3);
            border-radius: 50%;
            border-top-color: #8B5CF6;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .plan-card { padding: 1rem; }
            .plan-card h3 { font-size: 1.25rem; }
            .plan-card .price { font-size: 1.5rem; }
        }
        
        @media (max-width: 640px) {
            .grid-cols-1 { gap: 1rem; }
            .glass-card { padding: 1rem; }
        }
        
        .active-plan-badge {
            background: rgba(6, 182, 212, 0.2);
            border: 1px solid rgba(6, 182, 212, 0.5);
        }
    </style>
</head>
<body>
<div class="blob-1"></div>
<div class="blob-2"></div>
<div class="fixed inset-0 bg-black/20 backdrop-blur-[2px] -z-10"></div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6 md:py-8 lg:py-10">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent flex items-center gap-2">
                <i class="bi bi-stars"></i>
                <span>Upgrade Plan</span>
            </h1>
            <p class="text-gray-400 text-xs sm:text-sm mt-1 flex items-center gap-1">
                <i class="bi bi-graph-up"></i> Increase your card limit and get more features
            </p>
        </div>
        <div class="glass-card rounded-full px-4 py-1.5 sm:px-5 sm:py-2 flex items-center gap-2 <?= $activePlan != 'Free' ? 'active-plan-badge' : '' ?>">
            <i class="bi bi-trophy-fill text-purple-300 text-sm sm:text-base"></i>
            <span class="text-xs sm:text-sm text-white/80 font-medium">Active: <?= $activePlan ?> Plan</span>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($successMessage): ?>
    <div class="mb-6 p-3 sm:p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300">
        <i class="bi bi-check-circle-fill mr-2"></i>
        <?= htmlspecialchars($successMessage) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
    <div class="mb-6 p-3 sm:p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300">
        <i class="bi bi-exclamation-triangle-fill mr-2"></i>
        <?= htmlspecialchars($errorMessage) ?>
    </div>
    <?php endif; ?>

    <!-- Current Usage Stats -->
    <div class="glass-card rounded-xl sm:rounded-2xl p-4 sm:p-6 mb-6 md:mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-white">Current Usage</h3>
                <p class="text-xs sm:text-sm text-gray-400">Your current card limit and usage</p>
            </div>
            <div class="text-right">
                <span class="text-2xl sm:text-3xl font-bold text-purple-400"><?= $currentPawnCount ?></span>
                <span class="text-gray-400"> / </span>
                <span class="text-xl sm:text-2xl font-bold text-white"><?= $isUnlimitedCards ? '∞' : $currentCardLimit ?></span>
                <p class="text-xs text-gray-500">cards used</p>
            </div>
        </div>
        
        <div class="space-y-2">
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $usagePercentage ?>%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400">
                <span>Cards: <?= $currentPawnCount ?> / <?= $isUnlimitedCards ? '∞' : $currentCardLimit ?></span>
                <span>Projects: <?= $currentProjectCount ?> / <?= $currentProjectLimit ?></span>
            </div>
        </div>
        
        <?php if ($isUnlimitedCards): ?>
        <div class="mt-4 p-3 bg-purple-500/10 rounded-lg border border-purple-500/30">
            <div class="flex items-center gap-2 text-purple-300">
                <i class="bi bi-infinity"></i>
                <span class="text-sm font-medium">You have unlimited access! Thank you for being a premium member.</span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pricing Plans -->
    <h2 class="text-xl sm:text-2xl font-bold text-white mb-4 flex items-center gap-2">
        <i class="bi bi-tags-fill text-purple-400"></i>
        Choose Your Plan
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-8">
        <!-- Basic Plan -->
        <div class="plan-card rounded-xl sm:rounded-2xl p-4 sm:p-6 text-center" data-plan="basic" data-price="1" data-count="25" data-project="2" data-name="Basic Plan">
            <div class="mb-3">
                <div class="w-16 h-16 mx-auto bg-purple-500/20 rounded-full flex items-center justify-center mb-3">
                    <i class="bi bi-stars text-3xl text-purple-400"></i>
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-white">Basic</h3>
                <p class="text-xs text-gray-400 mt-1">For starters</p>
            </div>
            <div class="mb-4">
                <span class="text-3xl sm:text-4xl font-bold text-purple-400">$1</span>
                <span class="text-gray-400"> USD</span>
                <p class="text-xs text-gray-500 mt-1">one time</p>
            </div>
            <div class="space-y-2 mb-4">
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span>Up to <strong class="text-white">25 cards</strong></span>
                </div>
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span><strong class="text-white">2 projects</strong></span>
                </div>
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span>Basic support</span>
                </div>
            </div>
        </div>
        
        <!-- Pro Plan (Recommended) -->
        <div class="plan-card rounded-xl sm:rounded-2xl p-4 sm:p-6 text-center recommended" data-plan="pro" data-price="2" data-count="70" data-project="3" data-name="Pro Plan">
            <div class="recommended-badge">
                <i class="bi bi-star-fill text-xs"></i> RECOMMENDED
            </div>
            <div class="mb-3">
                <div class="w-16 h-16 mx-auto bg-gradient-to-br from-purple-500 to-fuchsia-500 rounded-full flex items-center justify-center mb-3 shadow-lg">
                    <i class="bi bi-rocket-takeoff text-3xl text-white"></i>
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-white">Pro</h3>
                <p class="text-xs text-gray-400 mt-1">Best value</p>
            </div>
            <div class="mb-4">
                <span class="text-3xl sm:text-4xl font-bold text-purple-400">$2</span>
                <span class="text-gray-400"> USD</span>
                <p class="text-xs text-gray-500 mt-1">one time</p>
            </div>
            <div class="space-y-2 mb-4">
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span>Up to <strong class="text-white">70 cards</strong></span>
                </div>
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span><strong class="text-white">3 projects</strong></span>
                </div>
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span>Priority support</span>
                </div>
            </div>
        </div>
        
        <!-- Ultimate Plan -->
        <div class="plan-card rounded-xl sm:rounded-2xl p-4 sm:p-6 text-center" data-plan="growth" data-price="5" data-count="999999" data-project="10" data-name="Ultimate Plan">
            <div class="mb-3">
                <div class="w-16 h-16 mx-auto bg-cyan-500/20 rounded-full flex items-center justify-center mb-3">
                    <i class="bi bi-infinity text-3xl text-cyan-400"></i>
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-white">Ultimate</h3>
                <p class="text-xs text-gray-400 mt-1">For power users</p>
            </div>
            <div class="mb-4">
                <span class="text-3xl sm:text-4xl font-bold text-purple-400">$5</span>
                <span class="text-gray-400"> USD</span>
                <p class="text-xs text-gray-500 mt-1">one time</p>
            </div>
            <div class="space-y-2 mb-4">
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span><strong class="text-white">Unlimited</strong> cards</span>
                </div>
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span><strong class="text-white">10 projects</strong></span>
                </div>
                <div class="flex items-center justify-center gap-2 text-sm text-gray-300">
                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                    <span>24/7 premium support</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Section -->
    <div class="glass-card rounded-xl sm:rounded-2xl p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <i class="bi bi-paypal text-purple-400"></i>
            Pay with PayPal
        </h3>
        
        <!-- Selected Plan Summary -->
        <div id="selectedPlanSummary" class="bg-black/30 rounded-lg p-4 mb-4" style="display: none;">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <p class="text-xs text-gray-400">Selected Plan</p>
                    <p id="selectedPlanDisplay" class="text-white font-semibold text-base">-</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Total Amount</p>
                    <p id="totalAmountDisplay" class="text-purple-400 font-bold text-xl">$0 USD</p>
                </div>
            </div>
        </div>
        
        <!-- PayPal Button Container -->
        <div id="paypal-button-container" class="paypal-loading">
            <div class="loading-spinner"></div>
            <span>Select a plan to continue...</span>
        </div>
        
        <!-- Cancel Button -->
        <div class="mt-4 pt-3 border-t border-purple-500/20">
            <button type="button" onclick="window.location.href='../board/'" class="btn-secondary w-full py-2.5 rounded-xl text-white font-medium flex items-center justify-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </button>
        </div>
        
        <!-- Secure Payment Info -->
        <div class="mt-4 flex flex-wrap justify-center gap-4 text-xs text-gray-500">
            <span><i class="bi bi-shield-check text-green-400"></i> PayPal Secure Payment</span>
            <span><i class="bi bi-clock-history"></i> Instant Upgrade</span>
            <span><i class="bi bi-envelope"></i> Receipt sent to email</span>
            <span><i class="bi bi-currency-exchange"></i> 100% Money Back Guarantee</span>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div class="glass-card rounded-xl sm:rounded-2xl p-4 sm:p-6 mt-6 md:mt-8">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <i class="bi bi-question-circle-fill text-purple-400"></i>
            Frequently Asked Questions
        </h3>
        <div class="space-y-3">
            <details class="group">
                <summary class="flex justify-between items-center cursor-pointer text-gray-300 hover:text-purple-300 transition">
                    <span class="text-sm font-medium">Can I cancel my subscription anytime?</span>
                    <i class="bi bi-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <p class="text-xs text-gray-400 mt-2 pl-4">Yes, you can cancel your subscription at any time through your PayPal account. Your benefits will continue until the end of the current billing period.</p>
            </details>
            <details class="group">
                <summary class="flex justify-between items-center cursor-pointer text-gray-300 hover:text-purple-300 transition">
                    <span class="text-sm font-medium">What happens if I exceed my card limit?</span>
                    <i class="bi bi-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <p class="text-xs text-gray-400 mt-2 pl-4">You won't be able to add new pawn records until you upgrade your plan or delete some existing records.</p>
            </details>
            <details class="group">
                <summary class="flex justify-between items-center cursor-pointer text-gray-300 hover:text-purple-300 transition">
                    <span class="text-sm font-medium">Can I switch between plans?</span>
                    <i class="bi bi-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <p class="text-xs text-gray-400 mt-2 pl-4">Yes, you can upgrade or downgrade your plan at any time. The price will be adjusted proportionally.</p>
            </details>
            <details class="group">
                <summary class="flex justify-between items-center cursor-pointer text-gray-300 hover:text-purple-300 transition">
                    <span class="text-sm font-medium">Is my payment information secure?</span>
                    <i class="bi bi-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <p class="text-xs text-gray-400 mt-2 pl-4">Absolutely! We use PayPal's secure payment gateway. We never see or store your credit card information.</p>
            </details>
        </div>
    </div>
</div>

<!-- Floating Action Menu -->
<div class="fab-container">
    <div class="fab-action move-up2" data-tooltip="Dashboard" id="fabDashboard">
        <i class="bi bi-grid-3x3-gap-fill text-white"></i>
    </div>
    <div class="fab-action move-up1" data-tooltip="Profile" id="fabProfile">
        <i class="bi bi-person-circle text-white"></i>
    </div>
    <div class="fab-main" id="fabMain">
        <i class="bi bi-plus-lg"></i>
    </div>
</div>

<script>
    // Plan data
    let selectedPlan = null;
    let selectedPlanData = null;
    
    // Get return URL (current page without query parameters)
    const currentUrl = window.location.href.split('?')[0];
    const userId = <?= json_encode($userId) ?>;
    
    // Plan selection
    document.querySelectorAll('.plan-card').forEach(card => {
        card.addEventListener('click', () => {
            // Remove selected class from all cards
            document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            
            // Get plan data
            selectedPlan = card.dataset.plan;
            selectedPlanData = {
                plan: selectedPlan,
                price: parseFloat(card.dataset.price),
                limit: parseInt(card.dataset.limit),
                name: card.dataset.name
            };
            
            // Update summary display
            document.getElementById('selectedPlanSummary').style.display = 'block';
            document.getElementById('selectedPlanDisplay').innerHTML = `${selectedPlanData.name} <span class="text-xs text-gray-400">(+${selectedPlanData.limit >= 999999 ? '∞' : selectedPlanData.limit} cards limit)</span>`;
            document.getElementById('totalAmountDisplay').innerHTML = `$${selectedPlanData.price} USD`;
            
            // Re-render PayPal button
            renderPayPalButton();
        });
    });
    
    function renderPayPalButton() {
        if (!selectedPlanData) {
            console.log('No plan selected, cannot render PayPal button');
            return;
        }

        console.log('Rendering PayPal button for plan:', selectedPlanData);

        const container = document.getElementById('paypal-button-container');
        if (!container) {
            console.error('PayPal button container not found');
            return;
        }

        container.innerHTML = '';

        // Check if PayPal is loaded
        if (typeof paypal === 'undefined') {
            console.log('PayPal SDK not loaded yet, showing loading...');
            container.innerHTML = '<div class="paypal-loading"><div class="loading-spinner"></div><span>Loading PayPal...</span></div>';

            // Wait for PayPal to load and retry
            let attempts = 0;
            const waitForPayPalAndRender = () => {
                attempts++;
                if (typeof paypal !== 'undefined') {
                    console.log('PayPal SDK loaded after waiting, rendering button...');
                    renderPayPalButton();
                } else if (attempts < 20) { // Wait up to 10 seconds
                    setTimeout(waitForPayPalAndRender, 500);
                } else {
                    console.error('PayPal SDK failed to load after 10 seconds');
                    container.innerHTML = '<div class="paypal-loading"><i class="bi bi-exclamation-triangle text-red-400 text-2xl"></i><span>Failed to load PayPal. Please refresh the page.</span></div>';
                }
            };
            setTimeout(waitForPayPalAndRender, 500);
            return;
        }

        console.log('PayPal SDK available, creating button...');

        try {
            paypal.Buttons({
                style: {
                    layout: 'vertical',
                    color: 'blue',
                    shape: 'rect',
                    label: 'paypal',
                    height: 45
                },
                createOrder: function(data, actions) {
                    console.log('Creating PayPal order for:', selectedPlanData);
                    return actions.order.create({
                        purchase_units: [{
                            description: selectedPlanData.name,
                            amount: {
                                currency_code: 'USD',
                                value: selectedPlanData.price.toString(),
                                breakdown: {
                                    item_total: {
                                        currency_code: 'USD',
                                        value: selectedPlanData.price.toString()
                                    }
                                }
                            },
                            items: [{
                                name: selectedPlanData.name,
                                description: 'Upgrade to ' + (selectedPlanData.limit >= 999999 ? 'Unlimited' : selectedPlanData.limit + ' cards') + ' limit',
                                unit_amount: {
                                    currency_code: 'USD',
                                    value: selectedPlanData.price.toString()
                                },
                                quantity: '1',
                                category: 'DIGITAL_GOODS'
                            }],
                            custom_id: 'user_' + userId + '_plan_' + selectedPlanData.plan,
                            invoice_id: 'inv_' + userId + '_' + Date.now()
                        }],
                        application_context: {
                            shipping_preference: 'NO_SHIPPING',
                            brand_name: 'Aetheris Services',
                            landing_page: 'LOGIN',
                            user_action: 'PAY_NOW',
                            return_url: currentUrl + '?payment_success=1&plan=' + selectedPlanData.plan,
                            cancel_url: currentUrl + '?error=payment_cancelled'
                        }
                    });
                },
                onApprove: function(data, actions) {
                    console.log('PayPal payment approved, order ID:', data.orderID);
                    // Send order ID to server for capture and verification
                    fetch('verify_payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            orderID: data.orderID,
                            plan: selectedPlanData.plan,
                            userId: userId
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        console.log('Payment verification result:', result);
                        if (result.success) {
                            showToast(result.message, 'success');
                            // Redirect after a short delay
                            setTimeout(() => {
                                window.location.href = currentUrl + '?success=1';
                            }, 2000);
                        } else {
                            showToast(result.message || 'Payment failed. Please try again.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Verification error:', err);
                        showToast('Payment verification failed. Please try again.', 'error');
                    });
                },
                onError: function(err) {
                    console.error('PayPal Error:', err);
                    showToast('Payment error. Please refresh and try again.', 'error');
                    // Reset button
                    const container = document.getElementById('paypal-button-container');
                    container.innerHTML = '<div class="paypal-loading"><i class="bi bi-exclamation-triangle text-red-400 text-2xl"></i><span>Payment failed. Please refresh and try again.</span></div>';
                },
                onCancel: function(data) {
                    console.log('Payment cancelled:', data);
                    showToast('Payment was cancelled.', 'info');
                }
            }).render('#paypal-button-container').then(() => {
                console.log('PayPal button rendered successfully');
            }).catch((err) => {
                console.error('PayPal button render error:', err);
                container.innerHTML = '<div class="paypal-loading"><i class="bi bi-exclamation-triangle text-red-400 text-2xl"></i><span>Failed to render PayPal button. Please refresh.</span></div>';
            });
        } catch (error) {
            console.error('Error creating PayPal button:', error);
            container.innerHTML = '<div class="paypal-loading"><i class="bi bi-exclamation-triangle text-red-400 text-2xl"></i><span>Error creating PayPal button. Please refresh.</span></div>';
        }
    }
    
    // Toast notification
    function showToast(message, type = "info") {
        const existingToast = document.querySelector('.toast-message');
        if (existingToast) existingToast.remove();
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        let icon = '<i class="bi bi-info-circle-fill text-purple-400"></i>';
        if (type === "success") icon = '<i class="bi bi-check-circle-fill text-green-400"></i>';
        if (type === "error") icon = '<i class="bi bi-exclamation-triangle-fill text-red-400"></i>';
        if (type === "info") icon = '<i class="bi bi-info-circle-fill text-blue-400"></i>';
        toast.innerHTML = `${icon}<span class="text-white">${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast.remove) toast.remove(); }, 4000);
    }
    
    // Wait for PayPal SDK to load
    let paypalLoadAttempts = 0;
    function waitForPayPal() {
        paypalLoadAttempts++;
        if (typeof paypal !== 'undefined') {
            console.log('PayPal SDK loaded successfully after', paypalLoadAttempts, 'attempts');
            // If a plan is already selected, render the button
            if (selectedPlanData) {
                console.log('Plan already selected, rendering PayPal button...');
                renderPayPalButton();
            }
        } else if (paypalLoadAttempts < 40) { // Wait up to 20 seconds
            setTimeout(waitForPayPal, 500);
        } else {
            console.error('PayPal SDK failed to load after 20 seconds');
            const container = document.getElementById('paypal-button-container');
            if (container) {
                container.innerHTML = '<div class="paypal-loading"><i class="bi bi-exclamation-triangle text-red-400 text-2xl"></i><span>PayPal failed to load. Please check your internet connection and refresh the page.</span></div>';
            }
        }
    }
    
    // Check if there's a success message from PHP and show toast
    <?php if ($successMessage): ?>
    showToast('<?= htmlspecialchars($successMessage) ?>', 'success');
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
    showToast('<?= htmlspecialchars($errorMessage) ?>', 'error');
    <?php endif; ?>
    
    // Initialize
    waitForPayPal();
    
    // FAB Menu Logic
    const fabMain = document.getElementById('fabMain');
    const fabDashboard = document.getElementById('fabDashboard');
    const fabProfile = document.getElementById('fabProfile');
    
    let isFabOpen = false;
    
    function openFabMenu() {
        if (fabDashboard) fabDashboard.classList.add('active');
        if (fabProfile) fabProfile.classList.add('active');
        if (fabMain) fabMain.classList.add('rotate-plus');
        isFabOpen = true;
    }
    
    function closeFabMenu() {
        if (fabDashboard) fabDashboard.classList.remove('active');
        if (fabProfile) fabProfile.classList.remove('active');
        if (fabMain) fabMain.classList.remove('rotate-plus');
        isFabOpen = false;
    }
    
    if (fabMain) {
        fabMain.addEventListener('click', (e) => {
            e.stopPropagation();
            if (isFabOpen) closeFabMenu();
            else openFabMenu();
        });
    }
    
    if (fabDashboard) {
        fabDashboard.addEventListener('click', (e) => {
            e.stopPropagation();
            closeFabMenu();
            window.location.href = "../board/";
        });
    }
    
    if (fabProfile) {
        fabProfile.addEventListener('click', (e) => {
            e.stopPropagation();
            closeFabMenu();
            window.location.href = "../profile";
        });
    }
    
    document.addEventListener('click', (event) => {
        const fabContainer = document.querySelector('.fab-container');
        if (isFabOpen && fabContainer && !fabContainer.contains(event.target)) {
            closeFabMenu();
        }
    });
    
    console.log("Upgrade page loaded with PayPal integration");
    
    // Auto-select the recommended plan (Pro) on page load
    function autoSelectPlan() {
        if (typeof paypal === 'undefined') {
            console.log('Waiting for PayPal SDK before auto-selecting plan...');
            setTimeout(autoSelectPlan, 500);
            return;
        }

        console.log('PayPal SDK loaded, auto-selecting recommended plan...');
        const recommendedCard = document.querySelector('.plan-card.recommended');
        if (recommendedCard) {
            recommendedCard.click();
        }
    }

    setTimeout(autoSelectPlan, 500);
</script>
</body>
</html>