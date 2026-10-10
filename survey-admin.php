<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/survey.php';

// Unlisted: nothing links here, and search engines are told to stay out.
// Nothing on this page is fit for a cache.
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

$errors  = [];
$created = null;   // the link just made, shown once: ['email', 'link', 'mailed']
$values  = ['login' => '', 'email' => '', 'subject' => '', 'body' => ''];

$post   = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$action = $post ? survey_input($_POST, 'action') : '';

// What was asked for, kept even when a check below refuses it, so the answer
// prints beside the button that was pressed.
$requested = $action;

// Redirect after POST, so a refresh repeats nothing.
$back = static function (string $query = ''): void {
    header('Location: /survey-admin' . ($query === '' ? '' : '?' . $query), true, 303);
    exit;
};

$writeFailed = 'The server could not write to the survey\'s data folder, so nothing '
             . 'was saved. Check that PHP can create ' . SURVEY_DATA_DIR . ' on the host.';

try {
    $admin = survey_current_admin();
} catch (RuntimeException $e) {
    $admin = null;
}

if ($post && FORM_REQUIRE_BROWSER_HEADERS && !request_from_own_form()) {
    $errors['form'] = 'That request did not come from this page. Reload it and try again.';
    $action = '';
}

/* ------------------------------------------------------------- sign-in -- */

if ($action === 'sign-in' && $admin === null) {
    $values['login'] = survey_input($_POST, 'login');
    $password        = survey_input($_POST, 'password');

    try {
        if (survey_login_blocked($values['login'])) {
            $errors['form'] = 'Too many failed attempts from here. Wait 15 minutes and try again.';
        } elseif (($account = survey_check_login($values['login'], $password)) === null) {
            survey_login_failed($values['login']);
            $errors['form'] = 'That username and password do not match an account.';
        } else {
            survey_login_cleared($values['login']);
            survey_sign_in($account);
            $back();
        }
    } catch (RuntimeException $e) {
        // Without the data folder there is no key to sign a cookie with.
        $errors['form'] = 'The server cannot write to the survey\'s data folder, so nobody can sign in yet.';
    }
}

/* ------------------------------------------------------------- actions -- */

if ($admin !== null && $action !== '') {
    try {
        if (!hash_equals(survey_csrf(), survey_input($_POST, 'csrf'))) {
            $errors['form'] = 'This page had gone stale. Reload it and try again.';
            $action = '';
        }

        $id     = survey_input($_POST, 'id');
        $invite = $id === '' ? null : survey_invite($id);

        switch ($action) {
            case 'sign-out':
                survey_sign_out();
                $back('signed-out=1');
                break;

            case 'save':
            case 'send':
                $values['email']   = trim(survey_input($_POST, 'email'));
                $values['subject'] = survey_line(survey_input($_POST, 'subject'));
                $values['body']    = survey_block(survey_input($_POST, 'body'));

                if ($values['subject'] === '') {
                    $errors['subject'] = 'The email needs a subject.';
                } elseif (survey_length($values['subject']) > 200) {
                    $errors['subject'] = 'That is longer than 200 characters.';
                }

                if ($values['body'] === '') {
                    $errors['body'] = 'The message is empty.';
                } elseif (survey_length($values['body']) > 10000) {
                    $errors['body'] = 'That is over the 10,000 character limit.';
                }

                if ($action === 'save') {
                    if ($errors === []) {
                        survey_draft_save($values['subject'], $values['body'], $admin);
                        $back('saved=1');
                    }
                    break;
                }

                if ($values['email'] === '') {
                    $errors['email'] = 'Enter the candidate\'s email address.';
                } elseif (!survey_is_email($values['email'])) {
                    $errors['email'] = 'That does not look like an email address.';
                }

                if ($errors === []) {
                    // Keep edited wording for the next invite. Wording left as it
                    // was is not saved, so it goes on following the defaults.
                    $draft = survey_draft();
                    if ($values['subject'] !== $draft['subject'] || $values['body'] !== $draft['body']) {
                        survey_draft_save($values['subject'], $values['body'], $admin);
                    }

                    [$invite, $token] = survey_invite_create($values['email'], $admin);
                    $created = [
                        'email'  => $invite['email'],
                        'link'   => survey_link($token),
                        'mailed' => survey_send_invite($invite, $token, $values['subject'], $values['body'], $admin),
                    ];
                    $values['email'] = '';
                }
                break;

            case 'reset':
                survey_draft_reset();
                $back('reset=1');
                break;

            case 'new-link':
                if ($invite !== null && survey_invite_status($invite) !== 'answered') {
                    $draft = survey_draft();
                    $token = survey_issue_link($invite);
                    $invite['withdrawn_at'] = null;
                    survey_invite_save($invite);
                    $created = [
                        'email'  => $invite['email'],
                        'link'   => survey_link($token),
                        'mailed' => survey_send_invite($invite, $token, $draft['subject'], $draft['body'], $admin),
                    ];
                }
                break;

            case 'withdraw':
                if ($invite !== null && survey_invite_status($invite) === 'waiting') {
                    $invite['withdrawn_at'] = gmdate('c');
                    survey_invite_save($invite);
                }
                $back('withdrawn=1');
                break;

            case 'hide':
            case 'show':
                survey_response_set_hidden($id, $action === 'hide');
                $back($action === 'hide' ? 'hidden=1' : 'shown=1');
                break;
        }
    } catch (RuntimeException $e) {
        $errors['form'] = $writeFailed;
    }
}

