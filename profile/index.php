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

// Fetch user data using your readuser function
$user = readuser($userId);
if (!$user) {
    die("User not found.");
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : $user['name'];
    $device = isset($_POST['device']) ? trim($_POST['device']) : $user['device'];
    $bot_token = isset($_POST['bot_token']) ? trim($_POST['bot_token']) : null;
    $bot_id = isset($_POST['bot_id']) ? trim($_POST['bot_id']) : null;
    $enableemail = isset($_POST['enableemail']) ? 1 : 0;
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Update user data
    $updateData = [];
    
    if (!empty($name) && $name !== $user['name']) {
        $updateData['name'] = $name;
    }
    
    if (!empty($device) && $device !== $user['device']) {
        $updateData['device'] = $device;
    }
    
    if ($bot_token !== $user['bot_token']) {
        $updateData['bot_token'] = $bot_token;
    }
    
    if ($bot_id !== $user['bot_id']) {
        $updateData['bot_id'] = $bot_id;
    }
    
    if ($enableemail !== $user['enableemail']) {
        $updateData['enableemail'] = $enableemail;
    }
    
    // Handle password change
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $message = 'Please enter your current password to change password.';
            $messageType = 'error';
        } elseif (!password_verify($current_password, $user['password'])) {
            $message = 'Current password is incorrect.';
            $messageType = 'error';
        } elseif (strlen($new_password) < 6) {
            $message = 'New password must be at least 6 characters long.';
            $messageType = 'error';
        } elseif ($new_password !== $confirm_password) {
            $message = 'New password and confirmation do not match.';
            $messageType = 'error';
        } else {
            $updateData['password'] = $new_password;
        }
    }
    
    // Execute update if there are changes
    if (!empty($updateData) && empty($message)) {
        $success = updateuser($userId, 
            $updateData['name'] ?? null,
            null,
            $updateData['password'] ?? null,
            null,
            $updateData['device'] ?? null,
            $updateData['bot_token'] ?? null,
            $updateData['bot_id'] ?? null,
            $updateData['enableemail'] ?? null,
            null
        );
        
        if ($success) {
            $message = 'Profile updated successfully!';
            $messageType = 'success';
            $user = readuser($userId);
            $_SESSION['user'] = $user;
        } else {
            $message = 'Failed to update profile. Please try again.';
            $messageType = 'error';
        }
    } elseif (empty($message)) {
        $message = 'No changes were made.';
        $messageType = 'info';
    }
}

