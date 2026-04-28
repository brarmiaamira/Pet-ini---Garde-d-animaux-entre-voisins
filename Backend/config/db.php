<?php
// Informations de connexion
$host     = "localhost";
$dbname   = "petini";
$user     = "root";
$password = "";

// Connexion
$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connexion échouée : " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>