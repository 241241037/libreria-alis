<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/config/database.php';

startAppSession();
requireLogin();

$t = loadTranslations();
$lang = currentLang();
$user = currentUser();
$admin = isAdmin();

$pdo = getConnection();
$libros = $pdo->query('SELECT * FROM libro ORDER BY titulo ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars(t('catalog', $t)) ?> · <?= htmlspecialchars(t('app_name', $t)) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-theme="light" data-accent="indigo">

<header class="app-header">
  <div class="header-row">
    <div class="brand-mark" aria-hidden="true">
      <svg width="28" height="28" viewBox="0 0 40 40" fill="none">
        <rect x="4" y="6" width="9" height="28" rx="1.5" fill="currentColor" opacity="0.55"/>
        <rect x="15.5" y="3" width="9" height="31" rx="1.5" fill="currentColor" opacity="0.8"/>
        <rect x="27" y="8" width="9" height="26" rx="1.5" fill="currentColor"/>
      </svg>
    </div>
    <h1 class="brand"><?= htmlspecialchars(t('app_name', $t)) ?></h1>
  </div>

  <div class="search-wrap">
    <span class="search-icon" aria-hidden="true">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
    </span>
    <input type="text" id="searchInput" autocomplete="off" placeholder="<?= htmlspecialchars(t('search_placeholder', $t)) ?>">
    <div class="autocomplete-list" id="autocompleteList"></div>
  </div>

  <div class="header-spacer"></div>

  <div class="header-controls">
    <div class="accent-picker" title="<?= htmlspecialchars(t('accent_color', $t)) ?>">
      <span class="accent-dot" data-accent="indigo" onclick="ALIS.setAccent('indigo')"></span>
      <span class="accent-dot" data-accent="emerald" onclick="ALIS.setAccent('emerald')"></span>
      <span class="accent-dot" data-accent="amber" onclick="ALIS.setAccent('amber')"></span>
      <span class="accent-dot" data-accent="rose" onclick="ALIS.setAccent('rose')"></span>
      <span class="accent-dot" data-accent="slate" onclick="ALIS.setAccent('slate')"></span>
    </div>

    <button class="icon-btn" id="themeToggle" title="<?= htmlspecialchars(t('theme', $t)) ?>" onclick="ALIS.toggleTheme()"></button>

    <?php if (!$admin): ?>
    <button class="icon-btn cart-btn" id="cartBtn" title="<?= htmlspecialchars(t('cart', $t)) ?>" onclick="openCart()">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
      <span class="cart-badge" id="cartBadge" hidden>0</span>
    </button>
    <?php endif; ?>

    <select class="lang-select" id="langSelect" aria-label="<?= htmlspecialchars(t('language', $t)) ?>">
      <?php foreach (LANG_NAMES as $code => $name): ?>
        <option value="<?= $code ?>" <?= $code === $lang ? 'selected' : '' ?>><?= $name ?></option>
      <?php endforeach; ?>
    </select>

    <span class="user-chip"><?= htmlspecialchars(t('welcome', $t)) ?> <strong><?= htmlspecialchars($user['nombre']) ?></strong><span class="role-badge"><?= $admin ? htmlspecialchars(t('role_admin', $t)) : htmlspecialchars(t('role_client', $t)) ?></span></span>

    <a href="logout.php" class="btn btn-ghost btn-sm"><?= htmlspecialchars(t('logout', $t)) ?></a>
  </div>
</header>

<main class="main">
  <div class="section-title">
    <h2><?= htmlspecialchars(t('catalog', $t)) ?></h2>
    <?php if ($admin): ?>
      <button class="btn btn-primary" onclick="openBookModal()"><?= htmlspecialchars(t('add_book', $t)) ?></button>
    <?php endif; ?>
  </div>

  <div class="book-grid" id="bookGrid">
    <?php foreach ($libros as $libro): ?>
      <article class="book-card" data-id="<?= (int) $libro['id'] ?>">
        <div class="book-cover">
          <?php if (!empty($libro['imagen_url'])): ?>
            <img src="<?= htmlspecialchars($libro['imagen_url']) ?>" alt="<?= htmlspecialchars($libro['titulo']) ?>" loading="lazy" onerror="this.parentElement.innerHTML='<svg width=&quot;36&quot; height=&quot;36&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;1.5&quot;><rect x=&quot;3&quot; y=&quot;4&quot; width=&quot;18&quot; height=&quot;16&quot; rx=&quot;1&quot;/><path d=&quot;M3 15l5-5 4 4 3-3 6 6&quot;/></svg>'">
          <?php else: ?>
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="16" rx="1"/><path d="M3 15l5-5 4 4 3-3 6 6"/></svg>
          <?php endif; ?>
        </div>
        <div class="book-info">
          <div class="book-title"><?= htmlspecialchars($libro['titulo']) ?></div>
          <div class="book-author"><?= htmlspecialchars($libro['autor']) ?></div>
          <div class="book-meta">
            <span class="book-price">$<?= number_format((float) $libro['precio'], 2) ?></span>
            <span class="stock-pill <?= $libro['stock'] > 0 ? 'in' : 'out' ?>">
              <?= $libro['stock'] > 0 ? htmlspecialchars(t('in_stock', $t)) . ' · ' . (int) $libro['stock'] : htmlspecialchars(t('out_of_stock', $t)) ?>
            </span>
          </div>
        </div>
        <?php if ($admin): ?>
          <div class="book-actions">
            <button class="btn btn-ghost btn-sm" onclick='openBookModal(<?= json_encode($libro) ?>)'><?= htmlspecialchars(t('edit', $t)) ?></button>
            <button class="btn btn-danger btn-sm" onclick="deleteBook(<?= (int) $libro['id'] ?>, '<?= htmlspecialchars(addslashes($libro['titulo'])) ?>')"><?= htmlspecialchars(t('delete', $t)) ?></button>
          </div>
        <?php else: ?>
          <div class="book-actions">
            <button class="btn btn-primary btn-sm" <?= $libro['stock'] <= 0 ? 'disabled' : '' ?> onclick="addToCart(<?= (int) $libro['id'] ?>, '<?= htmlspecialchars(addslashes($libro['titulo'])) ?>', <?= (int) $libro['stock'] ?>)"><?= htmlspecialchars(t('reserve', $t)) ?></button>
          </div>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if (empty($libros)): ?>
    <div class="empty-state"><?= htmlspecialchars(t('no_results', $t)) ?></div>
  <?php endif; ?>
</main>

<?php if ($admin): ?>
<div class="modal-backdrop" id="bookModalBackdrop">
  <div class="modal">
    <h3 id="modalTitle"><?= htmlspecialchars(t('add_book', $t)) ?></h3>

    <div class="gbooks-search">
      <label class="full" style="margin-bottom:10px;">Buscar en Google Books
        <div style="display:flex; gap:8px;">
          <input type="text" id="gbooksQuery" placeholder="Título, autor o ISBN…">
          <button type="button" class="btn btn-ghost btn-sm" id="gbooksSearchBtn" onclick="searchGoogleBooks()">Buscar</button>
        </div>
      </label>
      <div id="gbooksResults" class="gbooks-results"></div>
    </div>

    <form id="bookForm">
      <input type="hidden" id="bookId">
      <div class="modal-grid">
        <label class="full"><?= htmlspecialchars(t('title', $t)) ?>
          <input type="text" id="bookTitulo" required>
        </label>
        <label><?= htmlspecialchars(t('author', $t)) ?>
          <input type="text" id="bookAutor">
        </label>
        <label><?= htmlspecialchars(t('genre', $t)) ?>
          <input type="text" id="bookGenero">
        </label>
        <label><?= htmlspecialchars(t('isbn', $t)) ?>
          <input type="text" id="bookIsbn" required>
        </label>
        <label><?= htmlspecialchars(t('price', $t)) ?>
          <input type="number" id="bookPrecio" min="0" step="0.01">
        </label>
        <label><?= htmlspecialchars(t('stock', $t)) ?>
          <input type="number" id="bookStock" min="0" step="1">
        </label>
        <label class="full"><?= htmlspecialchars(t('image_url', $t)) ?>
          <input type="url" id="bookImagen" placeholder="https://…">
        </label>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeBookModal()"><?= htmlspecialchars(t('cancel', $t)) ?></button>
        <button type="submit" class="btn btn-primary"><?= htmlspecialchars(t('save', $t)) ?></button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (!$admin): ?>
<div class="modal-backdrop" id="cartModalBackdrop">
  <div class="modal">
    <h3><?= htmlspecialchars(t('cart', $t)) ?></h3>
    <p class="muted" style="margin-top:-8px;font-size:13px;"><?= htmlspecialchars(t('cart_subtitle', $t)) ?></p>
    <div id="cartItems"></div>
    <div class="modal-actions">
      <button type="button" class="btn btn-ghost" onclick="closeCart()"><?= htmlspecialchars(t('cancel', $t)) ?></button>
      <button type="button" class="btn btn-primary" id="confirmReservationBtn" onclick="confirmReservation()"><?= htmlspecialchars(t('confirm_reservation', $t)) ?></button>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="assets/js/app.js"></script>
<script>
ALIS.initTheme();

document.getElementById('langSelect').addEventListener('change', function () {
  window.location.href = '?lang=' + this.value;
});

/* ---------- Autocompletado de búsqueda (consulta la BD) ---------- */
const searchInput = document.getElementById('searchInput');
const acList = document.getElementById('autocompleteList');
const i18n = <?= json_encode($t) ?>;
const isAdminUser = <?= $admin ? 'true' : 'false' ?>;

let currentSuggestions = [];
let activeIndex = -1;
let typedQuery = '';

function renderSuggestionList() {
  acList.innerHTML = currentSuggestions.map((it, i) =>
    `<div class="autocomplete-item${i === activeIndex ? ' active' : ''}" data-index="${i}" data-title="${(it.titulo || '').replace(/"/g, '&quot;')}">
       ${escapeHtml(it.titulo)}
       <span class="ac-sub">${escapeHtml(it.autor || '')} · ${escapeHtml(it.isbn || '')}</span>
     </div>`
  ).join('');
  acList.classList.add('open');
}

function closeDropdown() {
  acList.classList.remove('open');
  acList.innerHTML = '';
  currentSuggestions = [];
  activeIndex = -1;
}

function setActiveIndex(i) {
  if (!currentSuggestions.length) return;
  activeIndex = ((i % currentSuggestions.length) + currentSuggestions.length) % currentSuggestions.length;
  // Sincroniza el texto del input con el libro resaltado en el dropdown
  const titulo = currentSuggestions[activeIndex].titulo;
  searchInput.value = titulo;
  searchInput.setSelectionRange(titulo.length, titulo.length);
  acList.querySelectorAll('.autocomplete-item').forEach(el => {
    el.classList.toggle('active', Number(el.dataset.index) === activeIndex);
  });
  const activeEl = acList.querySelector('.autocomplete-item.active');
  if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
}

function acceptActiveSuggestion() {
  if (activeIndex >= 0 && currentSuggestions[activeIndex]) {
    searchInput.value = currentSuggestions[activeIndex].titulo;
  }
  const len = searchInput.value.length;
  searchInput.setSelectionRange(len, len);
  closeDropdown();
}

const fetchSuggestions = ALIS.debounce(function (q) {
  if (!q) { closeDropdown(); return; }
  fetch('api/search.php?q=' + encodeURIComponent(q))
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(items => {
      if (!Array.isArray(items) || !items.length) { closeDropdown(); return; }
      currentSuggestions = items;
      activeIndex = -1;
      renderSuggestionList();

      // Autocompletado tipo "texto fantasma" (como la barra de direcciones):
      // si el primer resultado empieza igual que lo escrito, completa el resto
      // y lo deja seleccionado para que el usuario lo sobrescriba o lo acepte.
      if (!isDeleting) {
        const match = items.find(it => (it.titulo || '').toLowerCase().startsWith(q.toLowerCase()));
        if (match && match.titulo.length > q.length) {
          const typedLen = q.length;
          searchInput.value = q + match.titulo.slice(typedLen);
          searchInput.setSelectionRange(typedLen, match.titulo.length);
        }
      }
    })
    .catch(err => {
      console.error('Error en autocompletado (api/search.php):', err);
      ALIS.showToast('No se pudo conectar con el buscador. Revisa la consola (F12).');
    });
}, 220);

let isDeleting = false;
searchInput.addEventListener('keydown', function (e) {
  isDeleting = (e.key === 'Backspace' || e.key === 'Delete');

  if (e.key === 'ArrowDown') {
    if (!acList.classList.contains('open')) return;
    e.preventDefault();
    setActiveIndex(activeIndex + 1);
  } else if (e.key === 'ArrowUp') {
    if (!acList.classList.contains('open')) return;
    e.preventDefault();
    setActiveIndex(activeIndex - 1);
  } else if (e.key === 'Tab') {
    if (acList.classList.contains('open') && (activeIndex >= 0 || searchInput.selectionEnd > searchInput.selectionStart)) {
      e.preventDefault();
      acceptActiveSuggestion();
    }
  } else if (e.key === 'Enter') {
    e.preventDefault();
    if (activeIndex >= 0 && currentSuggestions[activeIndex]) {
      searchInput.value = currentSuggestions[activeIndex].titulo;
    }
    typedQuery = searchInput.value.trim();
    performSearch(typedQuery);
    closeDropdown();
  } else if (e.key === 'Escape') {
    closeDropdown();
  }
});

searchInput.addEventListener('input', function () {
  typedQuery = this.value.trim();
  fetchSuggestions(typedQuery);
  performSearch(typedQuery);
});

acList.addEventListener('mouseover', function (e) {
  const item = e.target.closest('.autocomplete-item');
  if (!item) return;
  const i = Number(item.dataset.index);
  activeIndex = i;
  acList.querySelectorAll('.autocomplete-item').forEach(el => {
    el.classList.toggle('active', Number(el.dataset.index) === i);
  });
});

acList.addEventListener('click', function (e) {
  const item = e.target.closest('.autocomplete-item');
  if (!item) return;
  searchInput.value = item.dataset.title;
  performSearch(item.dataset.title);
  closeDropdown();
});

document.addEventListener('click', function (e) {
  if (!e.target.closest('.search-wrap')) closeDropdown();
});

const performSearch = ALIS.debounce(function (q) {
  fetch('api/libros.php?q=' + encodeURIComponent(q))
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(renderGrid)
    .catch(err => {
      console.error('Error al buscar libros (api/libros.php):', err);
      ALIS.showToast('No se pudo cargar el catálogo. Revisa la consola (F12).');
    });
}, 200);

function renderGrid(libros) {
  if (!Array.isArray(libros)) {
    console.error('Respuesta inesperada de api/libros.php:', libros);
    ALIS.showToast((libros && libros.error) ? libros.error : 'Respuesta inesperada del servidor.');
    return;
  }
  document.querySelectorAll('.empty-state').forEach(el => el.remove());
  let gridEl = document.getElementById('bookGrid');
  if (!gridEl) {
    gridEl = document.createElement('div');
    gridEl.className = 'book-grid';
    gridEl.id = 'bookGrid';
    document.querySelector('.main').appendChild(gridEl);
  }
  if (!libros.length) {
    gridEl.innerHTML = '';
    gridEl.insertAdjacentHTML('afterend', `<div class="empty-state">${i18n.no_results}</div>`);
    return;
  }
  gridEl.innerHTML = libros.map(b => {
    const cover = b.imagen_url
      ? `<img src="${b.imagen_url}" alt="${escapeHtml(b.titulo)}" loading="lazy" onerror="this.parentElement.innerHTML=bookIconSvg()">`
      : bookIconSvg();
    const stockClass = b.stock > 0 ? 'in' : 'out';
    const stockLabel = b.stock > 0 ? `${i18n.in_stock} · ${b.stock}` : i18n.out_of_stock;
    const actions = isAdminUser
      ? `<div class="book-actions">
           <button class="btn btn-ghost btn-sm" onclick='openBookModal(${JSON.stringify(b)})'>${i18n.edit}</button>
           <button class="btn btn-danger btn-sm" onclick="deleteBook(${b.id}, '${escapeHtml(b.titulo).replace(/'/g, "\\'")}')">${i18n.delete}</button>
         </div>`
      : `<div class="book-actions"><button class="btn btn-primary btn-sm" ${b.stock <= 0 ? 'disabled' : ''} onclick="addToCart(${b.id}, '${escapeHtml(b.titulo).replace(/'/g, "\\'")}', ${b.stock})">${i18n.reserve}</button></div>`;
    return `<article class="book-card" data-id="${b.id}">
      <div class="book-cover">${cover}</div>
      <div class="book-info">
        <div class="book-title">${escapeHtml(b.titulo)}</div>
        <div class="book-author">${escapeHtml(b.autor || '')}</div>
        <div class="book-meta">
          <span class="book-price">$${Number(b.precio).toFixed(2)}</span>
          <span class="stock-pill ${stockClass}">${stockLabel}</span>
        </div>
      </div>
      ${actions}
    </article>`;
  }).join('');
}

function bookIconSvg() {
  return '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="16" rx="1"/><path d="M3 15l5-5 4 4 3-3 6 6"/></svg>';
}
function escapeHtml(str) {
  return (str || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

<?php if (!$admin): ?>
/* ---------- Carrito de reserva (simulación de compra) ---------- */
const CART_KEY = 'alis_cart_<?= (int) $user['id'] ?>';

function getCart() {
  try { return JSON.parse(sessionStorage.getItem(CART_KEY)) || []; }
  catch (e) { return []; }
}
function saveCart(cart) {
  sessionStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}
function updateCartBadge() {
  const cart = getCart();
  const count = cart.reduce((sum, it) => sum + it.cantidad, 0);
  const badge = document.getElementById('cartBadge');
  badge.textContent = count;
  badge.hidden = count === 0;
}

function addToCart(id, titulo, stock) {
  const cart = getCart();
  const existing = cart.find(it => it.id === id);
  const inCart = existing ? existing.cantidad : 0;
  if (inCart >= stock) {
    ALIS.showToast(i18n.max_stock_reached);
    return;
  }
  if (existing) {
    existing.cantidad += 1;
  } else {
    cart.push({ id, titulo, cantidad: 1, stock });
  }
  saveCart(cart);
  ALIS.showToast(`"${titulo}" ${i18n.added_to_cart}`);
}

function removeFromCart(id) {
  saveCart(getCart().filter(it => it.id !== id));
  renderCart();
}

function openCart() {
  renderCart();
  document.getElementById('cartModalBackdrop').classList.add('open');
}
function closeCart() {
  document.getElementById('cartModalBackdrop').classList.remove('open');
}

function renderCart() {
  const cart = getCart();
  const container = document.getElementById('cartItems');
  const confirmBtn = document.getElementById('confirmReservationBtn');
  if (!cart.length) {
    container.innerHTML = `<p class="muted" style="padding:18px 0;text-align:center;">${i18n.cart_empty}</p>`;
    confirmBtn.disabled = true;
    return;
  }
  confirmBtn.disabled = false;
  container.innerHTML = cart.map(it => `
    <div class="cart-row" data-id="${it.id}">
      <span class="cart-row-title">${escapeHtml(it.titulo)}</span>
      <span class="cart-row-qty">${i18n.qty}: ${it.cantidad}</span>
      <button type="button" class="btn btn-ghost btn-sm" onclick="removeFromCart(${it.id})">${i18n.remove}</button>
    </div>
  `).join('');
}

function confirmReservation() {
  const cart = getCart();
  if (!cart.length) return;
  const confirmBtn = document.getElementById('confirmReservationBtn');
  confirmBtn.disabled = true;

  fetch('api/reservas.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ items: cart.map(it => ({ libro_id: it.id, cantidad: it.cantidad })) }),
  })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
      confirmBtn.disabled = false;
      if (!ok) {
        ALIS.showToast(data.error || 'Error');
        return;
      }
      saveCart([]);
      closeCart();
      const expira = new Date(data.fecha_expiracion.replace(' ', 'T')).toLocaleString();
      alert(`${i18n.reservation_success} #${data.reserva_id}\n${i18n.reservation_expires} ${expira}`);
      performSearch(searchInput.value.trim());
    })
    .catch(err => {
      confirmBtn.disabled = false;
      console.error('Error al confirmar reserva:', err);
      ALIS.showToast('No se pudo confirmar la reserva. Revisa la consola (F12).');
    });
}