// Get pawn count for this user
$pawnCount = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM pawn WHERE user_id = ?");
    $stmt->execute([$userId]);
    $pawnCount = $stmt->fetchColumn();
} catch (Exception $e) {
    $pawnCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Profile | Aetheris Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
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

        .input-field {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
            color: white;
            font-size: 0.95rem;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #C084FC;
            box-shadow: 0 0 0 3px rgba(192, 132, 252, 0.2);
            background: rgba(0, 0, 0, 0.6);
        }
        
        .input-field:hover:not(:disabled) {
            border-color: rgba(192, 132, 252, 0.6);
        }
        
        .input-field:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background: linear-gradient(105deg, #8B5CF6, #C084FC);
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
        }
        .btn-primary:active { transform: scale(0.96); }
        
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
        .btn-secondary:active { transform: scale(0.96); }

        /* Blobs */
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

        /* Avatar animation */
        .avatar-glow {
            animation: avatarPulse 3s ease-in-out infinite;
        }
        @keyframes avatarPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
            50% { box-shadow: 0 0 0 15px rgba(139, 92, 246, 0); }
        }
        
        /* Switch toggle */
        .switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
            flex-shrink: 0;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(30, 27, 46, 0.8);
            border: 1px solid rgba(139, 92, 246, 0.5);
            transition: 0.3s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 3px;
            bottom: 2px;
            background-color: #8B5CF6;
            transition: 0.3s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background: linear-gradient(105deg, #8B5CF6, #C084FC);
        }
        input:checked + .slider:before {
            transform: translateX(23px);
            background-color: white;
        }
        
        /* FAB Container */
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
        
        /* Animation for stats */
        .stat-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
        }
        
        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .grid-cols-3 { gap: 1.5rem; }
        }
        
        @media (max-width: 768px) {
            .container { padding-left: 1rem; padding-right: 1rem; }
            .glass-card { padding: 1.25rem; }
            h1 { font-size: 1.75rem; }
        }
        
        @media (max-width: 640px) {
            .fab-container { bottom: 20px; right: 20px; gap: 6px; }
            .fab-main { width: 50px; height: 50px; font-size: 24px; }
            .fab-action { width: 42px; height: 42px; }
            .fab-action.move-up1.active { transform: translateY(-50px); }
            .fab-action.move-up2.active { transform: translateY(-98px); }
            
            .stat-card { padding: 0.75rem; }
            .stat-card i { font-size: 1.25rem; }
            .stat-card p.text-2xl { font-size: 1.5rem; }
            
            .btn-primary, .btn-secondary { padding: 0.625rem 1rem; font-size: 0.875rem; }
            
            .input-field { padding: 0.625rem 1rem; font-size: 0.875rem; }
            
            .switch { width: 46px; height: 24px; }
            .slider:before { height: 18px; width: 18px; }
            input:checked + .slider:before { transform: translateX(21px); }
        }
        
        @media (max-width: 480px) {
            .grid { gap: 1rem; }
            .glass-card { padding: 1rem; }
            .avatar-glow { width: 80px; height: 80px; }
            .avatar-glow i { font-size: 3rem; }
            h3.text-lg { font-size: 1.125rem; }
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Focus visible for accessibility */
        *:focus-visible {
            outline: 2px solid #C084FC;
            outline-offset: 2px;
        }
        
        /* Password strength indicator */
        .strength-bar {
            height: 3px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        /* Responsive form layout */
        @media (max-width: 640px) {
            .flex.items-center.justify-between {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.75rem;
            }
            .switch {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="blob-1"></div>
<div class="blob-2"></div>
<div class="fixed inset-0 bg-black/20 backdrop-blur-[2px] -z-10"></div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6 md:py-8 lg:py-10">
    <!-- Header with responsive flex -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent flex items-center gap-2">
                <i class="bi bi-person-circle text-2xl sm:text-3xl md:text-4xl"></i>
                <span>My Profile</span>
            </h1>
            <p class="text-gray-400 text-xs sm:text-sm mt-1 flex items-center gap-1">
                <i class="bi bi-sliders2"></i> Manage your account settings
            </p>
        </div>
        <div class="glass-card rounded-full px-4 py-1.5 sm:px-5 sm:py-2 flex items-center gap-2">
            <i class="bi bi-calendar-check text-purple-300 text-sm sm:text-base"></i>
            <span class="text-xs sm:text-sm text-white/80 font-medium">Member since <?= date('M Y', strtotime($user['created_at'])) ?></span>
        </div>
    </div>

    <!-- Profile Content - Responsive Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
        <!-- Left Column - Avatar & Stats -->
        <div class="lg:col-span-1">
            <div class="glass-card rounded-xl sm:rounded-2xl p-4 sm:p-6 text-center">
                <!-- Avatar -->
                <div class="relative inline-block mb-4">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 mx-auto rounded-xl sm:rounded-2xl bg-gradient-to-br from-purple-600 to-fuchsia-600 flex items-center justify-center avatar-glow">
                        <i class="bi bi-person-fill text-4xl sm:text-5xl md:text-6xl text-white opacity-90"></i>
                    </div>
                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-green-500 rounded-full px-2 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-semibold text-white whitespace-nowrap">
                        <i class="bi bi-check-circle-fill text-[8px] sm:text-xs"></i> Active
                    </div>
                </div>
                
                <h2 class="text-lg sm:text-xl font-bold text-white mb-1 break-words"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="text-gray-400 text-xs sm:text-sm mb-4 break-all"><?= htmlspecialchars($user['email']) ?></p>
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-2 sm:gap-3 mt-4">
                    <div class="stat-card glass-card rounded-lg sm:rounded-xl p-2 sm:p-3">
                        <i class="bi bi-key-fill text-purple-400 text-lg sm:text-xl"></i>
                        <p class="text-xl sm:text-2xl font-bold text-white mt-1"><?= $pawnCount ?></p>
                        <p class="text-[10px] sm:text-xs text-gray-400">Pawn Records</p>
                    </div>
                    <div class="stat-card glass-card rounded-lg sm:rounded-xl p-2 sm:p-3">
                        <i class="bi bi-shield-lock-fill text-green-400 text-lg sm:text-xl"></i>
                        <p class="text-xl sm:text-2xl font-bold text-white mt-1"><?= $user['count'] ?></p>
                        <p class="text-[10px] sm:text-xs text-gray-400">Card Limit</p>
                    </div>
                </div>
                
                <div class="mt-4 pt-3 sm:pt-4 border-t border-purple-500/20">
                    <div class="flex justify-center gap-2 sm:gap-3 flex-wrap">
                        <span class="text-[10px] sm:text-xs text-gray-400 flex items-center gap-1">
                            <i class="bi bi-device-ssd text-purple-400"></i> 
                            <span class="break-all"><?= htmlspecialchars($user['device'] ?: 'Not set') ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column - Edit Form -->
        <div class="lg:col-span-2">
            <div class="glass-card rounded-xl sm:rounded-2xl p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-white mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="bi bi-pencil-square text-purple-400"></i> Edit Profile
                </h3>
                
                <?php if ($message): ?>
                <div class="mb-4 p-3 rounded-lg sm:rounded-xl <?= $messageType === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-300' : ($messageType === 'error' ? 'bg-red-500/20 border border-red-500/50 text-red-300' : 'bg-blue-500/20 border border-blue-500/50 text-blue-300') ?>">
                    <i class="bi <?= $messageType === 'success' ? 'bi-check-circle-fill' : ($messageType === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') ?> mr-2"></i>
                    <span class="text-xs sm:text-sm"><?= htmlspecialchars($message) ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="profileForm" class="space-y-3 sm:space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-gray-300 text-xs sm:text-sm font-medium mb-1.5 sm:mb-2">
                            <i class="bi bi-person-fill text-purple-400"></i> Full Name
                        </label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" 
                               class="input-field w-full px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white text-sm sm:text-base" 
                               placeholder="Enter your name">
                    </div>
                    
                    <!-- Email (Read-only) -->
                    <div>
                        <label class="block text-gray-300 text-xs sm:text-sm font-medium mb-1.5 sm:mb-2">
                            <i class="bi bi-envelope-fill text-purple-400"></i> Email Address
                        </label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                               class="input-field w-full px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-gray-400 text-sm sm:text-base cursor-not-allowed" 
                               placeholder="Email">
                        <p class="text-[10px] sm:text-xs text-gray-500 mt-1 flex items-center gap-1">
                            <i class="bi bi-info-circle"></i> Email cannot be changed
                        </p>
                    </div>
                    
                    <!-- Device -->
                    <div>
                        <label class="block text-gray-300 text-xs sm:text-sm font-medium mb-1.5 sm:mb-2">
                            <i class="bi bi-device-ssd text-purple-400"></i> Device Name
                        </label>
                        <input type="text" name="device" value="<?= htmlspecialchars($user['device'] ?? '') ?>" 
                               class="input-field w-full px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white text-sm sm:text-base" 
                               placeholder="e.g., iPhone 14, Windows PC">
                    </div>
                    
                    <!-- Bot Token -->
                    <div>
                        <label class="block text-gray-300 text-xs sm:text-sm font-medium mb-1.5 sm:mb-2">
                            <i class="bi bi-robot text-purple-400"></i> Bot Token
                        </label>
                        <input type="text" name="bot_token" value="<?= htmlspecialchars($user['bot_token'] ?? '') ?>" 
                               class="input-field w-full px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white font-mono text-[11px] sm:text-sm" 
                               placeholder="Enter bot token">
                    </div>
                    
                    <!-- Bot ID -->
                    <div>
                        <label class="block text-gray-300 text-xs sm:text-sm font-medium mb-1.5 sm:mb-2">
                            <i class="bi bi-hash text-purple-400"></i> Bot ID
                        </label>
                        <input type="text" name="bot_id" value="<?= htmlspecialchars($user['bot_id'] ?? '') ?>" 
                               class="input-field w-full px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white font-mono text-sm" 
                               placeholder="Enter bot ID">
                    </div>
                    
                    <!-- Enable Email Toggle - Responsive -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 sm:p-4 rounded-lg sm:rounded-xl bg-black/20 border border-purple-500/20 gap-3 sm:gap-0">
                        <div>
                            <label class="text-gray-300 text-xs sm:text-sm font-medium flex items-center gap-2">
                                <i class="bi bi-envelope-paper-fill text-purple-400"></i> Email Notifications
                            </label>
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Receive email notifications about your account</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enableemail" value="1" <?= $user['enableemail'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <!-- Change Password Section -->
                    <div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-purple-500/20">
                        <h4 class="text-sm sm:text-md font-semibold text-white mb-3 flex items-center gap-2">
                            <i class="bi bi-key-fill text-purple-400"></i> Change Password
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-gray-300 text-xs sm:text-sm font-medium mb-1.5 sm:mb-2">Current Password</label>
                                <div class="relative">
                                    <input type="password" name="current_password" id="current_password" 
                                           class="input-field w-full px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white pr-8 sm:pr-10 text-sm sm:text-base" 
                                           placeholder="Enter current password">
                                    <button type="button" onclick="togglePassword('current_password')" class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-400">
                                        <i class="bi bi-eye-fill text-xs sm:text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-300 text-xs sm:text-sm font-medium mb-1.5 sm:mb-2">New Password</label>
                                <div class="relative">
                                    <input type="password" name="new_password" id="new_password" 
                                           class="input-field w-full px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white pr-8 sm:pr-10 text-sm sm:text-base" 
                                           placeholder="Enter new password (min 6 characters)">
                                    <button type="button" onclick="togglePassword('new_password')" class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-400">
                                        <i class="bi bi-eye-fill text-xs sm:text-sm"></i>
                                    </button>
                                </div>
                                <div class="mt-1 h-1 w-full bg-gray-700 rounded-full overflow-hidden">
                                    <div id="passwordStrength" class="strength-bar h-full w-0 bg-red-500 rounded-full"></div>
                                </div>
                                <p id="strengthText" class="text-[10px] text-gray-500 mt-1"></p>
                            </div>
                            <div>
                                <label class="block text-gray-300 text-xs sm:text-sm font-medium mb-1.5 sm:mb-2">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" id="confirm_password" 
                                           class="input-field w-full px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white pr-8 sm:pr-10 text-sm sm:text-base" 
                                           placeholder="Confirm new password">
                                    <button type="button" onclick="togglePassword('confirm_password')" class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-400">
                                        <i class="bi bi-eye-fill text-xs sm:text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Buttons - Responsive -->
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-3 sm:pt-4">
                        <button type="submit" class="btn-primary flex-1 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white font-semibold flex items-center justify-center gap-2 text-sm sm:text-base">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                        <button type="button" onclick="resetForm()" class="btn-secondary sm:flex-none px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white font-medium flex items-center justify-center gap-2 text-sm sm:text-base">
                            <i class="bi bi-arrow-repeat"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Info Card -->
            <div class="glass-card rounded-xl sm:rounded-2xl p-3 sm:p-4 mt-4 sm:mt-6">
                <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-3">
                    <i class="bi bi-info-circle-fill text-purple-400 text-lg sm:text-xl flex-shrink-0"></i>
                    <div class="text-[11px] sm:text-sm text-gray-400 space-y-1">
                        <p><strong class="text-white">Card Limit:</strong> You can view up to <strong class="text-purple-400"><?= $user['count'] ?></strong> pawn records at a time.</p>
                        <p><strong class="text-white">Security:</strong> Your password is encrypted and never stored in plain text.</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-3 sm:pt-4">
                        <a href="../auth/logout.php" class=" bg-red-900/50 flex-1 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-white font-semibold flex items-center justify-center gap-2 text-sm sm:text-base cursor-pointer hover:bg-red-900/70 transition">
                            <i class="bi bi-box-arrow-right"></i> Log out
                        </a>
                    </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Menu -->
<div class="fab-container">
    <div class="fab-action move-up2" data-tooltip="Board" id="fabDashboard">
        <i class="bi bi-grid-3x3-gap-fill text-white text-lg sm:text-xl"></i>
    </div>
    <div class="fab-action move-up1" data-tooltip="Pawns" id="fabBoard">
        <i class="bi bi-person-vcard text-white text-lg sm:text-xl"></i>
    </div>
    <div class="fab-main" id="fabMain">
        <i class="bi bi-plus-lg text-xl sm:text-2xl"></i>
    </div>
</div>

<script>
    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let message = '';
            let color = '';
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
                return;
            }
            
            // Length check
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            
            // Contains numbers
            if (/\d/.test(password)) strength++;
            
            // Contains letters
            if (/[a-zA-Z]/.test(password)) strength++;
            
            // Contains special characters
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Determine strength
            if (strength <= 2) {
                message = 'Weak password';
                color = 'bg-red-500';
                strengthBar.style.width = '25%';
            } else if (strength <= 4) {
                message = 'Medium password';
                color = 'bg-yellow-500';
                strengthBar.style.width = '60%';
            } else {
                message = 'Strong password';
                color = 'bg-green-500';
                strengthBar.style.width = '100%';
            }
            
            strengthBar.className = `strength-bar h-full ${color} rounded-full`;
            strengthText.textContent = message;
            strengthText.className = `text-[10px] mt-1 ${color.replace('bg-', 'text-')}`;
        });
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
        toast.innerHTML = `${icon}<span class="text-xs sm:text-sm">${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast.remove) toast.remove(); }, 2800);
    }
    
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const icon = button.querySelector('i');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('bi-eye-fill');
            icon.classList.add('bi-eye-slash-fill');
        } else {
            field.type = 'password';
            icon.classList.remove('bi-eye-slash-fill');
            icon.classList.add('bi-eye-fill');
        }
    }
    
    // Reset form to original values
    function resetForm() {
        if (confirm('Reset all changes? Any unsaved changes will be lost.')) {
            location.reload();
        }
    }
    
    // Form validation before submit
    const form = document.getElementById('profileForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                showToast('New password and confirmation do not match!', 'error');
                return false;
            }
            
            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                showToast('New password must be at least 6 characters long!', 'error');
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="loading"></div> Saving...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                // Form will submit normally
            }, 100);
        });
    }
    
    // FAB Menu Logic
    const fabMain = document.getElementById('fabMain');
    const fabBoard = document.getElementById('fabBoard');
    const fabDashboard = document.getElementById('fabDashboard');
    
    let isFabOpen = false;
    
    function openFabMenu() {
        if (fabBoard) fabBoard.classList.add('active');
        if (fabDashboard) fabDashboard.classList.add('active');
        if (fabMain) fabMain.classList.add('rotate-plus');
        isFabOpen = true;
    }
    
    function closeFabMenu() {
        if (fabBoard) fabBoard.classList.remove('active');
        if (fabDashboard) fabDashboard.classList.remove('active');
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
    
    if (fabBoard) {
        fabBoard.addEventListener('click', (e) => {
            e.stopPropagation();
            closeFabMenu();
            window.location.href = "../rook/";
        });
    }
    
    if (fabDashboard) {
        fabDashboard.addEventListener('click', (e) => {
            e.stopPropagation();
            closeFabMenu();
            window.location.href = "../board/";
        });
    }
    
    document.addEventListener('click', (event) => {
        const fabContainer = document.querySelector('.fab-container');
        if (isFabOpen && fabContainer && !fabContainer.contains(event.target)) {
            closeFabMenu();
        }
    });
    
    // Responsive touch improvements
    if ('ontouchstart' in window) {
        document.querySelectorAll('button, .fab-action, .fab-main').forEach(btn => {
            btn.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.96)';
            });
            btn.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
    }
    
    console.log("Profile page loaded - Responsive design enabled");
</script>
</body>
</html>