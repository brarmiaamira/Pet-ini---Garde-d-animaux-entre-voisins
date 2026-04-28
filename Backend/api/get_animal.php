<?php
// get_animal.php — retourne les détails d'un animal en JSON
session_start();
include '../config/db.php';

header('Content-Type: application/json');

//$owner_id = 2; // temporaire
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$animal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($animal_id == 0) {
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

$sql = "SELECT * FROM profils_animaux WHERE id = $animal_id AND proprietaire_id = $owner_id";
$result = mysqli_query($conn, $sql);
$animal = mysqli_fetch_assoc($result);

if (!$animal) {
    echo json_encode(['error' => 'Animal non trouvé']);
    exit;
}

// Formater la date
$animal['datee_formatee'] = date('d/m/Y', strtotime($animal['datee']));

echo json_encode($animal);