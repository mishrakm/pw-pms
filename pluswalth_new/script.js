(function () {
  const navToggle = document.querySelector('.nav-toggle');
  const navMenu = document.getElementById('site-menu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      const isOpen = navMenu.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape' || !navMenu.classList.contains('open')) return;
      navMenu.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
      navToggle.focus();
    });
  }

  function updateClocks() {
    document.querySelectorAll('[data-zone]').forEach((clock) => {
      const zone = clock.getAttribute('data-zone');
      const value = new Intl.DateTimeFormat('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
        timeZone: zone
      }).format(new Date());
      clock.textContent = value;
      clock.setAttribute('datetime', new Date().toISOString());
    });
  }

  updateClocks();
  window.setInterval(updateClocks, 1000);
})();
