<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$links = array_filter(CHANNELS, static fn (string $url): bool => trim($url) !== '');
?>
</main>

<footer class="colophon">
  <div class="shell">
    <div class="grid grid--3">

      <div>
        <h2>Pages</h2>
        <ul>
          <li><a href="/">Home</a></li>
          <li><a href="/about">About</a></li>
          <li><a href="/resources">Resources</a></li>
          <li><a href="/get-involved">Get Involved</a></li>
          <li><a href="/contact">Contact</a></li>
        </ul>
      </div>

      <div>
        <h2>Where to find us</h2>
<?php if ($links === []): ?>
        <p class="quiet">Accounts are not set up yet. Use the <a href="/contact">contact form</a> to reach us.</p>
<?php else: ?>
        <ul>
<?php foreach ($links as $name => $url): ?>
          <li><a href="<?= e($url) ?>" rel="me noopener"><?= e($name) ?></a></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
      </div>

      <div>
        <h2>Start here</h2>
        <ul>
          <li><a href="https://deflock.me" rel="noopener">Find the cameras near you</a></li>
          <li><a href="/get-involved#officials">Write to your commissioner</a></li>
          <li><a href="/get-involved#records">File a records request</a></li>
        </ul>
      </div>

    </div>

    <div class="colophon__base">
      <p>
        <?= e(SITE_NAME) ?> is an unaffiliated group of county residents. We are not
        a law firm and nothing here is legal advice.
      </p>
      <p>
        This site sets no cookies, runs no analytics and loads nothing from a
        third&nbsp;party.
      </p>
    </div>
  </div>
</footer>

<script src="/assets/js/nav.js" defer></script>
</body>
</html>
