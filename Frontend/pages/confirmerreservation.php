<?php
session_start();
include '../../Backend/config/db.php';

// 1. Vérifier si connecté
//if (!isset($_SESSION['user_id'])) {
 //   header("Location: login.php");
    //exit();
//}

// 2. Récupérer l'id depuis l'URL
////////////
// Temporaire — simule owner connecté
$user_id = 2;  // ← ajoute cette ligne
/////////
$reservation_id = intval($_GET['id']);

// 3. Récupérer les détails depuis la BD

$sql = "SELECT r.date_debut, r.date_fin, r.prix_total,
               u.nom, u.prenom,
               a.nom AS animal_nom,
               s.tarif
        FROM reservations r
        JOIN sitters s ON r.sitter_id = s.id
        JOIN utilisateurs u ON s.utilisateur_id = u.id
        JOIN profils_animaux a ON r.animal_id = a.id
        WHERE r.id = $reservation_id
        AND r.proprietaire_id = $user_id";//  AND r.proprietaire_id = {$_SESSION['user_id']}";
$result = mysqli_query($conn, $sql);
$reservation = mysqli_fetch_assoc($result);

// 4. Calculer durée
$debut = new DateTime($reservation['date_debut']);
$fin   = new DateTime($reservation['date_fin']);
$jours = $fin->diff($debut)->days + 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylereservation.css">
    <title>Confirmation Réservation</title>
</head>
<body>
    <?php include '../components/nav.php'; ?>
    <div>
        <h1>Réservation</h1>
        <p>Gardien :
            <span><?= $reservation['prenom'] ?> <?= $reservation['nom'] ?></span>
        </p>
        <p>Animal :
            <span><?= $reservation['animal_nom'] ?></span>
        </p>
        <p>Dates :
            <span>
                <?= date('d/m/Y', strtotime($reservation['date_debut'])) ?>
                →
                <?= date('d/m/Y', strtotime($reservation['date_fin'])) ?>
            </span>
        </p>
        <p>Durée :
            <span><?= $jours ?> jour(s)</span>
        </p>
        <p>Tarif :
            <span><?= $reservation['prix_total'] ?> TND</span>
        </p>
        <button onclick="confirmer()">Confirmer</button>
    </div>
    <script>
    function confirmer() {
        window.location.href = "accueil.php";
    }
    </script>
</body>
</html>