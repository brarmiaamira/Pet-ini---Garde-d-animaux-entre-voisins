<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Méthode non autorisée.");
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if (empty($email) || empty($password)) {
    header("Location: ../login.html?error=1");
    exit;
}

$stmt = $pdo->prepare("SELECT id, prenom, email, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user["password_hash"])) {
    header("Location: ../login.html?error=1");
    exit;
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_email"] = $user["email"];
$_SESSION["user_name"] = $user["prenom"];

header("Location: ../index.html");
exit;
?>