/* ---------------------------------------------------------------- view -- */

$notices = [
    'signed-out' => 'Signed out.',
    'saved'      => 'The message is saved. Every invite from now on starts from it.',
    'reset'      => 'The default subject and message are back.',
    'withdrawn'  => 'The invite is withdrawn. Its link no longer opens the survey.',
    'hidden'     => 'Those answers are off the public page. Nothing was deleted.',
    'shown'      => 'Those answers are back on the public page.',
];
$notice = null;
foreach ($notices as $flag => $text) {
    if (($_GET[$flag] ?? '') === '1') {
        $notice = $text;
    }
}

$rows   = [];
$counts = ['answered' => 0, 'waiting' => 0, 'withdrawn' => 0];
$draft  = ['subject' => SURVEY_INVITE_SUBJECT, 'body' => SURVEY_INVITE_BODY, 'saved' => false];

if ($admin !== null) {
    $draft = survey_draft();

    // A failed send keeps what was typed. Anything else starts from the draft.
    if ($values['subject'] === '' && $values['body'] === '') {
        $values['subject'] = $draft['subject'];
        $values['body']    = $draft['body'];
    }

    foreach (survey_invites() as $invite) {
        $status = survey_invite_status($invite);
        $counts[$status]++;
        $rows[] = [
            'invite'   => $invite,
            'status'   => $status,
            'response' => $status === 'answered' ? survey_response($invite['id']) : null,
        ];
    }
}

$date = static function (?string $iso): string {
    return $iso === null || $iso === '' ? '' : strtolower(survey_time($iso, 'M j, Y'));
};

// What the last action did. The list's own buttons return the reader to the
// list, so what they did prints there rather than above the invite form.
$feedbackAt = in_array($requested, ['new-link', 'withdraw', 'hide', 'show'], true) ? 'invites' : 'invite';
foreach (['withdrawn', 'hidden', 'shown'] as $flag) {
    if (($_GET[$flag] ?? '') === '1') {
        $feedbackAt = 'invites';
    }
}

$feedback = static function () use ($notice, $errors, $created): void { ?>
<?php if ($notice !== null): ?>
    <div class="notice notice--good" role="status"><p><?= e($notice) ?></p></div>
<?php endif; ?>

<?php if (isset($errors['form'])): ?>
    <div class="notice notice--bad" role="alert">
      <h2>Nothing changed</h2>
      <p><?= e($errors['form']) ?></p>
    </div>
<?php elseif ($errors !== []): ?>
    <div class="notice notice--bad" role="alert">
      <h2>Check the fields below</h2>
      <p>Nothing was sent or saved. Everything you typed is still here.</p>
    </div>
<?php endif; ?>

<?php if ($created !== null): ?>
    <div class="notice <?= $created['mailed'] ? 'notice--good' : 'notice--bad' ?>" role="status">
<?php if ($created['mailed']): ?>
      <h2>Sent to <?= e($created['email']) ?></h2>
      <p>
        This is the link in that email. It opens the survey for one set of
        answers. It is shown here once and is not kept on the server, so copy it
        now if you want to send it another way.
      </p>
<?php else: ?>
      <h2>The email did not go out</h2>
      <p>
        The server refused to send it. The invite exists and the link below
        works. Send it to <?= e($created['email']) ?> yourself. It is shown here
        once and is not kept on the server.
      </p>
<?php endif; ?>
      <div class="field mt-s">
        <label for="a-link" class="visually-hidden">Survey link for <?= e($created['email']) ?></label>
        <input id="a-link" type="text" readonly value="<?= e($created['link']) ?>">
      </div>
    </div>
<?php endif; ?>
<?php };

