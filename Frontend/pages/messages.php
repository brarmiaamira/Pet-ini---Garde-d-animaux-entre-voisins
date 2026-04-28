<?php
// Frontend/pages/messages.php
session_start();
include '../../Backend/config/db.php';

//$user_id = 2; // temporaire
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="messages.css">
    <title>Messages — Pet'ini</title>
</head>
<body>

<div class="chat-layout">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1>Messages</h1>
            <div class="search-box">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" id="search-input" placeholder="Rechercher une conversation…">
            </div>
        </div>
        <div class="conv-list" id="conv-list">
            <!-- chargé en JS -->
            <p style="padding:20px;color:var(--brown-light);font-size:0.85rem;">Chargement…</p>
        </div>
        <div class="poll-dot" title="Actualisation automatique"></div>
    </aside>

    <!-- ══ ZONE CHAT ══ -->
    <main class="chat-zone" id="chat-zone">

        <!-- État vide -->
        <div class="chat-empty" id="chat-empty">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <p>Sélectionne une conversation</p>
        </div>

        <!-- Header (caché jusqu'à sélection) -->
        <div class="chat-header" id="chat-header" style="display:none">
            <div id="header-avatar" class="avatar-placeholder">?</div>
            <span class="interlocutor" id="header-name">—</span>
            <span class="online-dot"></span>
        </div>

        <!-- Bulles messages -->
        <div class="messages-area" id="messages-area" style="display:none"></div>

        <!-- Input (caché jusqu'à sélection) -->
        <div class="chat-input-area" id="chat-input-area" style="display:none">
            <textarea class="msg-input" id="msg-input"
                      placeholder="Écris un message…"
                      rows="1"
                      maxlength="2000"></textarea>
            <button class="send-btn" id="send-btn" title="Envoyer">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m22 2-7 20-4-9-9-4 20-7z"/><path d="M22 2 11 13"/>
                </svg>
            </button>
        </div>

    </main>
</div>

<script>
// ── CONFIG ──────────────────────────────────────────────
const API       = '../../Backend/api/message.php';
const MY_ID     = <?= $user_id ?>;
const POLL_MS   = 3000;

// ── STATE ────────────────────────────────────────────────
let convs        = [];
let activeConvId = null;
let lastMsgId    = 0;
let pollTimer    = null;

// ── ÉLÉMENTS ────────────────────────────────────────────
const convList      = document.getElementById('conv-list');
const chatEmpty     = document.getElementById('chat-empty');
const chatHeader    = document.getElementById('chat-header');
const headerAvatar  = document.getElementById('header-avatar');
const headerName    = document.getElementById('header-name');
const messagesArea  = document.getElementById('messages-area');
const inputArea     = document.getElementById('chat-input-area');
const msgInput      = document.getElementById('msg-input');
const sendBtn       = document.getElementById('send-btn');
const searchInput   = document.getElementById('search-input');

// ── HELPERS ─────────────────────────────────────────────
function api(params = '', method = 'GET', body = null) {
    const opts = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) opts.body = JSON.stringify(body);
    return fetch(API + params, opts).then(r => r.json());
}

function initials(nom, prenom) {
    return ((prenom?.[0] ?? '') + (nom?.[0] ?? '')).toUpperCase() || '?';
}

function relativeTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const now = new Date();
    const diffDays = Math.floor((now - d) / 86400000);
    if (diffDays === 0) return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    if (diffDays === 1) return 'Hier';
    if (diffDays < 7)  return d.toLocaleDateString('fr-FR', { weekday: 'short' });
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
}

function scrollBottom() {
    messagesArea.scrollTop = messagesArea.scrollHeight;
}

// ── CONVERSATIONS ────────────────────────────────────────
async function loadConversations() {
    const data = await api('?action=conversations');
    convs = data;
    renderConvList(convs);
}

function renderConvList(list) {
    const q = searchInput.value.toLowerCase();
    const filtered = q ? list.filter(c =>
        (c.prenom + ' ' + c.nom).toLowerCase().includes(q)
    ) : list;

    if (!filtered.length) {
        convList.innerHTML = '<p style="padding:20px;color:var(--brown-light);font-size:0.85rem;">Aucune conversation.</p>';
        return;
    }

    convList.innerHTML = filtered.map(c => {
        const initls = initials(c.nom, c.prenom);
        const avatarHtml = c.photo
            ? `<img class="avatar" src="${c.photo}" alt="${c.prenom}" onerror="this.outerHTML='<div class=\\'avatar-placeholder\\'>${initls}</div>'">`
            : `<div class="avatar-placeholder">${initls}</div>`;
        return `
        <div class="conv-item ${c.id == activeConvId ? 'active' : ''}"
             data-id="${c.id}" data-nom="${c.prenom} ${c.nom}"
             data-photo="${c.photo ?? ''}" data-initials="${initls}"
             onclick="selectConv(${c.id}, '${c.prenom} ${c.nom}', '${c.photo ?? ''}', '${initls}')">
            ${avatarHtml}
            <div class="conv-info">
                <div class="name">${c.prenom} ${c.nom}</div>
                <div class="preview">${c.dernier_message ?? 'Démarrer la conversation'}</div>
            </div>
            <div class="conv-meta">
                <span class="time">${relativeTime(c.dernier_at)}</span>
                ${c.non_lus > 0 ? `<span class="badge">${c.non_lus}</span>` : ''}
            </div>
        </div>`;
    }).join('');
}

