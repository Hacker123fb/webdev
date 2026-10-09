<?php
/**
 * Student - My Borrowed Books
 * Semester V Web Development Mini Project
 */

// Include authentication and enforce Student role
require_once __DIR__ . '/../includes/auth.php';
require_student('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$user = get_logged_in_user();
$error_message = '';

// Resolve student record
$student_profile = null;
try {
    $stmt = $pdo->prepare("
        SELECT id, student_id, name FROM students 
        WHERE user_id = :uid OR student_id = :uname 
        LIMIT 1
    ");
    $stmt->execute([':uid' => $user['id'], ':uname' => $user['username']]);
    $student_profile = $stmt->fetch();
} catch (PDOException $e) {
    $error_message = "Failed to load student profile.";
}

// Fetch borrowed books strictly for THIS student
$borrowed_books = [];
if ($student_profile) {
    try {
        $loanStmt = $pdo->prepare("
            SELECT 
                ib.id,
                ib.issue_date,
                ib.due_date,
                ib.return_date,
                ib.status,
                b.book_name,
                b.author,
                b.isbn,
                b.category
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.id
            WHERE ib.student_id = :sid
            ORDER BY ib.id DESC
        ");
        $loanStmt->execute([':sid' => $student_profile['id']]);
        $borrowed_books = $loanStmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Failed to retrieve your borrowed books.";
    }
}

$page_title = "My Books - Student Portal";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <div class="toolbar">
            <div class="toolbar-left">
                <h1 class="dashboard-title" style="margin: 0; font-size: 1.5rem;">My Borrowed Books</h1>
                <span class="stock-pill stock-in"><?php echo count($borrowed_books); ?> Records</span>
            </div>
            <div class="toolbar-right">
                <a href="books.php" class="btn btn-outline btn-sm">&larr; Browse All Books</a>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Borrowed Books Table -->
        <div class="table-wrapper">
            <?php if (count($borrowed_books) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>ISBN</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowed_books as $row): 
                            $is_active = (strtolower($row['status']) === 'issued');
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['book_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><code><?php echo htmlspecialchars($row['isbn']); ?></code></td>
                                <td><?php echo htmlspecialchars($row['issue_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                                <td><?php echo !empty($row['return_date']) ? htmlspecialchars($row['return_date']) : '&mdash;'; ?></td>
                                <td style="text-align: center;">
                                    <?php if ($is_active): ?>
                                        <span class="stock-pill stock-out">Issued</span>
                                    <?php else: ?>
                                        <span class="stock-pill stock-in">Returned</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h2>No Borrowed Books</h2>
                    <p>You have not borrowed any books yet.</p>
                    <div style="margin-top: 1rem;">
                        <a href="books.php" class="btn btn-primary btn-sm">Browse Available Books</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
