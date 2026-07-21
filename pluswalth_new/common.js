(function () {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const isHome = currentPage === 'index.html' || currentPage === '';
  const isStory = currentPage === 'our-story.html';

  const homePrefix = isHome ? '' : 'index.html';
  const activeAttrs = 'class="active" aria-current="page"';
  const headerTarget = document.querySelector('[data-site-header]');
  const footerTarget = document.querySelector('[data-site-footer]');

  if (headerTarget) {
    headerTarget.outerHTML = `
      <header class="site-header" aria-label="Site header">
        <div class="header-inner">
          <a class="brand" href="index.html" aria-label="Plus Wealth home">
            <img src="assets/logo.png" alt="Plus Wealth">
          </a>

          <button class="nav-toggle" type="button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="site-menu">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
          </button>

          <nav class="nav-menu" id="site-menu" aria-label="Primary navigation">
            <a ${isStory ? activeAttrs : ''} href="our-story.html">Our Story</a>
            <a href="${homePrefix}#technology">Technology</a>
            <a href="https://pluswealth.com/culture/">Culture</a>
            <a href="#">Careers</a>
            <a href="${homePrefix}#offices">Contact Us</a>
            <a href="https://pluswealth.com/investor-charter/">Investor</a>
            <a href="https://pms.pluswealth.com/">PMS</a>
            <a href="https://pluswealthassets.com/">AIF</a>
          </nav>
        </div>
      </header>
    `;
  }

  if (footerTarget) {
    footerTarget.outerHTML = `
      <footer class="site-footer">
        <div class="footer-inner">
          <p>&copy; 2026 PlusWealth Capital Management LLP. SEBI Registration No.: INZ000163752 Portfolio Manager SEBI Registration No.: INP000009144</p>
          <nav aria-label="Footer navigation">
            <a href="https://accounts.pluswealth.com:5000/capexweb/capexweb/">Login</a>
            <a href="https://pluswealth.com/downloads/">Downloads</a>
            <a href="https://pluswealth.com/compliance/">Compliance</a>
            <a class="linkedin-link" href="https://www.linkedin.com/company/pluswealth-capital-management-llp/" aria-label="PlusWealth on LinkedIn">
              <img src="assets/linkedin-icon.png" alt="" aria-hidden="true">
            </a>
          </nav>
        </div>
      </footer>
      <a class="back-to-top" href="#page-top" aria-label="Back to top">Back to top</a>
    `;
  }
})();
