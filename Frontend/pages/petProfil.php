<?php
session_start();
include '../../Backend/config/db.php';

//$owner_id = 2;
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="petProfil.css">
    <title>Profil Animal</title>
</head>
<body>
    <?php include '../components/nav.php'; ?>
    <div class="page-content">
        <!-- PARTIE GAUCHE -->
        <div class="Left" id="animal-detail">
            <img id="detail-photo"
                 src="<?= htmlspecialchars($animal['photo'] ?? '') ?>"
                 alt="photo de <?= htmlspecialchars($animal['nom']) ?>"
                 onerror="this.src='images/photo de profil par default.png'">

            <h1 id="detail-nom"><?= htmlspecialchars($animal['nom']) ?></h1>
            <p><strong>Espèce :</strong> <span id="detail-espece"><?= htmlspecialchars($animal['espece']) ?></span></p>
            <p><strong>Race :</strong> <span id="detail-race"><?= htmlspecialchars($animal['race']) ?></span></p>
            <p><strong>Sexe :</strong> <span id="detail-sexe"><?= htmlspecialchars($animal['sexe']) ?></span></p>
            <p><strong>Poids :</strong> <span id="detail-poids"><?= htmlspecialchars($animal['poids']) ?></span> kg</p>
            <p><strong>Date de naissance :</strong> <span id="detail-date"><?= date('d/m/Y', strtotime($animal['datee'])) ?></span></p>
            <div class="section-bloc">
                <h3>Description</h3>
                <p id="detail-description" class="texte-bloc">
                    <?= nl2br(htmlspecialchars($animal['description'] ?? 'Aucune description.')) ?>
                </p>
            </div>

            <div class="section-bloc">
                <h3>Besoins &amp; Allergies</h3>
                <p id="detail-besoins" class="texte-bloc">
                    <?= nl2br(htmlspecialchars($animal['besoins_speciaux'] ?? 'Aucun besoin particulier.')) ?>
                </p>
            </div>
            <button 
                onclick="if(confirm('Voulez-vous vraiment supprimer cet animal ?')) 
                window.location.href='../../Backend/api/supprimer-animal.php?id=<?= $animal_id ?>'">
                Supprimer
            </button>
            <button onclick="modifierProfil(<?= $animal_id ?>)">Modifier profil</button>
        </div>

        <!-- PARTIE DROITE -->
        <div class="Right">
            <h1>Mes animaux</h1>

            <?php while ($a = mysqli_fetch_assoc($result_tous)): ?>
            <div class="animal-card <?= $a['id'] == $animal_id ? 'active' : '' ?>"
                 data-id="<?= $a['id'] ?>"
                 onclick="chargerProfil(<?= $a['id'] ?>)">

                <div class="photo">
                    <img src="<?= htmlspecialchars($a['photo'] ?? '') ?>"
                         alt="<?= htmlspecialchars($a['nom']) ?>"
                         onerror="this.src='images/photo de profil par default.png'">
                </div>
                <div class="info">
                    <p><strong><?= htmlspecialchars($a['nom']) ?></strong></p>
                    <p><?= htmlspecialchars($a['espece']) ?></p>
                    <p><?= htmlspecialchars($a['poids']) ?> kg</p>
                    <p><?= date('d/m/Y', strtotime($a['datee'])) ?></p>
                </div>
            </div>
            <?php endwhile; ?>
             
            <button onclick="window.location.href='ajouterPet.php'">+ Ajouter un animal</button>
        </div>

    </div>

    <script src="petProfil.js"></script>
</body>
</html>