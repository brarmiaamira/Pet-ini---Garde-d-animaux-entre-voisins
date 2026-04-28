<?php
session_start();
include '../../Backend/config/db.php';

// Temporaire — simule owner connecté (Sara id=2)
$owner_id = 2;

// Récupérer l'animal sélectionné (par défaut le premier)
$animal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si pas d'id → prendre le premier animal du owner
if ($animal_id == 0) {
    $sql = "SELECT id FROM profils_animaux 
            WHERE proprietaire_id = $owner_id 
            LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $animal_id = $row['id'];
}

// Récupérer les détails de l'animal sélectionné
$sql = "SELECT * FROM profils_animaux 
        WHERE id = $animal_id 
        AND proprietaire_id = $owner_id";
$result = mysqli_query($conn, $sql);
$animal = mysqli_fetch_assoc($result);

// Récupérer tous les animaux du owner
$sql_tous = "SELECT * FROM profils_animaux 
             WHERE proprietaire_id = $owner_id";
$result_tous = mysqli_query($conn, $sql_tous);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="petProfil.css">
    <link rel="stylesheet" href="style.css">
    <title>Profil Animal</title>
</head>
<body>
    <?php include '../components/nav.php'; ?>
    <div class="page-content">
        <div class="Left">
            <img src="<?= $animal['photo'] ?>" 
                alt="photo de <?= $animal['nom'] ?>"
                onerror="this.src=''">

            <h1><strong><?= $animal['nom'] ?></strong></h1>
            <p><strong>Espèce : <?= $animal['espece'] ?></strong></p>
            <p><strong>Race : <?= $animal['race'] ?></strong></p>
            <p><strong>Sexe : <?= $animal['sexe'] ?></strong></p>
            <p><strong>Poids : <?= $animal['poids'] ?> kg</strong></p>
            <p><strong>Date de naissance : 
                <?= date('d/m/Y', strtotime($animal['datee'])) ?>
            </strong></p>

            <label for="description">Description</label>
            <textarea name="Description" id="description" readonly>
                <?= $animal['description'] ?>
            </textarea>

            <label for="besoins">Besoins ou Allergies</label>
            <textarea name="Besoins" id="besoins" readonly>
                <?= $animal['besoins_speciaux'] ?>
            </textarea>

            <button onclick="modifierProfil(<?= $animal_id ?>)">
                Modifier profil
            </button>
        </div>

        <div class="Right">
            <h1><strong>Mes animaux</strong></h1>

            <?php while ($a = mysqli_fetch_assoc($result_tous)): ?>
            <div class="animal-card <?= $a['id'] == $animal_id ? 'active' : '' ?>"
                onclick="window.location.href='profil_animal.php?id=<?= $a['id'] ?>'">

                <div class="photo">
                    <img src="<?= $a['photo'] ?>" 
                        alt="<?= $a['nom'] ?>"
                        onerror="this.src=''">
                </div>

                <div class="info">
                    <p><strong>Nom : <?= $a['nom'] ?></strong></p>
                    <p><strong>Espèce : <?= $a['espece'] ?></strong></p>
                    <p><strong>Poids : <?= $a['poids'] ?> kg</strong></p>
                    <p><strong>Date de naissance : 
                        <?= date('d/m/Y', strtotime($a['datee'])) ?>
                    </strong></p>
                </div>
            </div>
            <?php endwhile; ?>

            <button onclick="window.location.href='ajouter_animal.php'">
                + Ajouter un animal
            </button>
        </div>
    </div>
    

    <script src="petProfil.js"></script>
</body>
</html>