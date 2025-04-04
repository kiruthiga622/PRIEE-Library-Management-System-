<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

include "db.php"; 
$json_input = file_get_contents("php://input");
file_put_contents("debug.log", "Raw Input: " . $json_input . PHP_EOL, FILE_APPEND);

if (empty($json_input)) {
    echo json_encode(["error" => "Invalid request - No JSON data received", "raw_input" => $json_input]);
    exit();
}

$data = json_decode($json_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["error" => "Invalid JSON format", "json_error" => json_last_error_msg()]);
    exit();
}
$user_id = isset($data["user_id"]) ? intval($data["user_id"]) : 0;
$book_ids = isset($data["books"]) ? $data["books"] : [];

file_put_contents("debug.log", "User ID: " . $user_id . PHP_EOL, FILE_APPEND);
file_put_contents("debug.log", "Books: " . json_encode($book_ids) . PHP_EOL, FILE_APPEND);

if ($user_id <= 0 || empty($book_ids)) {
    echo json_encode(["error" => "Invalid request - Missing user ID or books"]);
    exit();
}

foreach ($book_ids as $book_id) {
    $book_id = intval($book_id);
    $borrow_date = date("Y-m-d");
    $return_date = date("Y-m-d", strtotime("+2 weeks"));

    $stmt = $conn->prepare("INSERT INTO borrowed_books (user_id, book_id, borrow_date, return_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $book_id, $borrow_date, $return_date);

    if (!$stmt->execute()) {
        echo json_encode(["error" => "❌ Failed to borrow book ID: " . $book_id, "sql_error" => $stmt->error]);
        exit();
    }
}

echo json_encode(["success" => true, "message" => "✅ Books borrowed successfully"]);
?>


