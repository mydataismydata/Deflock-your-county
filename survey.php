<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/survey.php';

// A working link and a candidate's answers pass through this page. Neither
// belongs in a shared cache or a search index.
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

$token  = survey_input($_GET, 't');
$invite = $token === '' ? null : survey_invite_for_token($token);
$state  = $invite === null ? 'bad' : survey_invite_status($invite);

$errors = [];
$values = array_fill_keys(array_merge(['name', 'office', 'stance', 'signature'], array_keys(SURVEY_POSITIONS)), '');

/* ------------------------------------------------------------- handler -- */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $state === 'waiting') {

    $values['name']      = survey_line(survey_input($_POST, 'name'));
    $values['office']    = survey_line(survey_input($_POST, 'office'));
    $values['stance']    = survey_block(survey_input($_POST, 'stance'));
    $values['signature'] = survey_line(survey_input($_POST, 'signature'));

    foreach (array_keys(SURVEY_POSITIONS) as $key) {
        $values[$key] = survey_input($_POST, $key);
    }

    if (FORM_REQUIRE_BROWSER_HEADERS && !request_from_own_form()) {
        $errors['form'] = 'Your answers could not be submitted. Please try again.';
    }

    if ($values['name'] === '') {
        $errors['name'] = 'Print the candidate\'s full name.';
    } elseif (survey_length($values['name']) > 200) {
        $errors['name'] = 'That is longer than 200 characters.';
    }

    if ($values['office'] === '') {
        $errors['office'] = 'Name the office or branch.';
    } elseif (survey_length($values['office']) > 200) {
        $errors['office'] = 'That is longer than 200 characters.';
    }

    foreach (array_keys(SURVEY_POSITIONS) as $key) {
        if (!in_array($values[$key], SURVEY_CHOICES, true)) {
            $values[$key] = '';
            $errors[$key] = 'Choose Yes or No.';
        }
    }

    if ($values['stance'] === '') {
        $errors['stance'] = 'Question 10 has no answer yet.';
    } elseif (survey_length($values['stance']) > 20000) {
        $errors['stance'] = 'That is over the 20,000 character limit.';
    }

    if ($values['signature'] === '') {
        $errors['signature'] = 'Type your full name to sign.';
    } elseif (survey_length($values['signature']) > 200) {
        $errors['signature'] = 'That is longer than 200 characters.';
    }

    if ($errors === []) {
        try {
            $response = survey_response_create($invite, $values);
        } catch (RuntimeException $e) {
            $response = null;
            $errors['form'] = 'The server could not save your answers, so nothing was '
                            . 'published. Everything you entered is still here. Please try '
                            . 'again in a few minutes.';
        }

        if ($response !== null) {
            $sent = survey_send_copies($invite, $response);

            // Redirect after POST, so a refresh cannot submit twice. The link is
            // spent now, and the same address shows the receipt.
            header('Location: /survey?t=' . rawurlencode($token) . '&done=1'
                 . ($sent['candidate'] ? '' : '&copy=0'), true, 303);
            exit;
        }

        if (!isset($errors['form'])) {
            // Another submission on the same link won the race.
            $state = 'answered';
        }
    }
}

$answered = $state === 'answered' ? survey_response($invite['id']) : null;
$done     = ($_GET['done'] ?? '') === '1' && $answered !== null;
$noCopy   = ($_GET['copy'] ?? '') === '0';

$page   = '';
$title  = 'Candidate questionnaire';
$blurb  = SURVEY_TITLE;
$hidden = true;

require __DIR__ . '/includes/header.php';
?>

<?php if ($state === 'bad' || $state === 'withdrawn'): ?>
<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Candidate questionnaire</p>
<?php if ($state === 'bad'): ?>
    <h1 class="display-3" data-reveal>This link does not open a <span class="accent">survey</span>.</h1>
    <p class="lede lede--lg">
      It may be missing a few characters from the end, or it may have been
      replaced by a newer link. Ask whoever sent it for a new one, or write to
      us through the contact page.
    </p>
<?php else: ?>
    <h1 class="display-3" data-reveal>This invitation was <span class="accent">withdrawn</span>.</h1>
    <p class="lede lede--lg">
      If you still want to answer the questionnaire, write to us through the
      contact page and we will send a new link.
    </p>
<?php endif; ?>
    <div class="pill-row">
      <a class="pill" href="/contact">Contact us &rarr;</a>
      <a class="pill pill--ghost" href="/candidate-surveys">Published answers</a>
    </div>
  </div>
</section>

<?php elseif ($state === 'answered'): ?>
<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Candidate questionnaire</p>
<?php if ($done): ?>
    <h1 class="display-3" data-reveal>Your answers are <span class="accent">in</span>.</h1>
    <p class="lede lede--lg">
      Thank you, <?= e($answered['name']) ?>.
<?php if ($noCopy): ?>
      The server could not email you a copy. The published page below holds
      everything you submitted, word for word.
<?php else: ?>
      A copy is on its way to <?= e($invite['email']) ?>.
<?php endif; ?>
    </p>
<?php else: ?>
    <h1 class="display-3" data-reveal>This survey has been <span class="accent">answered</span>.</h1>
    <p class="lede lede--lg">
      Answers from <?= e($answered['name'] ?? 'this candidate') ?> arrived on
      <?= e(survey_time((string) ($answered['submitted_at'] ?? ''), 'F j, Y')) ?>.
      Each link takes one set of answers.
    </p>
<?php endif; ?>
    <div class="pill-row">
<?php if ($answered !== null && empty($answered['hidden'])): ?>
      <a class="pill" href="/candidate-surveys/<?= e($answered['slug']) ?>">See the published answers &rarr;</a>
