<?php
/**
 * Administrator - Add New Book
 * Semester V Web Development Mini Project
 */

// Include authentication and enforce Administrator role
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$error_message = '';
$book_name     = '';
$author        = '';
$category      = '';
$isbn          = '';
$publisher     = '';
$total_copies  = 1;

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
    } else {
        try {
            // Check for duplicate ISBN
            $checkStmt = $pdo->prepare("SELECT id FROM books WHERE isbn = :isbn LIMIT 1");
            $checkStmt->execute([':isbn' => $isbn]);
            if ($checkStmt->fetch()) {
                $error_message = "A book with ISBN '{$isbn}' already exists.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO books (book_name, author, category, isbn, publisher, total_copies, available_copies)
                    VALUES (:book_name, :author, :category, :isbn, :publisher, :total_copies, :available_copies)
                ");
                $stmt->execute([
                    ':book_name'        => $book_name,
                    ':author'           => $author,
                    ':category'         => $category,
                    ':isbn'             => $isbn,
                    ':publisher'        => $publisher,
                    ':total_copies'     => $total_copies,
                    ':available_copies' => $total_copies
                ]);

                header("Location: books.php?msg=added");
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Database error while adding book.";
        }
    }
}

$page_title = "Add Book - Library Management System";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 650px;">

        <div class="auth-card" style="margin-top: 1rem;">
            <div class="toolbar" style="margin-bottom: 1rem;">
                <h1 class="auth-title" style="margin: 0;">Add New Book</h1>
                <a href="books.php" class="btn btn-secondary btn-sm">&larr; Back to Books</a>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="add_book.php" method="POST">
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
                    <input type="number" id="total_copies" name="total_copies" class="form-control" min="1" value="<?php echo htmlspecialchars((string)$total_copies); ?>" required>
                </div>

                <div class="form-group" style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Book</button>
                    <a href="books.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
