</main>

<footer aria-label="Site footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="logo">
        <div class="logo-wrap">
          <img src="logo.png" alt="PlusWealth logo" class="logo-img" />
        </div>
      </div>
      <p>A SEBI-registered Portfolio Management Service committed to disciplined, evidence-based, rules-driven wealth creation for long-term investors.</p>
    </div>

    <div class="footer-col">
      <h5>Company</h5>
      <ul>
        <li><a href="about.php">About PlusWealth</a></li>
        <li><a href="compliance.php">Compliance and regulatory disclosures</a></li>
      </ul>
    </div>

    <!-- Office Locations moved to Connect section -->

    <div class="footer-col">
      <h5>Legal</h5>
      <ul>
        <li><a href="compliance.php">SEBI disclosures</a></li>
        <li><a href="compliance.php">Grievance redressal process</a></li>
        <li><a href="compliance.php">Privacy policy</a></li>
        <li><a href="compliance.php">Terms of use</a></li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <div class="footer-legal">
      © 2026 PlusWealth Capital Management LLP. All rights reserved.<br>
      Investment in securities is subject to market risk. Please read all related documents carefully before investing. Past performance (simulated or actual) is not indicative of future results. This website is for informational purposes only and does not constitute an offer or solicitation to invest.
    </div>
    <div class="footer-sebi">
      SEBI PM Reg: INP000009144<br>
      
    </div>
  </div>

</footer>

<script>
(function(){
// Sticky nav
const nav = document.getElementById('nav');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

// Mobile menu toggle
const navToggle = document.querySelector('.nav-toggle');
const navRight = document.querySelector('.nav-right');
if (navToggle && navRight) {
  navToggle.setAttribute('aria-expanded', navRight.classList.contains('open') ? 'true' : 'false');
  navToggle.addEventListener('click', () => {
    const isOpen = navRight.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
}

// Strategy dropdown toggle
const dropdownToggles = document.querySelectorAll('.drop-toggle');
dropdownToggles.forEach((toggle) => {
  const parent = toggle.closest('.dropdown');
  if (!parent) return;
  const menu = document.getElementById(toggle.getAttribute('aria-controls'));
  const menuLinks = menu ? Array.from(menu.querySelectorAll('a')) : [];

  function closeOtherDropdowns() {
    dropdownToggles.forEach((otherToggle) => {
      const otherParent = otherToggle.closest('.dropdown');
      if (!otherParent) return;
      otherParent.classList.remove('open');
      otherToggle.setAttribute('aria-expanded', 'false');
    });
  }

  function openDropdown() {
    closeOtherDropdowns();
    parent.classList.add('open');
    toggle.setAttribute('aria-expanded', 'true');
  }

  function closeDropdown() {
    parent.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
  }

  toggle.addEventListener('click', (event) => {
    event.stopPropagation();
    const isOpen = parent.classList.contains('open');
    if (isOpen) closeDropdown(); else openDropdown();
  });

  toggle.addEventListener('focus', openDropdown);

  toggle.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      openDropdown();
      if (menuLinks[0]) menuLinks[0].focus();
    }
    if (event.key === 'Escape') {
      event.preventDefault();
      closeDropdown();
    }
  });

  parent.addEventListener('focusout', () => {
    window.setTimeout(() => {
      if (!parent.contains(document.activeElement)) closeDropdown();
    }, 0);
  });

  menuLinks.forEach((link, index) => {
    link.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        closeDropdown();
        toggle.focus();
      }
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        const delta = event.key === 'ArrowDown' ? 1 : -1;
        const nextIndex = (index + delta + menuLinks.length) % menuLinks.length;
        menuLinks[nextIndex].focus();
      }
      if (event.key === 'Home') {
        event.preventDefault();
        menuLinks[0].focus();
      }
      if (event.key === 'End') {
        event.preventDefault();
        menuLinks[menuLinks.length - 1].focus();
      }
    });
  });
});

document.addEventListener('click', (event) => {
  if (event.target.closest('.dropdown')) return;

  dropdownToggles.forEach((toggle) => {
    const parent = toggle.closest('.dropdown');
    if (!parent) return;
    parent.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
  });
});

document.addEventListener('keydown', (event) => {
  if (event.key !== 'Escape') return;

  if (navRight && navToggle && navRight.classList.contains('open')) {
    navRight.classList.remove('open');
    navToggle.setAttribute('aria-expanded', 'false');
    navToggle.focus();
  }

  dropdownToggles.forEach((toggle) => {
    const parent = toggle.closest('.dropdown');
    if (!parent) return;
    parent.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
  });
});

// Scroll reveal
const revealEls = document.querySelectorAll('.reveal');
const revObs = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('in'); revObs.unobserve(e.target); }
  });
}, { threshold: 0.12 });
revealEls.forEach(el => revObs.observe(el));
})();

// FAQ
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(i => {
    i.classList.remove('open');
    const q = i.querySelector('.faq-q');
    const a = i.querySelector('.faq-a');
    if (q) q.setAttribute('aria-expanded', 'false');
    if (a) a.hidden = true;
  });
  if (!isOpen) {
    item.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    const answer = item.querySelector('.faq-a');
    if (answer) answer.hidden = false;
  }
}

