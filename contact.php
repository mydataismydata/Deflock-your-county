<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/mailer.php';

$page  = 'contact';
$title = 'Contact';
$blurb = 'Reach ' . SITE_NAME . '. One form, one inbox, nothing stored on the server.';

const CONTACT_TOPICS = [
    'General question',
    'Camera sighting',
    'Records or documents',
    'Volunteering',
    'Press',
    'Correction to this site',
];

$errors = [];
$values = ['name' => '', 'email' => '', 'topic' => CONTACT_TOPICS[0], 'message' => ''];
$failed = false;

/* ------------------------------------------------------------- handler -- */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {

    $values['name']    = trim((string) ($_POST['name'] ?? ''));
    $values['email']   = trim((string) ($_POST['email'] ?? ''));
    $values['topic']   = (string) ($_POST['topic'] ?? '');
    $values['message'] = trim((string) ($_POST['message'] ?? ''));

    $trap  = trim((string) ($_POST['website'] ?? ''));
    $token = (string) ($_POST['t'] ?? '');
    $age   = form_token_age($token);

    if (form_secret_unset()) {
        $errors['form'] = 'This form is not finished being set up. Please email us directly instead.';
    }

    if ($trap !== '') {
        // A bot filled the hidden field. Fail quietly with the generic message.
        $errors['form'] = $errors['form'] ?? 'Your message could not be sent. Please try again.';
    }

    if ($age === null) {
        $errors['form'] = $errors['form'] ?? 'This form expired before it was submitted. Please send it again.';
    } elseif ($age < FORM_MIN_SECONDS) {
        $errors['form'] = $errors['form'] ?? 'That was submitted faster than a person can type. Please try again.';
    } elseif ($age > FORM_MAX_SECONDS) {
        $errors['form'] = $errors['form'] ?? 'This page had been open too long. Please send it again.';
    }

    $length = function (string $s): int {
        return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    };

    if ($values['name'] === '') {
        $errors['name'] = 'Tell us what to call you.';
    } elseif ($length($values['name']) > 120) {
        $errors['name'] = 'That is longer than 120 characters.';
    }

    if ($values['email'] === '') {
        $errors['email'] = 'We need an address to reply to.';
    } elseif (strlen($values['email']) > 254 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'That does not look like an email address.';
    }

    if (!in_array($values['topic'], CONTACT_TOPICS, true)) {
        $errors['topic'] = 'Pick one of the listed topics.';
    }

    if ($values['message'] === '') {
        $errors['message'] = 'The message is empty.';
    } elseif ($length($values['message']) < 20) {
        $errors['message'] = 'Give us a little more than that, at least 20 characters.';
    } elseif ($length($values['message']) > 5000) {
        $errors['message'] = 'That is over the 5,000 character limit. Send the short version and we will ask for the rest.';
    }

    if ($errors === []) {
        $ok = send_contact_message(
            $values['name'],
            $values['email'],
            $values['topic'],
            $values['message']
        );

        if ($ok) {
            // Redirect after POST so a refresh does not resend the message.
            header('Location: /contact?sent=1', true, 303);
            exit;
        }

        $failed = true;
    }
}

$sent = ($_GET['sent'] ?? '') === '1';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Contact</p>
    <h1 class="display-1" data-reveal>Send us <span class="accent">something</span>.</h1>
    <p class="lede lede--lg">
      Questions, corrections, a camera you spotted, a document an agency sent
      you. It all goes to one inbox read by people who live here.
    </p>
  </div>
</section>

<section class="section section--tight">
  <div class="shell split split--form">

    <div>
      <h2 class="h-section" data-reveal>One form, one inbox.</h2>
      <p class="lede lede--tight">Nothing is stored on the server.</p>

<?php if ($sent): ?>
      <div class="notice notice--good mt-l" role="status">
        <h2>Message sent</h2>
        <p>
          It is in the inbox. Someone will read it, though not necessarily
          today. If you asked a question and hear nothing within a week, send it
          again.
        </p>
      </div>
<?php endif; ?>

<?php if ($failed): ?>
      <div class="notice notice--bad mt-l" role="alert">
        <h2>The server could not send it</h2>
        <p>
          Nothing was lost. Your message is still in the form below. The mail
          service refused the handoff, which is a problem on our end rather than
          yours. Try once more in a few minutes.
        </p>
      </div>
<?php endif; ?>

<?php if (isset($errors['form'])): ?>
      <div class="notice notice--bad mt-l" role="alert">
        <h2>Not sent</h2>
        <p><?= e($errors['form']) ?></p>
      </div>