updateCartBadge();
<?php endif; ?>

<?php if ($admin): ?>
/* ---------- Admin: modal de alta/edición ---------- */
const backdrop = document.getElementById('bookModalBackdrop');
const bookForm = document.getElementById('bookForm');

function openBookModal(book) {
  document.getElementById('modalTitle').textContent = book ? i18n.edit : i18n.add_book;
  document.getElementById('bookId').value = book ? book.id : '';
  document.getElementById('bookTitulo').value = book ? book.titulo : '';
  document.getElementById('bookAutor').value = book ? (book.autor || '') : '';
  document.getElementById('bookGenero').value = book ? (book.genero || '') : '';
  document.getElementById('bookIsbn').value = book ? book.isbn : '';
  document.getElementById('bookPrecio').value = book ? book.precio : '';
  document.getElementById('bookStock').value = book ? book.stock : '';
  document.getElementById('bookImagen').value = book ? (book.imagen_url || '') : '';
  document.getElementById('gbooksQuery').value = book ? book.titulo : '';
  document.getElementById('gbooksResults').innerHTML = '';
  backdrop.classList.add('open');
}
function closeBookModal() { backdrop.classList.remove('open'); }
backdrop.addEventListener('click', function (e) { if (e.target === backdrop) closeBookModal(); });

