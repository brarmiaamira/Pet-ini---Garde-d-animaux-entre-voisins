<?php
session_start();
include '../config/db.php';

//$owner_id = 2;
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID manquant");
}

$animal_id = (int) $_GET['id'];

/* propriétaire */
$check = mysqli_query($conn, "
    SELECT id FROM profils_animaux 
    WHERE id = $animal_id 
    AND proprietaire_id = $owner_id
");

if (mysqli_num_rows($check) == 0) {
    die("Non autorisé");
}

/* réservation */
$res = mysqli_query($conn, "
    SELECT id FROM reservations 
    WHERE animal_id = $animal_id
    LIMIT 1
");

if (mysqli_num_rows($res) > 0) {
    header("Location: ../../Frontend/pages/petProfil.php?error=reservation");
    exit();
}

/* suppression */
mysqli_query($conn, "
    DELETE FROM profils_animaux 
    WHERE id = $animal_id
");

/* retour */
header("Location: ../../Frontend/pages/petProfil.php?success=deleted");
exit();
?>