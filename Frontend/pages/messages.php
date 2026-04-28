<?php
// Frontend/pages/messages.php
session_start();
include '../../Backend/config/db.php';

$user_id = 2; // temporaire
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800;900&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
    <!-- style.css chargé EN PREMIER -->
    <link rel="stylesheet" href="style.css">
    <!-- messages.css surcharge ensuite -->
    <link rel="stylesheet" href="messages.css">
    <link rel="stylesheet" href="recherche.css">
    <title>Messages — Pet'ini</title>
</head>
<!-- Classe page-messages pour cibler body sans affecter les autres pages -->
<body class="page-messages">

<!-- ══ WRAPPER GLOBAL : navbar + chat empilés ══ -->
<div class="messages-wrapper">

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.html" class="nav-logo">
                <img src="logo.png" alt="Pet'ini logo" class="logo-img">
            </a>
        </div>
        <div class="nav-links" id="nav-links">
            <a href="accueil.html">Accueil</a>
            <a href="petProfil.php">Profil animal</a>
            <a href="recherche.html">Recherche</a>
            <a href="carte.html">Carte</a>
            <a href="reservation.php">Réservation</a>
            <a href="messages.php">Message</a>
            <a href="avis.html">Avis</a>
        </div>
        <div class="nav-right">
            <button class="hamburger" onclick="toggleMenu()">☰</button>
        </div>
        <div id="menu">
            <ul>
                <li><a href="accueil.html">Accueil</a></li>
                <li><a href="petProfil.php">Profil animal</a></li>
                <li><a href="recherche.html">Recherche</a></li>
                <li><a href="carte.html">Carte</a></li>
                <li><a href="reservation.php">Réservation</a></li>
                <li><a href="messages.php">Message</a></li>
                <li><a href="avis.html">Avis</a></li>
                <li><a href="index.html">Se déconnecter</a></li>
            </ul>
        </div>
    </nav>

    <!-- ══ CHAT LAYOUT (remplit l'espace restant) ══ -->
    <div class="chat-layout">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>Messages</h1>
                <div class="search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text" id="search-input" placeholder="Rechercher…">
                </div>
            </div>
            <div class="conv-list" id="conv-list">
                <p style="padding:20px;color:var(--brown-light);font-size:0.85rem;">Chargement…</p>
            </div>
            <div class="poll-dot" title="Actualisation auto"></div>
        </aside>

        <!-- ZONE CHAT -->
        <main class="chat-zone" id="chat-zone">

            <!-- État vide -->
            <div class="chat-empty" id="chat-empty">
                <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <p>Sélectionne une conversation</p>
            </div>

            <!-- Header conversation (masqué par défaut) -->
            <div class="chat-header" id="chat-header" style="display:none">
                <div id="header-avatar" class="msg-avatar-placeholder">?</div>
                <span class="interlocutor" id="header-name">—</span>
                <span class="online-dot"></span>
            </div>

            <!-- Bulles -->
            <div class="messages-area" id="messages-area" style="display:none"></div>

            <!-- Saisie (masquée par défaut) -->
            <div class="chat-input-area" id="chat-input-area" style="display:none">
                <textarea class="msg-input" id="msg-input"
                          placeholder="Écris un message…"
                          rows="1"
                          maxlength="2000"></textarea>
                <button class="send-btn" id="send-btn" title="Envoyer">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m22 2-7 20-4-9-9-4 20-7z"/><path d="M22 2 11 13"/>
                    </svg>
                </button>
            </div>

        </main>
    </div><!-- /chat-layout -->

</div><!-- /messages-wrapper -->

<script>
// ── CONFIG ──────────────────────────────────────────────
const API     = '../../Backend/api/message.php';
const MY_ID   = <?= $user_id ?>;
const POLL_MS = 3000;

// ── STATE ────────────────────────────────────────────────
let convs        = [];
let activeConvId = null;
let lastMsgId    = 0;
let pollTimer    = null;

// ── ÉLÉMENTS ─────────────────────────────────────────────
const convList    = document.getElementById('conv-list');
const chatEmpty   = document.getElementById('chat-empty');
const chatHeader  = document.getElementById('chat-header');
const headerName  = document.getElementById('header-name');
const msgsArea    = document.getElementById('messages-area');
const inputArea   = document.getElementById('chat-input-area');
const msgInput    = document.getElementById('msg-input');
const sendBtn     = document.getElementById('send-btn');
const searchInput = document.getElementById('search-input');

// ── HELPERS ──────────────────────────────────────────────
function apiFetch(params = '', method = 'GET', body = null) {
    const opts = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) opts.body = JSON.stringify(body);
    return fetch(API + params, opts).then(r => r.json());
}

