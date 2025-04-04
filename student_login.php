<?php
session_start();
include "db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

header("Content-Type: application/json");

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

      
        file_put_contents("debug_log.txt", print_r($_POST, true));

        $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";

        if (empty($username) || empty($password)) {
            throw new Exception("All fields are required!");
        }

        if (!$conn) {
            throw new Exception("DB connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM students WHERE username = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("SQL error: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            file_put_contents("debug_log.txt", "DB Password: " . $row['password'] . "\n", FILE_APPEND);
            file_put_contents("debug_log.txt", "Entered Password: " . $password . "\n", FILE_APPEND);

            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["username"] = $row["username"];

                echo json_encode(["success" => true, "user_id" => $row["id"]]);
            } else {
                throw new Exception("Incorrect password!");
            }
        } else {
            throw new Exception("User not found!");
        }

        $stmt->close();
    }
} catch (Exception $e) {

    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
