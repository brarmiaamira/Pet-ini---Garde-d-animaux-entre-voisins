<?php
// Backend/api/message.php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

// Temporaire — owner connecté
//$user_id = 2;

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ─── GET ───────────────────────────────────────────────
if ($method === 'GET') {

    // Liste des conversations
    if ($action === 'conversations') {
        $sql = "SELECT c.id, c.user1_id, c.user2_id,
                       u.nom, u.prenom, u.photo,
                       (SELECT contenu FROM messages m WHERE m.conv_id = c.id ORDER BY sent_at DESC LIMIT 1) AS dernier_message,
                       (SELECT sent_at FROM messages m WHERE m.conv_id = c.id ORDER BY sent_at DESC LIMIT 1) AS dernier_at,
                       (SELECT COUNT(*) FROM messages m WHERE m.conv_id = c.id AND m.lu = 0 AND m.sender_id != $user_id) AS non_lus
                FROM conversations c
                JOIN utilisateurs u ON u.id = IF(c.user1_id = $user_id, c.user2_id, c.user1_id)
                WHERE c.user1_id = $user_id OR c.user2_id = $user_id
                ORDER BY dernier_at DESC";
        $result = mysqli_query($conn, $sql);
        $convs = [];
        while ($row = mysqli_fetch_assoc($result)) $convs[] = $row;
        echo json_encode($convs);
        exit;
    }

    // Messages d'une conversation
    if ($action === 'messages' && isset($_GET['conv_id'])) {
        $conv_id = intval($_GET['conv_id']);

        // Vérifier que l'utilisateur appartient à la conversation
        $check = mysqli_query($conn, "SELECT id FROM conversations WHERE id = $conv_id AND (user1_id = $user_id OR user2_id = $user_id)");
        if (!mysqli_num_rows($check)) { echo json_encode(['error' => 'Accès refusé']); exit; }

        // Marquer comme lus
        mysqli_query($conn, "UPDATE messages SET lu = 1 WHERE conv_id = $conv_id AND sender_id != $user_id AND lu = 0");

        $sql = "SELECT m.id, m.sender_id, m.contenu, m.lu,
                       DATE_FORMAT(m.sent_at, '%H:%i') AS heure,
                       m.sent_at
                FROM messages m
                WHERE m.conv_id = $conv_id
                ORDER BY m.sent_at ASC";
        $result = mysqli_query($conn, $sql);
        $msgs = [];
        while ($row = mysqli_fetch_assoc($result)) $msgs[] = $row;
        echo json_encode($msgs);
        exit;
    }

    // Nouveaux messages depuis un certain id (polling)
    if ($action === 'poll' && isset($_GET['conv_id'], $_GET['last_id'])) {
        $conv_id = intval($_GET['conv_id']);
        $last_id = intval($_GET['last_id']);
        $check = mysqli_query($conn, "SELECT id FROM conversations WHERE id = $conv_id AND (user1_id = $user_id OR user2_id = $user_id)");
        if (!mysqli_num_rows($check)) { echo json_encode([]); exit; }

        mysqli_query($conn, "UPDATE messages SET lu = 1 WHERE conv_id = $conv_id AND sender_id != $user_id AND lu = 0");

        $sql = "SELECT m.id, m.sender_id, m.contenu, m.lu,
                       DATE_FORMAT(m.sent_at, '%H:%i') AS heure,
                       m.sent_at
                FROM messages m
                WHERE m.conv_id = $conv_id AND m.id > $last_id
                ORDER BY m.sent_at ASC";
        $result = mysqli_query($conn, $sql);
        $msgs = [];
        while ($row = mysqli_fetch_assoc($result)) $msgs[] = $row;
        echo json_encode($msgs);
        exit;
    }
}

// ─── POST ──────────────────────────────────────────────
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Envoyer un message
    if ($action === 'send') {
        $conv_id  = intval($data['conv_id'] ?? 0);
        $contenu  = mysqli_real_escape_string($conn, trim($data['contenu'] ?? ''));
        if (!$conv_id || !$contenu) { echo json_encode(['error' => 'Données manquantes']); exit; }

        $check = mysqli_query($conn, "SELECT id FROM conversations WHERE id = $conv_id AND (user1_id = $user_id OR user2_id = $user_id)");
        if (!mysqli_num_rows($check)) { echo json_encode(['error' => 'Accès refusé']); exit; }

        $sql = "INSERT INTO messages (conv_id, sender_id, contenu, lu, sent_at)
                VALUES ($conv_id, $user_id, '$contenu', 0, NOW())";
        mysqli_query($conn, $sql);
        $insert_id = mysqli_insert_id($conn);

        echo json_encode(['success' => true, 'id' => $insert_id]);
        exit;
    }

    // Créer une conversation
    if ($action === 'creer') {
        $autre_id = intval($data['autre_id'] ?? 0);
        if (!$autre_id || $autre_id === $user_id) { echo json_encode(['error' => 'Utilisateur invalide']); exit; }

        // Vérifier si conversation existante
        $check = mysqli_query($conn, "SELECT id FROM conversations WHERE (user1_id = $user_id AND user2_id = $autre_id) OR (user1_id = $autre_id AND user2_id = $user_id)");
        if ($row = mysqli_fetch_assoc($check)) {
            echo json_encode(['success' => true, 'conv_id' => $row['id']]);
            exit;
        }

        mysqli_query($conn, "INSERT INTO conversations (user1_id, user2_id) VALUES ($user_id, $autre_id)");
        echo json_encode(['success' => true, 'conv_id' => mysqli_insert_id($conn)]);
        exit;
    }
}

echo json_encode(['error' => 'Action inconnue']);