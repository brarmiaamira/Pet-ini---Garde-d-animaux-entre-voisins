<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Méthode non autorisée.");
}

$prenom = trim($_POST["prenom"] ?? "");
$nom = trim($_POST["nom"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$password_confirm = $_POST["password_confirm"] ?? "";
$role = $_POST["role"] ?? "";

if (empty($prenom) || empty($nom) || empty($email) || empty($password) || empty($password_confirm) || empty($role)) {
    exit("Tous les champs sont obligatoires.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Email invalide.");
}

if (strlen($password) < 8) {
    exit("Le mot de passe doit contenir au moins 8 caractères.");
}

if ($password !== $password_confirm) {
    exit("Les mots de passe ne correspondent pas.");
}

$allowed_roles = ["proprietaire", "gardien", "les_deux"];
if (!in_array($role, $allowed_roles)) {
    exit("Rôle invalide.");
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    exit("Cet email existe déjà.");
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (prenom, nom, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$prenom, $nom, $email, $password_hash, $role]);

$_SESSION["user_email"] = $email;
$_SESSION["user_name"] = $prenom;

header("Location: ../login.html");
exit;
?>