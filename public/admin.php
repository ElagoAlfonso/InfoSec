<?php
session_start();
include "../configs/config.php";
include "../configs/jwt.php";

// 1. Must be logged in with a valid JWT
if (!isset($_SESSION['jwt'])) {
    header("Location: ../public/login.html");
    exit();
}

$decoded = verify_jwt($_SESSION['jwt']);
if (!$decoded) {
    session_destroy();
    header("Location: ../public/login.html");
    exit();
}

if ($decoded['role'] !== 'admin') {
    header("Location: ../public/welcome.php");
    exit();
}

$adminUsername = $decoded['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_role'])) {
    $userId  = (int) $_POST['user_id'];
    $newRole = $_POST['new_role'] === 'admin' ? 'admin' : 'user';
    $update  = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $update->bind_param("si", $newRole, $userId);
    $update->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$totalResult = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalUsers  = $totalResult->fetch_assoc()['total'];

$adminResult = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
$totalAdmins = $adminResult->fetch_assoc()['total'];

$regularUsers = $totalUsers - $totalAdmins;

$usersResult = $conn->query("SELECT id, username, email, role FROM users ORDER BY id ASC");
$users = [];
while ($row = $usersResult->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="scanline"></div>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-tag">// system</div>
        <h1>Cyber<span>App</span></h1>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">// navigation</div>
        <a class="nav-item active" href="#">
            <span class="icon">⬡</span> Dashboard
        </a>
        <a class="nav-item" href="../public/welcome.php">
            <span class="icon">◈</span> My Profile
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-badge">
            <div class="avatar"><?php echo strtoupper(substr($adminUsername, 0, 1)); ?></div>
            <div class="admin-info">
                <div class="name"><?php echo htmlspecialchars($adminUsername); ?></div>
                <div class="role-tag">ADMIN</div>
            </div>
        </div>
        <a href="../auth/logout.php" class="logout-btn">⏻ &nbsp;Logout</a>
    </div>
</aside>

<main class="main">

    <div class="topbar">
        <div class="topbar-title">Admin <span>Dashboard</span></div>
        <div class="topbar-time" id="clock"></div>
    </div>

    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-label">// total users</div>
            <div class="stat-value"><?php echo $totalUsers; ?></div>
            <div class="stat-sub">Registered accounts</div>
            <div class="stat-icon">◉</div>
        </div>
        <div class="stat-card admins">
            <div class="stat-label">// administrators</div>
            <div class="stat-value"><?php echo $totalAdmins; ?></div>
            <div class="stat-sub">Admin-level accounts</div>
            <div class="stat-icon">⬡</div>
        </div>
        <div class="stat-card users">
            <div class="stat-label">// regular users</div>
            <div class="stat-value"><?php echo $regularUsers; ?></div>
            <div class="stat-sub">Standard accounts</div>
            <div class="stat-icon">◈</div>
        </div>
    </div>

    <div class="section-header">
        <div class="section-title">User Registry</div>
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="user-count-badge"><?php echo $totalUsers; ?> total</span>
            <div class="search-wrap">
                <span class="search-icon">⌕</span>
                <input type="text" id="searchInput" placeholder="Search users...">
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <?php if (empty($users)): ?>
                    <tr class="empty-row">
                        <td colspan="4">// no users found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr data-name="<?php echo strtolower(htmlspecialchars($user['username'])); ?>"
                        data-email="<?php echo strtolower(htmlspecialchars($user['email'])); ?>">
                        <td class="td-id"><?php echo str_pad($user['id'], 3, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="td-user">
                                <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge <?php echo $user['role']; ?>">
                                <?php echo strtoupper($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="role-form">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="new_role" class="role-select">
                                    <option value="user"  <?php echo $user['role'] === 'user'  ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <button type="submit" class="role-btn">Apply</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script src="../assets/js/admin.js"></script>

</body>
</html>