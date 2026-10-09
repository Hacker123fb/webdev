<?php
/**
 * Administrator - Issue Book
 * Semester V Web Development Mini Project
 */

// Include authentication and enforce Administrator role
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$error_message = '';
$selected_student_id = (int)($_POST['student_id'] ?? ($_GET['student_id'] ?? 0));
$selected_book_id    = (int)($_POST['book_id'] ?? ($_GET['book_id'] ?? 0));
$issue_date          = $_POST['issue_date'] ?? date('Y-m-d');
$due_date            = $_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days'));

// Fetch students for dropdown
try {
    $students = $pdo->query("SELECT id, student_id, name FROM students ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $students = [];
}

// Fetch available books for dropdown
try {
    $available_books = $pdo->query("
        SELECT id, book_name, author, available_copies 
        FROM books 
        WHERE available_copies > 0 
        ORDER BY book_name ASC
    ")->fetchAll();
} catch (PDOException $e) {
    $available_books = [];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($selected_student_id <= 0 || $selected_book_id <= 0) {
        $error_message = "Please select both a student and a book.";
    } elseif (empty($issue_date) || empty($due_date)) {
        $error_message = "Please provide both issue date and due date.";
    } elseif ($due_date < $issue_date) {
        $error_message = "Due date cannot be earlier than issue date.";
    } else {
        // Safe database transaction
        try {
            $pdo->beginTransaction();

            // Lock the book to check inventory safely
            $lockBook = $pdo->prepare("SELECT id, book_name, available_copies FROM books WHERE id = :id FOR UPDATE");
            $lockBook->execute([':id' => $selected_book_id]);
            $bookRow = $lockBook->fetch();

            if (!$bookRow) {
                $pdo->rollBack();
                $error_message = "Selected book does not exist.";
            } elseif ((int)$bookRow['available_copies'] <= 0) {
                $pdo->rollBack();
                $error_message = "No copies of '{$bookRow['book_name']}' are currently available.";
            } else {
                // Record the issue
                $insertStmt = $pdo->prepare("
                    INSERT INTO issued_books (student_id, book_id, issue_date, due_date, status)
                    VALUES (:student_id, :book_id, :issue_date, :due_date, 'issued')
                ");
                $insertStmt->execute([
                    ':student_id' => $selected_student_id,
                    ':book_id'    => $selected_book_id,
                    ':issue_date' => $issue_date,
                    ':due_date'   => $due_date
                ]);

                // Decrement available copies by 1
                $updateBook = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = :id");
                $updateBook->execute([':id' => $selected_book_id]);

                $pdo->commit();

                header("Location: issued_books.php?msg=issued");
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message = "Database transaction failed. Please try again.";
        }
    }
}

$page_title = "Issue Book - Library Management System";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 650px;">

        <div class="auth-card" style="margin-top: 1rem;">
            <div class="toolbar" style="margin-bottom: 1rem;">
                <h1 class="auth-title" style="margin: 0;">Issue a Book</h1>
                <a href="issued_books.php" class="btn btn-secondary btn-sm">&larr; Issued Ledger</a>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="issue_book.php" method="POST">
                <div class="form-group">
                    <label for="student_id" class="form-label">Select Student *</label>
                    <select id="student_id" name="student_id" class="form-control" required autofocus>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($students as $stu): ?>
                            <option value="<?php echo (int)$stu['id']; ?>" <?php echo ($selected_student_id === (int)$stu['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($stu['name']); ?> (<?php echo htmlspecialchars($stu['student_id']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="book_id" class="form-label">Select Book *</label>
                    <select id="book_id" name="book_id" class="form-control" required>
                        <option value="">-- Choose Book (Only Available) --</option>
                        <?php foreach ($available_books as $bk): ?>
                            <option value="<?php echo (int)$bk['id']; ?>" <?php echo ($selected_book_id === (int)$bk['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bk['book_name']); ?> - <?php echo htmlspecialchars($bk['author']); ?> (<?php echo (int)$bk['available_copies']; ?> available)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="issue_date" class="form-label">Issue Date *</label>
                        <input type="date" id="issue_date" name="issue_date" class="form-control" value="<?php echo htmlspecialchars($issue_date); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="due_date" class="form-label">Due Date *</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" value="<?php echo htmlspecialchars($due_date); ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Confirm Issue</button>
                    <a href="issued_books.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
