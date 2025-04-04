<?php
session_start();
include "db.php"; 
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit();
}
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['book_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid request. No book ID provided."]);
    exit();
}

$book_id = $data['book_id'];
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM borrowed_books WHERE book_id = ? AND user_id = ? AND returned = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $book_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "This book is not borrowed by the user or has already been returned."]);
    exit();
}
$updateSql = "UPDATE borrowed_books SET returned = 1, return_date = NOW() WHERE book_id = ? AND user_id = ?";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("ii", $book_id, $user_id);

if ($updateStmt->execute()) {
    echo json_encode(["success" => true, "message" => "Book returned successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update return status."]);
}

?>
