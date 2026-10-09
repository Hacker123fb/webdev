<?php
/**
 * REST API Endpoint - Get Books Catalog
 * Method: GET
 * URL: /api/books.php
 * Optional Query: ?q=search_term
 */

header('Content-Type: application/json; charset=utf-8');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status'  => 405,
        'error'   => 'Method Not Allowed',
        'message' => 'Only GET requests are supported on this endpoint.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Include central database connection
require_once __DIR__ . '/../config/database.php';

$search_query = trim($_GET['q'] ?? '');

try {
    if ($search_query !== '') {
        $sql = "
            SELECT id, book_name, author, category, isbn, publisher, available_copies 
            FROM books 
            WHERE book_name LIKE :q 
               OR author LIKE :q 
               OR category LIKE :q 
               OR isbn LIKE :q
            ORDER BY book_name ASC
        ";
        $stmt = $pdo->prepare($sql);
        $wildcard = "%{$search_query}%";
        $stmt->execute([':q' => $wildcard]);
    } else {
        $sql = "
            SELECT id, book_name, author, category, isbn, publisher, available_copies 
            FROM books 
            ORDER BY book_name ASC
        ";
        $stmt = $pdo->query($sql);
    }

    $books = $stmt->fetchAll();

    // Cast integer types cleanly
    $response = array_map(function ($row) {
        return [
            'id'               => (int)$row['id'],
            'book_name'        => $row['book_name'],
            'author'           => $row['author'],
            'category'         => $row['category'],
            'isbn'             => $row['isbn'],
            'publisher'        => $row['publisher'] ?? '',
            'available_copies' => (int)$row['available_copies']
        ];
    }, $books);

    http_response_code(200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 500,
        'error'   => 'Database Error',
        'message' => 'An error occurred while retrieving book data.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
