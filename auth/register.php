<?php
include "../configs/config.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        echo "error: All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "error: Invalid email format!";
    } elseif (strlen($password) < 8) {
        echo "error: Password must be at least 8 characters!";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            echo "error: Email already exists!";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                echo "success"; // tell success so register.js fetch() can detect it
            } else {
                echo "error: " . $conn->error;
            }
        }
    }
    exit();
}
?>