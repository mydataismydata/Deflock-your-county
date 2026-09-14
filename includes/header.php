<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** @var string $page  Slug of the current page, used to mark the nav item. */
/** @var string $title Text for <title>, without the site name. */
/** @var string $blurb One-line description for search results and link previews. */

$page  = $page  ?? '';
$title = $title ?? '';
$blurb = $blurb ?? 'A St. Johns County group organizing against automated license plate readers and other mass surveillance.';

$menu = [
    'home'      => ['/',             'Home'],
    'about'     => ['/about',        'About'],
    'resources' => ['/resources',    'Resources'],
    'involved'  => ['/get-involved', 'Get Involved'],
    'contact'   => ['/contact',      'Contact'],
];

$fullTitle = $title === '' ? SITE_NAME : $title . ' | ' . SITE_NAME;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($blurb) ?>">
<meta name="color-scheme" content="dark">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($blurb) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<link rel="stylesheet" href="/assets/css/site.css">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
</head>
<body>

<a class="skip" href="#main">Skip to content</a>

<header class="masthead">
  <div class="shell masthead__inner">
    <a class="wordmark" href="/">
      De<span>flock</span> St. Johns
      <small><?= e(SITE_TAGLINE) ?></small>
    </a>

    <button class="nav-toggle" type="button" hidden
            aria-expanded="false" aria-controls="primary-nav">Menu</button>

    <nav class="nav" id="primary-nav" aria-label="Primary">
      <ul>
<?php foreach ($menu as $slug => [$href, $label]): ?>
        <li><a href="<?= e($href) ?>"<?= $slug === $page ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
      </ul>
    </nav>
  </div>
</header>

<main id="main">
