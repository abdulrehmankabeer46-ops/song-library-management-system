<?php
// ============================================================
//  Song Library Management System — index.php
//  Main entry point (UI only — data loaded via api.php)
//  Course: Advanced Web Development | Student: L1F24BSCS0428
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>🎵 Song Library — Management System</title>

  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!-- ===================== HEADER ===================== -->
<header class="site-header">
  <div class="brand">
    <div class="brand-icon">🎵</div>
    <div class="brand-title">Song<span>Vault</span></div>
  </div>
  <div class="header-actions">
    <button class="btn btn-primary" onclick="openCreate()">
      ＋ Add Song
    </button>
  </div>
</header>

<!-- ===================== STATS BAR ===================== -->
<div class="stats-bar">
  <div class="stat-item">
    <span class="stat-value" id="stat-songs">—</span>
    <span class="stat-label">Total Songs</span>
  </div>
  <div class="stat-item">
    <span class="stat-value" id="stat-artists">—</span>
    <span class="stat-label">Artists</span>
  </div>
  <div class="stat-item">
    <span class="stat-value" id="stat-genres">—</span>
    <span class="stat-label">Genres</span>
  </div>
  <div class="stat-item">
    <span class="stat-value" id="stat-plays">—</span>
    <span class="stat-label">Total Plays</span>
  </div>
  <div class="stat-item">
    <span class="stat-value" id="stat-rating">—</span>
    <span class="stat-label">Avg Rating</span>
  </div>
</div>

<!-- ===================== MAIN LAYOUT ===================== -->
<main class="main-layout">

  <!-- Filter Bar -->
  <div class="filter-bar">
    <div class="filter-group" style="flex:2;min-width:220px">
      <label>🔍 Search</label>
      <input type="text" id="search-input" class="form-control"
             placeholder="Title, artist, album…" />
    </div>
    <div class="filter-group">
      <label>🎸 Genre</label>
      <select id="genre-filter" class="form-select">
        <option value="">All Genres</option>
      </select>
    </div>
    <div class="filter-group">
      <label>↕ Sort By</label>
      <select id="sort-select" class="form-select">
        <option value="title">Title</option>
        <option value="artist">Artist</option>
        <option value="genre">Genre</option>
        <option value="year">Year</option>
        <option value="rating">Rating</option>
        <option value="play_count">Plays</option>
      </select>
    </div>
  </div>

  <!-- Songs Table -->
  <div class="table-wrap">
    <div class="table-header-bar">
      <div class="table-title">
        🎶 Library
        <span class="count-badge" id="song-count">0</span>
      </div>
      <button class="btn btn-primary btn-sm" onclick="openCreate()">
        ＋ Add Song
      </button>
    </div>

    <div style="overflow-x:auto">
      <table class="songs-table">
        <thead>
          <tr>
            <th class="sortable" data-sort="title">Title / Album</th>
            <th class="sortable" data-sort="artist">Artist</th>
            <th class="sortable" data-sort="genre">Genre</th>
            <th class="sortable" data-sort="year">Year</th>
            <th>Duration</th>
            <th class="sortable" data-sort="rating">Rating</th>
            <th class="sortable" data-sort="play_count">Plays</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="songs-tbody">
          <tr>
            <td colspan="8">
              <div class="empty-state">
                <span class="spinner"></span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div><!-- /.table-wrap -->

</main>

<!-- ===================== ADD / EDIT MODAL ===================== -->
<div class="modal fade" id="song-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modal-title">Add New Song</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="song-form" novalidate>
        <div class="modal-body">
          <div class="form-row">

            <!-- Title -->
            <div class="form-group full">
              <label class="form-label" for="f-title">Song Title *</label>
              <input type="text" id="f-title" name="title" class="form-control"
                     placeholder="e.g. Blinding Lights" required />
            </div>

            <!-- Artist -->
            <div class="form-group">
              <label class="form-label" for="f-artist">Artist *</label>
              <select id="f-artist" name="artist_id" class="form-select" required>
                <option value="">— Select Artist —</option>
              </select>
            </div>

            <!-- Genre -->
            <div class="form-group">
              <label class="form-label" for="f-genre">Genre *</label>
              <select id="f-genre" name="genre_id" class="form-select" required>
                <option value="">— Select Genre —</option>
              </select>
            </div>

            <!-- Album -->
            <div class="form-group full">
              <label class="form-label" for="f-album">Album</label>
              <input type="text" id="f-album" name="album" class="form-control"
                     placeholder="Album name (optional)" />
            </div>

            <!-- Duration -->
            <div class="form-group">
              <label class="form-label">Duration *</label>
              <div style="display:flex;gap:8px;align-items:center">
                <input type="number" id="f-dur-min" name="dur_min" class="form-control"
                       placeholder="mm" min="0" max="60" value="3" style="width:80px" />
                <span style="color:var(--muted);font-size:1.1rem">:</span>
                <input type="number" id="f-dur-sec" name="dur_sec" class="form-control"
                       placeholder="ss" min="0" max="59" value="30" style="width:80px" />
                <span style="color:var(--muted);font-size:.8rem">min : sec</span>
              </div>
            </div>

            <!-- Year -->
            <div class="form-group">
              <label class="form-label" for="f-year">Release Year *</label>
              <input type="number" id="f-year" name="year" class="form-control"
                     placeholder="2024" min="1900" max="<?= date('Y') ?>"
                     value="<?= date('Y') ?>" required />
            </div>

            <!-- Rating -->
            <div class="form-group full">
              <label class="form-label">Rating</label>
              <div class="star-rating-input">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                       <?= $i === 3 ? 'checked' : '' ?> />
                <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label>
                <?php endfor; ?>
              </div>
            </div>

          </div><!-- /.form-row -->
        </div><!-- /.modal-body -->

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="submit" id="save-btn" class="btn btn-primary">
            Add Song
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- ===================== CONFIRM DELETE DIALOG ===================== -->
<div id="confirm-overlay">
  <div id="confirm-box">
    <div class="confirm-icon">🗑️</div>
    <h5>Delete Song?</h5>
    <p>
      Are you sure you want to delete<br>
      <strong id="confirm-song-name" style="color:var(--accent)"></strong>?<br>
      This action cannot be undone.
    </p>
    <div class="confirm-actions">
      <button class="btn btn-secondary" id="confirm-no">Cancel</button>
      <button class="btn btn-primary" id="confirm-yes"
              style="background:var(--red);box-shadow:0 4px 14px rgba(231,76,60,.35)">
        Yes, Delete
      </button>
    </div>
  </div>
</div>

<!-- ===================== TOAST CONTAINER ===================== -->
<div id="toast-container"></div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="js/app.js"></script>

</body>
</html>
