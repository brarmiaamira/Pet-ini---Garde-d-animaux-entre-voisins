document.addEventListener("DOMContentLoaded", function () {
  const map = L.map("map").setView([36.8065, 10.1815], 7);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors"
  }).addTo(map);

  async function loadPetsittersOnMap() {
    try {
      const response = await fetch("/petini/backend/api/map.php");
      const petsitters = await response.json();

      petsitters.forEach(function (petsitter) {
        const lat = parseFloat(petsitter.latitude);
        const lng = parseFloat(petsitter.longitude);

        if (!lat || !lng) return;

        const popupContent = `
          <div class="map-popup">
            <strong>${petsitter.nom}</strong><br>
            📍 ${petsitter.ville}<br>
            🐾 ${petsitter.type_animal}<br>
            💰 ${petsitter.tarif_par_jour} DT / jour<br>
            ⭐ ${petsitter.note}
          </div>
        `;

        L.marker([lat, lng])
          .addTo(map)
          .bindPopup(popupContent);
      });
    } catch (error) {
      console.error("Erreur carte:", error);
    }
  }

  loadPetsittersOnMap();
});