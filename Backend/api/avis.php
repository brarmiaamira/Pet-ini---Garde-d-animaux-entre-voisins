<?php
// ============================================================
//  PET'INI — api/review.php
//  POST  → soumettre un avis
//  GET   → récupérer les avis d'un petsitter + avg_rating
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ── DB CONFIG ──────────────────────────────────────────────
$host   = 'localhost';
$db     = 'petini';
$user   = 'root';
$pass   = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

// ── CREATE TABLE IF NOT EXISTS ─────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS reviews (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        petsitter_id    INT NOT NULL,
        owner_id        INT,
        owner_name      VARCHAR(100) NOT NULL,
        animal          VARCHAR(100),
        note_global     TINYINT NOT NULL CHECK (note_global BETWEEN 1 AND 5),
        note_accueil    TINYINT CHECK (note_accueil BETWEEN 1 AND 5),
        note_comm       TINYINT CHECK (note_comm BETWEEN 1 AND 5),
        note_soin       TINYINT CHECK (note_soin BETWEEN 1 AND 5),
        commentaire     TEXT NOT NULL,
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ── ROUTER ────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    handlePost($pdo);
} elseif ($method === 'GET') {
    handleGet($pdo);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

// ══════════════════════════════════════════════════════════
//  POST — Soumettre un avis
// ══════════════════════════════════════════════════════════
function handlePost(PDO $pdo): void
{
    // Accept JSON body or form-data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    // ── Validation ────────────────────────────────────────
    $errors = [];

    $petsitter_id = isset($data['petsitter_id']) ? (int)$data['petsitter_id'] : 0;
    $owner_name   = trim($data['owner_name'] ?? '');
    $commentaire  = trim($data['commentaire'] ?? '');
    $note_global  = isset($data['note_global']) ? (int)$data['note_global'] : 0;

    if ($petsitter_id <= 0)          $errors[] = 'petsitter_id invalide';
    if (empty($owner_name))          $errors[] = 'owner_name requis';
    if (empty($commentaire))         $errors[] = 'commentaire requis';
    if ($note_global < 1 || $note_global > 5) $errors[] = 'note_global doit être entre 1 et 5';

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors]);
        return;
    }

    // ── Optional fields ───────────────────────────────────
    $owner_id    = isset($data['owner_id'])    ? (int)$data['owner_id']    : null;
    $animal      = trim($data['animal']      ?? '');
    $note_accueil = isset($data['note_accueil']) ? (int)$data['note_accueil'] : null;
    $note_comm    = isset($data['note_comm'])    ? (int)$data['note_comm']    : null;
    $note_soin    = isset($data['note_soin'])    ? (int)$data['note_soin']    : null;

    // Clamp optional notes
    if ($note_accueil !== null) $note_accueil = max(1, min(5, $note_accueil));
    if ($note_comm    !== null) $note_comm    = max(1, min(5, $note_comm));
    if ($note_soin    !== null) $note_soin    = max(1, min(5, $note_soin));

    // ── Insert ────────────────────────────────────────────
    $stmt = $pdo->prepare("
        INSERT INTO reviews
            (petsitter_id, owner_id, owner_name, animal,
             note_global, note_accueil, note_comm, note_soin, commentaire)
        VALUES
            (:petsitter_id, :owner_id, :owner_name, :animal,
             :note_global, :note_accueil, :note_comm, :note_soin, :commentaire)
    ");

    $stmt->execute([
        ':petsitter_id' => $petsitter_id,
        ':owner_id'     => $owner_id,
        ':owner_name'   => $owner_name,
        ':animal'       => $animal,
        ':note_global'  => $note_global,
        ':note_accueil' => $note_accueil,
        ':note_comm'    => $note_comm,
        ':note_soin'    => $note_soin,
        ':commentaire'  => $commentaire,
    ]);

    $new_id = $pdo->lastInsertId();

    // ── Recalcul avg_rating ───────────────────────────────
    $avg = getAvgRating($pdo, $petsitter_id);
    updatePetsitterRating($pdo, $petsitter_id, $avg);

    http_response_code(201);
    echo json_encode([
        'success'      => true,
        'review_id'    => (int)$new_id,
        'avg_rating'   => $avg['avg_global'],
        'total_reviews'=> $avg['total'],
    ]);
}

