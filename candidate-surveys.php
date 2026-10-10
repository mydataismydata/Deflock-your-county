<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/survey.php';

// A new answer has to appear the moment it is submitted.
header('Cache-Control: no-cache');

/*
 * Two views. /candidate-surveys is the tiles, one per candidate who answered.
 * /candidate-surveys/jane-doe is one candidate's answers in full; .htaccess
 * and router.php turn that address into ?c=jane-doe.
 */

$slug     = survey_input($_GET, 'c');
$response = null;

if ($slug !== '') {
    $response = preg_match('/^[a-z0-9-]{1,80}$/', $slug) === 1 ? survey_response_by_slug($slug) : null;

    if ($response === null) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }
}

$responses = $response === null ? survey_responses() : [];

$page = 'surveys';

if ($response === null) {
    $title = 'Candidate surveys';
    $blurb = 'What candidates told ' . SITE_NAME . ' about mass surveillance, published as they wrote it.';
} else {
    $title = $response['name'] . ', candidate survey';
    $blurb = 'The answers ' . $response['name'] . ', ' . $response['office']
           . ', gave to the ' . SITE_NAME . ' questionnaire on mass surveillance.';
}

$arrow = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>';

require __DIR__ . '/includes/header.php';
?>

<?php if ($response === null): ?>
<!-- ========================================================= tiles ==== -->
<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Candidate surveys</p>
    <h1 class="display-1" data-reveal>Where the candidates <span class="accent">stand</span>.</h1>
    <p class="lede lede--lg">
      We sent candidates and elected officials one questionnaire on mass
      surveillance: nine yes-or-no questions and one to answer in their own
      words. Every response is published here as it was submitted, with no
      edits and no commentary.
    </p>
    <p class="label-mono mt-s">answers due <?= e(strtolower(SURVEY_MEMO['DEADLINE'])) ?></p>
  </div>
</section>

<section class="section section--tight">
  <div class="shell shell--wide">
<?php if ($responses === []): ?>
    <div class="rule-top">
      <p class="label-mono">no answers yet</p>
      <p class="note mt-s">
        Each candidate's answers appear here as soon as they are submitted.
      </p>
    </div>
<?php else: ?>
    <ul class="tilegrid">
<?php foreach ($responses as $r): ?>
      <li>
        <a class="tile" href="/candidate-surveys/<?= e($r['slug']) ?>">
          <span class="tile__name"><?= e($r['name']) ?></span>
          <span class="tile__office"><?= e($r['office']) ?></span>
          <span class="tile__foot">
            <span class="label-mono">answered <?= e(strtolower(survey_time($r['submitted_at'], 'M j, Y'))) ?></span>
            <?= $arrow ?>
          </span>
        </a>
      </li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
  </div>
</section>

<?php else: ?>
<!-- ===================================================== one answer ==== -->
<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Candidate survey</p>
    <h1 class="display-3" data-reveal><?= e($response['name']) ?></h1>
    <p class="lede lede--lg"><?= e($response['office']) ?></p>
    <p class="label-mono mt-s">answered <?= e(strtolower(survey_time($response['submitted_at'], 'F j, Y'))) ?></p>
  </div>
</section>

<section class="section section--tight">
  <div class="shell">
    <h2 class="eyebrow"><?= e(SURVEY_PART1) ?></h2>
    <ol class="answers">
<?php foreach ($response['positions'] as $row): ?>
      <li>
        <p><?= e($row['question']) ?></p>
        <span class="badge"><?= e($row['answer']) ?></span>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section section--tight">
  <div class="shell">
    <h2 class="eyebrow"><?= e(SURVEY_PART2) ?></h2>
    <p class="note note--wide"><?= e($response['stance']['question']) ?></p>
    <div class="stance mt-m"><?= e($response['stance']['answer']) ?></div>
  </div>
</section>

<section class="section section--tight">
  <div class="shell">
    <h2 class="eyebrow"><?= e(SURVEY_SIGNATURE_HEADING) ?></h2>
    <p class="note note--wide"><?= e(SURVEY_SIGNATURE_TEXT) ?></p>
    <p class="signed mt-m"><?= e($response['signature']) ?></p>
    <p class="label-mono mt-s">signed <?= e(strtolower(survey_time($response['submitted_at'], 'F j, Y, g:i a T'))) ?></p>

    <div class="pill-row">
      <a class="pill pill--ghost" href="/candidate-surveys">Every candidate's answers</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
