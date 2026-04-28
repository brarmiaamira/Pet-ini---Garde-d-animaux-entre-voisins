<?php
session_start();
include '../../backend/config/db.php';

$owner_id = 2;
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

$animal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($animal_id == 0) {
    $sql = "SELECT id FROM profils_animaux WHERE proprietaire_id = $owner_id LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $animal_id = $row['id'];
}

$sql = "SELECT * FROM profils_animaux WHERE id = $animal_id AND proprietaire_id = $owner_id";
$result = mysqli_query($conn, $sql);
$animal = mysqli_fetch_assoc($result);

$sql_tous = "SELECT * FROM profils_animaux WHERE proprietaire_id = $owner_id";
$result_tous = mysqli_query($conn, $sql_tous);

// Prépare les données pour la vue
$animaux = [];
while ($a = mysqli_fetch_assoc($result_tous)) {
    $animaux[] = $a;
}

// Charge la vue HTML
include 'petProfil.html.php';
?>