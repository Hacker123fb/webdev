<?php
/**
 * Administrator Dashboard
 * Semester V Web Development Mini Project
 */

// Include authentication helpers and enforce Admin role
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Fetch live database-driven statistics
$book_count           = 0;
$student_count        = 0;
$available_copies_sum = 0;
$issued_count         = 0;

try {
    $book_count           = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $student_count        = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $available_copies_sum = (int)$pdo->query("SELECT COALESCE(SUM(available_copies), 0) FROM books")->fetchColumn();
    $issued_count         = (int)$pdo->query("SELECT COUNT(*) FROM issued_books WHERE LOWER(status) = 'issued'")->fetchColumn();
} catch (PDOException $e) {
    // Defaults to 0 on failure
}

$user = get_logged_in_user();
$page_title = "Admin Dashboard - Library Management System";
$base_path = "../";

// Include shared header
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Welcome Banner -->
        <section class="dashboard-banner">
            <div>
                <span class="badge-role badge-admin">Administrator</span>
                <h1 class="dashboard-title">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p class="dashboard-subtitle">
                    Manage book inventory, student registrations, and book circulation.
                </p>
            </div>
            <div>
                <form action="../logout.php" method="POST">
                    <button type="submit" class="btn btn-outline btn-sm">Sign Out</button>
                </form>
            </div>
        </section>

        <!-- Live Database-Driven Statistics -->
        <section class="stats-grid">
            <div class="stat-card" style="border-top-color: #1e3a8a;">
                <span class="stat-number"><?php echo $book_count; ?></span>
                <span class="stat-label">Total Books</span>
            </div>
            <div class="stat-card" style="border-top-color: #0284c7;">
                <span class="stat-number"><?php echo $student_count; ?></span>
                <span class="stat-label">Registered Students</span>
            </div>
            <div class="stat-card" style="border-top-color: #16a34a;">
                <span class="stat-number"><?php echo $available_copies_sum; ?></span>
                <span class="stat-label">Available Copies</span>
            </div>
            <div class="stat-card" style="border-top-color: #dc2626;">
                <span class="stat-number"><?php echo $issued_count; ?></span>
                <span class="stat-label">Currently Issued</span>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="feature-card" style="margin-top: 1rem;">
            <h2 class="feature-title" style="margin-bottom: 1rem;">Quick Actions</h2>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="books.php" class="btn btn-primary btn-sm">Manage Books</a>
                <a href="add_book.php" class="btn btn-outline btn-sm">+ Add Book</a>
                <a href="students.php" class="btn btn-primary btn-sm">Manage Students</a>
                <a href="add_student.php" class="btn btn-outline btn-sm">+ Add Student</a>
                <a href="issue_book.php" class="btn btn-primary btn-sm">Issue Book</a>
                <a href="issued_books.php" class="btn btn-outline btn-sm">Issued Books Ledger</a>
            </div>
        </section>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
