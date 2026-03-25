<?php
session_start();
include "../configs/config.php";
include "../configs/jwt.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Fetch user including lockout fields
    $stmt = $conn->prepare("SELECT username, password, role, failed_attempts, lockout_until FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Check if account is currently locked
        if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
            $remaining = ceil((strtotime($user['lockout_until']) - time()) / 60);
            echo "Account locked. Try again in {$remaining} minute(s).";
            exit();
        }

        if (password_verify($password, $user['password'])) {
            // SUCCESS: reset failed attempts
            $reset = $conn->prepare("UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE email = ?");
            $reset->bind_param("s", $email);
            $reset->execute();

            $_SESSION["username"] = $user['username'];
            $_SESSION["role"] = $user['role'];

            $token = generate_jwt($user['username'], $user['role']);
            $_SESSION['jwt'] = $token;

            echo "success";
            exit();
        } else {
            // FAILED: increment counter on the account
            $newAttempts = $user['failed_attempts'] + 1;

            if ($newAttempts >= 5) {
                $lockoutUntil = date('Y-m-d H:i:s', time() + 5 * 60);
                $update = $conn->prepare("UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE email = ?");
                $update->bind_param("iss", $newAttempts, $lockoutUntil, $email);
                $update->execute();
                echo "Too many failed attempts. Account locked for 5 minutes.";
            } else {
                $update = $conn->prepare("UPDATE users SET failed_attempts = ? WHERE email = ?");
                $update->bind_param("is", $newAttempts, $email);
                $update->execute();
                $attemptsLeft = 5 - $newAttempts;
                echo "Invalid email or password. {$attemptsLeft} attempt(s) remaining.";
            }
            exit();
        }
    } else {
        echo "Invalid email or password.";
        exit();
    }
}
?>