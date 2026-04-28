 const LABELS = ['','Très mauvais 😞','Mauvais 😕','Correct 😐','Bien 😊','Excellent ! 🌟'];
    let avis = JSON.parse(localStorage.getItem('petini_avis') || 'null');
    let filtre = 0;

    if (!avis) {
      avis = [
        { nom:'Sarah M.', gardien:'Ahmed B.', animal:'🐶 Rex', note:5, comment:'Rex était aux anges ! Beaucoup de photos envoyées. Je recommande vivement !', date:'15 Avril 2025', init:'SM' },
        { nom:'Karim T.', gardien:'Leila H.', animal:'🐱 Mimi', note:5, comment:'Leila a pris soin de Mimi comme si c\'était son propre chat. Très professionnelle.', date:'10 Avril 2025', init:'KT' },
        { nom:'Amira B.', gardien:'Yassine K.', animal:'🐰 Coco', note:4, comment:'Très bonne expérience. Coco était bien nourrie et heureuse à notre retour.', date:'5 Avril 2025', init:'AB' },
        { nom:'Mohamed S.', gardien:'Fatma R.', animal:'🐶 Bruno', note:5, comment:'Bruno adore Fatma ! C\'est la 3ème fois. Toujours parfait !', date:'1 Avril 2025', init:'MS' },
      ];
      localStorage.setItem('petini_avis', JSON.stringify(avis));
    }

    document.querySelectorAll('.star-rating input').forEach(inp => {
      inp.addEventListener('change', () => {
        document.getElementById('rating-label').textContent = LABELS[inp.value];
      });
    });

    function rateCriteria(name, val) {
      const stars = document.querySelectorAll('#c-' + name + ' .criteria-star');
      stars.forEach((s, i) => s.classList.toggle('on', i < val));
    }

    function stars(n) { return '★'.repeat(n) + '☆'.repeat(5-n); }

    function render() {
      const list = document.getElementById('avis-list');
      const empty = document.getElementById('avis-empty');
      list.innerHTML = '';
      const filtered = filtre === 0 ? avis : avis.filter(a => a.note === filtre);

      if (filtered.length === 0) { empty.style.display = 'block'; return; }
      empty.style.display = 'none';

      filtered.forEach(a => {
        const d = document.createElement('div');
        d.className = 'avis-card';
        d.innerHTML = `
          <div class="avis-card-header">
            <div class="avis-avatar">${a.init}</div>
            <div>
              <div class="avis-card-name">${a.nom}</div>
              <div class="avis-card-date">${a.date}</div>
            </div>
            <div class="avis-card-stars">${stars(a.note)}</div>
          </div>
          <div class="avis-card-animal">🐾 ${a.animal}</div>
          <p class="avis-card-text">${a.comment}</p>
          <p class="avis-card-gardien">Gardien : ${a.gardien}</p>
        `;
        list.appendChild(d);
      });

      updateSummary();
    }

    function updateSummary() {
      const total = avis.length;
      if (!total) return;
      const avg = (avis.reduce((s,a) => s + a.note, 0) / total).toFixed(1);
      document.getElementById('score-num').textContent = avg;
      document.getElementById('score-count').textContent = total + ' avis';
      for (let i = 1; i <= 5; i++) {
        const c = avis.filter(a => a.note === i).length;
        document.getElementById('bar-' + i).style.width = (c/total*100).toFixed(0) + '%';
        document.getElementById('cnt-' + i).textContent = c;
      }
    }

    function filterAvis(btn, n) {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filtre = n;
      render();
    }

    function submitAvis() {
      const nom = document.getElementById('n-nom').value.trim();
      const comment = document.getElementById('n-comment').value.trim();
      const inp = document.querySelector('.star-rating input:checked');
      let ok = true;

      document.getElementById('n-nom').classList.toggle('error', !nom);
      document.getElementById('n-comment').classList.toggle('error', !comment);
      if (!nom || !comment || !inp) return;

      const init = nom.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2);
      const date = new Date().toLocaleDateString('fr-FR', {day:'numeric',month:'long',year:'numeric'});

      avis.unshift({
        nom, init, date,
        gardien: document.getElementById('n-gardien').value.trim() || 'Pet\'ini',
        animal: document.getElementById('n-animal').value.trim() || '🐾',
        note: parseInt(inp.value),
        comment
      });

      localStorage.setItem('petini_avis', JSON.stringify(avis));

      document.getElementById('n-nom').value = '';
      document.getElementById('n-gardien').value = '';
      document.getElementById('n-animal').value = '';
      document.getElementById('n-comment').value = '';
      document.querySelectorAll('.star-rating input').forEach(i => i.checked = false);
      document.getElementById('rating-label').textContent = 'Cliquez pour noter';
      document.querySelectorAll('.criteria-star').forEach(s => s.classList.remove('on'));

      const notif = document.getElementById('notif-success');
      notif.style.display = 'block';
      setTimeout(() => notif.style.display = 'none', 3000);

      filtre = 0;
      document.querySelectorAll('.filter-btn').forEach((b,i) => b.classList.toggle('active', i===0));
      render();
    }

    render();