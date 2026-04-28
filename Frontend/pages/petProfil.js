// Frontend/pages/petProfil.js

function modifierProfil(animal_id) {
    window.location.href = 'modifier_animal.php?id=' + animal_id;
}

function chargerProfil(animal_id) {
    // Marquer la carte active
    document.querySelectorAll('.animal-card').forEach(card => {
        card.classList.remove('active');
        if (parseInt(card.dataset.id) === animal_id) {
            card.classList.add('active');
        }
    });

    // Appel API vers Backend/api/get_animal.php
    fetch('../../Backend/api/get_animal.php?id=' + animal_id)
        .then(res => res.json())
        .then(a => {
            if (a.error) {
                console.error('Erreur:', a.error);
                return;
            }

            // Photo
            const photo = document.getElementById('detail-photo');
            photo.src = a.photo && a.photo !== '' ? a.photo : 'images/photo de profil par default.png';
            photo.alt = 'photo de ' + a.nom;
            photo.onerror = function() {
                this.src = 'images/photo de profil par default.png';
                this.onerror = null; // évite boucle infinie
            };

            // Infos texte
            document.getElementById('detail-nom').textContent      = a.nom        || '';
            document.getElementById('detail-espece').textContent   = a.espece     || '';
            document.getElementById('detail-race').textContent     = a.race       || '';
            document.getElementById('detail-sexe').textContent     = a.sexe       || '';
            document.getElementById('detail-poids').textContent    = a.poids      || '';
            document.getElementById('detail-date').textContent     = a.datee_formatee || '';

            // Paragraphes description et besoins
            document.getElementById('detail-description').innerHTML =
                a.description ? a.description.replace(/\n/g, '<br>') : 'Aucune description.';
            document.getElementById('detail-besoins').innerHTML =
                a.besoins_speciaux ? a.besoins_speciaux.replace(/\n/g, '<br>') : 'Aucun besoin particulier.';

            // Bouton modifier
            document.querySelector('.Left button').setAttribute('onclick', 'modifierProfil(' + a.id + ')');

            // Scroll vers le haut sur mobile
            if (window.innerWidth <= 768) {
                document.getElementById('animal-detail').scrollIntoView({ behavior: 'smooth' });
            }
        })
        .catch(err => console.error('Erreur chargement profil:', err));
}