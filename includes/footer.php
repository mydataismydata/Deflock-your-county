<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
?>
</main>

<footer class="site-footer">
  <div class="site-footer__cols">

    <div>
      <p class="wordmark"><?= e(SITE_NAME) ?></p>
      <p class="statement">
        We are real people in <?= e(COUNTY) ?> who want to stop mass
        surveillance in our community.
      </p>
    </div>

    <nav aria-label="Pages">
      <h2>Pages</h2>
      <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/about">About</a></li>
        <li><a href="/resources">Resources</a></li>
        <li><a href="/get-involved">Get Involved</a></li>
        <li><a href="/contact">Contact</a></li>
      </ul>
    </nav>

    <nav aria-label="Start here">
      <h2>Start here</h2>
      <ul>
        <li><a href="https://deflock.org" rel="noopener">Find the cameras near you</a></li>
        <li><a href="/get-involved#officials">Write to your commissioner</a></li>
        <li><a href="/get-involved#records">File a records request</a></li>
      </ul>
    </nav>

    <nav aria-label="Elsewhere">
      <h2>Elsewhere</h2>
      <ul>
        <li><a href="https://deflock.org" rel="noopener">DeFlock map</a></li>
        <li><a href="https://haveibeenflocked.com" rel="noopener">Have I Been Flocked?</a></li>
      </ul>
    </nav>

  </div>

  <div class="site-footer__base">
    <p>This site sets no cookies, runs no analytics and loads nothing from a third&nbsp;party.</p>
    <p>We are not a law firm and nothing on this site is legal advice.</p>
  </div>
</footer>

<script src="<?= e(asset('/assets/js/scene-motion.js')) ?>" defer></script>
</body>
</html>
