<?php
require_once "db.php";

header("Content-Type: application/json; charset=UTF-8");

$sql = "SELECT id, nom, ville, tarif_par_jour, photo, note, type_animal, latitude, longitude
        FROM petsitters
        WHERE latitude IS NOT NULL
        AND longitude IS NOT NULL";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$petsitters = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($petsitters, JSON_UNESCAPED_UNICODE);