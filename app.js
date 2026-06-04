/* ============================================================
   Song Library Management System — app.js
   Handles all CRUD operations via Fetch API + UI interactions
   ============================================================ */

'use strict';

/* ==================== State ==================== */
const state = {
  songs:    [],
  genres:   [],
  artists:  [],
  editId:   null,
  filter:   { search: '', genre_id: '', sort: 'title' }
};

/* ==================== DOM refs ==================== */
const $ = id => document.getElementById(id);
const DOM = {
  tbody:        $('songs-tbody'),
  songCount:    $('song-count'),
  searchInput:  $('search-input'),
  genreFilter:  $('genre-filter'),
  sortSelect:   $('sort-select'),
  modalEl:      $('song-modal'),
  modalTitle:   $('modal-title'),
  form:         $('song-form'),
  saveBtn:      $('save-btn'),
  statSongs:    $('stat-songs'),
  statArtists:  $('stat-artists'),
  statGenres:   $('stat-genres'),
  statPlays:    $('stat-plays'),
  statRating:   $('stat-rating'),
  toastCont:    $('toast-container'),
  confirmBox:   $('confirm-overlay'),
};

let modal = null;          // Bootstrap modal instance
let deleteCallback = null; // pending delete confirmation

/* ==================== Init ==================== */
document.addEventListener('DOMContentLoaded', () => {
  modal = new bootstrap.Modal(DOM.modalEl);
  loadOptions();
  loadSongs();
  loadStats();
  bindEvents();
});

/* ==================== Event Bindings ==================== */
function bindEvents() {
  // Filter bar
  DOM.searchInput.addEventListener('input', debounce(() => {
    state.filter.search = DOM.searchInput.value.trim();
    loadSongs();
  }, 350));

  DOM.genreFilter.addEventListener('change', () => {
    state.filter.genre_id = DOM.genreFilter.value;
    loadSongs();
  });

  DOM.sortSelect.addEventListener('change', () => {
    state.filter.sort = DOM.sortSelect.value;
    loadSongs();
  });

  // Form submit
  DOM.form.addEventListener('submit', handleSave);

  // Confirm dialog buttons
  $('confirm-yes').addEventListener('click', () => {
    if (deleteCallback) deleteCallback();
    deleteCallback = null;
    DOM.confirmBox.classList.remove('active');
  });
  $('confirm-no').addEventListener('click', () => {
    deleteCallback = null;
    DOM.confirmBox.classList.remove('active');
  });

  // Column sort (th.sortable)
  document.querySelectorAll('th.sortable').forEach(th => {
    th.addEventListener('click', () => {
      DOM.sortSelect.value = th.dataset.sort;
      state.filter.sort = th.dataset.sort;
      loadSongs();
    });
  });
}

/* ==================== Load Options ==================== */
async function loadOptions() {
  const data = await api('options');
  if (!data.success) return;

  state.genres  = data.genres;
  state.artists = data.artists;

  // Populate genre filter dropdown
  data.genres.forEach(g => {
    DOM.genreFilter.insertAdjacentHTML('beforeend',
      `<option value="${g.id}">${g.name}</option>`);
  });

  // Populate form selects
  const genreSelect  = $('f-genre');
  const artistSelect = $('f-artist');

  data.genres.forEach(g => {
    genreSelect.insertAdjacentHTML('beforeend',
      `<option value="${g.id}">${g.name}</option>`);
  });
  data.artists.forEach(a => {
    artistSelect.insertAdjacentHTML('beforeend',
      `<option value="${a.id}">${a.name}</option>`);
  });
}

/* ==================== Load Stats ==================== */
async function loadStats() {
  const data = await api('stats');
  if (!data.success) return;
  const s = data.data;
  DOM.statSongs.textContent   = Number(s.total_songs).toLocaleString();
  DOM.statArtists.textContent = Number(s.total_artists).toLocaleString();
  DOM.statGenres.textContent  = Number(s.total_genres).toLocaleString();
  DOM.statPlays.textContent   = Number(s.total_plays || 0).toLocaleString();
  DOM.statRating.textContent  = s.avg_rating || '—';
}

/* ==================== Load Songs (READ) ==================== */
async function loadSongs() {
  DOM.tbody.innerHTML = `<tr><td colspan="9" class="empty-state"><span class="spinner"></span></td></tr>`;

  const params = new URLSearchParams({
    action:   'list',
    search:   state.filter.search,
    genre_id: state.filter.genre_id,
    sort:     state.filter.sort
  });

  const data = await api('list', null, params);
  if (!data.success) { showToast('Failed to load songs', 'error'); return; }

  state.songs = data.data;
  renderTable(state.songs);
}