$page   = '';
$title  = 'Survey admin';
$hidden = true;

require __DIR__ . '/includes/header.php';
?>

<?php if ($admin === null): ?>
<!-- ======================================================= sign in ==== -->
<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Survey admin</p>
    <h1 class="display-3" data-reveal>Sign <span class="accent">in</span>.</h1>
  </div>
</section>

<section class="section section--tight">
  <div class="shell shell--narrow">

<?php if ($notice !== null): ?>
    <div class="notice notice--good" role="status"><p><?= e($notice) ?></p></div>
<?php endif; ?>

<?php if (survey_accounts() === []): ?>
    <div class="notice" role="status">
      <h2>No accounts yet</h2>
      <p>
        Create one on your own computer with
        <code>php tools/survey-admin.php add you@example.org</code>, then upload
        <code>includes/survey-admins.php</code>. The README has the details.
      </p>
    </div>
<?php endif; ?>

<?php if (isset($errors['form'])): ?>
    <div class="notice notice--bad" role="alert">
      <h2>Not signed in</h2>
      <p><?= e($errors['form']) ?></p>
    </div>
<?php endif; ?>

    <form class="panel panel--form panel--slim" method="post" action="/survey-admin" novalidate>
      <input type="hidden" name="action" value="sign-in">

      <div class="field">
        <label for="a-login">Username
          <span class="hint">Your email address.</span>
        </label>
        <input id="a-login" name="login" type="text" inputmode="email" required
               autocomplete="username" autocapitalize="none" spellcheck="false"
               value="<?= e($values['login']) ?>">
      </div>

      <div class="field">
        <label for="a-password">Password</label>
        <input id="a-password" name="password" type="password" required minlength="8"
               autocomplete="current-password">
      </div>

      <div class="pill-row">
        <button class="pill" type="submit">Sign in &rarr;</button>
      </div>
    </form>
  </div>
</section>

<?php else: ?>
<!-- ===================================================== dashboard ==== -->
<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Survey admin</p>
    <h1 class="display-3" data-reveal>Candidate <span class="accent">invites</span>.</h1>
    <p class="lede">
      Signed in as <?= e($admin['login']) ?>. Each invite emails one candidate a
      link that opens the questionnaire once. Their answers are published at
      <a class="plain-link" href="/candidate-surveys">/candidate-surveys</a>
      the moment they submit, and a copy goes to them and to whoever sent the
      invite.
    </p>
    <form class="pill-row" method="post" action="/survey-admin">
      <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">
      <button class="pill pill--ghost" type="submit" name="action" value="sign-out">Sign out</button>
    </form>
  </div>
</section>

<section class="section section--tight" id="invite">
  <div class="shell shell--narrow">

<?php if ($feedbackAt === 'invite') { $feedback(); } ?>

    <h2 class="h-section" data-reveal>Invite a candidate.</h2>

    <form class="panel panel--form mt-m" method="post" action="/survey-admin#invite" novalidate>
      <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">

      <div class="field<?= isset($errors['email']) ? ' field--bad' : '' ?>">
        <label for="a-email">Candidate's email</label>
        <input id="a-email" name="email" type="email" maxlength="254"
               autocomplete="off" value="<?= e($values['email']) ?>"
               <?= isset($errors['email']) ? 'aria-describedby="e-email" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['email'])): ?>
        <span class="field__error" id="e-email"><?= e($errors['email']) ?></span>
<?php endif; ?>
      </div>

      <div class="field<?= isset($errors['subject']) ? ' field--bad' : '' ?>">
        <label for="a-subject">Email subject</label>
        <input id="a-subject" name="subject" type="text" required maxlength="200"
               value="<?= e($values['subject']) ?>"
               <?= isset($errors['subject']) ? 'aria-describedby="e-subject" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['subject'])): ?>
        <span class="field__error" id="e-subject"><?= e($errors['subject']) ?></span>
