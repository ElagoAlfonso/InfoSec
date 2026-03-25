<?php
include "../configs/config.php";
session_start();

$message = "";
$error = "";
$submitted = false;
$maskedEmail = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            $error = "No account found with that email. Please enter a registered email.";
        } else {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + 15 * 60);

            $insert = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $email, $token, $expiry);

            if (!$insert->execute()) {
                $error = "Something went wrong. Please try again.";
            } else {
                // Mask the email: us****@gmail.com
                $parts = explode("@", $email);
                $name = $parts[0];
                $domain = $parts[1];
                $masked = substr($name, 0, 2) . str_repeat("*", max(4, strlen($name) - 2));
                $maskedEmail = $masked . "@" . $domain;

                $resetLink = "http://localhost/cyberapp/auth/reset_password.php?token=" . $token;
                $submitted = true;
            }
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
            <h2>Forgot Password</h2>

            <?php if ($submitted): ?>
                <!-- SUCCESS STATE: hide form, show masked email -->
                <div style="text-align:center;">
                    <p style="color:#0fc; font-size:1em; margin-bottom:10px;">
                        ✅ Reset link sent to<br>
                        <strong style="font-size:1.1em;"><?php echo htmlspecialchars($maskedEmail); ?></strong>
                    </p>
                    <p style="color:#aaa; font-size:0.82em; margin-bottom:20px;">
                        This link will expire in 15 minutes.
                    </p>
                    <!-- For testing only, remove in production -->
                    <p style="font-size:0.8em;">
                        <a href="<?php echo $resetLink; ?>" style="color:#0fc;">Click here to reset</a>
                    </p>
                    <hr style="border-color:#333; margin:20px 0;">
                    <a href="../auth/forgot_password.php" style="color:#aaa; font-size:0.9em;">
                        🔄 Use another email
                    </a>
                </div>

            <?php else: ?>
                <!-- FORM STATE -->
                <form method="POST" action="">

                    <?php if ($error): ?>
                        <p style="color:red; text-align:center; font-size:0.9em; margin-bottom:15px;">
                            ❌ <?php echo htmlspecialchars($error); ?>
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
            <?php endif; ?>

        </div>
    </section>
</body>
</html>