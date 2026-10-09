<?php
/**
 * Administrator - Book Catalog & Search
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
    $delete_id = (int)($_POST['book_id'] ?? 0);

    if ($delete_id <= 0) {
        $error_message = "Invalid book identifier specified.";
    } else {
        try {
            // Check for active loan records before deleting
            $activeLoanStmt = $pdo->prepare("SELECT COUNT(*) FROM issued_books WHERE book_id = :id AND status = 'issued'");
            $activeLoanStmt->execute([':id' => $delete_id]);
            if ((int)$activeLoanStmt->fetchColumn() > 0) {
                $error_message = "Cannot delete this book because it is currently issued to one or more students.";
            } else {
                // Delete associated loan history or check constraints
                $delLoans = $pdo->prepare("DELETE FROM issued_books WHERE book_id = :id");
                $delLoans->execute([':id' => $delete_id]);

                $delStmt = $pdo->prepare("DELETE FROM books WHERE id = :id LIMIT 1");
                $delStmt->execute([':id' => $delete_id]);

                header("Location: books.php?msg=deleted");
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Database error while deleting book.";
        }
    }
}

// Flash messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $success_message = "Book successfully removed.";
    if ($_GET['msg'] === 'added')   $success_message = "New book successfully added.";
    if ($_GET['msg'] === 'updated') $success_message = "Book details successfully updated.";
}

// Search and fetch books
$search_query = trim($_GET['q'] ?? '');
$books = [];

try {
    if ($search_query !== '') {
        $stmt = $pdo->prepare("
            SELECT * FROM books 
            WHERE book_name LIKE :q OR author LIKE :q OR category LIKE :q OR isbn LIKE :q 
            ORDER BY id DESC
        ");
        $stmt->execute([':q' => "%{$search_query}%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
    }
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Failed to load book records.";
}

$page_title = "Books - Library Management System";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <div class="toolbar">
            <div class="toolbar-left">
                <h1 class="dashboard-title" style="margin: 0; font-size: 1.5rem;">Book Catalog</h1>
                <span class="stock-pill stock-in"><?php echo count($books); ?> Books</span>
            </div>
            <div class="toolbar-right">
                <a href="add_book.php" class="btn btn-primary">+ Add New Book</a>
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
            <form action="books.php" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="q" 
                    class="search-input" 
                    placeholder="Search by Title, Author, Category, ISBN..." 
                    value="<?php echo htmlspecialchars($search_query); ?>"
                >
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <?php if ($search_query !== ''): ?>
                    <a href="books.php" class="btn btn-secondary btn-sm">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Books Table -->
        <div class="table-wrapper">
            <?php if (count($books) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>ISBN</th>
                            <th>Publisher</th>
                            <th style="text-align: center;">Total</th>
                            <th style="text-align: center;">Available</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $b): ?>
                            <tr>
                                <td><?php echo (int)$b['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($b['book_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($b['author']); ?></td>
                                <td><?php echo htmlspecialchars($b['category']); ?></td>
                                <td><code><?php echo htmlspecialchars($b['isbn']); ?></code></td>
                                <td><?php echo htmlspecialchars($b['publisher']); ?></td>
                                <td style="text-align: center;"><?php echo (int)$b['total_copies']; ?></td>
                                <td style="text-align: center;">
                                    <?php if ((int)$b['available_copies'] > 0): ?>
                                        <span class="stock-pill stock-in"><?php echo (int)$b['available_copies']; ?> Available</span>
                                    <?php else: ?>
                                        <span class="stock-pill stock-out">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div class="table-actions" style="justify-content: center;">
                                        <a href="edit_book.php?id=<?php echo (int)$b['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                        <form 
                                            action="books.php" 
                                            method="POST" 
                                            style="display: inline;" 
                                            onsubmit="return confirm('Are you sure you want to delete this book?');"
                                        >
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="book_id" value="<?php echo (int)$b['id']; ?>">
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
                    <h2>No Books Found</h2>
                    <p>No books match your criteria or the catalog is empty.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
