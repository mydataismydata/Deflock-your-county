<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/survey.php';

// A new answer has to appear the moment it is submitted.
header('Cache-Control: no-cache');

/*
 * Three views. /candidate-surveys is the tiles, one per candidate who
 * answered. /candidate-surveys/jane-doe is one candidate's answers in full.
 * /candidate-surveys/questions is the questions alone, to read rather than
 * answer. survey_slug() never hands a candidate that name.
 *
 * The name is read from the address the browser asked for. On Ionos,
 * MultiViews hands /candidate-surveys/jane-doe straight to this file and the
 * rewrite rule in .htaccess never runs, so ?c= never arrives. Under Docker
 * and router.php the rewrite does run. The requested address is the same in
 * every case, and ?c=jane-doe still works on its own.
 */

$path     = (string) parse_url(survey_input($_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
$slug     = preg_match('~^/candidate-surveys(?:\.php)?/([^/]+)/?$~', $path, $m) === 1 ? $m[1] : survey_input($_GET, 'c');
$response  = null;
$questions = $slug === SURVEY_QUESTIONS_SLUG;

if ($slug !== '' && !$questions) {
    $response = preg_match('/^[a-z0-9-]{1,80}$/', $slug) === 1 ? survey_response_by_slug($slug) : null;

    if ($response === null) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }
}

$responses = $response === null && !$questions ? survey_responses() : [];

$page = 'surveys';

if ($questions) {
    $title = 'Candidate survey questions';
    $blurb = 'The ten questions ' . SITE_NAME . ' asks every candidate about mass surveillance.';
} elseif ($response === null) {
    $title = 'Candidate surveys';
    $blurb = 'What candidates told ' . SITE_NAME . ' about mass surveillance, published as they wrote it.';
} else {
    $seat  = $response['listing'];
    $title = $response['name'] . ', candidate survey';
    $blurb = 'The answers ' . $response['name'] . ', ' . $response['office']
           . ', gave to the ' . SITE_NAME . ' questionnaire on mass surveillance.';
}

$arrow = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>';

require __DIR__ . '/includes/header.php';
?>

<?php if ($questions): ?>
<!-- ===================================================== questions ==== -->
<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Candidate surveys</p>
    <h1 class="display-3" data-reveal>The questions.</h1>
    <p class="lede lede--lg">
      Every candidate gets the same ten. Questions 1 to 9 take a yes or a no.
      Question 10 takes the candidate's own words.
    </p>
  </div>
</section>

<section class="section section--tight">
  <div class="shell">
    <h2 class="eyebrow"><?= e(SURVEY_PART1) ?></h2>
    <ol class="answers">
<?php foreach (SURVEY_POSITIONS as $question): ?>
      <li><p><?= e($question) ?></p></li>
<?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section section--tight">
  <div class="shell">
    <h2 class="eyebrow"><?= e(SURVEY_PART2) ?></h2>
    <ol class="answers">
      <li><p><?= e(SURVEY_STANCE) ?></p></li>
    </ol>

    <div class="pill-row">
      <a class="pill pill--ghost" href="/candidate-surveys">Every candidate's answers</a>
    </div>
  </div>
</section>

<?php elseif ($response === null): ?>
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
    <p class="mt-m">
      <a class="arrow-link" href="/candidate-surveys/<?= SURVEY_QUESTIONS_SLUG ?>">Read the survey questions
        <?= $arrow ?>
      </a>
    </p>
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
<?php foreach ($responses as $r):
        $yes   = survey_yes_count($r);
        $asked = count($r['positions']);
        $seat  = $r['listing']; ?>
      <li>
        <a class="tile" href="/candidate-surveys/<?= e($r['slug']) ?>">
          <span class="tile__office"><?= e(survey_seat($seat)) ?></span>
          <span class="tile__name"><?= e($r['name']) ?></span>
<?php if ($seat['incumbent']): ?>
          <span class="tile__seat label-mono"><span class="incumbent">incumbent</span></span>
<?php endif; ?>
          <span class="badge tile__count">
            <span aria-hidden="true"><?= $yes ?>/<?= $asked ?> <?= e(SURVEY_CHOICES[0]) ?></span>
            <span class="visually-hidden"><?= e(SURVEY_CHOICES[0]) ?> to <?= $yes ?> of <?= $asked ?> questions</span>
          </span>
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
    <p class="eyebrow">
      <span class="dot" aria-hidden="true"></span>
      <span>
        Candidate survey &middot; <?= e(survey_seat($seat)) ?>
<?php if ($seat['incumbent']): ?>
        &middot; <span class="incumbent">Incumbent</span>
<?php endif; ?>
      </span>
    </p>
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