// ══════════════════════════════════════════════════════════
//  GET — Récupérer les avis d'un petsitter
//  ?petsitter_id=X  [&page=1] [&limit=10] [&note=5]
// ══════════════════════════════════════════════════════════
function handleGet(PDO $pdo): void
{
    $petsitter_id = isset($_GET['petsitter_id']) ? (int)$_GET['petsitter_id'] : 0;

    if ($petsitter_id <= 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'petsitter_id requis']);
        return;
    }

    // Pagination
    $page  = max(1, (int)($_GET['page']  ?? 1));
    $limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    // Filter by note
    $note_filter = isset($_GET['note']) ? (int)$_GET['note'] : 0;

    // ── Build query ───────────────────────────────────────
    $where = 'WHERE r.petsitter_id = :petsitter_id';
    $params = [':petsitter_id' => $petsitter_id];

    if ($note_filter >= 1 && $note_filter <= 5) {
        $where .= ' AND r.note_global = :note_filter';
        $params[':note_filter'] = $note_filter;
    }

    // Total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews r $where");
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();

    // Reviews
    $params[':limit']  = $limit;
    $params[':offset'] = $offset;

    $stmt = $pdo->prepare("
        SELECT
            r.id,
            r.owner_name,
            r.animal,
            r.note_global,
            r.note_accueil,
            r.note_comm,
            r.note_soin,
            r.commentaire,
            r.created_at,
            -- Initiales pour l'avatar
            CONCAT(
                UPPER(LEFT(SUBSTRING_INDEX(r.owner_name,' ',1), 1)),
                UPPER(LEFT(SUBSTRING_INDEX(r.owner_name,' ',-1), 1))
            ) AS initiales
        FROM reviews r
        $where
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':petsitter_id', $petsitter_id, PDO::PARAM_INT);
    if ($note_filter >= 1 && $note_filter <= 5) {
        $stmt->bindValue(':note_filter', $note_filter, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll();

    // ── Avg rating ────────────────────────────────────────
    $avg = getAvgRating($pdo, $petsitter_id);

    // ── Distribution par étoile ───────────────────────────
    $dist_stmt = $pdo->prepare("
        SELECT note_global, COUNT(*) as count
        FROM reviews
        WHERE petsitter_id = :petsitter_id
        GROUP BY note_global
        ORDER BY note_global DESC
    ");
    $dist_stmt->execute([':petsitter_id' => $petsitter_id]);
    $dist_raw = $dist_stmt->fetchAll();

    $distribution = array_fill(1, 5, 0);
    foreach ($dist_raw as $row) {
        $distribution[(int)$row['note_global']] = (int)$row['count'];
    }

    // Format dates
    foreach ($reviews as &$r) {
        $r['created_at_formatted'] = (new DateTime($r['created_at']))
            ->setTimezone(new DateTimeZone('Africa/Tunis'))
            ->format('d F Y');
    }

    echo json_encode([
        'success'      => true,
        'petsitter_id' => $petsitter_id,
        'avg_rating'   => $avg,
        'distribution' => $distribution,
        'pagination'   => [
            'page'        => $page,
            'limit'       => $limit,
            'total'       => $total,
            'total_pages' => (int)ceil($total / $limit),
        ],
        'reviews'      => $reviews,
    ]);
}

// ══════════════════════════════════════════════════════════
//  HELPERS
// ══════════════════════════════════════════════════════════

/**
 * Calcule la moyenne globale et par critère pour un petsitter
 */
function getAvgRating(PDO $pdo, int $petsitter_id): array
{
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*)                        AS total,
            ROUND(AVG(note_global), 2)      AS avg_global,
            ROUND(AVG(note_accueil), 2)     AS avg_accueil,
            ROUND(AVG(note_comm), 2)        AS avg_comm,
            ROUND(AVG(note_soin), 2)        AS avg_soin
        FROM reviews
        WHERE petsitter_id = :id
    ");
    $stmt->execute([':id' => $petsitter_id]);
    $row = $stmt->fetch();

    return [
        'total'       => (int)$row['total'],
        'avg_global'  => $row['avg_global']  ? (float)$row['avg_global']  : null,
        'avg_accueil' => $row['avg_accueil'] ? (float)$row['avg_accueil'] : null,
        'avg_comm'    => $row['avg_comm']    ? (float)$row['avg_comm']    : null,
        'avg_soin'    => $row['avg_soin']    ? (float)$row['avg_soin']    : null,
    ];
}

/**
 * Met à jour avg_rating dans la table petsitters (si elle existe)
 */
function updatePetsitterRating(PDO $pdo, int $petsitter_id, array $avg): void
{
    try {
        $stmt = $pdo->prepare("
            UPDATE petsitters
            SET avg_rating     = :avg,
                total_reviews  = :total
            WHERE id = :id
        ");
        $stmt->execute([
            ':avg'   => $avg['avg_global'],
            ':total' => $avg['total'],
            ':id'    => $petsitter_id,
        ]);
    } catch (PDOException $e) {
        // Table petsitters n'existe pas encore — on ignore
    }
}