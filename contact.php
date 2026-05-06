<?php
$page_title = 'Contact — PlusWealth PMS';
include 'header.php';
?>

<section class="section-wrap" id="contact">
  <div class="inner">
    <div style="max-width:880px;margin:0 auto;">
      <?php if (isset($_GET['sent'])): ?>
        <?php if ($_GET['sent'] == '1'): ?>
          <div style="padding:14px 18px;border:1px solid var(--green);background:rgba(34,197,94,0.06);color:var(--green);border-radius:6px;margin-bottom:18px;">Thank you — your message was sent. We'll contact you shortly.</div>
        <?php else: ?>
          <div style="padding:14px 18px;border:1px solid var(--red);background:rgba(248,113,113,0.06);color:var(--red);border-radius:6px;margin-bottom:18px;">Sorry — we couldn't send your message. Please try again or email <a href="mailto:pmscompliance@pluswealth.com">pmscompliance@pluswealth.com</a>.</div>
        <?php endif; ?>
      <?php endif; ?>

      <h2 class="s-title">Get in touch</h2>
      <p class="s-body">Leave your details and we will reach out to schedule a conversation.</p>

      <form action="assets/mail.php" method="POST" id="contact-form" class="contact-form" style="margin-top:18px;">
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
          <input type="text" id="name" name="name" placeholder="Name" required style="padding:12px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:var(--panel);color:var(--white);" />
          <input type="email" id="email" name="email" placeholder="Email" required style="padding:12px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:var(--panel);color:var(--white);" />
          <input type="text" id="phone" name="phone" placeholder="Phone" style="padding:12px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:var(--panel);color:var(--white);" />
        </div>
        <div style="margin-top:16px;">
          <button type="submit" class="btn-gold">SEND MESSAGE</button>
        </div>
      </form>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