function initials(prenom, nom) {
    return ((prenom?.[0] ?? '') + (nom?.[0] ?? '')).toUpperCase() || '?';
}

function relativeTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr), now = new Date();
    const days = Math.floor((now - d) / 86400000);
    if (days === 0) return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    if (days === 1) return 'Hier';
    if (days < 7)  return d.toLocaleDateString('fr-FR', { weekday: 'short' });
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
              .replace(/"/g,'&quot;').replace(/\n/g,'<br>');
}

function scrollBottom() { msgsArea.scrollTop = msgsArea.scrollHeight; }

// ── CONVERSATIONS ─────────────────────────────────────────
async function loadConversations() {
    const data = await apiFetch('?action=conversations');
    if (!Array.isArray(data)) return;
    convs = data;
    renderConvList(convs);
}

function renderConvList(list) {
    const q = searchInput.value.toLowerCase();
    const filtered = q
        ? list.filter(c => (c.prenom + ' ' + c.nom).toLowerCase().includes(q))
        : list;

    if (!filtered.length) {
        convList.innerHTML = '<p style="padding:20px;color:var(--brown-light);font-size:0.85rem;">Aucune conversation.</p>';
        return;
    }

    convList.innerHTML = filtered.map(c => {
        const initls = initials(c.prenom, c.nom);
        const avatarHtml = c.photo
            ? `<img class="msg-avatar" src="${c.photo}" alt="${c.prenom}"
                    onerror="this.outerHTML='<div class=\\'msg-avatar-placeholder\\'>${initls}</div>'">`
            : `<div class="msg-avatar-placeholder">${initls}</div>`;

        return `
        <div class="conv-item ${c.id == activeConvId ? 'active' : ''}"
             data-id="${c.id}"
             onclick="selectConv(${c.id},'${escapeHtml(c.prenom+' '+c.nom)}','${c.photo ?? ''}','${initls}')">
            ${avatarHtml}
            <div class="conv-info">
                <div class="conv-name">${escapeHtml(c.prenom)} ${escapeHtml(c.nom)}</div>
                <div class="conv-preview">${c.dernier_message ? escapeHtml(c.dernier_message) : 'Démarrer la conversation'}</div>
            </div>
            <div class="conv-meta">
                <span class="conv-time">${relativeTime(c.dernier_at)}</span>
                ${c.non_lus > 0 ? `<span class="msg-badge">${c.non_lus}</span>` : ''}
            </div>
        </div>`;
    }).join('');
}

