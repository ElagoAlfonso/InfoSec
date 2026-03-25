<?php
session_start();
include "../configs/jwt.php"; // Include JWT helper

// Verify the JWT token stored in the session
if (!isset($_SESSION['jwt'])) {
    header("Location: ../public/login.html");
    exit();
}

// Decode and validate the token
$decoded = verify_jwt($_SESSION['jwt']);

// If token is invalid or expired, kick them out
if (!$decoded) {
    session_destroy();
    header("Location: ../public/login.html");
    exit();
}

// Pull data from the verified token payload
$username = $decoded['username'];
$role     = $decoded['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <section>
        <div class="welcome-box">
            <h1>Welcome to CyberSecurity</h1>
            <p>Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong></p>
            <p>Role: <strong><?php echo htmlspecialchars($role); ?></strong></p>
            <?php if ($role === 'admin'): ?>
                <a href="../public/admin.php"><button id="adminBtn">Admin Dashboard</button></a>
            <?php endif; ?>
            <a href="../auth/logout.php"><button id="logoutBtn">Logout</button></a>
        </div>
    </section>
</body>
</html>