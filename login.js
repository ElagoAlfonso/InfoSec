// Wait for the user to click the login button
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    // Stop the page from refreshing because we're using AJAX/Fetch
    e.preventDefault();

    // Grab what they typed in the boxes
    const email = document.getElementById('Email').value.trim();
    const password = document.getElementById('Password').value;

    // Basic check so we don't send garbage to the server
    if (!email.includes("@")) {
        alert("❌ Email must contain an '@' sign.");
        return;
    }

    // Pack the data into a format PHP can understand
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    try {
        // Send the data to the login script and wait for it to finish
        const response = await fetch('login.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.text();

        // If PHP liked the credentials, send them to the welcome page
        if (result.trim() === "success") {
            alert("✅ Login successful!");
            window.location.href = "welcome.php";
        } else {
            // Otherwise, just show whatever error PHP spat out
            alert("❌ " + result); 
        }
    } catch (error) {
        // This is usually because we forgot to turn on XAMPP
        alert("❌ Connection error. Is XAMPP/Apache running?");
    }
});

// This part makes the eye icon work so you can see your password
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('Password');
    // Swap between dots and actual text
    if (passwordField.type === "password") {
        passwordField.type = "text";
        this.textContent = "🙈";
    } else {
        passwordField.type = "password";
        this.textContent = "👁";
    }
});