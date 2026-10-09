<?php
/**
 * REST API Endpoint - Get Single Book Details
 * Method: GET
 * URL: /api/book.php?id=BOOK_ID
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

$book_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validate book ID parameter
if (!$book_id || $book_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'status'  => 400,
        'error'   => 'Bad Request',
        'message' => 'Invalid or missing book ID query parameter.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Include central database connection
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->prepare("
        SELECT id, book_name, author, category, isbn, publisher, total_copies, available_copies 
        FROM books 
        WHERE id = :id 
        LIMIT 1
    ");
    $stmt->execute([':id' => $book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        http_response_code(404);
        echo json_encode([
            'status'  => 404,
            'error'   => 'Not Found',
            'message' => "Book with ID {$book_id} was not found."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $response = [
        'id'               => (int)$book['id'],
        'book_name'        => $book['book_name'],
        'author'           => $book['author'],
        'category'         => $book['category'],
        'isbn'             => $book['isbn'],
        'publisher'        => $book['publisher'],
        'total_copies'     => (int)$book['total_copies'],
        'available_copies' => (int)$book['available_copies']
    ];

    http_response_code(200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 500,
        'error'   => 'Database Error',
        'message' => 'An error occurred while retrieving book details.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