// ── SÉLECTION CONVERSATION ───────────────────────────────
async function selectConv(convId, nom, photo, initls) {
    activeConvId = convId;
    lastMsgId    = 0;

    // UI : header
    chatEmpty.style.display   = 'none';
    chatHeader.style.display  = 'flex';
    messagesArea.style.display= 'flex';
    inputArea.style.display   = 'flex';

    headerName.textContent = nom;
    headerAvatar.outerHTML = photo
        ? `<img id="header-avatar" class="avatar" src="${photo}" alt="${nom}" onerror="this.outerHTML='<div id=\\'header-avatar\\' class=\\'avatar-placeholder\\'>${initls}</div>'">`
        : `<div id="header-avatar" class="avatar-placeholder">${initls}</div>`;

    // Marquer active dans sidebar
    document.querySelectorAll('.conv-item').forEach(el =>
        el.classList.toggle('active', el.dataset.id == convId));

    // Charger les messages
    messagesArea.innerHTML = '';
    const msgs = await api(`?action=messages&conv_id=${convId}`);
    if (msgs.length) {
        lastMsgId = msgs[msgs.length - 1].id;
        renderMessages(msgs, true);
    }

    // Démarrer polling
    clearInterval(pollTimer);
    pollTimer = setInterval(poll, POLL_MS);

    // Mettre à jour badge non_lus dans la liste
    loadConversations();
}

// ── RENDU MESSAGES ───────────────────────────────────────
function renderMessages(msgs, fullReplace = false) {
    if (fullReplace) {
        messagesArea.innerHTML = '';
        let prevDate = null;
        msgs.forEach(m => {
            const d = m.sent_at ? m.sent_at.split(' ')[0] : '';
            if (d && d !== prevDate) {
                messagesArea.insertAdjacentHTML('beforeend',
                    `<div class="date-sep">${new Date(d).toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long' })}</div>`);
                prevDate = d;
            }
            messagesArea.insertAdjacentHTML('beforeend', bubbleHTML(m));
        });
    } else {
        msgs.forEach(m => messagesArea.insertAdjacentHTML('beforeend', bubbleHTML(m)));
    }
    scrollBottom();
}

function bubbleHTML(m) {
    const mine = m.sender_id == MY_ID;
    const tick = mine ? `<span class="read-tick ${m.lu ? 'read' : ''}">✓✓</span>` : '';
    return `
    <div class="msg-row ${mine ? 'mine' : 'theirs'}">
        <div class="bubble ${mine ? 'mine' : 'theirs'}">
            ${escapeHtml(m.contenu)}
            <span class="time-tag">${m.heure} ${tick}</span>
        </div>
    </div>`;
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
              .replace(/"/g,'&quot;').replace(/\n/g,'<br>');
}

// ── POLLING ──────────────────────────────────────────────
async function poll() {
    if (!activeConvId) return;
    const msgs = await api(`?action=poll&conv_id=${activeConvId}&last_id=${lastMsgId}`);
    if (msgs && msgs.length) {
        lastMsgId = msgs[msgs.length - 1].id;
        renderMessages(msgs, false);
        // Rafraîchir liste conversations pour badge non_lus
        loadConversations();
    }
}

// ── ENVOI MESSAGE ─────────────────────────────────────────
async function sendMessage() {
    const contenu = msgInput.value.trim();
    if (!contenu || !activeConvId) return;

    sendBtn.disabled = true;
    msgInput.value   = '';
    msgInput.style.height = '';

    const res = await api('?action=send', 'POST', { conv_id: activeConvId, contenu });
    if (res.success) {
        // Afficher immédiatement sans attendre le poll
        const now = new Date();
        const heure = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        messagesArea.insertAdjacentHTML('beforeend', bubbleHTML({
            id: res.id, sender_id: MY_ID, contenu, lu: 0, heure, sent_at: ''
        }));
        lastMsgId = res.id;
        scrollBottom();
        loadConversations();
    }
    sendBtn.disabled = false;
    msgInput.focus();
}

// ── EVENTS ───────────────────────────────────────────────
sendBtn.addEventListener('click', sendMessage);

msgInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// Auto-resize textarea
msgInput.addEventListener('input', () => {
    msgInput.style.height = 'auto';
    msgInput.style.height = Math.min(msgInput.scrollHeight, 120) + 'px';
});

searchInput.addEventListener('input', () => renderConvList(convs));

// ── INIT ─────────────────────────────────────────────────
loadConversations();
</script>

</body>
</html>