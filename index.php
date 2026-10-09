<?php
/**
 * Library Management System - Landing Page
 * Semester V Web Development Course Project
 */

// Set dynamic page title
$page_title = "Home - Library Management System";

// Include common header
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Hero Section -->
        <section class="hero-section">
            <h1 class="hero-title">Welcome to the Library Management System</h1>
            <p class="hero-subtitle">
                A simple, efficient academic portal designed to manage book catalogs, student memberships, and book issue/return tracking.
            </p>
            <div class="hero-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo ($_SESSION['role'] === 'admin') ? 'admin/dashboard.php' : 'student/dashboard.php'; ?>" class="btn btn-primary">Go to Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Login to Portal</a>
                <?php endif; ?>
                <a href="#about" class="btn btn-outline">Learn More</a>
            </div>
        </section>

        <!-- System Architecture & Module Overview Cards -->
        <section id="about" class="features-grid">
            <article class="feature-card">
                <div class="feature-icon-badge">B</div>
                <h2 class="feature-title">Book Catalog Management</h2>
                <p class="feature-text">
                    Organize books by title, author, category, publisher, and ISBN. Track total copies alongside real-time available stock.
                </p>
            </article>

            <article class="feature-card">
                <div class="feature-icon-badge">S</div>
                <h2 class="feature-title">Student Records</h2>
                <p class="feature-text">
                    Maintain structured records for enrolled students across departments and semesters with verified contact details.
                </p>
            </article>

            <article class="feature-card">
                <div class="feature-icon-badge">C</div>
                <h2 class="feature-title">Circulation Tracking</h2>
                <p class="feature-text">
                    Record issue dates, enforce scheduled return deadlines, and preserve transaction histories with relational database integrity.
                </p>
            </article>
        </section>

        <!-- Academic Project Information -->
        <section class="project-banner">
            <h3>Web Development Mini Project Foundation</h3>
            <p>
                Built using HTML5, Vanilla CSS3, PHP, and MySQL. Prepared for XAMPP / WAMP local development environment.
            </p>
        </section>

    </div>
</main>

<?php
// Include common footer
require_once __DIR__ . '/includes/footer.php';
?>