/* ---------- Búsqueda en Google Books (autocompletar datos del libro) ---------- */
function searchGoogleBooks() {
  const q = document.getElementById('gbooksQuery').value.trim();
  const resultsEl = document.getElementById('gbooksResults');
  const btn = document.getElementById('gbooksSearchBtn');
  if (!q) return;

  btn.disabled = true;
  btn.textContent = 'Buscando…';
  resultsEl.innerHTML = '';

  fetch('api/google_books.php?q=' + encodeURIComponent(q))
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
      btn.disabled = false;
      btn.textContent = 'Buscar';
      if (!ok) {
        resultsEl.innerHTML = `<p class="muted" style="font-size:12.5px;">${data.error || 'Error al buscar en Google Books.'}</p>`;
        return;
      }
      if (!data.length) {
        resultsEl.innerHTML = `<p class="muted" style="font-size:12.5px;">Sin resultados en Google Books.</p>`;
        return;
      }
      resultsEl.innerHTML = data.map((b, i) => `
        <div class="gbooks-item" onclick='applyGoogleBook(${JSON.stringify(b)})'>
          <div class="gbooks-cover">${b.imagen_url ? `<img src="${b.imagen_url}" alt="">` : ''}</div>
          <div class="gbooks-info">
            <strong>${escapeHtml(b.titulo)}</strong>
            <span>${escapeHtml(b.autor || 'Autor desconocido')}${b.anio ? ' · ' + b.anio : ''}</span>
          </div>
        </div>
      `).join('');
    })
    .catch(err => {
      btn.disabled = false;
      btn.textContent = 'Buscar';
      console.error('Error al consultar Google Books:', err);
      resultsEl.innerHTML = `<p class="muted" style="font-size:12.5px;">No se pudo conectar con Google Books. Revisa la consola (F12).</p>`;
    });
}

