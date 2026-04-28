function toggleMenu() {
  const menu = document.getElementById("menu");
  menu.classList.toggle("active");
}
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("search-form");
  const results = document.getElementById("results");

  async function loadPetsitters(query = "") {
    try {
      const url = `/petini/backend/api/search.php${query}`;
      const response = await fetch(url);
      const data = await response.json();

      results.innerHTML = "";

      if (data.length === 0) {
        results.innerHTML = "<p>Aucun petsitter trouvé.</p>";
        return;
      }

      data.forEach(function (petsitter) {
        const card = document.createElement("div");
        card.className = "petsitter-card";

        card.innerHTML = `
          <img src="${petsitter.photo || "default-petsitter.jpg"}" alt="${petsitter.nom}">
          <div class="petsitter-info">
            <h2>${petsitter.nom}</h2>
            <p>📍 ${petsitter.ville}</p>
            <p>🐾 ${petsitter.type_animal}</p>
            <p>⭐ ${petsitter.note}</p>
            <p class="price">${petsitter.tarif_par_jour} DT / jour</p>
            <a href="profil-petsitter.html" class="btn-profile">Voir le profil</a>
          </div>
        `;

        results.appendChild(card);
      });

    } catch (error) {
      console.error(error);
      results.innerHTML = "<p>Erreur lors du chargement des petsitters.</p>";
    }
  }
  

  // Load all petsitters at start
  loadPetsitters();

  // Only when button is clicked
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const ville = document.getElementById("ville").value.trim();
    const animal = document.getElementById("animal").value;
    const prix = document.getElementById("prix").value.trim();
    const date = document.getElementById("date").value;

    const params = new URLSearchParams();

    if (ville) params.append("ville", ville);
    if (animal) params.append("animal", animal);
    if (prix) params.append("prix", prix);
    if (date) params.append("date", date);

    const query = params.toString() ? `?${params.toString()}` : "";

    loadPetsitters(query);
  });
});