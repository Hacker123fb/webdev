<?php
/**
 * Student - Browse Catalog Books (Live AJAX Search)
 * Semester V Web Development Mini Project
 */

// Include authentication and enforce Student role
require_once __DIR__ . '/../includes/auth.php';
require_student('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$search_query = trim($_GET['q'] ?? '');
$books = [];
$error_message = '';

try {
    if ($search_query !== '') {
        $stmt = $pdo->prepare("
            SELECT id, book_name, author, category, isbn, publisher, available_copies 
            FROM books 
            WHERE book_name LIKE :q OR author LIKE :q OR category LIKE :q OR isbn LIKE :q 
            ORDER BY book_name ASC
        ");
        $stmt->execute([':q' => "%{$search_query}%"]);
    } else {
        $stmt = $pdo->query("
            SELECT id, book_name, author, category, isbn, publisher, available_copies 
            FROM books 
            ORDER BY book_name ASC
        ");
    }
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Failed to load catalog books.";
}

$page_title = "Browse Books - Student Portal";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <div class="toolbar">
            <div class="toolbar-left">
                <h1 class="dashboard-title" style="margin: 0; font-size: 1.5rem;">Library Catalog</h1>
                <span id="resultsCount" class="stock-pill stock-in"><?php echo count($books); ?> Titles</span>
            </div>
            <div class="toolbar-right">
                <a href="my_books.php" class="btn btn-outline btn-sm">My Borrowed Books &rarr;</a>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- AJAX Live Search Input -->
        <div class="feature-card" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
            <form action="books.php" method="GET" class="search-form" id="searchForm">
                <input 
                    type="text" 
                    id="liveSearchInput"
                    name="q" 
                    class="search-input" 
                    placeholder="Type to search books (Live AJAX search)..." 
                    value="<?php echo htmlspecialchars($search_query); ?>"
                    autocomplete="off"
                >
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <?php if ($search_query !== ''): ?>
                    <a href="books.php" class="btn btn-secondary btn-sm">Clear</a>
                <?php endif; ?>
            </form>
            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                ⚡ Live search automatically fetches matching books as you type.
            </small>
        </div>

        <!-- Books Table -->
        <div class="table-wrapper">
            <table class="table table-striped" id="catalogTable">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>ISBN</th>
                        <th>Publisher</th>
                        <th style="text-align: center;">Availability</th>
                    </tr>
                </thead>
                <tbody id="booksTableBody">
                    <?php if (count($books) > 0): ?>
                        <?php foreach ($books as $b): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($b['book_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($b['author']); ?></td>
                                <td><?php echo htmlspecialchars($b['category']); ?></td>
                                <td><code><?php echo htmlspecialchars($b['isbn']); ?></code></td>
                                <td><?php echo htmlspecialchars($b['publisher'] ?? '—'); ?></td>
                                <td style="text-align: center;">
                                    <?php if ((int)$b['available_copies'] > 0): ?>
                                        <span class="stock-pill stock-in">Available (<?php echo (int)$b['available_copies']; ?>)</span>
                                    <?php else: ?>
                                        <span class="stock-pill stock-out">Checked Out</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">No books found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<!-- jQuery AJAX Live Book Search Script -->
<script>
$(document).ready(function () {
    let debounceTimer = null;
    const $input = $('#liveSearchInput');
    const $tbody = $('#booksTableBody');
    const $count = $('#resultsCount');

    // Debounced search on input
    $input.on('keyup input', function () {
        const query = $(this).val().trim();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            $.ajax({
                url: '../api/books.php',
                type: 'GET',
                data: { q: query },
                dataType: 'json',
                success: function (data) {
                    $count.text(data.length + ' Titles');
                    if (!data || data.length === 0) {
                        $tbody.html('<tr><td colspan="6" style="text-align: center; padding: 2rem;">No books found.</td></tr>');
                        return;
                    }
                    let html = '';
                    data.forEach(function (b) {
                        const isAvail = Number(b.available_copies) > 0;
                        const badge = isAvail
                            ? '<span class="stock-pill stock-in">Available (' + b.available_copies + ')</span>'
                            : '<span class="stock-pill stock-out">Checked Out</span>';

                        html += '<tr>' +
                            '<td><strong>' + $('<div>').text(b.book_name).html() + '</strong></td>' +
                            '<td>' + $('<div>').text(b.author).html() + '</td>' +
                            '<td>' + $('<div>').text(b.category).html() + '</td>' +
                            '<td><code>' + $('<div>').text(b.isbn).html() + '</code></td>' +
                            '<td>' + $('<div>').text(b.publisher || '—').html() + '</td>' +
                            '<td style="text-align: center;">' + badge + '</td>' +
                            '</tr>';
                    });
                    $tbody.html(html);
                }
            });
        }, 300);
    });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
