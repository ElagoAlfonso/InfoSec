<?php
session_start();
if (!isset($_SESSION["username"])) {
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
    <div class="welcome-box">
        <h1>Welcome to CyberSecurity</h1>
        <p>Logged in as: **<?php echo htmlspecialchars($_SESSION["username"]); ?>** (<?php echo htmlspecialchars($_SESSION["email"]); ?>)</p>
        <a href="logout.php"><button>Logout</button></a>
    </div>
</body>
</html>