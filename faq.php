<?php
$page_title = 'FAQ - PlusWealth PMS';
include 'header.php';
?>

<!-- FAQ -->
<div class="section-wrap" id="faq" style="padding-bottom: 80px;">
  <div class="inner" style="text-align:center; max-width:640px; margin:0 auto 0;">
    <div class="s-eyebrow reveal" style="justify-content:center;">Common Questions</div>
    <h2 class="s-title reveal d1">Everything you need<br><em>to decide.</em></h2>
  </div>
  <div class="faq-wrap reveal d2">
    <div class="faq-item open">
      <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="true" aria-controls="faq-answer-1">
        What is the minimum investment?
        <span class="faq-toggle" aria-hidden="true">+</span>
      </button>
      <div class="faq-a" id="faq-answer-1"><p>Rs. 50 Lakhs as prescribed by SEBI for all Portfolio Management Services. This can be a combination of cash and/or securities transferred in-kind to your dedicated PMS DEMAT account. Your assets are never pooled with other investors.</p></div>
    </div>
    <div class="faq-item">
      <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false" aria-controls="faq-answer-2">
        Is there a lock-in period or exit load?
        <span class="faq-toggle" aria-hidden="true">+</span>
      </button>
      <div class="faq-a" id="faq-answer-2" hidden><p>Lock-in periods and exit loads are defined in your PMS agreement. Short-term redemptions may incur an exit fee. We strongly recommend a minimum 2-year horizon to allow the strategy's compounding effect to meaningfully express itself.</p></div>
    </div>
    <div class="faq-item">
      <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false" aria-controls="faq-answer-3">
        How is risk managed in the portfolio?
        <span class="faq-toggle" aria-hidden="true">+</span>
      </button>
      <div class="faq-a" id="faq-answer-3" hidden><p>Risk is managed through continuous monitoring against predefined limits, stress-testing scenarios, market regime identification, and tactical hedges that activate when valuation or volatility thresholds are breached. No leverage is ever used.</p></div>
    </div>
    <div class="faq-item">
      <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false" aria-controls="faq-answer-4">
        Do I get access to dashboards and performance reports?
        <span class="faq-toggle" aria-hidden="true">+</span>
      </button>
      <div class="faq-a" id="faq-answer-4" hidden><p>Yes. Clients receive periodic reports and live dashboards showing holdings, performance attribution, and risk metrics via our custodian Nuvama Asset Services. Full transparency is a core commitment of the service.</p></div>
    </div>
    <div class="faq-item">
      <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false" aria-controls="faq-answer-5">
        How are taxes handled?
        <span class="faq-toggle" aria-hidden="true">+</span>
      </button>
      <div class="faq-a" id="faq-answer-5" hidden><p>Since you directly own the securities in your DEMAT account, tax treatment can be optimised through timing of realisations. Consolidated tax reports are provided annually. We recommend consulting a tax advisor for your specific situation.</p></div>
    </div>
    <div class="faq-item">
      <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false" aria-controls="faq-answer-6">
        PMS vs mutual fund: what's the key difference?
        <span class="faq-toggle" aria-hidden="true">+</span>
      </button>
      <div class="faq-a" id="faq-answer-6" hidden><p>In PMS, you directly own specific securities in your own DEMAT account, unlike mutual funds where you hold units of a pooled vehicle. PMS offers greater customisation, full transparency, and better tax control. The trade-off is a higher minimum and greater concentration. PMS suits HNIs seeking tailored, actively managed portfolios with full visibility.</p></div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
