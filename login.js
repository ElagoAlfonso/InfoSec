document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('Email').value.trim();
    const password = document.getElementById('Password').value;

    if (!email.includes("@")) {
        alert("❌ Email must contain an '@' sign.");
        return;
    }

    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    try {
        const response = await fetch('login.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.text();

        if (result.trim() === "success") {
            alert("✅ Login successful!");
            window.location.href = "welcome.php";
        } else {

            alert("❌ " + result); 
        }
    } catch (error) {
        alert("❌ Connection error. Is XAMPP/Apache running?");
    }
});

document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('Password');
    if (passwordField.type === "password") {
        passwordField.type = "text";
        this.textContent = "🙈";
    } else {
        passwordField.type = "password";
        this.textContent = "👁";
    }
});