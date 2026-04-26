<?php
session_start();
// include_once '../../config/db.php'; // Commented out - using JSON instead of MySQL
include_once '../../config/function.php';
// Include your mailer
include_once '../../mail/send.php'; 

$error = '';
$success = '';
$action = $_GET['action'] ?? 'login'; // 'login', 'forgot', 'verify', 'reset'

// --- POST REQUEST HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. LOGIN LOGIC
    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password';
        } else {
            $user = authenticate($email, $password);
            if ($user) {
                $_SESSION['user'] = $user;
                header('Location: ../../board/index.php');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        }
    }
    
    // 2. FORGOT PASSWORD LOGIC (Send Email)
    elseif ($action === 'forgot') {
        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            $error = 'Please enter your email';
        } else {
            // Note: You should ideally check if the email exists in your DB here first
            
            $code = rand(100000, 999999);
            $_SESSION['reset_code'] = $code;
            $_SESSION['reset_email'] = $email;

            // Using inline CSS to match your Aetheris purple theme in the email
            $body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #8B5CF6, #C084FC); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .code { font-size: 32px; font-weight: bold; color: #8B5CF6; text-align: center; padding: 20px; background: white; border-radius: 10px; margin: 20px 0; letter-spacing: 5px; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Password Reset Request</h2>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>" . htmlspecialchars($email) . "</strong>,</p>
                        <p>We received a request to reset the password for your Aetheris account. Please use the verification code below to set a new password:</p>
                        <div class='code'>" . $code . "</div>
                        <p>This code will expire in <strong>5 minutes</strong>.</p>
                        <p>If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>
                        <br>
                        <p>Best regards,<br>The Aetheris Team</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2024 Aetheris. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
                     
            if (sendEmail($email, $body, "Aetheris - Password Reset Code")) {
                header('Location: ?action=verify');
                exit;
            } else {
                $error = 'Failed to send email. Please try again.';
            }
        }
    } 
    
    // 3. VERIFY CODE LOGIC
    elseif ($action === 'verify') {
        $code = $_POST['code'] ?? '';
        if (isset($_SESSION['reset_code']) && $code == $_SESSION['reset_code']) {
            $_SESSION['code_verified'] = true;
            header('Location: ?action=reset');
            exit;
        } else {
            $error = 'Invalid verification code.';
        }
    } 
    
    // 4. RESET PASSWORD LOGIC
    elseif ($action === 'reset') {
        $new_password = $_POST['new_password'] ?? '';
        
        // Security check to ensure they verified the code
        if (!isset($_SESSION['code_verified']) || $_SESSION['code_verified'] !== true) {
            header('Location: ?action=forgot');
            exit;
        }
        
        if (empty($new_password)) {
            $error = 'Please enter a new password';
        } else {
            $email = $_SESSION['reset_email'];
            
            // Find user by email and update password
            $user = readuserByEmail($email);
            if ($user) {
                // Update the password using the updateuser function
                if (updateuser($user['id'], null, null, $new_password)) {
                    // Clean up session variables
                    unset($_SESSION['reset_code']);
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['code_verified']);
                    
                    $success = 'Password successfully updated! You can now login.';
                    $action = 'login'; // Switch the view back to login
                } else {
                    $error = 'Failed to update password. Please try again.';
                }
            } else {
                $error = 'User not found. Please try again.';
            }
        }
    }
}

