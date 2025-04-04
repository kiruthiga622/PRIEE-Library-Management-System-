<?php
session_start();
include "db.php"; 
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT books.id, books.title, books.author, books.genre, borrowed_books.borrow_date, borrowed_books.return_date 
        FROM borrowed_books 
        JOIN books ON borrowed_books.book_id = books.id 
        WHERE borrowed_books.user_id = ? AND borrowed_books.returned = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$borrowedBooks = [];

while ($row = $result->fetch_assoc()) {
    $borrowedBooks[] = $row;
}

echo json_encode($borrowedBooks);
?>
