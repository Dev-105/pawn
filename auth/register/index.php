<?php 
session_start();
include_once '../../config/db.php';
include_once '../../config/function.php';
include_once '../../mail/send.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if ($data) {
        $action = $data['action'] ?? 'register';
        
        if ($action === 'register') {
            // Step 1: Save registration data to session and send verification code
            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $email_notifications = $data['email_notifications'] ?? 'no';
            $telegram_enabled = $data['telegram_enabled'] ?? 'no';
            $telegram_bot_token = $data['telegram_bot_token'] ?? null;
            $telegram_chat_id = $data['telegram_chat_id'] ?? null;

            // Generate verification code
            $verification_code = rand(100000, 999999);
            
            // Save all data to session
            $_SESSION['temp_registration'] = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'email_notifications' => $email_notifications,
                'telegram_enabled' => $telegram_enabled,
                'telegram_bot_token' => $telegram_bot_token,
                'telegram_chat_id' => $telegram_chat_id,
                'verification_code' => $verification_code,
                'code_expiry' => time() + 300 // 5 minutes expiry
            ];
            
            // Send verification email
            $subject = "Verify Your Email - Aetheris";
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
                        <h2>Welcome to Aetheris!</h2>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>" . htmlspecialchars($username) . "</strong>,</p>
                        <p>Thank you for registering with Aetheris. Please use the verification code below to complete your registration:</p>
                        <div class='code'>" . $verification_code . "</div>
                        <p>This code will expire in <strong>5 minutes</strong>.</p>
                        <p>If you didn't create an account with Aetheris, please ignore this email.</p>
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
            
            if (sendEmail($email, $body, $subject)) {
                echo json_encode(['success' => true, 'message' => 'Verification code sent to your email', 'requires_verification' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send verification email']);
            }
            exit;
        }
        
        elseif ($action === 'verify') {
            // Step 2: Verify code and complete registration
            $verification_code = $data['verification_code'] ?? '';
            
            if (!isset($_SESSION['temp_registration'])) {
                echo json_encode(['success' => false, 'message' => 'Registration session expired. Please start over.']);
                exit;
            }
            
            $temp_data = $_SESSION['temp_registration'];
            
            // Check if code is expired
            if (time() > $temp_data['code_expiry']) {
                unset($_SESSION['temp_registration']);
                echo json_encode(['success' => false, 'message' => 'Verification code expired. Please register again.']);
                exit;
            }
            
            // Verify code
            if ($verification_code != $temp_data['verification_code']) {
                echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
                exit;
            }
            
            // Code is valid, proceed with registration
            $username = $temp_data['username'];
            $email = $temp_data['email'];
            $password = $temp_data['password'];
            $email_notifications = $temp_data['email_notifications'];
            $telegram_enabled = $temp_data['telegram_enabled'];
            $telegram_bot_token = $temp_data['telegram_bot_token'];
            $telegram_chat_id = $temp_data['telegram_chat_id'];
            
            $enableemail = ($email_notifications === 'yes') ? 1 : 0;
            $token = rand(100000, 999999);
            $device = 'web';
            
            $bot_token = ($telegram_enabled === 'yes') ? $telegram_bot_token : null;
            $bot_id = ($telegram_enabled === 'yes') ? $telegram_chat_id : null;
            
            try {
                if (createuser($username, $email, $password, $token, $device, $bot_token, $bot_id, $enableemail)) {
                    // Clear session data after successful registration
                    unset($_SESSION['temp_registration']);
                    
                    // Send welcome email
                    $welcome_subject = "Welcome to Aetheris!";
                    $welcome_body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: linear-gradient(135deg, #8B5CF6, #C084FC); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Welcome to Aetheris!</h2>
                            </div>
                            <div class='content'>
                                <p>Hello <strong>" . htmlspecialchars($username) . "</strong>,</p>
                                <p>Your account has been successfully created and verified!</p>
                                <p>You can now log in to your account and start exploring Aetheris.</p>
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
                    sendEmail($email, $welcome_body, $welcome_subject);
                    
                    echo json_encode(['success' => true, 'message' => 'User registered successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Registration failed']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Register | Aetheris</title>
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

        .btn-primary {
            background: linear-gradient(105deg, #8B5CF6, #C084FC);
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.3);
        }
        .btn-primary:active:not(:disabled) { transform: scale(0.97); }
        .btn-primary:disabled { opacity: 0.45; cursor: not-allowed; }
        
        .btn-secondary {
            background: rgba(30, 27, 46, 0.7);
            border: 1px solid rgba(139, 92, 246, 0.5);
        }
        .btn-secondary:active:not(:disabled) { transform: scale(0.97); }

        .input-glow:focus {
            outline: none;
            border-color: #C084FC;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.4), 0 0 10px #A855F7;
            background-color: rgba(8, 6, 18, 0.9);
        }

        .choice-card {
            background: rgba(20, 18, 30, 0.65);
            border: 1.5px solid rgba(139, 92, 246, 0.35);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .choice-card:active { transform: scale(0.97); }
        .choice-card.active {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.35), rgba(192, 132, 252, 0.25));
            border-color: #C084FC;
            box-shadow: 0 0 16px rgba(192, 132, 252, 0.45);
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

        .step-transition-enter {
            animation: fadeSlideUp 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step-badge-modern {
            background: rgba(30, 27, 46, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(139, 92, 246, 0.4);
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

        @media (max-width: 480px) {
            .glass-card { padding: 1.25rem; }
        }
        
        .page-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        
        /* Code input styling */
        .code-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.5rem;
            font-family: monospace;
        }
    </style>
</head>
<body>
<?php include '../../config/cursor.php'; ?>

    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <div class="fixed inset-0 bg-black/20 backdrop-blur-[2px] -z-10"></div>

    <div class="page-container text-white">
        <div class="w-full max-w-lg mx-auto">
            <div class="glass-card rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-2xl w-full">
                <div class="flex justify-between items-center mb-6 pb-2 border-b border-white/10">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold bg-gradient-to-r from-purple-300 to-fuchsia-400 bg-clip-text text-transparent">
                            Aetheris
                        </h1>
                        <p class="text-gray-400 text-[11px] sm:text-xs mt-0.5">secure onboarding</p>
                    </div>
                    <div class="step-badge-modern rounded-full px-3 py-1.5 sm:px-4 flex items-center gap-1.5">
                        <i class="bi bi-stack text-purple-300 text-xs"></i>
                        <span id="stepCounter" class="text-xs font-mono font-medium">Step 1/5</span>
                    </div>
                </div>

                <div id="stepContainer" class="min-h-[400px] sm:min-h-[420px] transition-all duration-300"></div>

                <div class="flex justify-between gap-3 mt-7 pt-2">
                    <button id="backBtn" class="btn-secondary disabled:opacity-40 disabled:cursor-not-allowed px-4 sm:px-5 py-2.5 rounded-xl font-medium text-white/90 flex items-center gap-2 transition-all text-sm sm:text-base">
                        <i class="bi bi-arrow-left text-sm"></i> <span>Back</span>
                    </button>
                    <button id="nextBtn" class="btn-primary disabled:opacity-40 disabled:cursor-not-allowed px-5 sm:px-6 py-2.5 rounded-xl font-semibold text-white flex items-center gap-2 transition-all shadow-lg text-sm sm:text-base">
                        <span>Next</span> <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
                <div class="text-center mt-4 pt-2 border-t border-white/10">
                    <p class="text-gray-400 text-sm">
                        Already have an account? 
                        <a href="../login/" class="text-purple-400 hover:text-purple-300 font-semibold transition">Sign in →</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let userData = {
            username: '',
            email: '',
            password: '',
            emailNotifications: null,
            telegramEnabled: null,
            telegramBotToken: '',
            telegramChatId: ''
        };
        
        let isVerificationMode = false;
        
        const coreSteps = [
            { id: 0, name: 'username', title: 'Create username' },
            { id: 1, name: 'credentials', title: 'Email & password' },
            { id: 2, name: 'email_pref', title: 'Email alerts' },
            { id: 3, name: 'telegram_choice', title: 'Telegram bot' }
        ];
        const telegramDetailsStep = { id: 4, name: 'telegram_config', title: 'Bot & Chat ID' };
        
        let activeSteps = [...coreSteps];
        let currentStepIdx = 0;
        let isAnimating = false;
        let isSubmitting = false;
        
        const stepContainer = document.getElementById('stepContainer');
        const nextBtn = document.getElementById('nextBtn');
        const backBtn = document.getElementById('backBtn');
        const stepCounterSpan = document.getElementById('stepCounter');
        
        function rebuildActiveSteps() {
            if (userData.telegramEnabled === 'yes') {
                if (!activeSteps.some(step => step.id === 4)) {
                    activeSteps = [...coreSteps, telegramDetailsStep];
                }
            } else {
                activeSteps = activeSteps.filter(step => step.id !== 4);
            }
            if (currentStepIdx >= activeSteps.length) currentStepIdx = activeSteps.length - 1;
            if (currentStepIdx < 0) currentStepIdx = 0;
            updateStepCounterDisplay();
        }
        
        function updateStepCounterDisplay() {
            if (!isVerificationMode) {
                const total = activeSteps.length;
                const currentNumber = currentStepIdx + 1;
                stepCounterSpan.innerText = `Step ${currentNumber}/${total}`;
            } else {
                stepCounterSpan.innerText = `Verification`;
            }
        }
        
        function isCurrentStepValid() {
            if (isVerificationMode) return true;
            if (activeSteps.length === 0) return false;
            const stepId = activeSteps[currentStepIdx].id;
            switch(stepId) {
                case 0: return userData.username.trim().length >= 3;
                case 1: {
                    const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
                    return emailRegex.test(userData.email) && userData.password.length >= 6;
                }
                case 2: return userData.emailNotifications !== null;
                case 3: return userData.telegramEnabled !== null;
                case 4: return userData.telegramBotToken.trim().length > 0 && userData.telegramChatId.trim().length > 0;
                default: return false;
            }
        }
        
        function updateNextButtonState() {
            if (isSubmitting) return;
            const isValid = isCurrentStepValid();
            nextBtn.disabled = !isValid;
            backBtn.disabled = (currentStepIdx === 0 && !isVerificationMode);
        }
        
        function showToast(message, type = "info") {
            const existingToast = document.querySelector('.toast-message');
            if (existingToast) existingToast.remove();
            const toast = document.createElement('div');
            toast.className = 'toast-message';
            let icon = '<i class="bi bi-info-circle-fill text-purple-400"></i>';
            if (type === "loading") icon = '<i class="bi bi-arrow-repeat animate-spin text-purple-300"></i>';
            if (type === "success") icon = '<i class="bi bi-check-circle-fill text-green-400"></i>';
            if (type === "error") icon = '<i class="bi bi-exclamation-triangle-fill text-red-400"></i>';
            toast.innerHTML = `${icon}<span class="text-white">${message}</span>`;
            document.body.appendChild(toast);
            if (type !== "loading") {
                setTimeout(() => { if (toast && toast.remove) toast.remove(); }, 3500);
            }
        }
        
        function escapeHtml(str) { 
            if (!str) return ''; 
            return str.replace(/[&<>]/g, function(m) { 
                if (m === '&') return '&amp;'; 
                if (m === '<') return '&lt;'; 
                if (m === '>') return '&gt;'; 
                return m;
            }); 
        }
        
        function renderVerificationStep() {
            return `
                <div class="step-content space-y-6">
                    <div class="text-center">
                        <div class="w-14 h-14 mx-auto bg-gradient-to-br from-purple-600/30 to-fuchsia-500/30 rounded-2xl flex items-center justify-center mb-3 border border-purple-400/40">
                            <i class="bi bi-envelope-paper text-2xl sm:text-3xl text-purple-300"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-semibold text-white">Verify Your Email</h3>
                        <p class="text-gray-400 text-xs sm:text-sm mt-2">We've sent a 6-digit verification code to</p>
                        <p class="text-purple-300 font-medium text-sm mt-1">${escapeHtml(userData.email)}</p>
                    </div>
                    <div class="space-y-4 mt-4">
                        <label class="text-sm font-medium text-purple-200 flex items-center gap-2"><i class="bi bi-shield-check"></i> Verification Code</label>
                        <input type="text" id="verificationCode" maxlength="6" placeholder="000000" class="code-input input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-4 text-white text-center text-2xl tracking-wider font-mono">
                        <button id="resendCodeBtn" class="text-purple-400 hover:text-purple-300 text-sm transition w-full text-center">
                            <i class="bi bi-arrow-repeat"></i> Resend verification code
                        </button>
                    </div>
                </div>
            `;
        }
        
        function renderCurrentStep() {
            if (isAnimating) return;
            
            if (isVerificationMode) {
                stepContainer.innerHTML = renderVerificationStep();
                const codeInput = document.getElementById('verificationCode');
                const resendBtn = document.getElementById('resendCodeBtn');
                
                if (codeInput) {
                    codeInput.addEventListener('input', function(e) {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                        if (this.value.length === 6) {
                            nextBtn.disabled = false;
                        } else {
                            nextBtn.disabled = true;
                        }
                    });
                }
                
                if (resendBtn) {
                    resendBtn.addEventListener('click', resendVerificationCode);
                }
                
                nextBtn.disabled = true;
                backBtn.disabled = false;
                updateStepCounterDisplay();
                return;
            }
            
            const stepDef = activeSteps[currentStepIdx];
            if (!stepDef) return;
            const html = generateStepHTML(stepDef.id);
            stepContainer.innerHTML = html;
            const contentDiv = stepContainer.querySelector('.step-content');
            if (contentDiv) {
                contentDiv.classList.add('step-transition-enter');
                contentDiv.addEventListener('animationend', () => {
                    contentDiv.classList.remove('step-transition-enter');
                }, { once: true });
            }
            attachStepEventHandlers(stepDef.id);
            updateNextButtonState();
            updateStepCounterDisplay();
        }
        
        function generateStepHTML(stepId) {
            switch(stepId) {
                case 0:
                    return `<div class="step-content space-y-6">
                        <div class="text-center mb-1">
                            <div class="w-14 h-14 mx-auto bg-gradient-to-br from-purple-600/30 to-fuchsia-500/30 rounded-2xl flex items-center justify-center mb-3 border border-purple-400/40">
                                <i class="bi bi-person-plus text-2xl sm:text-3xl text-purple-300"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-semibold text-white">Pick identity</h3>
                            <p class="text-gray-400 text-xs sm:text-sm">Choose a unique username</p>
                        </div>
                        <div class="space-y-2 mt-2">
                            <label class="text-sm font-medium text-purple-200 flex items-center gap-2"><i class="bi bi-at"></i> Username</label>
                            <input type="text" id="usernameField" value="${escapeHtml(userData.username)}" placeholder="e.g., cosmic_wave" class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white placeholder:text-gray-500 text-base">
                            <div class="text-xs text-gray-400 flex items-center gap-1"><i class="bi bi-info-circle-fill text-[10px]"></i> Minimum 3 characters</div>
                        </div>
                    </div>`;
                case 1:
                    return `<div class="step-content space-y-6">
                        <div class="text-center"><i class="bi bi-shield-lock text-2xl sm:text-3xl text-purple-300 bg-purple-900/30 p-3 rounded-2xl inline-block mb-2"></i><h3 class="text-xl sm:text-2xl font-semibold text-white">Secure credentials</h3></div>
                        <div class="space-y-4">
                            <div><label class="text-sm font-medium text-purple-200 flex gap-2"><i class="bi bi-envelope"></i> Email</label>
                            <input type="email" id="emailField" value="${escapeHtml(userData.email)}" placeholder="hello@aetheris.com" class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white"></div>
                            <div><label class="text-sm font-medium text-purple-200 flex gap-2"><i class="bi bi-key"></i> Password</label>
                            <input type="password" id="passwordField" value="${escapeHtml(userData.password)}" placeholder="••••••••" class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white">
                            <p class="text-xs text-gray-400 mt-1"><i class="bi bi-shield-check"></i> Min. 6 characters</p></div>
                        </div>
                    </div>`;
                case 2:
                    return `<div class="step-content space-y-7">
                        <div class="text-center"><i class="bi bi-envelope-paper-heart text-2xl sm:text-3xl text-fuchsia-300 bg-fuchsia-900/30 p-3 rounded-2xl inline-block"></i><h3 class="text-xl sm:text-2xl font-semibold text-white mt-2">Email updates</h3><p class="text-gray-300 text-sm">Receive insights & important alerts</p></div>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center mt-2">
                            <div class="choice-card ${userData.emailNotifications === 'yes' ? 'active' : ''} flex items-center justify-center gap-2 p-3.5 rounded-xl w-full sm:w-36" data-choice-email="yes"><i class="bi bi-check-circle text-lg"></i> <span class="font-medium">Yes</span></div>
                            <div class="choice-card ${userData.emailNotifications === 'no' ? 'active' : ''} flex items-center justify-center gap-2 p-3.5 rounded-xl w-full sm:w-36" data-choice-email="no"><i class="bi bi-x-circle text-lg"></i> <span class="font-medium">No thanks</span></div>
                        </div>
                    </div>`;
                case 3:
                    return `<div class="step-content space-y-7">
                        <div class="text-center"><i class="bi bi-telegram text-2xl sm:text-3xl text-sky-300 bg-sky-900/30 p-3 rounded-2xl inline-block"></i><h3 class="text-xl sm:text-2xl font-semibold text-white mt-2">Telegram bot</h3><p class="text-gray-300 text-sm">Get real-time notifications on Telegram</p></div>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center mt-2">
                            <div class="choice-card ${userData.telegramEnabled === 'yes' ? 'active' : ''} flex items-center justify-center gap-2 p-3.5 rounded-xl w-full sm:w-36" data-telegram-choice="yes"><i class="bi bi-telegram"></i> <span>Connect</span></div>
                            <div class="choice-card ${userData.telegramEnabled === 'no' ? 'active' : ''} flex items-center justify-center gap-2 p-3.5 rounded-xl w-full sm:w-36" data-telegram-choice="no"><i class="bi bi-slash-circle"></i> <span>Skip</span></div>
                        </div>
                    </div>`;
                case 4:
                    return `<div class="step-content space-y-6">
                        <div class="text-center"><i class="bi bi-robot text-2xl sm:text-3xl text-purple-300 bg-purple-800/30 p-3 rounded-2xl inline-block"></i><h3 class="text-xl sm:text-2xl font-semibold text-white">Telegram credentials</h3><p class="text-gray-400 text-xs">Get token & chat ID from @BotFather</p></div>
                        <div class="space-y-5">
                            <div><label class="text-sm font-medium text-purple-200 flex gap-2"><i class="bi bi-token"></i> Bot Token</label><input type="text" id="botTokenField" value="${escapeHtml(userData.telegramBotToken)}" placeholder="7192837465:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw" class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white font-mono text-sm"></div>
                            <div><label class="text-sm font-medium text-purple-200 flex gap-2"><i class="bi bi-chat-dots"></i> Chat ID</label><input type="text" id="chatIdField" value="${escapeHtml(userData.telegramChatId)}" placeholder="123456789 or @username" class="input-glow w-full bg-black/30 border border-purple-500/40 rounded-xl px-4 py-3 text-white"><p class="text-[11px] text-gray-400 mt-1"><i class="bi bi-info-square"></i> Required for message delivery</p></div>
                        </div>
                    </div>`;
                default: return `<div class="step-content p-4 text-white">Error</div>`;
            }
        }
        
        function attachStepEventHandlers(stepId) {
            if (stepId === 0) {
                const usernameInput = document.getElementById('usernameField');
                if (usernameInput) usernameInput.addEventListener('input', (e) => { userData.username = e.target.value; updateNextButtonState(); });
            }
            else if (stepId === 1) {
                const emailInput = document.getElementById('emailField');
                const passInput = document.getElementById('passwordField');
                if (emailInput) emailInput.addEventListener('input', (e) => { userData.email = e.target.value; updateNextButtonState(); });
                if (passInput) passInput.addEventListener('input', (e) => { userData.password = e.target.value; updateNextButtonState(); });
            }
            else if (stepId === 2) {
                const yesChoice = document.querySelector('[data-choice-email="yes"]');
                const noChoice = document.querySelector('[data-choice-email="no"]');
                const setEmailChoice = (val) => {
                    userData.emailNotifications = val;
                    if (yesChoice && noChoice) {
                        if (val === 'yes') { yesChoice.classList.add('active'); noChoice.classList.remove('active'); }
                        else { noChoice.classList.add('active'); yesChoice.classList.remove('active'); }
                    }
                    updateNextButtonState();
                };
                if (yesChoice) yesChoice.addEventListener('click', () => setEmailChoice('yes'));
                if (noChoice) noChoice.addEventListener('click', () => setEmailChoice('no'));
            }
            else if (stepId === 3) {
                const teleYes = document.querySelector('[data-telegram-choice="yes"]');
                const teleNo = document.querySelector('[data-telegram-choice="no"]');
                const setTelegramChoice = (choice) => {
                    userData.telegramEnabled = choice;
                    if (teleYes && teleNo) {
                        if (choice === 'yes') { teleYes.classList.add('active'); teleNo.classList.remove('active'); }
                        else { teleNo.classList.add('active'); teleYes.classList.remove('active'); }
                    }
                    rebuildActiveSteps();
                    renderCurrentStep();
                    updateNextButtonState();
                };
                if (teleYes) teleYes.addEventListener('click', () => setTelegramChoice('yes'));
                if (teleNo) teleNo.addEventListener('click', () => setTelegramChoice('no'));
            }
            else if (stepId === 4) {
                const tokenField = document.getElementById('botTokenField');
                const chatField = document.getElementById('chatIdField');
                if (tokenField) tokenField.addEventListener('input', (e) => { userData.telegramBotToken = e.target.value; updateNextButtonState(); });
                if (chatField) chatField.addEventListener('input', (e) => { userData.telegramChatId = e.target.value; updateNextButtonState(); });
            }
        }
        
        async function sendVerificationCode() {
            showToast("Sending verification code...", "loading");
            
            const payload = {
                action: 'register',
                username: userData.username,
                email: userData.email,
                password: userData.password,
                email_notifications: userData.emailNotifications,
                telegram_enabled: userData.telegramEnabled,
                telegram_bot_token: (userData.telegramEnabled === 'yes') ? userData.telegramBotToken : null,
                telegram_chat_id: (userData.telegramEnabled === 'yes') ? userData.telegramChatId : null
            };
            
            try {
                const response = await fetch('./index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (result.success && result.requires_verification) {
                    showToast("Verification code sent to your email!", "success");
                    isVerificationMode = true;
                    renderCurrentStep();
                    updateNextButtonState();
                } else {
                    showToast(result.message || "Failed to send verification code", "error");
                }
            } catch(e) {
                console.error("Error:", e);
                showToast("Network error. Please try again.", "error");
            }
        }
        
        async function resendVerificationCode() {
            await sendVerificationCode();
        }
        
        async function verifyCodeAndRegister() {
            const codeInput = document.getElementById('verificationCode');
            const code = codeInput ? codeInput.value : '';
            
            if (code.length !== 6) {
                showToast("Please enter the 6-digit verification code", "error");
                return;
            }
            
            isSubmitting = true;
            nextBtn.disabled = true;
            showToast("Verifying and creating account...", "loading");
            
            const payload = {
                action: 'verify',
                verification_code: code
            };
            
            try {
                const response = await fetch('./index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast("Registration successful! Redirecting...", "success");
                    setTimeout(() => {
                        window.location.href = '../login/';
                    }, 2000);
                } else {
                    showToast(result.message || "Verification failed", "error");
                    isSubmitting = false;
                    nextBtn.disabled = false;
                }
            } catch(e) {
                console.error("Error:", e);
                showToast("Network error. Please try again.", "error");
                isSubmitting = false;
                nextBtn.disabled = false;
            }
        }
        
        async function goToNextStep() {
            if (isAnimating || isSubmitting) return;
            
            if (isVerificationMode) {
                await verifyCodeAndRegister();
                return;
            }
            
            if (!isCurrentStepValid()) return;
            
            const currentId = activeSteps[currentStepIdx]?.id;
            
            // If this is the last step of registration, send verification code
            if (currentStepIdx === activeSteps.length - 1) {
                await sendVerificationCode();
                return;
            }
            
            if (currentId === 3) rebuildActiveSteps();
            
            currentStepIdx++;
            renderCurrentStep();
        }
        
        function goToPrevStep() {
            if (isAnimating || isSubmitting) return;
            
            if (isVerificationMode) {
                isVerificationMode = false;
                renderCurrentStep();
                return;
            }
            
            if (currentStepIdx === 0) return;
            currentStepIdx--;
            renderCurrentStep();
        }
        
        nextBtn.addEventListener('click', goToNextStep);
        backBtn.addEventListener('click', goToPrevStep);
        
        rebuildActiveSteps();
        renderCurrentStep();
        updateNextButtonState();
    </script>
</body>
</html>