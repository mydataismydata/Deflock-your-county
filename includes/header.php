<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** @var string $page  Slug of the current page, used to mark the nav item. */
/** @var string $title Text for <title>, without the site name. */
/** @var string $blurb One-line description for search results and link previews. */

$page  = $page  ?? '';
$title = $title ?? '';
$blurb = $blurb ?? 'A ' . COUNTY . ' group organizing against automated license plate
    readers and other mass surveillance.';

// Home is reachable through the wordmark; the menu lists the rest.
$menu = [
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
<meta name="theme-color" content="#000000">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($blurb) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/site.css')) ?>">
<link rel="preload" href="/assets/fonts/dm-serif-display-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/ibm-plex-sans-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="icon" href="<?= e(asset('/assets/img/favicon-32.png')) ?>" type="image/png" sizes="32x32">
<link rel="icon" href="<?= e(asset('/assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(asset('/assets/img/favicon-180.png')) ?>">
</head>
<body>

<a class="skip" href="#main">Skip to content</a>

<!-- One film over the whole page, so every screen shares the same grain. -->
<div class="grain" aria-hidden="true"></div>

<header class="site-header" data-scrolled="false">
  <div class="site-header__bar">
    <a class="wordmark" href="/"><?= e(SITE_NAME) ?></a>

    <button class="nav-toggle" type="button" hidden
            aria-expanded="false" aria-controls="primary-nav">Menu</button>

    <nav class="site-nav" id="primary-nav" aria-label="Primary">
<?php foreach ($menu as $slug => [$href, $label]): ?>
      <a href="<?= e($href) ?>"<?= $slug === $page ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
<?php endforeach; ?>
    </nav>
  </div>
</header>

<main id="main">
