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
  <div class="shell">
    <span class="eyebrow">404</span>
    <h1>Nothing at this address.</h1>
    <p class="lede">
      The page was moved, renamed or never existed. The five pages this site has
      are all in the menu above.
    </p>
    <div class="btn-row">
      <a class="btn btn--loud" href="/">Back to the front page</a>
      <a class="btn btn--ghost" href="/contact">Report the broken link</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
