<?php
// ============================================================
//  Helper / Utility Functions
//  Song Library Management System
// ============================================================

require_once __DIR__ . '/db.php';

/* ---------- Sanitise ---------- */
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function jsonResponse(bool $success, string $message, array $data = []): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

/* ---------- Genres & Artists (for dropdowns) ---------- */
function getAllGenres(): array {
    $db   = getDB();
    $res  = $db->query("SELECT * FROM genres ORDER BY name");
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $db->close();
    return $rows;
}

function getAllArtists(): array {
    $db   = getDB();
    $res  = $db->query("SELECT * FROM artists ORDER BY name");
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $db->close();
    return $rows;
}

/* ---------- Songs CRUD ---------- */

/**
 * READ – list with optional search / filter
 */
function getSongs(string $search = '', string $genre_id = '', string $sort = 'title'): array {
    $db = getDB();

    $allowed_sorts = ['title', 'artist', 'genre', 'year', 'rating', 'play_count'];
    if (!in_array($sort, $allowed_sorts)) $sort = 'title';

    $where  = [];
    $params = [];
    $types  = '';

    if ($search !== '') {
        $where[]  = "(s.title LIKE ? OR a.name LIKE ? OR s.album LIKE ?)";
        $like     = '%' . $search . '%';
        $params   = array_merge($params, [$like, $like, $like]);
        $types   .= 'sss';
    }
    if ($genre_id !== '' && is_numeric($genre_id)) {
        $where[]  = "s.genre_id = ?";
        $params[] = (int)$genre_id;
        $types   .= 'i';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT s.id, s.title, a.name AS artist, g.name AS genre,
               s.album, s.duration_sec,
               CONCAT(LPAD(FLOOR(s.duration_sec/60),2,'0'),':',LPAD(MOD(s.duration_sec,60),2,'0')) AS duration_fmt,
               s.year, s.rating, s.play_count
        FROM songs s
        JOIN artists a ON a.id = s.artist_id
        JOIN genres  g ON g.id = s.genre_id
        $whereClause
        ORDER BY $sort ASC
    ";

    $stmt = $db->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $db->close();
    return $rows;
}

/**
 * READ – single song by id
 */
function getSongById(int $id): ?array {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT * FROM songs WHERE id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $db->close();
    return $row ?: null;
}

/**
 * CREATE – insert a new song
 */
function createSong(array $d): bool {
    $db   = getDB();
    $stmt = $db->prepare(
        "INSERT INTO songs (title, artist_id, genre_id, album, duration_sec, year, rating)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'siisiis',
        $d['title'], $d['artist_id'], $d['genre_id'],
        $d['album'], $d['duration_sec'], $d['year'], $d['rating']
    );
    $ok = $stmt->execute();
    $stmt->close();
    $db->close();
    return $ok;
}

/**
 * UPDATE – edit an existing song
 */
function updateSong(int $id, array $d): bool {
    $db   = getDB();
    $stmt = $db->prepare(
        "UPDATE songs
         SET title=?, artist_id=?, genre_id=?, album=?, duration_sec=?, year=?, rating=?
         WHERE id=?"
    );
    $stmt->bind_param(
        'siisiisi',
        $d['title'], $d['artist_id'], $d['genre_id'],
        $d['album'], $d['duration_sec'], $d['year'], $d['rating'], $id
    );
    $ok = $stmt->execute();
    $stmt->close();
    $db->close();
    return $ok;
}

/**
 * DELETE – remove a song
 */
function deleteSong(int $id): bool {
    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM songs WHERE id = ?");
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    $db->close();
    return $ok;
}

/**
 * Stats for dashboard cards
 */
function getStats(): array {
    $db  = getDB();
    $row = $db->query("
        SELECT
            COUNT(*) AS total_songs,
            COUNT(DISTINCT artist_id)  AS total_artists,
            COUNT(DISTINCT genre_id)   AS total_genres,
            SUM(play_count)            AS total_plays,
            ROUND(AVG(rating),1)       AS avg_rating
        FROM songs
    ")->fetch_assoc();
    $db->close();
    return $row;
}