<?php endif; ?>
      <a class="pill pill--ghost" href="/candidate-surveys">Every candidate's answers</a>
    </div>
  </div>
</section>

<?php else: ?>
<!-- ========================================================== memo ==== -->
<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> <?= e(SURVEY_KIND) ?> &middot; <?= e(STATE) ?></p>
    <h1 class="display-3" data-reveal><?= e(SURVEY_TOPIC) ?></h1>

    <dl class="memo mt-m">
<?php foreach (SURVEY_MEMO as $label => $value): ?>
      <div>
        <dt><?= e($label) ?></dt>
        <dd><?= e($value) ?></dd>
      </div>
<?php endforeach; ?>
    </dl>

    <h2 class="h-block mt-l"><?= e(SURVEY_RULES_HEADING) ?></h2>
    <ul class="bullets">
<?php foreach (SURVEY_RULES as [$rule, $text]): ?>
      <li><span><strong><?= e($rule) ?>:</strong> <?= e($text) ?></span></li>
<?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- ========================================================== form ==== -->
<section class="section section--tight" id="answers">
  <div class="shell shell--narrow">

<?php if (isset($errors['form'])): ?>
    <div class="notice notice--bad" role="alert">
      <h2>Not submitted</h2>
      <p><?= e($errors['form']) ?></p>
    </div>
<?php elseif ($errors !== []): ?>
    <div class="notice notice--bad" role="alert">
      <h2>Check the answers marked below</h2>
      <p>Nothing was submitted. Everything you entered is still here.</p>
    </div>
<?php endif; ?>

    <form class="panel panel--form" method="post" action="/survey?t=<?= e(rawurlencode($token)) ?>#answers" novalidate>

      <div class="field-pair">
        <div class="field<?= isset($errors['name']) ? ' field--bad' : '' ?>">
          <label for="s-name"><?= e(SURVEY_NAME_LABEL) ?> <span class="req" aria-hidden="true">*</span></label>
          <input id="s-name" name="name" type="text" required maxlength="200"
                 autocomplete="name" value="<?= e($values['name']) ?>"
                 <?= isset($errors['name']) ? 'aria-describedby="e-name" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['name'])): ?>
          <span class="field__error" id="e-name"><?= e($errors['name']) ?></span>
<?php endif; ?>
        </div>

        <div class="field<?= isset($errors['office']) ? ' field--bad' : '' ?>">
          <label for="s-office"><?= e(SURVEY_OFFICE_LABEL) ?> <span class="req" aria-hidden="true">*</span></label>
          <input id="s-office" name="office" type="text" required maxlength="200"
                 value="<?= e($values['office']) ?>"
                 <?= isset($errors['office']) ? 'aria-describedby="e-office" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['office'])): ?>
          <span class="field__error" id="e-office"><?= e($errors['office']) ?></span>
<?php endif; ?>
        </div>
      </div>

      <h2 class="eyebrow mt-m"><?= e(SURVEY_PART1) ?> <?= e(SURVEY_PART1_HINT) ?></h2>

<?php foreach (SURVEY_POSITIONS as $key => $question): ?>
      <fieldset class="yesno<?= isset($errors[$key]) ? ' yesno--bad' : '' ?>"
                <?= isset($errors[$key]) ? 'aria-describedby="e-' . e($key) . '"' : '' ?>>
        <legend><?= e($question) ?> <span class="req" aria-hidden="true">*</span></legend>
        <div class="yesno__choices">
<?php foreach (SURVEY_CHOICES as $choice): ?>
          <label class="choice">
            <input type="radio" name="<?= e($key) ?>" value="<?= e($choice) ?>" required<?= $values[$key] === $choice ? ' checked' : '' ?>>
            <span><?= e($choice) ?></span>
          </label>
<?php endforeach; ?>
        </div>
<?php if (isset($errors[$key])): ?>
        <span class="field__error" id="e-<?= e($key) ?>"><?= e($errors[$key]) ?></span>
<?php endif; ?>
      </fieldset>
<?php endforeach; ?>

      <h2 class="eyebrow mt-m"><?= e(SURVEY_PART2) ?></h2>

      <div class="field<?= isset($errors['stance']) ? ' field--bad' : '' ?>">
        <label for="s-stance"><?= e(SURVEY_STANCE) ?> <span class="req" aria-hidden="true">*</span></label>
        <textarea id="s-stance" name="stance" rows="12" required maxlength="20000"
                  <?= isset($errors['stance']) ? 'aria-describedby="e-stance" aria-invalid="true"' : '' ?>><?= e($values['stance']) ?></textarea>
<?php if (isset($errors['stance'])): ?>
        <span class="field__error" id="e-stance"><?= e($errors['stance']) ?></span>
<?php endif; ?>
      </div>

      <h2 class="eyebrow mt-m"><?= e(SURVEY_SIGNATURE_HEADING) ?></h2>

      <div class="field<?= isset($errors['signature']) ? ' field--bad' : '' ?>">
        <label for="s-signature"><?= e(SURVEY_SIGNATURE_TEXT) ?> <span class="req" aria-hidden="true">*</span></label>
        <input id="s-signature" name="signature" type="text" required maxlength="200"
               value="<?= e($values['signature']) ?>"
               <?= isset($errors['signature']) ? 'aria-describedby="e-signature" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['signature'])): ?>
        <span class="field__error" id="e-signature"><?= e($errors['signature']) ?></span>
<?php endif; ?>
      </div>

      <p class="fine">
        This link takes one set of answers. When you submit, a copy goes to
        <?= e($invite['email']) ?> and the answers are published on this site.
      </p>

      <div class="pill-row">
        <button class="pill" type="submit">Submit answers &rarr;</button>
      </div>
    </form>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
