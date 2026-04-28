document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    const animalSelect = document.getElementById("animaux");
    const dateDebut = document.getElementById("date_debut");
    const dateFin = document.getElementById("date_fin");

    // ✅ Date minimum = aujourd'hui
    const today = new Date().toISOString().split("T")[0];
    dateDebut.setAttribute("min", today);
    dateFin.setAttribute("min", today);

    // ✅ Date fin doit être après date début
    dateDebut.addEventListener("change", function () {
        dateFin.setAttribute("min", dateDebut.value);
        if (dateFin.value && dateFin.value < dateDebut.value) {
            dateFin.value = "";
        }
    });

    // ✅ Validation avant envoi
    form.addEventListener("submit", function (e) {

        let valid = true;
        let message = "";

        // Vérifier animal
        if (animalSelect.value === "") {
            valid = false;
            message += "⚠️ Veuillez choisir un animal.\n";
            animalSelect.style.border = "2px solid red";
        } else {
            animalSelect.style.border = "2px solid green";
        }

        // Vérifier date début
        if (dateDebut.value === "") {
            valid = false;
            message += "⚠️ Veuillez choisir une date de début.\n";
            dateDebut.style.border = "2px solid red";
        } else {
            dateDebut.style.border = "2px solid green";
        }

        // Vérifier date fin
        if (dateFin.value === "") {
            valid = false;
            message += "⚠️ Veuillez choisir une date de fin.\n";
            dateFin.style.border = "2px solid red";
        } else {
            dateFin.style.border = "2px solid green";
        }

        // Vérifier date fin > date début
        if (dateDebut.value && dateFin.value && dateFin.value < dateDebut.value) {
            valid = false;
            message += "⚠️ La date de fin doit être après la date de début.\n";
            dateFin.style.border = "2px solid red";
        }

        if (!valid) {
            e.preventDefault();
            alert(message);
        }
    });
});