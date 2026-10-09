<?php
/**
 * Administrator - Student Directory & Search
 * Semester V Web Development Mini Project
 */

// Include authentication and enforce Administrator role
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$success_message = '';
$error_message   = '';

// Handle Delete Request via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete_id = (int)($_POST['student_db_id'] ?? 0);

    if ($delete_id <= 0) {
        $error_message = "Invalid student identifier specified.";
    } else {
        try {
            // Check for active issued books
            $activeLoansStmt = $pdo->prepare("SELECT COUNT(*) FROM issued_books WHERE student_id = :id AND status = 'issued'");
            $activeLoansStmt->execute([':id' => $delete_id]);
            if ((int)$activeLoansStmt->fetchColumn() > 0) {
                $error_message = "Cannot delete student because they currently hold unreturned issued book(s).";
            } else {
                // Delete historical loans and student record
                $delLoans = $pdo->prepare("DELETE FROM issued_books WHERE student_id = :id");
                $delLoans->execute([':id' => $delete_id]);

                $delStmt = $pdo->prepare("DELETE FROM students WHERE id = :id LIMIT 1");
                $delStmt->execute([':id' => $delete_id]);

                header("Location: students.php?msg=deleted");
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Database error while deleting student.";
        }
    }
}

// Flash messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $success_message = "Student record successfully removed.";
    if ($_GET['msg'] === 'added')   $success_message = "New student successfully registered.";
    if ($_GET['msg'] === 'updated') $success_message = "Student details successfully updated.";
}

// Search and list students
$search_query = trim($_GET['q'] ?? '');
$students = [];

try {
    if ($search_query !== '') {
        $stmt = $pdo->prepare("
            SELECT * FROM students 
            WHERE student_id LIKE :q OR name LIKE :q OR email LIKE :q OR department LIKE :q
            ORDER BY id DESC
        ");
        $stmt->execute([':q' => "%{$search_query}%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
    }
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Failed to load student records.";
}

$page_title = "Students - Library Management System";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <div class="toolbar">
            <div class="toolbar-left">
                <h1 class="dashboard-title" style="margin: 0; font-size: 1.5rem;">Student Directory</h1>
                <span class="stock-pill stock-in"><?php echo count($students); ?> Students</span>
            </div>
            <div class="toolbar-right">
                <a href="add_student.php" class="btn btn-primary">+ Add New Student</a>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Search Form -->
        <div class="feature-card" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
            <form action="students.php" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="q" 
                    class="search-input" 
                    placeholder="Search by ID, Name, Department, Email..." 
                    value="<?php echo htmlspecialchars($search_query); ?>"
                >
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <?php if ($search_query !== ''): ?>
                    <a href="students.php" class="btn btn-secondary btn-sm">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Students Table -->
        <div class="table-wrapper">
            <?php if (count($students) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th style="text-align: center;">Semester</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?php echo (int)$s['id']; ?></td>
                                <td><code><?php echo htmlspecialchars($s['student_id']); ?></code></td>
                                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                <td><?php echo htmlspecialchars($s['phone']); ?></td>
                                <td><?php echo htmlspecialchars($s['department']); ?></td>
                                <td style="text-align: center;"><?php echo (int)$s['semester']; ?></td>
                                <td style="text-align: center;">
                                    <div class="table-actions" style="justify-content: center;">
                                        <a href="edit_student.php?id=<?php echo (int)$s['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                        <form 
                                            action="students.php" 
                                            method="POST" 
                                            style="display: inline;" 
                                            onsubmit="return confirm('Are you sure you want to delete this student?');"
                                        >
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="student_db_id" value="<?php echo (int)$s['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h2>No Students Found</h2>
                    <p>No student records match your search or directory is empty.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
