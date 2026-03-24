<?php
session_start();
include "../configs/config.php";
include "../configs/jwt.php";

// Initialize attempts counter if not set
if (!isset($_SESSION['FailedAttempts'])) {
    $_SESSION['FailedAttempts'] = 0;
}

$error = ""; // For showing messages in the UI

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // FIX: Check if LockoutTime exists before trying to read it
    if ($_SESSION['FailedAttempts'] >= 5 && isset($_SESSION['LockoutTime'])) {
        $lockoutDuration = 5 * 60; // 5 minutes in seconds
        $elapsed = time() - $_SESSION['LockoutTime'];

        if ($elapsed < $lockoutDuration) {
            $remaining = ceil(($lockoutDuration - $elapsed) / 60);
            echo "Too many failed attempts. Try again in {$remaining} minute(s).";
            exit();
        } else {
            // Timer has expired, reset the counter and allow login again
            $_SESSION['FailedAttempts'] = 0;
            unset($_SESSION['LockoutTime']);
        }
    }

    // Look up user
     $stmt = $conn->prepare("SELECT username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
 
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION["username"] = $user['username'];
            $_SESSION["role"] = $user['role'];
            $_SESSION['FailedAttempts'] = 0; // reset on success
            unset($_SESSION['LockoutTime']);
 
            // Generate JWT token and store it in the session
            $token = generate_jwt($user['username'], $user['role']);
            $_SESSION['jwt'] = $token;
 
            echo "success";
            exit();
        }
    }
 
    // Increment attempts on failure
    $_SESSION['FailedAttempts']++;
 
    // Record the time when the 5th failure is hit
    if ($_SESSION['FailedAttempts'] >= 5) {
        $_SESSION['LockoutTime'] = time();
        echo "Too many failed attempts. Try again in 5 minute(s).";
    } else {
        $attemptsLeft = 5 - $_SESSION['FailedAttempts'];
        echo "Invalid email or password. {$attemptsLeft} attempt(s) remaining.";
    }
    exit();
}
?>