<?php elseif ($errors !== []): ?>
      <div class="notice notice--bad mt-l" role="alert">
        <h2>Check the fields below</h2>
        <p>The message was not sent. Everything you typed is still here.</p>
      </div>
<?php endif; ?>

      <div class="panel panel--form mt-l">
        <form method="post" action="/contact" novalidate>

          <!-- Spam trap. A person never sees this; a bot fills it in. -->
          <div class="trap" aria-hidden="true">
            <label for="f-website">Leave this field empty</label>
            <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off" value="">
          </div>

          <div class="field-pair">
            <div class="field<?= isset($errors['name']) ? ' field--bad' : '' ?>">
              <label for="f-name">Your name <span class="req" aria-hidden="true">*</span>
                <span class="hint">A first name is fine.</span>
              </label>
              <input id="f-name" name="name" type="text" required maxlength="120"
                     autocomplete="name" value="<?= e($values['name']) ?>"
                     <?= isset($errors['name']) ? 'aria-describedby="e-name" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['name'])): ?>
              <span class="field__error" id="e-name"><?= e($errors['name']) ?></span>
<?php endif; ?>
            </div>

            <div class="field<?= isset($errors['email']) ? ' field--bad' : '' ?>">
              <label for="f-email">Email <span class="req" aria-hidden="true">*</span>
                <span class="hint">Used to reply to you and for nothing else.</span>
              </label>
              <input id="f-email" name="email" type="email" required maxlength="254"
                     autocomplete="email" value="<?= e($values['email']) ?>"
                     <?= isset($errors['email']) ? 'aria-describedby="e-email" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['email'])): ?>
              <span class="field__error" id="e-email"><?= e($errors['email']) ?></span>
<?php endif; ?>
            </div>
          </div>

          <div class="field<?= isset($errors['topic']) ? ' field--bad' : '' ?>">
            <label for="f-topic">Topic</label>
            <select id="f-topic" name="topic">
<?php foreach (CONTACT_TOPICS as $topic): ?>
              <option value="<?= e($topic) ?>"<?= $topic === $values['topic'] ? ' selected' : '' ?>><?= e($topic) ?></option>
<?php endforeach; ?>
            </select>
<?php if (isset($errors['topic'])): ?>
            <span class="field__error"><?= e($errors['topic']) ?></span>
<?php endif; ?>
          </div>

          <div class="field<?= isset($errors['message']) ? ' field--bad' : '' ?>">
            <label for="f-message">Message <span class="req" aria-hidden="true">*</span>
              <span class="hint">For a camera sighting, the nearest cross streets and the direction it faces are the useful part.</span>
            </label>
            <textarea id="f-message" name="message" rows="6" required maxlength="5000"
                      <?= isset($errors['message']) ? 'aria-describedby="e-message" aria-invalid="true"' : '' ?>><?= e($values['message']) ?></textarea>
<?php if (isset($errors['message'])): ?>
            <span class="field__error" id="e-message"><?= e($errors['message']) ?></span>
<?php endif; ?>
          </div>

          <input type="hidden" name="t" value="<?= e(form_token()) ?>">

          <div class="pill-row">
            <button class="pill" type="submit">Send message &rarr;</button>
          </div>

          <p class="fine mt-s">
            We do not record your IP address or your browser. Your email
            provider and ours both see the message, which is true of any email.
          </p>
        </form>
      </div>
    </div>

    <aside class="sidenote">
      <div>
        <h3>What happens to what you send.</h3>
        <p>
          The form hands your message straight to the mail server and forgets
          it. There is no database behind this site and no copy kept on the web
          host.
        </p>
      </div>
      <div>
        <h3>Found a camera?</h3>
        <p>
          Add it to <a class="plain-link" href="https://deflock.org" rel="noopener">DeFlock</a>
          first, then tell us. The map entry is the part that lasts.
        </p>
      </div>
      <div>
        <h3>Got records back from an agency?</h3>
        <p>Say so in the first line. Those move to the front of the queue.</p>
      </div>
<?php if (LEGAL_HELP_URL !== ''): ?>
      <div>
        <h3>Not legal advice.</h3>
        <p>
          We are not lawyers and cannot advise you on a case. If you need legal
          help, the <a class="plain-link" href="<?= e(LEGAL_HELP_URL) ?>" rel="noopener"><?= e(LEGAL_HELP_NAME) ?></a>
          takes intake requests.
        </p>
      </div>
<?php endif; ?>
    </aside>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
