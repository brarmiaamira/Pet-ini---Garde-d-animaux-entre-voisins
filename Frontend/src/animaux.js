let animaux   = JSON.parse(localStorage.getItem('petini_animaux') || '[]');
    let editIdx   = -1;
    let deleteIdx = -1;
    let emoji     = '🐶';

    /* ── RENDER ── */
    function render() {
      const grid  = document.getElementById('animaux-grid');
      const empty = document.getElementById('animaux-empty');
      const count = document.getElementById('animal-count');
      grid.innerHTML = '';

      if (animaux.length === 0) {
        empty.style.display = 'flex';
        grid.style.display  = 'none';
        count.textContent   = 'Aucun animal ajouté';
        return;
      }

      empty.style.display = 'none';
      grid.style.display  = 'grid';
      count.textContent   = animaux.length + ' compagnon' + (animaux.length > 1 ? 's' : '');

      animaux.forEach((a, i) => {
        const card = document.createElement('div');
        card.className = 'animal-card';
        card.innerHTML = `
          <div class="animal-card-top">
            <div class="animal-emoji-wrap">${a.emoji}</div>
            <div>
              <div class="animal-card-name">${a.nom}</div>
              <div class="animal-card-species">${a.espece || ''}${a.race ? ' · ' + a.race : ''}</div>
            </div>
          </div>
          <div class="animal-card-body">
            <div class="animal-tags">
              ${a.age   ? `<span class="animal-tag">🎂 ${a.age} ans</span>`  : ''}
              ${a.poids ? `<span class="animal-tag">⚖️ ${a.poids} kg</span>` : ''}
            </div>
            ${a.besoins ? `<p class="animal-besoins">📋 ${a.besoins}</p>` : ''}
          </div>
          <div class="animal-card-actions">
            <button class="btn-edit" onclick="openEdit(${i})">✏️ Modifier</button>
            <button class="btn-delete" onclick="openDelete(${i})">🗑️</button>
          </div>
        `;
        grid.appendChild(card);
      });
    }

    /* ── OPEN MODAL ── */
    function openModal(reset = true) {
      if (reset) {
        editIdx = -1;
        document.getElementById('modal-title').textContent = 'Ajouter un animal';
        document.getElementById('modal-sub').textContent   = 'Renseignez les informations de votre compagnon';
        document.getElementById('f-nom').value     = '';
        document.getElementById('f-espece').value  = '';
        document.getElementById('f-race').value    = '';
        document.getElementById('f-age').value     = '';
        document.getElementById('f-poids').value   = '';
        document.getElementById('f-besoins').value = '';
        emoji = '🐶';
        document.querySelectorAll('.emoji-btn').forEach((b,i) => b.classList.toggle('selected', i===0));
      }
      document.getElementById('modal').classList.add('active');
    }

    function closeModal() { document.getElementById('modal').classList.remove('active'); }

    /* ── OPEN EDIT ── */
    function openEdit(i) {
      editIdx = i;
      const a = animaux[i];
      document.getElementById('modal-title').textContent = '✏️ Modifier ' + a.nom;
      document.getElementById('modal-sub').textContent   = 'Mettez à jour les informations de votre compagnon';
      document.getElementById('f-nom').value     = a.nom;
      document.getElementById('f-espece').value  = a.espece || '';
      document.getElementById('f-race').value    = a.race   || '';
      document.getElementById('f-age').value     = a.age    || '';
      document.getElementById('f-poids').value   = a.poids  || '';
      document.getElementById('f-besoins').value = a.besoins|| '';
      emoji = a.emoji;
      document.querySelectorAll('.emoji-btn').forEach(b => {
        b.classList.toggle('selected', b.textContent === a.emoji);
      });
      openModal(false);
    }

    /* ── SAVE ── */
    function saveAnimal() {
      const nom = document.getElementById('f-nom').value.trim();
      if (!nom) {
        document.getElementById('f-nom').classList.add('error');
        document.getElementById('f-nom').focus();
        return;
      }
      document.getElementById('f-nom').classList.remove('error');

      const animal = {
        emoji,
        nom,
        espece : document.getElementById('f-espece').value,
        race   : document.getElementById('f-race').value.trim(),
        age    : document.getElementById('f-age').value,
        poids  : document.getElementById('f-poids').value,
        besoins: document.getElementById('f-besoins').value.trim(),
      };

      if (editIdx >= 0) animaux[editIdx] = animal;
      else              animaux.push(animal);

      localStorage.setItem('petini_animaux', JSON.stringify(animaux));
      closeModal();
      render();
    }

    /* ── DELETE ── */
    function openDelete(i) {
      deleteIdx = i;
      document.getElementById('del-name').textContent = animaux[i].nom;
      document.getElementById('modal-del').classList.add('active');
    }
    function closeDelete() { document.getElementById('modal-del').classList.remove('active'); }
    function confirmDelete() {
      animaux.splice(deleteIdx, 1);
      localStorage.setItem('petini_animaux', JSON.stringify(animaux));
      closeDelete();
      render();
    }

    /* ── EMOJI ── */
    function pickEmoji(btn, e) {
      document.querySelectorAll('.emoji-btn').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
      emoji = e;
    }

    /* ── CLOSE ON OVERLAY CLICK ── */
    function closeOutside(e, id) {
      if (e.target.id === id) {
        if (id === 'modal')     closeModal();
        if (id === 'modal-del') closeDelete();
      }
    }

    render();