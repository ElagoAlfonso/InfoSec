<?php
// Get the database connection stuff again
include "config.php";

// Standard check to make sure the form was actually sent
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Grab the stuff they typed in and clean up extra spaces
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Make the password unreadable so hackers don't see it in the DB
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


    // Check if this email is already taken before we try to save it
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        // If it's in there, just stop everything and complain
        die("Email already exists!");
    }

    // Actually save the new user info into the table
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    // If the insert worked, tell them it's all good
    if ($stmt->execute()) {
        echo "success";
    } else {
        // If it broke, print out the error so we can Google it
        echo "Error: " . $conn->error;
    }
}
?>