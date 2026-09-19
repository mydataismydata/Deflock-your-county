<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

http_response_code(404);

$page  = '';
$title = 'Page not found';
$blurb = 'That address does not exist on this site.';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> 404</p>
    <h1 class="display-1" data-reveal>Nothing at this <span class="accent">address</span>.</h1>
    <p class="lede lede--lg">
      The page was moved, renamed or never existed. The five pages this site has
      are all in the menu above.
    </p>
    <div class="pill-row">
      <a class="pill" href="/">Back to the front page &rarr;</a>
      <a class="pill pill--ghost" href="/contact">Report the broken link</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
