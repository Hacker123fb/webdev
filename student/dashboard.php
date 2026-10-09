<?php
/**
 * Student Dashboard
 * Semester V Web Development Mini Project
 */

// Include authentication helpers and enforce Student role
require_once __DIR__ . '/../includes/auth.php';
require_student('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$user = get_logged_in_user();
$page_title = "Student Dashboard - Library Management System";
$base_path = "../";

// Retrieve student profile from students table
$student_profile = null;
try {
    $stmt = $pdo->prepare("
        SELECT id, student_id, name, email, department, semester 
        FROM students 
        WHERE user_id = :uid OR student_id = :uname 
        LIMIT 1
    ");
    $stmt->execute([':uid' => $user['id'], ':uname' => $user['username']]);
    $student_profile = $stmt->fetch();
} catch (PDOException $e) {
    $student_profile = null;
}

// Counts
$total_available = 0;
$my_issued_count = 0;

try {
    $total_available = (int)$pdo->query("SELECT COALESCE(SUM(available_copies), 0) FROM books")->fetchColumn();
    if ($student_profile) {
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM issued_books WHERE student_id = :sid AND LOWER(status) = 'issued'");
        $stmt2->execute([':sid' => $student_profile['id']]);
        $my_issued_count = (int)$stmt2->fetchColumn();
    }
} catch (PDOException $e) {
    // Defaults to 0
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Welcome Banner -->
        <section class="dashboard-banner">
            <div>
                <span class="badge-role badge-student">Student</span>
                <h1 class="dashboard-title">Welcome, <?php echo htmlspecialchars($student_profile ? $student_profile['name'] : $user['username']); ?>!</h1>
                <p class="dashboard-subtitle">
                    Search college library books and review your borrowed books.
                </p>
            </div>
            <div>
                <form action="../logout.php" method="POST">
                    <button type="submit" class="btn btn-outline btn-sm">Sign Out</button>
                </form>
            </div>
        </section>

        <!-- Basic Statistics -->
        <section class="stats-grid">
            <div class="stat-card" style="border-top-color: #16a34a;">
                <span class="stat-number"><?php echo $total_available; ?></span>
                <span class="stat-label">Available Books in Library</span>
            </div>
            <div class="stat-card" style="border-top-color: #0284c7;">
                <span class="stat-number"><?php echo $my_issued_count; ?></span>
                <span class="stat-label">Books Issued to You</span>
            </div>
        </section>

        <!-- Student Profile Card -->
        <?php if ($student_profile): ?>
            <section class="feature-card" style="margin-bottom: 1.5rem;">
                <h2 class="feature-title" style="margin-bottom: 0.75rem;">Your Student Profile</h2>
                <div class="profile-details-grid">
                    <div><strong>Student ID:</strong> <?php echo htmlspecialchars($student_profile['student_id']); ?></div>
                    <div><strong>Name:</strong> <?php echo htmlspecialchars($student_profile['name']); ?></div>
                    <div><strong>Department:</strong> <?php echo htmlspecialchars($student_profile['department']); ?></div>
                    <div><strong>Semester:</strong> Semester <?php echo (int)$student_profile['semester']; ?></div>
                    <div><strong>Email:</strong> <?php echo htmlspecialchars($student_profile['email']); ?></div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Quick Links -->
        <section class="feature-card">
            <h2 class="feature-title" style="margin-bottom: 1rem;">Library Links</h2>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="books.php" class="btn btn-primary btn-sm">Browse Catalog (Live Search)</a>
                <a href="my_books.php" class="btn btn-outline btn-sm">View My Borrowed Books</a>
            </div>
        </section>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
