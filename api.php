<?php
// ============================================================
//  AJAX API Endpoint  –  api.php
//  Handles: create | read | update | delete | stats | options
//  Song Library Management System
// ============================================================

header('Content-Type: application/json');
require_once __DIR__ . '/includes/helpers.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    /* ---- READ: list songs ---- */
    case 'list':
        $songs = getSongs(
            $_GET['search']   ?? '',
            $_GET['genre_id'] ?? '',
            $_GET['sort']     ?? 'title'
        );
        echo json_encode(['success' => true, 'data' => $songs]);
        break;

    /* ---- READ: single song ---- */
    case 'get':
        $id   = (int)($_GET['id'] ?? 0);
        $song = getSongById($id);
        if ($song) {
            echo json_encode(['success' => true, 'data' => $song]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Song not found']);
        }
        break;

    /* ---- CREATE ---- */
    case 'create':
        $d = validateSongInput($_POST);
        if ($d['error']) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $d['error']]);
            break;
        }
        $ok = createSong($d);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Song added successfully!' : 'Failed to add song.'
        ]);
        break;

    /* ---- UPDATE ---- */
    case 'update':
        parse_str(file_get_contents('php://input'), $put);
        $id = (int)($put['id'] ?? 0);
        $d  = validateSongInput($put);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid song ID']);
            break;
        }
        if ($d['error']) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $d['error']]);
            break;
        }
        $ok = updateSong($id, $d);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Song updated successfully!' : 'Failed to update song.'
        ]);
        break;

    /* ---- DELETE ---- */
    case 'delete':
        parse_str(file_get_contents('php://input'), $del);
        $id = (int)($del['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid song ID']);
            break;
        }
        $ok = deleteSong($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Song deleted.' : 'Failed to delete song.'
        ]);
        break;

    /* ---- STATS for dashboard ---- */
    case 'stats':
        echo json_encode(['success' => true, 'data' => getStats()]);
        break;

    /* ---- Dropdown options ---- */
    case 'options':
        echo json_encode([
            'success' => true,
            'genres'  => getAllGenres(),
            'artists' => getAllArtists()
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}

/* ================================================================
   Input validation helper
================================================================ */
function validateSongInput(array $p): array {
    $title       = trim($p['title']       ?? '');
    $artist_id   = (int)($p['artist_id']  ?? 0);
    $genre_id    = (int)($p['genre_id']   ?? 0);
    $album       = trim($p['album']       ?? '');
    $min         = (int)($p['dur_min']    ?? 0);
    $sec         = (int)($p['dur_sec']    ?? 0);
    $year        = (int)($p['year']       ?? 0);
    $rating      = (int)($p['rating']     ?? 3);

    if ($title === '')                    return ['error' => 'Title is required.'];
    if ($artist_id < 1)                   return ['error' => 'Please select an artist.'];
    if ($genre_id  < 1)                   return ['error' => 'Please select a genre.'];
    if ($year < 1900 || $year > date('Y')) return ['error' => 'Invalid release year.'];
    if ($rating < 1 || $rating > 5)       return ['error' => 'Rating must be 1–5.'];

    $duration_sec = $min * 60 + $sec;
    if ($duration_sec < 1)                return ['error' => 'Duration must be greater than 0.'];

    return [
        'error'        => null,
        'title'        => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
        'artist_id'    => $artist_id,
        'genre_id'     => $genre_id,
        'album'        => htmlspecialchars($album, ENT_QUOTES, 'UTF-8'),
        'duration_sec' => $duration_sec,
        'year'         => $year,
        'rating'       => $rating,
    ];
}
