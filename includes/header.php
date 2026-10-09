<?php
/**
 * Common Header Template
 * Provides standard HTML5 head structure and primary navigation.
 */
if (!isset($page_title)) {
    $page_title = "Library Management System";
}
if (!isset($base_path)) {
    $base_path = "";
}

// Ensure session is started for role check in navigation
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_authenticated = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$session_role     = $_SESSION['role'] ?? null;
$session_username = $_SESSION['username'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <!-- Core Vanilla CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_path); ?>css/style.css">
</head>
<body>

    <!-- Main Navigation Header -->
    <header class="site-header">
        <div class="container nav-bar">
            <a href="<?php echo htmlspecialchars($base_path); ?>index.php" class="brand-logo">
                <span class="brand-icon">L</span>
                <span>Library System</span>
            </a>
            <nav>
                <ul class="nav-links">
                    <?php if ($is_authenticated): ?>
                        <?php if ($session_role === 'admin'): ?>
                            <li><a href="<?php echo htmlspecialchars($base_path); ?>admin/dashboard.php" class="nav-link">Dashboard</a></li>
                            <li><a href="<?php echo htmlspecialchars($base_path); ?>admin/books.php" class="nav-link">Books</a></li>
                            <li><a href="<?php echo htmlspecialchars($base_path); ?>admin/students.php" class="nav-link">Students</a></li>
                            <li><a href="<?php echo htmlspecialchars($base_path); ?>admin/issue_book.php" class="nav-link">Issue Book</a></li>
                            <li><a href="<?php echo htmlspecialchars($base_path); ?>admin/issued_books.php" class="nav-link">Issued Books</a></li>
                        <?php elseif ($session_role === 'student'): ?>
                            <li><a href="<?php echo htmlspecialchars($base_path); ?>student/dashboard.php" class="nav-link">Dashboard</a></li>
                            <li><a href="<?php echo htmlspecialchars($base_path); ?>student/books.php" class="nav-link">Browse Books</a></li>
                            <li><a href="<?php echo htmlspecialchars($base_path); ?>student/my_books.php" class="nav-link">My Books</a></li>
                        <?php endif; ?>
                        
                        <li class="nav-user-item">
                            <span class="user-pill">
                                <strong><?php echo htmlspecialchars($session_username); ?></strong>
                                <span class="role-tag role-<?php echo htmlspecialchars($session_role); ?>">
                                    <?php echo htmlspecialchars(ucfirst($session_role)); ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <form action="<?php echo htmlspecialchars($base_path); ?>logout.php" method="POST" style="display:inline;">
                                <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo htmlspecialchars($base_path); ?>index.php" class="nav-link">Home</a></li>
                        <li><a href="<?php echo htmlspecialchars($base_path); ?>login.php" class="btn btn-primary btn-sm">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
