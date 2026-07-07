/* Librería ALIS — utilidades de tema (claro/oscuro + acento) */
window.ALIS = (function () {
  const THEME_KEY = 'alis_theme';
  const ACCENT_KEY = 'alis_accent';

  function initTheme() {
    const theme = localStorage.getItem(THEME_KEY) || 'light';
    const accent = localStorage.getItem(ACCENT_KEY) || 'indigo';
    document.body.setAttribute('data-theme', theme);
    document.body.setAttribute('data-accent', accent);
    syncControls(theme, accent);
  }

  function syncControls(theme, accent) {
    const toggleBtn = document.getElementById('themeToggle');
    if (toggleBtn) {
      toggleBtn.innerHTML = theme === 'dark' ? iconSun() : iconMoon();
    }
    document.querySelectorAll('.accent-dot').forEach(function (dot) {
      dot.classList.toggle('active', dot.dataset.accent === accent);
    });
  }

  function toggleTheme() {
    const current = document.body.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-theme', next);
    localStorage.setItem(THEME_KEY, next);
    syncControls(next, document.body.getAttribute('data-accent'));
  }

  function setAccent(accent) {
    document.body.setAttribute('data-accent', accent);
    localStorage.setItem(ACCENT_KEY, accent);
    syncControls(document.body.getAttribute('data-theme'), accent);
  }

  function iconMoon() {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"/></svg>';
  }
  function iconSun() {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4.5"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg>';
  }

  function showToast(msg) {
    let toast = document.getElementById('alisToast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'alisToast';
      toast.className = 'toast';
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(function () { toast.classList.remove('show'); }, 2600);
  }

  function debounce(fn, delay) {
    let timer = null;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(null, args); }, delay);
    };
  }

  return { initTheme, toggleTheme, setAccent, showToast, debounce };
})();
