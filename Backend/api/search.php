<?php
require_once "db.php";

header("Content-Type: application/json; charset=UTF-8");

$ville = trim($_GET['ville'] ?? '');
$animal = trim($_GET['animal'] ?? '');
$prix = $_GET['prix'] ?? '';
$date = $_GET['date'] ?? '';

$sql = "SELECT  p.*
        FROM petsitters p
        LEFT JOIN petsitter_disponibilites d ON p.id = d.petsitter_id
        WHERE 1=1";

$params = [];

// filtre ville
if (!empty($ville)) {
    $sql .= " AND p.ville LIKE ?";
    $params[] = "%" . $ville . "%";
}

// filtre animal
if (!empty($animal)) {
    $sql .= " AND p.type_animal = ?";
    $params[] = $animal;
}

// filtre prix
if (!empty($prix) && is_numeric($prix)) {
    $sql .= " AND p.tarif_par_jour <= ?";
    $params[] = $prix;
}

// filtre date
if (!empty($date)) {
    $sql .= " AND ? BETWEEN d.date_debut AND d.date_fin";
    $params[] = $date;
}

$sql .= " ORDER BY p.note DESC, p.tarif_par_jour ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$petsitters = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($petsitters, JSON_UNESCAPED_UNICODE);