// ── SÉLECTION CONVERSATION ────────────────────────────────
async function selectConv(convId, nom, photo, initls) {
    activeConvId = convId;
    lastMsgId    = 0;

    // Afficher les zones
    chatEmpty.style.display  = 'none';
    chatHeader.style.display = 'flex';
    msgsArea.style.display   = 'flex';
    inputArea.style.display  = 'flex';

    // Header
    headerName.textContent = nom;
    const avatarEl = document.getElementById('header-avatar');
    if (photo) {
        avatarEl.outerHTML = `<img id="header-avatar" class="msg-avatar" src="${photo}" alt="${nom}"
            onerror="this.outerHTML='<div id=\\'header-avatar\\' class=\\'msg-avatar-placeholder\\'>${initls}</div>'">`;
    } else {
        avatarEl.className   = 'msg-avatar-placeholder';
        avatarEl.textContent = initls;
    }

    // Marquer active
    document.querySelectorAll('.conv-item').forEach(el =>
        el.classList.toggle('active', el.dataset.id == convId));

    // Charger messages
    msgsArea.innerHTML = '';
    const msgs = await apiFetch(`?action=messages&conv_id=${convId}`);
    if (Array.isArray(msgs) && msgs.length) {
        lastMsgId = msgs[msgs.length - 1].id;
        renderMessages(msgs, true);
    }

    // Polling
    clearInterval(pollTimer);
    pollTimer = setInterval(poll, POLL_MS);

    loadConversations();
}

// ── RENDU MESSAGES ────────────────────────────────────────
function renderMessages(msgs, fullReplace = false) {
    if (fullReplace) {
        msgsArea.innerHTML = '';
        let prevDate = null;
        msgs.forEach(m => {
            const d = m.sent_at ? m.sent_at.split(' ')[0] : '';
            if (d && d !== prevDate) {
                const label = new Date(d).toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long' });
                msgsArea.insertAdjacentHTML('beforeend', `<div class="date-sep">${label}</div>`);
                prevDate = d;
            }
            msgsArea.insertAdjacentHTML('beforeend', bubbleHTML(m));
        });
    } else {
        msgs.forEach(m => msgsArea.insertAdjacentHTML('beforeend', bubbleHTML(m)));
    }
    scrollBottom();
}

function bubbleHTML(m) {
    const mine = parseInt(m.sender_id) === MY_ID;
    const tick = mine ? `<span class="read-tick ${m.lu ? 'read' : ''}">✓✓</span>` : '';
    return `
    <div class="msg-row ${mine ? 'mine' : 'theirs'}">
        <div class="bubble ${mine ? 'mine' : 'theirs'}">
            ${escapeHtml(m.contenu)}
            <span class="time-tag">${m.heure ?? ''}${tick}</span>
        </div>
    </div>`;
}

// ── POLLING ───────────────────────────────────────────────
async function poll() {
    if (!activeConvId) return;
    const msgs = await apiFetch(`?action=poll&conv_id=${activeConvId}&last_id=${lastMsgId}`);
    if (Array.isArray(msgs) && msgs.length) {
        lastMsgId = msgs[msgs.length - 1].id;
        renderMessages(msgs, false);
        loadConversations();
    }
}

// ── ENVOI ─────────────────────────────────────────────────
async function sendMessage() {
    const contenu = msgInput.value.trim();
    if (!contenu || !activeConvId) return;

    sendBtn.disabled   = true;
    msgInput.value     = '';
    msgInput.style.height = '';

    const res = await apiFetch('?action=send', 'POST', { conv_id: activeConvId, contenu });
    if (res.success) {
        const heure = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        msgsArea.insertAdjacentHTML('beforeend', bubbleHTML({
            id: res.id, sender_id: MY_ID, contenu, lu: 0, heure, sent_at: ''
        }));
        lastMsgId = res.id;
        scrollBottom();
        loadConversations();
    }

    sendBtn.disabled = false;
    msgInput.focus();
}

// ── EVENTS ────────────────────────────────────────────────
sendBtn.addEventListener('click', sendMessage);

msgInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

msgInput.addEventListener('input', () => {
    msgInput.style.height = 'auto';
    msgInput.style.height = Math.min(msgInput.scrollHeight, 120) + 'px';
});

searchInput.addEventListener('input', () => renderConvList(convs));

// ── INIT ──────────────────────────────────────────────────
loadConversations();

// Hamburger menu
function toggleMenu() {
    document.getElementById('menu').classList.toggle('active');
}
</script>

</body>
</html>