// Animate numbers on entry
function animateCounter(el, target, decimals, prefix, suffix, duration) {
  let start = null;
  const step = (ts) => {
    if (!start) start = ts;
    const p = Math.min((ts - start) / duration, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    const val = (target * ease).toFixed(decimals);
    el.textContent = prefix + val + suffix;
    if (p < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}

// Trigger counters when KPI row enters view
(function(){
const kpiRow = document.querySelector('.perf-kpi-row');
if (kpiRow) {
  const cObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting && !e.target.dataset.counted) {
        e.target.dataset.counted = '1';
        const vals = e.target.querySelectorAll('.kpi-tile-val');
        const configs = [
          { el: vals[0], target: 6.95, d: 2, pre: '+', suf: '%', dur: 1200 },
          { el: vals[1], target: 5.98, d: 2, pre: '+', suf: '%', dur: 1200 },
          { el: vals[2], target: 5.07, d: 2, pre: '−', suf: '%', dur: 1000 },
          { el: vals[3], target: 0.62, d: 2, pre: '',  suf: '',  dur: 1000 },
        ];
        configs.forEach(c => animateCounter(c.el, c.target, c.d, c.pre, c.suf, c.dur));
      }
    });
  }, { threshold: 0.3 });
  cObs.observe(kpiRow);
}
})();

// Smooth scroll for anchor links
document.addEventListener('click', (e) => {
  const a = e.target.closest('a[href^="#"]');
  if (!a) return;
  e.preventDefault();
  const id = a.getAttribute('href');
  const el = document.querySelector(id);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    if (!el.hasAttribute('tabindex')) el.setAttribute('tabindex', '-1');
    el.focus({ preventScroll: true });
  }
});
</script>

<script>
(function(){
  var loader = document.getElementById('site-loader');
  var overlay = document.getElementById('error-overlay');
  var elog = document.getElementById('error-log');
  var eclose = document.getElementById('error-close');
  function hideLoader(){ if(loader) loader.style.display='none'; }
  function showOverlay(msg){ if(elog) elog.textContent = msg; if(overlay) overlay.hidden = false; hideLoader(); }
  window.addEventListener('error', function(ev){
    var msg = (ev && ev.message ? ev.message : String(ev)) + '\n' + (ev && ev.filename ? ev.filename : '') + ':' + (ev && ev.lineno ? ev.lineno : '') + ':' + (ev && ev.colno ? ev.colno : '') + '\n' + (ev && ev.error && ev.error.stack ? ev.error.stack : '');
    console.error('Captured error:', msg);
    showOverlay(msg);
  });
  window.addEventListener('unhandledrejection', function(ev){
    var msg = 'Unhandled Rejection: ' + (ev && ev.reason && (ev.reason.stack || ev.reason));
    console.error(msg);
    showOverlay(msg);
  });
  if(eclose) eclose.addEventListener('click', function(){ if(overlay) overlay.hidden = true; });
  window.addEventListener('load', function(){ hideLoader(); });
  setTimeout(hideLoader, 8000);
})();
</script>

<script>
// AJAX submit for contact form and toast popup
(function(){
  var form = document.getElementById('contact-form');
  if(!form) return;

  function showToast(message, ok){
    var toast = document.createElement('div');
    toast.className = 'site-toast';
    toast.innerHTML = '<div class="site-toast-body">' + message + '</div>';
    document.body.appendChild(toast);
    // basic styles
    Object.assign(toast.style, {
      position: 'fixed', left: '50%', top: '20px', transform: 'translateX(-50%)',
      background: ok ? 'rgba(34,197,94,0.10)' : 'rgba(248,113,113,0.08)',
      color: ok ? 'var(--green)' : 'var(--red)',
      border: '1px solid ' + (ok ? 'rgba(34,197,94,0.18)' : 'rgba(248,113,113,0.18)'),
      padding: '12px 18px', borderRadius: '8px', zIndex: 1100, fontFamily: 'var(--f-body)'
    });
    setTimeout(function(){ toast.style.opacity = '0'; toast.style.transition = 'opacity 0.35s'; }, 3000);
    setTimeout(function(){ if(toast && toast.parentNode) toast.parentNode.removeChild(toast); }, 3400);
  }

  form.addEventListener('submit', function(e){
    e.preventDefault();
    var btn = form.querySelector('button[type="submit"]');
    if(btn) btn.disabled = true;
    var fd = new FormData(form);
    fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(res){ return res.json(); })
      .then(function(json){
        if(json && json.success){
          showToast(json.message || 'Message sent — thank you.', true);
          form.reset();
        } else {
          showToast((json && json.message) || 'Failed to send message.', false);
        }
      })
      .catch(function(err){
        console.error('Contact form error', err);
        showToast('Network error — please try again.', false);
      })
      .finally(function(){ if(btn) btn.disabled = false; });
  });
})();
</script>


</body>
</html>
