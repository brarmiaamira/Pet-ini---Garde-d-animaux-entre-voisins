
-- TABLE UTILISATEURS
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    mail VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('owner', 'sitter') NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);


-- TABLE SITTERS
-- ville = pour la recherche (obligatoire)

CREATE TABLE sitters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    tarif DECIMAL(10,2) NOT NULL,
    description TEXT,
    categories_acceptees VARCHAR(255),
    disponibilite BOOLEAN DEFAULT 1,
    ville VARCHAR(100) NOT NULL,
    animal BOOLEAN NOT NULL ,
    vehicule BOOLEAN NOT NULL ,
    locale ENUM('appartement ', 'maison '),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- TABLE LOCALISATIONS
-- optionnel - seulement pour la carte GPS

CREATE TABLE localisations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sitter_id INT NOT NULL,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    adresse VARCHAR(255),
    FOREIGN KEY (sitter_id) REFERENCES sitters(id) ON DELETE CASCADE
);

-- TABLE PROFILS ANIMAUX

CREATE TABLE profils_animaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proprietaire_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    espece VARCHAR(100) NOT NULL,
    race VARCHAR(100),
    datee date ,
    sexe ENUM('male','female') NOT NULL ,
    poids INT ,
    photo VARCHAR(255) DEFAULT 'images/default_animal.png',
    description TEXT,
    besoins_speciaux TEXT,
    FOREIGN KEY (proprietaire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);


-- TABLE RESERVATIONS

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proprietaire_id INT NOT NULL,
    sitter_id INT NOT NULL,
    animal_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('en_attente', 'acceptee', 'refusee', 'annulee', 'terminee') DEFAULT 'en_attente',
    lieu_garde VARCHAR(255),
    prix_total DECIMAL(10,2),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proprietaire_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (sitter_id) REFERENCES sitters(id),
    FOREIGN KEY (animal_id) REFERENCES profils_animaux(id)
);

-- TABLE MESSAGES

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    contenu TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT 0,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id)
);

-- TABLE AVIS

CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL UNIQUE,
    auteur_id INT NOT NULL,
    cible_id INT NOT NULL,
    note INT CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id),
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (cible_id) REFERENCES utilisateurs(id)
);

-- DONNEES DE TEST

INSERT INTO utilisateurs (nom, prenom, mail, mot_de_passe, role)
VALUES ('Ben Ali', 'Ahmed', 'ahmed@test.com', '123456', 'sitter');

INSERT INTO utilisateurs (nom, prenom, mail, mot_de_passe, role)
VALUES ('Trabelsi', 'Sara', 'sara@test.com', '123456', 'owner');

INSERT INTO sitters (utilisateur_id, tarif, description, categories_acceptees, disponibilite, ville, animal, vehicule, locale)
VALUES (1, 50.00, 'Je garde vos animaux avec amour !', 'chien, chat', 1, 'Tunis', 1, 1, 'maison');

INSERT INTO localisations (sitter_id, latitude, longitude, adresse)
VALUES (1, 36.8190, 10.1658, 'Rue de la Liberté');

INSERT INTO profils_animaux (proprietaire_id, nom, espece, race, datee, sexe, poids)
VALUES (2, 'Rex', 'Chien', 'Labrador', '2021-03-15', 'male', 30);