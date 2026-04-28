<?php
session_start();
include '../../Backend/config/db.php';
// Temporaire — simule un owner connecté (Sara id=2)
$owner_id = 2; 

// 1. Vérifier si connecté
//if (!isset($_SESSION['user_id'])) {
 //   header("Location: login.php");
 //   exit();
//}
$erreur = "";
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
            echo "Reservation ID : " . $reservation_id . "<br>";
            echo "Redirection vers : confirmerreservation.php?id=" . $reservation_id . "<br>";
            echo "<a href='confirmerreservation.php?id=" . $reservation_id . "'>Clique ici</a>";
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
  <link rel="stylesheet" href="stylereservation.css">
  <link rel="stylesheet" href="style.css">
  <title>Confirmation Réservation</title>

</head>
<body>
    <?php include '../components/nav.php'; ?>
    <h1>Reservation</h1>
    <h3>reserverz-votre petsitter </h3>
    <p>Details de reservation</p>
    <div>
        <p id="nomp"></p>
        <p id="localisationp"></p>
        <span id="avisp"></span>
        <p id="prixp"></p>
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
        <input type="submit" value="reserver">
    </form>
    <script src="reservation.js "></script>
</body>
</html>