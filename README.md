🐾 Pet'ini — Garde d'animaux entre voisins
Plateforme web mettant en relation les propriétaires d'animaux avec des pet sitters de confiance.


Description
Pet'ini permet aux propriétaires de trouver facilement un gardien pour leur animal lors de leurs absences, et aux pet sitters de proposer leurs services de garde.

Fonctionnalités
 * Recherche de pet sitters par ville
 * Carte interactive des sitters disponibles 
 * Réservation en ligne avec calcul automatique du prix
 * Messagerie entre propriétaires et sitters
 * Avis après chaque garde
 * Profil animal pour chaque animal

Technologies utilisées
Technologie         Rôle     
HTML           Structure des pages
CSS            Style et mise en page
JavaScript     Interactions et validation
               Carte GPS interactive
PHP            Backend et logique métier
MySQL          Base de données
XAMPP          Serveur local (Apache + MySQL)

📁 Structure du projet

C:.
¦   logo1.png
¦   pawsleft.png
¦   pawsright.png
¦   pet'ini (1).pdf
¦   README.md
¦   structure.txt
¦   
+---Backend
¦   +---api
¦   ¦       db.php
¦   ¦       login.php
¦   ¦       logout.php
¦   ¦       map.php
¦   ¦       search.php
¦   ¦       signup.php
¦   ¦       
¦   +---config
¦   ¦       db.php
¦   ¦       
¦   +---controllers
¦   +---models
¦   +---routes
¦           reservation.php
¦           
+---Diagrams
¦       book a reservation.drawio
¦       Diagramme_de_sequence_Pet'ini.drawio
¦       pet'ini activite diag.drawio
¦       pet'ini general.drawio
¦       
+---Frontend
    +---components
    ¦       nav.php
    ¦       
    +---pages
    ¦   ¦   confirmerreservation.php
    ¦   ¦   index.html
    ¦   ¦   login.html
    ¦   ¦   petProfil.html
    ¦   ¦   reservation.js
    ¦   ¦   reservation.php
    ¦   ¦   signup.html
    ¦   ¦   style.css
    ¦   ¦   stylereservation.css
    ¦   ¦   
    ¦   +---css
    ¦   +---services
    ¦       +---images
    ¦               background.png
    ¦               contact.jpg
    ¦               dog_left_bottom.png
    ¦               dog_right.png
    ¦               dog_top_left.png
    ¦               logo.png
    ¦               mission.jpg
    ¦               
    +---src
            login.css
            login.html
            main.js
            recherche.css
            recherche.html
            search.js
            signup.css
            signup.html
            style.css
            

