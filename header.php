<?php
/* header.php - common header for all pages */
header('Content-Security-Policy: default-src https: \'self\'; script-src https: \'self\' \'unsafe-inline\'; style-src https: \'self\' \'unsafe-inline\' https://fonts.googleapis.com; font-src https: \'self\' https://fonts.gstatic.com; img-src https: data:; connect-src https:;');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
<title><?php echo isset($page_title) ? $page_title : 'PlusWealth PMS'; ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="pw_new.css?v=<?php echo time(); ?>">
<link rel="icon" type="image/png" href="logo.png">

</head>

<body>

<?php $current_strategy = isset($current_strategy) ? $current_strategy : ''; ?>

<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- DEBUG: Loader + Error overlay -->
<div id="site-loader" aria-hidden="true">
  <div class="loader-inner">Loading...</div>
</div>
<div id="error-overlay" role="alert" aria-live="assertive" hidden>
  <div id="error-overlay-inner">
    <strong>Client-side error detected</strong>
    <pre id="error-log" style="white-space:pre-wrap;word-break:break-word;margin-top:8px;max-height:260px;overflow:auto;"></pre>
    <button id="error-close" type="button">Hide</button>
  </div>
</div>

<!-- NAVIGATION -->
<nav id="nav" aria-label="Primary navigation">
  <a href="index.php" class="logo" aria-label="PlusWealth Home">
    <div class="logo-wrap">
      <img src="logo.png" alt="PlusWealth logo" class="logo-img" />
    </div>
  </a>

  <button class="nav-toggle" type="button" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="primary-nav-links">&#9776;</button>

  <div class="nav-right" id="primary-nav-links">
    <ul class="nav-links">
      <li class="dropdown">
        <button
          class="drop-toggle<?php echo $current_strategy !== '' ? ' active' : ''; ?>"
          type="button"
          aria-expanded="false"
          aria-controls="strategy-menu"
        >
          <span>Strategy</span>
          <span class="drop-caret" aria-hidden="true">&#9662;</span>
        </button>
        <ul class="drop-links" id="strategy-menu">
          <li><a href="fusion.php"<?php echo $current_strategy === 'fusion' ? ' class="active" aria-current="page"' : ''; ?>>Fusion strategy</a></li>
          <li><a href="catalyst.php"<?php echo $current_strategy === 'catalyst' ? ' class="active" aria-current="page"' : ''; ?>>Catalyst strategy</a></li>
        </ul>
      </li>
      <li><a href="knowledge.php">Knowledge Center</a></li>
      <li><a href="team.php">Team</a></li>
      <li><a href="faq.php">FAQ</a></li>
    </ul>

    <a href="contact.php" class="nav-btn" aria-label="Connect with PlusWealth">
      Connect
      <svg aria-hidden="true" focusable="false" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M5 12h14M12 5l7 7-7 7"/>
      </svg>
    </a>
  </div>
</nav>

<main id="main-content" tabindex="-1">

<script>
(function(){
  var nav = document.getElementById('nav');
  if(!nav) return;
  function updateAtTop(){
    if(window.scrollY === 0) nav.classList.add('at-top'); else nav.classList.remove('at-top');
  }
  updateAtTop();
  window.addEventListener('scroll', updateAtTop, {passive:true});
})();
</script>

