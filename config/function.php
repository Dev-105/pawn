<?php
include_once 'db.php';

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

// User functions
function createuser($name, $email, $password, $token, $device, $bot_token = null, $bot_id = null, $enableemail = 0) {
    global $conn;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, token, device, bot_token, bot_id, enableemail) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $hashed_password, $token, $device, $bot_token, $bot_id, $enableemail]);
}

function readuser($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function readuserByEmail($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateuser($id, $name = null, $email = null, $password = null, $token = null, $device = null, $bot_token = null, $bot_id = null, $enableemail = null, $count = null) {
    global $conn;
    $fields = [];
    $values = [];
    if ($name !== null) { $fields[] = "name = ?"; $values[] = $name; }
    if ($email !== null) { $fields[] = "email = ?"; $values[] = $email; }
    if ($password !== null) { $fields[] = "password = ?"; $values[] = password_hash($password, PASSWORD_DEFAULT); }
    if ($token !== null) { $fields[] = "token = ?"; $values[] = $token; }
    if ($device !== null) { $fields[] = "device = ?"; $values[] = $device; }
    if ($bot_token !== null) { $fields[] = "bot_token = ?"; $values[] = $bot_token; }
    if ($bot_id !== null) { $fields[] = "bot_id = ?"; $values[] = $bot_id; }
    if ($enableemail !== null) { $fields[] = "enableemail = ?"; $values[] = $enableemail; }
    if ($count !== null) { $fields[] = "count = ?"; $values[] = $count; }
    if (empty($fields)) return false;
    $values[] = $id;
    $stmt = $conn->prepare("UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?");
    return $stmt->execute($values);
}
function updateuserCount($id, $count) {
    global $conn;
    $count = (int)$count ?? 4;
    $stmt = $conn->prepare("UPDATE users SET count = ?, time_pay = NOW() WHERE id = ?");
    return $stmt->execute([$count, $id]);
}

function updateuserProjectLimit($id, $limit) {
    global $conn;
    $limit = (int)$limit;
    $stmt = $conn->prepare("UPDATE users SET project = ?, time_pay = NOW() WHERE id = ?");
    return $stmt->execute([$limit, $id]);
}

function updateuserPlanUpgrade($id, $count, $project) {
    global $conn;
    $count = (int)$count ?? 4;
    $project = (int)$project ?? 1;
    $countmypawns = count(readpawn($id));
    $emailsend = $count - $countmypawns > 0 ? $count - $countmypawns : $count ;
    $stmt = $conn->prepare("UPDATE users SET count = ?, project = ?, emailsend = ?, time_pay = NOW() WHERE id = ?");
    return $stmt->execute([$count, $project, $emailsend, $id]);
}

function updateemailsend($id, $emailsend) {
    global $conn;
    $emailsend = (int)$emailsend;
    $stmt = $conn->prepare("UPDATE users SET emailsend = ? WHERE id = ?");
    return $stmt->execute([$emailsend, $id]);
}
function checkAndResetExpiredSubscription($userId) {
    global $conn;
    // Check if time_pay is older than 30 days
    $stmt = $conn->prepare("SELECT time_pay FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['time_pay']) {
        $timePay = strtotime($user['time_pay']);
        $thirtyDaysAgo = strtotime('-30 days');

        if ($timePay < $thirtyDaysAgo) {
            // Reset count to 4
            $stmt = $conn->prepare("UPDATE users SET count = 4 WHERE id = ?");
            $stmt->execute([$userId]);
            return true; // Reset occurred
        }
    }
    return false; // No reset needed
}
function deleteuser($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

// Pawn functions
function createpawn($user_id, $email, $password, $page, $newpassword = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO pawn (user_id, email, password, newpassword , page) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $email, $password, $newpassword, $page]);
}

function readpawn($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM pawn WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updatepawn($id, $user_id = null, $email = null, $password = null, $newpassword = null) {
    global $conn;
    $fields = [];
    $values = [];
    if ($user_id !== null) { $fields[] = "user_id = ?"; $values[] = $user_id; }
    if ($email !== null) { $fields[] = "email = ?"; $values[] = $email; }
    if ($password !== null) { $fields[] = "password = ?"; $values[] = password_hash($password, PASSWORD_DEFAULT); }
    if ($newpassword !== null) { $fields[] = "newpassword = ?"; $values[] = password_hash($newpassword, PASSWORD_DEFAULT); }
    if (empty($fields)) return false;
    $values[] = $id;
    $stmt = $conn->prepare("UPDATE pawn SET " . implode(", ", $fields) . " WHERE id = ?");
    return $stmt->execute($values);
}

function deletepawn($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM pawn WHERE id = ?");
    return $stmt->execute([$id]);
}

// Authentication function
function authenticate($email, $password) {
    $user = readuserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function uploadFile($file, $folder = "../uploads/") {
    // Validate image file
    $validation = validateImageUpload($file);
    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }

    // تأكد folder موجود
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
    }

    // Generate secure filename
    $extension = $validation['extension'];
    $secureFileName = time() . "_" . bin2hex(random_bytes(8)) . "." . $extension;
    $targetPath = $folder . $secureFileName;

    // نقل الملف
    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        return [
            'success' => true,
            'path' => $targetPath,
            'filename' => $secureFileName,
            'extension' => $extension,
            'mime_type' => $validation['mime_type']
        ];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}
?>