// If user is already logged in, redirect to home
if ((isset($_SESSION['user_id']) || isset($_SESSION['user'])) && $action === 'login') {
    header('Location: ../../board/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Login | Aetheris</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap"
        rel="stylesheet">
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

        /* Glassmorphism Core */
        .glass-card {
            background: rgba(12, 10, 22, 0.78);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(139, 92, 246, 0.35);
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(139, 92, 246, 0.2) inset;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(105deg, #8B5CF6, #C084FC);
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -6px rgba(139, 92, 246, 0.6);
        }

        .btn-primary:active {
            transform: scale(0.97);
        }

        /* Input focus glow */
        .input-glow {
            transition: all 0.2s ease;
        }

        .input-glow:focus {
            outline: none;
            border-color: #C084FC;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.4), 0 0 10px #A855F7;
            background-color: rgba(8, 6, 18, 0.9);
        }

        /* Animated blobs */
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
            pointer-events: none;
        }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); }
            100% { transform: translate(8%, 12%) rotate(6deg) scale(1.15); }
        }

        @keyframes floatBlob2 {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); }
            100% { transform: translate(-10%, -10%) rotate(-5deg) scale(1.2); }
        }

        @media (max-width: 480px) {
            .glass-card { padding: 1.25rem; }
        }

        /* Messages */
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            text-align: center;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include '../../config/cursor.php'; ?>

    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <div class="fixed inset-0 bg-black/20 backdrop-blur-[2px] -z-10"></div>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md mx-auto">
            <div class="glass-card rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl w-full">

                <?php if ($error): ?>
                    <div class="error-message mb-5">
                        <i class="bi bi-exclamation-triangle-fill mr-2"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message mb-5">
                        <i class="bi bi-check-circle-fill mr-2"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($action === 'login'): ?>
                    <div class="text-center mb-7">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-br from-purple-600/30 to-fuchsia-500/30 rounded-2xl flex items-center justify-center mb-3 border border-purple-400/40">
                            <i class="bi bi-box-arrow-in-right text-3xl text-purple-300"></i>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent">
                            Welcome Back
                        </h2>
                        <p class="text-gray-400 text-sm mt-1">Sign in to your account</p>
                    </div>

                    <form method="POST" action="?action=login">
                        <div class="space-y-5">
                            <div>
                                <label class="text-sm font-medium text-purple-200 flex gap-2 mb-1">
                                    <i class="bi bi-envelope"></i> Email Address
                                </label>
                                <input type="email" name="email" required
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    placeholder="hello@aetheris.com"
                                    class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white placeholder:text-gray-500 focus:outline-none">
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="text-sm font-medium text-purple-200 flex gap-2">
                                        <i class="bi bi-lock"></i> Password
                                    </label>
                                    <a href="?action=forgot" class="text-xs text-purple-400 hover:text-purple-300 transition-colors">Forgot?</a>
                                </div>
                                <input type="password" name="password" required placeholder="••••••••"
                                    class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white focus:outline-none">
                            </div>

                            <button type="submit" class="btn-primary w-full py-3 rounded-xl font-semibold mt-4 flex items-center justify-center gap-2 transition-all">
                                <i class="bi bi-key"></i> Login
                            </button>

                            <div class="text-center mt-4 pt-2 border-t border-white/10">
                                <p class="text-gray-400 text-sm">
                                    Don't have an account?
                                    <a href="../register/" class="text-purple-400 hover:text-purple-300 font-semibold transition">Create account →</a>
                                </p>
                            </div>
                        </div>
                    </form>

                <?php elseif ($action === 'forgot'): ?>
                    <div class="text-center mb-7">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-br from-purple-600/30 to-fuchsia-500/30 rounded-2xl flex items-center justify-center mb-3 border border-purple-400/40">
                            <i class="bi bi-shield-lock text-3xl text-purple-300"></i>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent">
                            Reset Password
                        </h2>
                        <p class="text-gray-400 text-sm mt-1">Enter email to receive a recovery code</p>
                    </div>

                    <form method="POST" action="?action=forgot">
                        <div class="space-y-5">
                            <div>
                                <label class="text-sm font-medium text-purple-200 flex gap-2 mb-1">
                                    <i class="bi bi-envelope"></i> Email Address
                                </label>
                                <input type="email" name="email" required placeholder="hello@aetheris.com"
                                    class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white placeholder:text-gray-500 focus:outline-none">
                            </div>

                            <button type="submit" class="btn-primary w-full py-3 rounded-xl font-semibold mt-4 flex items-center justify-center gap-2 transition-all">
                                <i class="bi bi-send"></i> Send Code
                            </button>

                            <div class="text-center mt-4 pt-2 border-t border-white/10">
                                <a href="?action=login" class="text-gray-400 hover:text-white text-sm transition">← Back to Login</a>
                            </div>
                        </div>
                    </form>

                <?php elseif ($action === 'verify'): ?>
                    <div class="text-center mb-7">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-br from-purple-600/30 to-fuchsia-500/30 rounded-2xl flex items-center justify-center mb-3 border border-purple-400/40">
                            <i class="bi bi-123 text-3xl text-purple-300"></i>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent">
                            Enter Code
                        </h2>
                        <p class="text-gray-400 text-sm mt-1">Sent to <?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?></p>
                    </div>

                    <form method="POST" action="?action=verify">
                        <div class="space-y-5">
                            <div>
                                <input type="text" name="code" required maxlength="6" placeholder="000000"
                                    class="input-glow text-center text-2xl tracking-[0.5em] font-bold w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-4 text-white placeholder:text-gray-600 focus:outline-none">
                            </div>

                            <button type="submit" class="btn-primary w-full py-3 rounded-xl font-semibold mt-4 flex items-center justify-center gap-2 transition-all">
                                <i class="bi bi-check2-circle"></i> Verify Code
                            </button>
                            
                            <div class="text-center mt-4 pt-2 border-t border-white/10">
                                <a href="?action=forgot" class="text-gray-400 hover:text-white text-sm transition">Didn't receive it? Try again</a>
                            </div>
                        </div>
                    </form>

                <?php elseif ($action === 'reset'): ?>
                    <div class="text-center mb-7">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-br from-purple-600/30 to-fuchsia-500/30 rounded-2xl flex items-center justify-center mb-3 border border-purple-400/40">
                            <i class="bi bi-key-fill text-3xl text-purple-300"></i>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent">
                            New Password
                        </h2>
                        <p class="text-gray-400 text-sm mt-1">Create a secure new password</p>
                    </div>

                    <form method="POST" action="?action=reset">
                        <div class="space-y-5">
                            <div>
                                <label class="text-sm font-medium text-purple-200 flex gap-2 mb-1">
                                    <i class="bi bi-lock"></i> New Password
                                </label>
                                <input type="password" name="new_password" required placeholder="••••••••"
                                    class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white focus:outline-none">
                            </div>

                            <button type="submit" class="btn-primary w-full py-3 rounded-xl font-semibold mt-4 flex items-center justify-center gap-2 transition-all">
                                <i class="bi bi-arrow-repeat"></i> Update Password
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>

</body>
</html>