<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('⚠️ Please log in first!'); window.location='student_login.html';</script>";
    exit();
}

include "db.php"; 

$user_id = $_SESSION["user_id"];

$sql = "SELECT books.title, books.author, borrowed_books.borrow_date, borrowed_books.return_date
        FROM borrowed_books
        JOIN books ON borrowed_books.book_id = books.id
        WHERE borrowed_books.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Borrowed Books</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION["username"]; ?>!</h2>
    <h3>📚 Your Borrowed Books</h3>
    
    <?php if ($result->num_rows > 0) { ?>
        <table border="1">
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td><?php echo htmlspecialchars($row["author"]); ?></td>
                    <td><?php echo $row["borrow_date"]; ?></td>
                    <td><?php echo $row["return_date"]; ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>❌ No books borrowed yet.</p>
    <?php } ?>

    <br>
    <a href="logout.php">🔴 Logout</a>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