/* ==================== Render Table ==================== */
function renderTable(songs) {
  DOM.songCount.textContent = songs.length;

  if (songs.length === 0) {
    DOM.tbody.innerHTML = `
      <tr>
        <td colspan="9">
          <div class="empty-state">
            <div style="font-size:3rem;margin-bottom:12px;opacity:.35">🎵</div>
            <p>No songs found. Add one to get started!</p>
          </div>
        </td>
      </tr>`;
    return;
  }

  DOM.tbody.innerHTML = songs.map(s => `
    <tr data-id="${s.id}">
      <td>
        <div class="song-title-cell">${esc(s.title)}</div>
        <div class="song-album">${esc(s.album || '—')}</div>
      </td>
      <td class="artist-cell">${esc(s.artist)}</td>
      <td><span class="genre-pill">${esc(s.genre)}</span></td>
      <td class="year-cell">${s.year}</td>
      <td class="duration-cell">${s.duration_fmt}</td>
      <td class="stars">${starsHtml(s.rating)}</td>
      <td class="play-count">▶ ${Number(s.play_count).toLocaleString()}</td>
      <td>
        <div class="action-btns">
          <button class="btn btn-edit-sm" onclick="openEdit(${s.id})" title="Edit">
            ✏️ Edit
          </button>
          <button class="btn btn-danger-sm" onclick="confirmDelete(${s.id}, '${esc(s.title)}')" title="Delete">
            🗑 Del
          </button>
        </div>
      </td>
    </tr>
  `).join('');
}

/* ==================== CREATE — open blank modal ==================== */
window.openCreate = function () {
  state.editId = null;
  DOM.modalTitle.textContent = '🎵 Add New Song';
  DOM.form.reset();
  setRating(3);
  DOM.saveBtn.textContent = 'Add Song';
  modal.show();
};

/* ==================== UPDATE — open pre-filled modal ==================== */
window.openEdit = async function (id) {
  state.editId = id;
  DOM.modalTitle.textContent = '✏️ Edit Song';
  DOM.saveBtn.textContent = 'Save Changes';

  const data = await api('get', null, new URLSearchParams({ action: 'get', id }));
  if (!data.success) { showToast('Could not load song', 'error'); return; }

  const s = data.data;
  $('f-title').value     = s.title;
  $('f-artist').value    = s.artist_id;
  $('f-genre').value     = s.genre_id;
  $('f-album').value     = s.album || '';
  $('f-dur-min').value   = Math.floor(s.duration_sec / 60);
  $('f-dur-sec').value   = s.duration_sec % 60;
  $('f-year').value      = s.year;
  setRating(s.rating);

  modal.show();
};

/* ==================== SAVE (Create or Update) ==================== */
async function handleSave(e) {
  e.preventDefault();

  const formData = new FormData(DOM.form);
  formData.append('action', state.editId ? 'update' : 'create');
  if (state.editId) formData.append('id', state.editId);

  DOM.saveBtn.disabled = true;
  DOM.saveBtn.innerHTML = '<span class="spinner"></span> Saving…';

  let data;
  if (state.editId) {
    // PUT-style: send as URL-encoded body
    const body = new URLSearchParams(formData).toString();
    data = await fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body
    }).then(r => r.json()).catch(() => ({ success: false, message: 'Network error' }));
  } else {
    data = await fetch('api.php', { method: 'POST', body: formData })
      .then(r => r.json()).catch(() => ({ success: false, message: 'Network error' }));
  }

  DOM.saveBtn.disabled = false;
  DOM.saveBtn.textContent = state.editId ? 'Save Changes' : 'Add Song';

  if (data.success) {
    modal.hide();
    showToast(data.message);
    loadSongs();
    loadStats();
  } else {
    showToast(data.message, 'error');
  }
}

/* ==================== DELETE ==================== */
window.confirmDelete = function (id, title) {
  $('confirm-song-name').textContent = title;
  DOM.confirmBox.classList.add('active');
  deleteCallback = () => doDelete(id);
};

async function doDelete(id) {
  const data = await fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=delete&id=${id}`
  }).then(r => r.json()).catch(() => ({ success: false, message: 'Network error' }));

  if (data.success) {
    showToast(data.message);
    loadSongs();
    loadStats();
  } else {
    showToast(data.message, 'error');
  }
}

/* ==================== Toast ==================== */
function showToast(msg, type = 'success') {
  const el = document.createElement('div');
  el.className = `toast-msg${type === 'error' ? ' error' : ''}`;
  el.textContent = (type === 'success' ? '✅ ' : '❌ ') + msg;
  DOM.toastCont.appendChild(el);
  setTimeout(() => el.remove(), 3500);
}

/* ==================== Helpers ==================== */
async function api(action, body = null, extraParams = null) {
  let url = `api.php?action=${action}`;
  if (extraParams) {
    extraParams.delete('action');
    url = `api.php?action=${action}&${extraParams.toString()}`;
  }
  try {
    const res = await fetch(url, body
      ? { method: 'POST', body }
      : { method: 'GET' }
    );
    return await res.json();
  } catch {
    return { success: false, message: 'Network error' };
  }
}

function starsHtml(r) {
  return '★'.repeat(r) + '☆'.repeat(5 - r);
}

function esc(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function setRating(val) {
  const inputs = DOM.form.querySelectorAll('input[name="rating"]');
  inputs.forEach(inp => { inp.checked = Number(inp.value) === Number(val); });
}

function debounce(fn, ms) {
  let timer;
  return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}
