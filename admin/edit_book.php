<?php
/**
 * Administrator - Edit Book
 * Semester V Web Development Mini Project
 */

// Include authentication and enforce Administrator role
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$book_id = (int)($_GET['id'] ?? 0);
if ($book_id <= 0) {
    header("Location: books.php");
    exit;
}

$error_message = '';

// 1. Fetch current book data
try {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        header("Location: books.php?msg=not_found");
        exit;
    }

    // Count currently issued copies
    $loanStmt = $pdo->prepare("SELECT COUNT(*) FROM issued_books WHERE book_id = :id AND status = 'issued'");
    $loanStmt->execute([':id' => $book_id]);
    $currently_issued = (int)$loanStmt->fetchColumn();

} catch (PDOException $e) {
    die("Database error loading book.");
}

$book_name    = $book['book_name'];
$author       = $book['author'];
$category     = $book['category'];
$isbn         = $book['isbn'];
$publisher    = $book['publisher'];
$total_copies = (int)$book['total_copies'];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_name    = trim($_POST['book_name'] ?? '');
    $author       = trim($_POST['author'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $isbn         = trim($_POST['isbn'] ?? '');
    $publisher    = trim($_POST['publisher'] ?? '');
    $total_copies = (int)($_POST['total_copies'] ?? 0);

    // Basic validation
    if ($book_name === '' || $author === '' || $category === '' || $isbn === '' || $publisher === '') {
        $error_message = "All fields are required.";
    } elseif ($total_copies <= 0) {
        $error_message = "Total copies must be a positive number.";
    } elseif ($total_copies < $currently_issued) {
        $error_message = "Cannot reduce total copies below currently issued copies ({$currently_issued}).";
    } else {
        try {
            // Check for duplicate ISBN in other books
            $dupStmt = $pdo->prepare("SELECT id FROM books WHERE isbn = :isbn AND id != :id LIMIT 1");
            $dupStmt->execute([':isbn' => $isbn, ':id' => $book_id]);
            if ($dupStmt->fetch()) {
                $error_message = "The ISBN '{$isbn}' is already used by another book.";
            } else {
                $new_available = $total_copies - $currently_issued;

                $updateStmt = $pdo->prepare("
                    UPDATE books 
                    SET book_name = :book_name,
                        author = :author,
                        category = :category,
                        isbn = :isbn,
                        publisher = :publisher,
                        total_copies = :total_copies,
                        available_copies = :available_copies
                    WHERE id = :id LIMIT 1
                ");
                $updateStmt->execute([
                    ':book_name'        => $book_name,
                    ':author'           => $author,
                    ':category'         => $category,
                    ':isbn'             => $isbn,
                    ':publisher'        => $publisher,
                    ':total_copies'     => $total_copies,
                    ':available_copies' => $new_available,
                    ':id'               => $book_id
                ]);

                header("Location: books.php?msg=updated");
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Database error while updating book.";
        }
    }
}

$page_title = "Edit Book - Library Management System";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 650px;">

        <div class="auth-card" style="margin-top: 1rem;">
            <div class="toolbar" style="margin-bottom: 1rem;">
                <h1 class="auth-title" style="margin: 0;">Edit Book</h1>
                <a href="books.php" class="btn btn-secondary btn-sm">&larr; Back to Books</a>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="edit_book.php?id=<?php echo $book_id; ?>" method="POST">
                <div class="form-group">
                    <label for="book_name" class="form-label">Book Title *</label>
                    <input type="text" id="book_name" name="book_name" class="form-control" value="<?php echo htmlspecialchars($book_name); ?>" required autofocus>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="author" class="form-label">Author *</label>
                        <input type="text" id="author" name="author" class="form-control" value="<?php echo htmlspecialchars($author); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category" class="form-label">Category *</label>
                        <input type="text" id="category" name="category" class="form-control" value="<?php echo htmlspecialchars($category); ?>" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="isbn" class="form-label">ISBN *</label>
                        <input type="text" id="isbn" name="isbn" class="form-control" value="<?php echo htmlspecialchars($isbn); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="publisher" class="form-label">Publisher *</label>
                        <input type="text" id="publisher" name="publisher" class="form-control" value="<?php echo htmlspecialchars($publisher); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="total_copies" class="form-label">Total Number of Copies *</label>
                    <input type="number" id="total_copies" name="total_copies" class="form-control" min="<?php echo max(1, $currently_issued); ?>" value="<?php echo htmlspecialchars((string)$total_copies); ?>" required>
                    <small style="color: var(--text-muted);">Currently issued: <?php echo $currently_issued; ?> copy(ies).</small>
                </div>

                <div class="form-group" style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Update Book</button>
                    <a href="books.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
