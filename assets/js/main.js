(() => {
  const navToggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');
  if (navToggle && nav) {
    navToggle.addEventListener('click', () => nav.classList.toggle('is-open'));
  }

  // show/hide password
  document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-toggle-password');
      const input = document.getElementById(id);
      if (!input) return;
      const nextType = input.type === 'password' ? 'text' : 'password';
      input.type = nextType;
      btn.textContent = nextType === 'password' ? 'Show' : 'Hide';
    });
  });

  // auto-dismiss alerts
  document.querySelectorAll('[data-autodismiss="1"]').forEach(el => {
    setTimeout(() => { el.style.opacity = '0'; el.style.transform='translateY(-6px)'; }, 3500);
    setTimeout(() => { el.remove(); }, 4200);
  });

  // confirm delete buttons
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      const msg = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });
})();