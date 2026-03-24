<?php
include "../configs/config.php";
session_start();

$error = "";
$success = "";
$validToken = false;
$token = isset($_GET['token']) ? trim($_GET['token']) : "";

// Step 1: Validate the token from the URL
if (empty($token)) {
    $error = "Invalid or missing reset token.";
} else {
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_reset_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Check if the token has expired
        if (strtotime($row['expires_at']) < time()) {
            $error = "This reset link has expired. Please request a new one.";
        } else {
            $validToken = true; // Token is valid, show the form
            $resetEmail = $row['email'];
        }
    } else {
        $error = "Invalid reset token.";
    }
}

// Step 2: Handle the new password form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $validToken) {
    $newPassword = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    if (empty($newPassword) || strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters.";
        $validToken = true;
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
        $validToken = true;
    } else {
        // Hash the new password
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $hashed, $resetEmail);
        $update->execute();

        // Delete the used token so it can't be reused
        $delete = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        $delete->bind_param("s", $token);
        $delete->execute();

        $success = "Password reset successful! You can now <a href='../public/login.html' style='color:#0fc;'>login</a>.";
        $validToken = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <section>
        <div class="login-box">
            <form method="POST" action="">
                <h2>Reset Password</h2>

                <?php if ($error): ?>
                    <p style="color: red; text-align:center; font-size:0.9em; margin-bottom:15px;">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endif; ?>

                <?php if ($success): ?>
                    <p style="color: #0fc; text-align:center; font-size:0.9em; margin-bottom:15px;">
                        <?php echo $success; ?>
                    </p>
                <?php endif; ?>

                <?php if ($validToken): ?>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="input-box">
                        <input type="password" id="password" name="password" required>
                        <label>New Password</label>
                    </div>

                    <div class="input-box">
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <label>Confirm New Password</label>
                    </div>

                    <button type="submit">Reset Password</button>
                <?php endif; ?>

                <?php if (!$validToken && empty($success)): ?>
                    <div class="register-link">
                        <p><a href="../public/forgot_password.php">Request a new link</a></p>
                    </div>
                <?php endif; ?>

                <div class="register-link">
                    <p>Remembered it? <a href="../public/login.html">Login</a></p>
                </div>
            </form>
        </div>
    </section>
</body>
</html>