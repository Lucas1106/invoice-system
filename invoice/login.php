<?php
session_start();

require_once 'auth/db.php';

$email = $_POST['email'] ?? '';
$senha = $_POST['password'] ?? '';

$sql = "SELECT * FROM users WHERE email = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $senha);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $_SESSION['logged_in'] = true;
    $_SESSION['user'] = $result->fetch_assoc();
    header("Location: LCR/index.php");
    exit();
} else {
    header("Location: index.php?error=1");
    exit();
}
