<?php
// Set up where the DB is and how to get in 
$servername = "localhost";
$username = "root";
$password = "";
$database = "infosec_db";

// Actually try to open the door to the database
$conn = new mysqli($servername, $username, $password, $database);

// If the door is locked or something is broken, stop everything and tell us why
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure emojis and weird characters don't break the site
$conn->set_charset("utf8mb4");
?>