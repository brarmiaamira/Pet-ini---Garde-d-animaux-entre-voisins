<?php
session_start();
include '../../Backend/config/db.php';
// Temporaire — simule un owner connecté (Sara id=2)
$owner_id = 2; 

// 1. Vérifier si connecté
//if (!isset($_SESSION['user_id'])) {
  //  header("Location: login.php");
  // exit();
//}
$erreur = "";

////////recuperation info de sitter //////

$sql_sitter = "SELECT 
    u.nom,
    u.prenom,
    u.photo,
    s.tarif,
    s.ville,
    COALESCE(AVG(a.note), 0) AS avis_moyen
FROM sitters s
JOIN utilisateurs u ON s.utilisateur_id = u.id
LEFT JOIN avis a ON a.cible_id = u.id
WHERE u.id = 3
GROUP BY u.id, u.nom, u.prenom, u.photo, s.tarif, s.ville";
////$sitter_id
$result_sitter = mysqli_query($conn, $sql_sitter);
$sitter = mysqli_fetch_assoc($result_sitter);

if (!$sitter) {
    die("Sitter non trouvé.");
}

// 2. Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données
    $animal_id  = intval($_POST['animal']);
    $date_debut = htmlspecialchars($_POST['date_debut']);
    $date_fin   = htmlspecialchars($_POST['date_fin']);
    $message    = htmlspecialchars($_POST['message']);
    //changement temporaire puis je le reecrire 
/////////
    //$sitter_id  = intval($_POST['id']);
    //$owner_id   = $_SESSION['owner_id'];
    $owner_id = 2;
////////////////
     // Temporaire pour tester
    // Vérifier les champs
    if (empty($animal_id) || empty($date_debut) || empty($date_fin)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($date_fin < $date_debut) {
        $erreur = "La date de fin doit être après la date de début.";
    } else {
        // Calculer prix
        $sql = "SELECT tarif FROM sitters WHERE id = 2";
        $result = mysqli_query($conn, $sql);
        $sitter = mysqli_fetch_assoc($result);
        $tarif = $sitter['tarif'];

        $debut = new DateTime($date_debut);
        $fin   = new DateTime($date_fin);
        $jours = $fin->diff($debut)->days + 1;
        $prix_total = $tarif * $jours;

        // Enregistrer
        $sql = "INSERT INTO reservations 
                (proprietaire_id, sitter_id, animal_id, date_debut, date_fin, statut, prix_total)
                VALUES 
                ($owner_id, 2, $animal_id, '$date_debut', '$date_fin', 'en_attente', $prix_total)";

        $result = mysqli_query($conn, $sql);

        //if ($result) {
         //   $reservation_id = mysqli_insert_id($conn);
           // header("Location: confirmerreservation.php?id=$reservation_id");
            //exit();
        //} else {
          //  $erreur = "Erreur lors de la réservation.";
        //}
        if ($result) {
            $reservation_id = mysqli_insert_id($conn);
            header("Location: confirmerreservation.php?id=" . $reservation_id);
            exit();
        }
    }
}
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
  <link rel="stylesheet" href="stylereservation.css">
  <link rel="stylesheet" href="style.css">
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
    <h1>Reservation</h1>
    <h3>reserverz-votre petsitter </h3>
    <p>Details de reservation</p>
    <div class="sitter-card">
        <?php 
        $sql = "SELECT 
            u.nom,
            u.prenom,
            s.tarif,
            s.ville,
            u.photo,
            u.id AS sitter_id,
            AVG(a.note) AS avis_moyen
        FROM sitters s
        JOIN utilisateurs u ON s.utilisateur_id = u.id
        LEFT JOIN avis a ON a.cible_id = u.id
        WHERE s.disponibilite = 1
        GROUP BY u.id, u.nom, u.prenom, s.tarif, s.ville";   
        ?>
        <img src="<?= $sitter['photo'] ?? 'images/photo de profil par default.png' ?>" alt="Photo sitter">
        <p><strong>Nom :</strong> <?= $sitter['prenom'] ?> <?= $sitter['nom'] ?></p>
        <p><strong>Localisation :</strong> <?= $sitter['ville'] ?></p>
        <p><strong>Avis :</strong> ⭐ <?= round($sitter['avis_moyen'],1) ?>/5</p>
        <p><strong>Prix :</strong> <?= $sitter['tarif'] ?> TND / jour</p>          
    </div>

    <form action="reservation.php" method="post">
        <label for="animaux">Votre animal</label>
        <select name="animal" id="animaux">
            <option value="">-- Choisir un animal --</option>
            <?php
            // ✅ utilise $owner_id pas $_SESSION
            $sql = "SELECT id, nom, espece FROM profils_animaux 
                    WHERE proprietaire_id = $owner_id";
            $result = mysqli_query($conn, $sql);
            while ($animal = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $animal['id'] . "'>"
                   . $animal['nom'] . " (" . $animal['espece'] . ")"
                   . "</option>";
            }
            ?>
        </select>
        <label for="date_debut">Date de début</label>
        <input type="date" name="date_debut" id="date_debut">
        <label for="date_fin">Date de fin</label>
        <input type="date" name="date_fin" id="date_fin">
        <label for="messagepo">Message au gardien (optionnel)</label>
        <textarea name="message" id="messagepo"></textarea>
        <button class="but" type="submit" value="reserver">Reserver</button>
    </form>
    <script src="reservation.js "></script>
</body>
</html>