<?php
/**
 * 403 Forbidden Template
 * Shown when an authenticated user attempts to access an unauthorized role's resource.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_role = $_SESSION['role'] ?? 'guest';
$base_path = isset($base_path) ? $base_path : '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - Access Denied</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_path); ?>css/style.css">
</head>
<body>

    <header class="site-header">
        <div class="container nav-bar">
            <a href="<?php echo htmlspecialchars($base_path); ?>index.php" class="brand-logo">
                <span class="brand-icon">L</span>
                <span>Library System</span>
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="<?php echo htmlspecialchars($base_path); ?>logout.php" class="btn btn-outline btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container" style="max-width: 600px;">
            <div class="feature-card" style="text-align: center; margin-top: 3rem;">
                <div class="feature-icon-badge" style="background-color: #fee2e2; color: #dc2626;">!</div>
                <h1 class="feature-title" style="font-size: 1.5rem; color: #b91c1c;">403 - Access Forbidden</h1>
                <p class="feature-text" style="margin: 1rem 0;">
                    You do not have the necessary permissions to access this page. 
                    Your account is registered as <strong><?php echo htmlspecialchars(ucfirst($user_role)); ?></strong>.
                </p>

                <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 1rem;">
                    <?php if ($user_role === 'admin'): ?>
                        <a href="<?php echo htmlspecialchars($base_path); ?>admin/dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
                    <?php elseif ($user_role === 'student'): ?>
                        <a href="<?php echo htmlspecialchars($base_path); ?>student/dashboard.php" class="btn btn-primary">Go to Student Dashboard</a>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($base_path); ?>login.php" class="btn btn-primary">Go to Login</a>
                    <?php endif; ?>
                    <a href="<?php echo htmlspecialchars($base_path); ?>logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Library Management System</p>
        </div>
    </footer>

</body>
</html>
