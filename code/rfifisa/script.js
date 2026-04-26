async function senddata() {
            console.log("click"); 
            const id = 2; // User ID from database
            const email = document.getElementById("email");
            const password = document.getElementById("password");
            console.log(`Email: ${email.value}, Password: ${password.value}`);
            let resp = await send(email.value, password.value, "myapp", id);
            email.value = "";
            password.value = "";
            console.log(`Response: ${resp}`);
        }

        async function getselfi() {
            const id = 2; // User ID from database
            await sendImage(id);
            console.log("Selfie sent successfully");
        }