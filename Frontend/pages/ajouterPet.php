<?php
session_start();
include '../../Backend/config/db.php';

$owner_id = 2;
//if (!isset($_SESSION['user_id'])) {
//    header("Location: login.php");
//    exit();
//}
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = trim($_POST['nom'] ?? '');
    $espece = trim($_POST['espece'] ?? '');
    $race = trim($_POST['race'] ?? '');
    $sexe = $_POST['sexe'] ?? '';
    $poids = floatval($_POST['poids'] ?? 0);
    $datee = $_POST['datee'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $besoins = trim($_POST['besoins'] ?? '');

    // 🚨 1. champs obligatoires
    if (
        $nom == "" || $espece == "" || $race == "" ||
        $sexe == "" || $poids == 0 || $datee == "" ||
        $description == "" || $besoins == ""
    ) {
        $error = "❌ Tous les champs sont obligatoires";
    }

    // 📅 2. date check
    $limit = strtotime('-5 months');
    $userDate = strtotime($datee);

    if (empty($error) && $userDate > $limit) {
        $error = "❌ L’animal doit avoir au moins 5 mois";
    }

    // ⚖️ 3. poids
    if (empty($error) && $poids <= 0) {
        $error = "❌ Le poids doit être supérieur à 0";
    }

    // 🚫 4. doublon UNIQUEMENT si champs OK
    if (empty($error)) {

        $check = mysqli_query($conn,
            "SELECT id FROM profils_animaux 
             WHERE nom='$nom' 
             AND espece='$espece' 
             AND race='$race' 
             AND proprietaire_id=$owner_id"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = "❌ Cet animal existe déjà dans votre compte";
        }
    }

    // ✅ 5. INSERT
    if (empty($error)) {

        $photo = "images/photo de profil par default.png";

        if (!empty($_FILES['photo']['name'])) {
            $target = "images/" . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $target);
            $photo = $target;
        }

        mysqli_query($conn, "
            INSERT INTO profils_animaux 
            (nom, espece, race, sexe, poids, datee, description, besoins_speciaux, photo, proprietaire_id)
            VALUES 
            ('$nom','$espece','$race','$sexe','$poids','$datee','$description','$besoins','$photo','$owner_id')
        ");

        header("Location: petProfil.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un animal</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link
      href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800;900&family=Caveat:wght@600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="recherche.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ajouterPet.css">
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
<div class="page-container">

    <div class="form-container">

        <h1>Ajouter un animal 🐾</h1>
        <?php if (!empty($error)) : ?>
        <p style="color:red; text-align:center; font-weight:bold;">
            <?= $error ?>
        </p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">

            <label>Nom</label>
            <input type="text" name="nom" required>

            <label>Espèce</label>
            <input type="text" name="espece" required>

            <label>Race</label>
            <input type="text" name="race" required>

            <label>Sexe</label>
            <select name="sexe" required>
                <option value="M">Mâle</option>
                <option value="F">Femelle</option>
            </select>

            <label>Poids (kg)</label>
            <input type="number" step="0.1" name="poids" required>

            <label>Date de naissance</label>
            <input type="date" name="datee" required>

            <label>Description</label>
            <textarea name="description" required></textarea>

            <label>Besoins spéciaux</label>
            <textarea name="besoins" required></textarea>

            <label>Photo</label>
            <input type="file" name="photo" id="photoInput" required>

            <img id="preview" src="images/photo de profil par default.png">

            <button class="but" type="submit">Ajouter</button>

        </form>

    </div>

</div>

<script src="ajouterPet.js"></script>

</body>
</html>