<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
?>
</main>

<footer class="colophon">
  <div class="shell">
    <div class="colophon__cols">

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
        <h2>Who we are</h2>
        <p>
          We are real people in <?= e(COUNTY) ?> who want to stop mass
          surveillance in our community.
        </p>
        <p>
          We are not a law firm and nothing on this site is legal advice.
        </p>
      </div>

      <div>
        <h2>Start here</h2>
        <ul>
          <li><a href="https://deflock.org" rel="noopener">Find the cameras near you</a></li>
          <li><a href="/get-involved#officials">Write to your commissioner</a></li>
          <li><a href="/get-involved#records">File a records request</a></li>
        </ul>
      </div>

    </div>

    <div class="colophon__base">
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
