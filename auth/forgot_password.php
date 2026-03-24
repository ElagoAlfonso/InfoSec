<?php
include "../configs/config.php";
session_start();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if the email exists in users table
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            // Don't reveal if email exists or not 
            $message = "If this email exists, a reset link has been generated.";
        } else {
            // Generate a secure random token
            $token = bin2hex(random_bytes(32)); // 64-char hex string
            $expiry = date('Y-m-d H:i:s', time() + 15 * 60); // expires in 15 minutes

            // Delete any old tokens for this email first
            $delete = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
            $delete->bind_param("s", $email);
            $delete->execute();

            // Store the new token in the database
            $insert = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $email, $token, $expiry);
            $insert->execute();

            // In a real app, this link would be emailed to the user.
            // For local/XAMPP, we display it directly on screen.
            $resetLink = "http://localhost/cyberapp/auth/reset_password.php?token=" . $token;
            $message = "Reset link generated!<br><a href='$resetLink' style='color:#0fc;'>Click here to reset your password</a><br><small style='color:#aaa;fontweight:BOLD;'>This link will expire in 15 minutes.</small'>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <section>
        <div class="login-box">
            <form method="POST" action="">
                <h2>Forgot Password</h2>

                <?php if ($message): ?>
                    <p style="color: #0fc; text-align:center; font-size:0.9em; margin-bottom:15px;">
                        <?php echo $message; ?>
                    </p>
                <?php endif; ?>

                <?php if ($error): ?>
                    <p style="color: red; text-align:center; font-size:0.9em; margin-bottom:15px;">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endif; ?>

                <div class="input-box">
                    <input type="email" name="email" required>
                    <label>Enter your Email</label>
                </div>

                <button type="submit">Send Reset Link</button>

                <div class="register-link">
                    <p>Remembered it? <a href="../public/login.html">Login</a></p>
                </div>
            </form>
        </div>
    </section>
</body>
</html>