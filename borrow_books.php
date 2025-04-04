<?php
include 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["error" => "⚠️ User is not logged in!"]);
    exit;
}

$user_id = $_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode([
        "error" => "Invalid request - No JSON data received",
        "raw_input" => file_get_contents("php://input")
    ]);
    exit;
}

if (!isset($data['books']) || !is_array($data['books'])) {
    echo json_encode(["error" => "Invalid request format"]);
    exit;
}
$query = "INSERT INTO borrowed_books (user_id, book_id, title, borrow_date, return_date) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

foreach ($data['books'] as $book) {
    if (!isset($book['id']) || !isset($book['title'])) {
        echo json_encode(["error" => "Missing book ID or title"]);
        exit;
    }

    $bookId = $book['id'];
    $title = $book['title'];
    $borrowDate = date('Y-m-d');
    $returnDate = date('Y-m-d', strtotime('+14 days'));

    $stmt->bind_param("iisss", $user_id, $bookId, $title, $borrowDate, $returnDate);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(["success" => true]);
?>