function applyGoogleBook(b) {
  document.getElementById('bookTitulo').value = b.titulo || '';
  document.getElementById('bookAutor').value = b.autor || '';
  document.getElementById('bookGenero').value = b.genero || '';
  if (b.isbn) document.getElementById('bookIsbn').value = b.isbn;
  if (b.imagen_url) document.getElementById('bookImagen').value = b.imagen_url;
  ALIS.showToast('Datos completados desde Google Books. Revisa precio y existencias.');
}

bookForm.addEventListener('submit', function (e) {
  e.preventDefault();
  const id = document.getElementById('bookId').value;
  const payload = {
    titulo: document.getElementById('bookTitulo').value.trim(),
    autor: document.getElementById('bookAutor').value.trim(),
    genero: document.getElementById('bookGenero').value.trim(),
    isbn: document.getElementById('bookIsbn').value.trim(),
    precio: parseFloat(document.getElementById('bookPrecio').value || 0),
    stock: parseInt(document.getElementById('bookStock').value || 0, 10),
    imagen_url: document.getElementById('bookImagen').value.trim(),
  };
  if (id) payload.id = parseInt(id, 10);

  fetch('api/libros.php', {
    method: id ? 'PUT' : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
      if (!ok) { ALIS.showToast(i18n[data.error] || data.error || 'Error'); return; }
      ALIS.showToast(i18n.book_saved);
      closeBookModal();
      performSearch(searchInput.value.trim());
    });
});

function deleteBook(id, titulo) {
  if (!confirm(i18n.confirm_delete + '\n\n' + titulo)) return;
  fetch('api/libros.php', {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id }),
  })
    .then(r => r.json())
    .then(() => {
      ALIS.showToast(i18n.book_deleted);
      performSearch(searchInput.value.trim());
    });
}
<?php endif; ?>
</script>
</body>
</html>
