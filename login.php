<?php
// Start the session so we can actually remember who is logged in
session_start();
// Grab the database connection stuff from the other file
include "config.php";

// Only run this if the user actually clicked the submit button
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Go find the user in the database based on their email
    $stmt = $conn->prepare("SELECT username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If we actually found someone...
    if ($user = $result->fetch_assoc()) {

        // Check if the password they typed matches the hashed one in the DB
        if (password_verify($password, $user['password'])) {
            // Save their username to the session and tell the front-end it worked
            $_SESSION["username"] = $user['username'];
            echo "success"; 
            exit();
        }
    }
    // If we get here, something went wrong (wrong email or wrong pass)
    echo "Invalid email or password"; 
}
?>