<?php endif; ?>
      </div>

      <div class="field<?= isset($errors['body']) ? ' field--bad' : '' ?>">
        <label for="a-body">Message
          <span class="hint">The candidate's survey link is added on its own line after the last line.</span>
        </label>
        <textarea id="a-body" name="body" rows="12" required maxlength="10000"
                  <?= isset($errors['body']) ? 'aria-describedby="e-body" aria-invalid="true"' : '' ?>><?= e($values['body']) ?></textarea>
<?php if (isset($errors['body'])): ?>
        <span class="field__error" id="e-body"><?= e($errors['body']) ?></span>
<?php endif; ?>
      </div>

      <div class="pill-row">
        <button class="pill" type="submit" name="action" value="send">Send the invite &rarr;</button>
        <button class="pill pill--ghost" type="submit" name="action" value="save">Save the message</button>
      </div>

<?php if ($draft['saved']): ?>
      <p class="mt-s">
        <button class="accent-link textbutton" type="submit" name="action" value="reset">Put back the default subject and message</button>
      </p>
<?php endif; ?>
    </form>
  </div>
</section>

<section class="section section--tight" id="invites">
  <div class="shell">
<?php if ($feedbackAt === 'invites') { $feedback(); } ?>

    <h2 class="h-section" data-reveal>Invites.</h2>
    <p class="label-mono mt-s">
      <?= (int) $counts['answered'] ?> answered &middot;
      <?= (int) $counts['waiting'] ?> waiting &middot;
      <?= (int) $counts['withdrawn'] ?> withdrawn
    </p>

<?php if ($rows === []): ?>
    <p class="note mt-m">Nobody has been invited yet.</p>
<?php else: ?>
    <ul class="ledger mt-m">
<?php foreach ($rows as ['invite' => $invite, 'status' => $status, 'response' => $response]): ?>
      <li>
        <div>
          <p class="ledger__email"><?= e($invite['email']) ?></p>
<?php if ($response !== null): ?>
          <p class="ledger__who"><?= e($response['name']) ?>, <?= e($response['office']) ?></p>
<?php endif; ?>
          <p class="label-mono">
            invited <?= e($date($invite['invited_at'] ?? '')) ?> by <?= e((string) ($invite['invited_by'] ?? '')) ?>
<?php if ((int) ($invite['links_issued'] ?? 1) > 1): ?>
            &middot; link sent <?= (int) $invite['links_issued'] ?> times
<?php endif; ?>
<?php if ($status === 'answered' && $response !== null): ?>
            &middot; answered <?= e($date($response['submitted_at'] ?? '')) ?>
<?php elseif ($status === 'withdrawn'): ?>
            &middot; withdrawn <?= e($date($invite['withdrawn_at'] ?? '')) ?>
<?php endif; ?>
          </p>
        </div>

        <span class="badge"><?= e($status) ?><?= $response !== null && !empty($response['hidden']) ? ', hidden' : '' ?></span>

        <form class="ledger__actions" method="post" action="/survey-admin#invites">
          <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">
          <input type="hidden" name="id" value="<?= e($invite['id']) ?>">
<?php if ($status === 'answered' && $response !== null): ?>
<?php if (empty($response['hidden'])): ?>
          <a class="accent-link" href="/candidate-surveys/<?= e($response['slug']) ?>">Read</a>
          <button class="accent-link textbutton" type="submit" name="action" value="hide">Hide from the site</button>
<?php else: ?>
          <button class="accent-link textbutton" type="submit" name="action" value="show">Put back on the site</button>
<?php endif; ?>
<?php else: ?>
          <button class="accent-link textbutton" type="submit" name="action" value="new-link">Send a new link</button>
<?php if ($status === 'waiting'): ?>
          <button class="accent-link textbutton" type="submit" name="action" value="withdraw">Withdraw</button>
<?php endif; ?>
<?php endif; ?>
        </form>
      </li>
<?php endforeach; ?>
    </ul>
    <p class="note mt-m">
      A new link replaces the old one, so the old one stops working. It goes out
      with the saved message, not with anything unsaved in the form above.
    </p>
<?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
