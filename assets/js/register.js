// Wait for the user to click the register button
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    // Stop the page from refreshing because we're using AJAX/Fetch
    e.preventDefault();

    // Grab what they typed in the boxes
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Check passwords match before sending to server
    if (password !== confirmPassword) {
        alert("❌ Passwords do not match.");
        return;
    }

    // Pack the data into a format PHP can understand
    const formData = new FormData();
    formData.append('username', username);
    formData.append('email', email);
    formData.append('password', password);

    try {
        // Send the data to the register script and wait for it to finish
        const response = await fetch('../auth/register.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.text();

        // If PHP liked the data, show success and redirect to login
        if (result.trim() === "success") {
            alert("✅ Registration successful! You can now login.");
            window.location.href = "../public/login.html";
        } else {
            // Otherwise, show whatever error PHP sent back
            alert("❌ " + result.replace("error: ", ""));
        }
    } catch (error) {
        // This is usually because we forgot to turn on XAMPP
        alert("❌ Connection error. Is XAMPP/Apache running?");
    }
});

// Password strength checker
function checkStrength() {
    const password = document.getElementById('password').value;
    const msg = document.getElementById('strength-message');

    if (password.length === 0) {
        msg.textContent = "";
    } else if (password.length < 8) {
        msg.textContent = "Weak password";
        msg.style.color = "red";
    } else if (password.match(/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])/)) {
        msg.textContent = "Strong password";
        msg.style.color = "lightgreen";
    } else {
        msg.textContent = "Moderate password";
        msg.style.color = "orange";
    }
}