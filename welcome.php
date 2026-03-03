<?php
// Gotta start this or we can't see who is logged in
session_start();

// If the "username" isn't in the session, they aren't logged in
if (!isset($_SESSION["username"])) {
    // Kick them back to the login page so they can't sneak in
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section> 
        <div class="welcome-box">
            <h1>Welcome to CyberSecurity</h1>
            <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong> </p>
            <a href="logout.php"><button id="logoutBtn">Logout</button></a>
        </div>
    </section>
</body>
</html>