(function () {
  const navToggle = document.querySelector('.nav-toggle');
  const navMenu = document.getElementById('site-menu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      const isOpen = navMenu.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      navToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
    });

    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape' || !navMenu.classList.contains('open')) return;
      navMenu.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
      navToggle.setAttribute('aria-label', 'Open navigation menu');
      navToggle.focus();
    });

    navMenu.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.setAttribute('aria-label', 'Open navigation menu');
      });
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

    document.querySelectorAll('[data-clock-zone]').forEach((clockFace) => {
      const zone = clockFace.getAttribute('data-clock-zone');
      const parts = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric',
        hour12: false,
        timeZone: zone
      }).formatToParts(new Date());
      const time = parts.reduce((result, part) => {
        if (part.type !== 'literal') result[part.type] = Number(part.value);
        return result;
      }, {});

      const hours = time.hour % 12;
      const minutes = time.minute || 0;
      const seconds = time.second || 0;
      const hourDeg = (hours * 30) + (minutes * 0.5);
      const minuteDeg = (minutes * 6) + (seconds * 0.1);
      const secondDeg = seconds * 6;

      clockFace.querySelector('.hour-hand').style.transform = `translateX(-50%) rotate(${hourDeg}deg)`;
      clockFace.querySelector('.minute-hand').style.transform = `translateX(-50%) rotate(${minuteDeg}deg)`;
      clockFace.querySelector('.second-hand').style.transform = `translateX(-50%) rotate(${secondDeg}deg)`;
    });
  }

  updateClocks();
  window.setInterval(updateClocks, 1000);

  const disclaimer = document.getElementById('disclaimer');
  const closeDisclaimer = document.querySelector('.disclaimer-close');
  const understandButton = document.querySelector('.understand-btn');
  const previousFocus = document.activeElement;

  function hideDisclaimer() {
    if (!disclaimer) return;
    disclaimer.hidden = true;
    if (previousFocus && typeof previousFocus.focus === 'function') {
      previousFocus.focus();
    }
  }

  if (disclaimer) {
    const firstButton = closeDisclaimer || understandButton;
    if (firstButton) firstButton.focus();

    [closeDisclaimer, understandButton].forEach((button) => {
      if (!button) return;
      button.addEventListener('click', hideDisclaimer);
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !disclaimer.hidden) {
        hideDisclaimer();
      }
    });
  }
})();
