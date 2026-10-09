<?php
/**
 * Administrator - Circulation Ledger & Book Returns
 * Semester V Web Development Mini Project
 */

// Include authentication and enforce Administrator role
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$success_message = '';
$error_message   = '';

// Handle Book Return via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return') {
    $issue_id = (int)($_POST['issue_id'] ?? 0);

    if ($issue_id <= 0) {
        $error_message = "Invalid circulation record identifier.";
    } else {
        // Safe database transaction
        try {
            $pdo->beginTransaction();

            $lockIssue = $pdo->prepare("SELECT id, book_id, status FROM issued_books WHERE id = :id FOR UPDATE");
            $lockIssue->execute([':id' => $issue_id]);
            $issueRecord = $lockIssue->fetch();

            if (!$issueRecord) {
                $pdo->rollBack();
                $error_message = "Circulation record not found.";
            } elseif (strtolower($issueRecord['status']) === 'returned') {
                $pdo->rollBack();
                $error_message = "This book has already been marked as returned.";
            } else {
                $book_id = (int)$issueRecord['book_id'];

                // Update issue status
                $updateIssue = $pdo->prepare("UPDATE issued_books SET status = 'returned', return_date = CURRENT_DATE() WHERE id = :id");
                $updateIssue->execute([':id' => $issue_id]);

                // Increment available copies on book
                $updateBook = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = :id");
                $updateBook->execute([':id' => $book_id]);

                $pdo->commit();

                header("Location: issued_books.php?msg=returned");
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message = "Transaction error occurred while returning book.";
        }
    }
}

// Flash messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'issued')   $success_message = "Book successfully issued to student.";
    if ($_GET['msg'] === 'returned') $success_message = "Book successfully returned to inventory.";
}

// Filter (all, issued, returned)
$filter = trim($_GET['filter'] ?? 'all');
$where  = "";
if ($filter === 'issued')   $where = "WHERE LOWER(ib.status) = 'issued'";
if ($filter === 'returned') $where = "WHERE LOWER(ib.status) = 'returned'";

// Fetch records
$records = [];
try {
    $sql = "
        SELECT 
            ib.id,
            ib.issue_date,
            ib.due_date,
            ib.return_date,
            ib.status,
            s.student_id AS student_roll,
            s.name AS student_name,
            b.book_name
        FROM issued_books ib
        JOIN students s ON ib.student_id = s.id
        JOIN books b ON ib.book_id = b.id
        {$where}
        ORDER BY ib.id DESC
    ";
    $records = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $error_message = "Failed to load circulation records.";
}

$page_title = "Issued Books - Library Management System";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <div class="toolbar">
            <div class="toolbar-left">
                <h1 class="dashboard-title" style="margin: 0; font-size: 1.5rem;">Circulation Ledger</h1>
                <span class="stock-pill stock-in"><?php echo count($records); ?> Records</span>
            </div>
            <div class="toolbar-right">
                <a href="issue_book.php" class="btn btn-primary">+ Issue Book</a>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Simple Filter Tabs -->
        <div style="margin-bottom: 1rem; display: flex; gap: 0.5rem;">
            <a href="issued_books.php?filter=all" class="btn btn-sm <?php echo ($filter === 'all') ? 'btn-primary' : 'btn-outline'; ?>">All</a>
            <a href="issued_books.php?filter=issued" class="btn btn-sm <?php echo ($filter === 'issued') ? 'btn-primary' : 'btn-outline'; ?>">Currently Issued</a>
            <a href="issued_books.php?filter=returned" class="btn btn-sm <?php echo ($filter === 'returned') ? 'btn-primary' : 'btn-outline'; ?>">Returned</a>
        </div>

        <!-- Ledger Table -->
        <div class="table-wrapper">
            <?php if (count($records) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Book Title</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): 
                            $is_active = (strtolower($row['status']) === 'issued');
                        ?>
                            <tr>
                                <td><?php echo (int)$row['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['student_name']); ?></strong><br>
                                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($row['student_roll']); ?></small>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['book_name']); ?></strong></td>
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
                                <td style="text-align: center;">
                                    <?php if ($is_active): ?>
                                        <form 
                                            action="issued_books.php" 
                                            method="POST" 
                                            style="display: inline;" 
                                            onsubmit="return confirm('Confirm return for \'<?php echo htmlspecialchars(addslashes($row['book_name'])); ?>\'?');"
                                        >
                                            <input type="hidden" name="action" value="return">
                                            <input type="hidden" name="issue_id" value="<?php echo (int)$row['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Return</button>
                                        </form>
                                    <?php else: ?>
                                        <small style="color: var(--text-muted);">Completed</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h2>No Records Found</h2>
                    <p>No book circulation records currently recorded.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
