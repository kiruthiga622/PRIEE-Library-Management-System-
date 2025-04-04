
<?php
 if (session_status() == PHP_SESSION_NONE) {
    session_start();
 }

include "db.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = isset($_POST["first"]) ? trim($_POST["first"]) : "";
    $last_name = isset($_POST["last"]) ? trim($_POST["last"]) : "";
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
    $roll_no = isset($_POST["roll"]) ? trim($_POST["roll"]) : "";
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    if (empty($first_name) || empty($last_name) || empty($username) || empty($password) || empty($roll_no) || empty($email)) {
        die("<p class='error-message'>Error: All fields are required!</p>");
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $checkQuery = "SELECT * FROM students WHERE email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("<p class='error-message'>Error: Email already registered!</p>");
    }

    $sql = "INSERT INTO students (first_name, last_name, username, password, roll_no, email) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $first_name, $last_name, $username, $hashed_password, $roll_no, $email);

    if ($stmt->execute()) {
        echo "<p class='success-message'>Registration successful! Redirecting...</p>";
        echo "<script>setTimeout(function() { window.location='student_login.html'; }, 2000);</script>";
    } else {
        echo "<p class='error-message'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$conn->close();
?> 
