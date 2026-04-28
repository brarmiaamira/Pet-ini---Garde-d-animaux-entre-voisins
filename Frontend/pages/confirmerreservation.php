<?php
session_start();
include '../../Backend/config/db.php';

//1. Vérifier si connecté
//if (!isset($_SESSION['user_id'])) {
  //  header("Location: login.php");
   // exit();
//}
if (isset($_POST['confirmer'])) {

    $update = "UPDATE reservations 
               SET statut = 'confirmée' 
               WHERE id = $reservation_id 
               AND proprietaire_id = $user_id";

    mysqli_query($conn, $update);

    // Redirection après confirmation
    header("Location: accueil.php");
    exit();
}
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
    <link rel="icon" type="image/png" href="logo.png">
    <link
      href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800;900&family=Caveat:wght@600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylereservation.css">
    <link rel="stylesheet" href="recherche.css">
    <title>Confirmation Réservation</title>
</head>
<body>
<nav class="navbar">
      <div class="nav-left">
        <a href="index.html" class="nav-logo">
          <img src="logo.png" alt="Pet'ini logo" class="logo-img" />
        </a>
      </div>

      <div class="nav-links">
        <a href="accueil.html" class="nav-pill">Accueil</a>
        <a href="petProfil.php">Profil animal</a>
        <a href="recherche.html" class="nav-pill">Recherche</a>
        <a href="carte.html" class="nav-pill">Carte</a>
        <a href="reservation.php" class="nav-pill">Reservation</a>
        <a href="messages.php" class="nav-pill">Message</a>
        <a href="avis.html" class="nav-pill">Avis</a>
      </div>

      <div class="nav-right">
        <button class="hamburger" onclick="toggleMenu()">☰</button>
      </div>
      <div id="menu">
        <ul>
          <li><a href="acceil.html">Acceuil</a></li>
          <li><a href="petProfil.php">Profil animal</a></li>
          <li><a href="recherche.html">Recherche</a></li>
          <li><a href="carte.html">Carte</a></li>
          <li><a href="reservation.php">Reservation</a></li>
          <li><a href="messages.php">Message</a></li>
          <li><a href="avis.html">Avis</a></li>
          <li><a href="index.html">Se déconnecter</a></li>
        </ul>
      </div>
      
    </nav>
    <div class="card-reservation">    
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
        <form method="POST">
            <button class="but" type="submit" name="confirmer">Confirmer</button>
        </form>  
    </div>
    <script>
    function confirmer() {
        window.location.href = "accueil.php";
    }
    </script>
</body>
</html>