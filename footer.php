<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="logo">
        <div class="logo-wrap">
          <img src="logo.png" alt="PlusWealth" class="logo-img" />
        </div>
      </div>
      <p>A SEBI-registered Portfolio Management Service committed to disciplined, evidence-based, rules-driven wealth creation for long-term investors.</p>
    </div>

    <div class="footer-col">
      <h5>Strategies</h5>
      <ul>
        <li><a href="fusion.php">Fusion PMS</a></li>
        <li><a href="index.php#process">Investment Process</a></li>
        <li><a href="invest.php">Risk Framework</a></li>
        <li><a href="fusion.php#performance">Performance</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h5>Company</h5>
      <ul>
        <li><a href="about.php">About PlusWealth</a></li>
        <li><a href="team.php">Team</a></li>
        <li><a href="download.php">Downloads</a></li>
        <li><a href="compliance.php">Compliance</a></li>
      </ul>
    </div>

    <!-- Office Locations moved to Connect section -->

    <div class="footer-col">
      <h5>Legal</h5>
      <ul>
        <li><a href="compliance.php#sebi">SEBI Disclosures</a></li>
        <li><a href="compliance.php#grievance">Grievance Redressal</a></li>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Use</a></li>
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
  navToggle.addEventListener('click', () => {
    navRight.classList.toggle('open');
  });
}

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
  document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
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
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
