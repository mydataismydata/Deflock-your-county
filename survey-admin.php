<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/survey.php';

// Unlisted: nothing links here, and search engines are told to stay out.
// Nothing on this page is fit for a cache.
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

$errors  = [];
$created = null;   // the link just made, shown once: ['email', 'link', 'mailed']
$imported = null;  // what an import did: ['rows', 'updated', 'queued', 'problems']
$values  = ['login' => '', 'name' => '', 'email' => '', 'subject' => '', 'body' => ''];

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

// The queue in words, for the page and for the script that keeps it moving.
$queueView = static function (array $q): array {
    $n = $q['queued'] . ($q['queued'] === 1 ? ' invite' : ' invites') . ' queued.';

    if (!$q['running']) {
        $summary = $q['queued'] === 0 ? 'Nothing is queued.' : $n . ' Not sending.';
    } elseif ($q['paused']) {
        $summary = $n . ' The server refused the last one, so sending waits until '
                 . survey_time(gmdate('c', time() + $q['wait']), 'g:i a') . '.';
    } else {
        $minutes = $q['queued'] * SURVEY_QUEUE_INTERVAL / 60;
        $left    = $minutes < 60 ? round($minutes) . ' min' : floor($minutes / 60) . ' hr ' . round(fmod($minutes, 60)) . ' min';
        $summary = $n . ' Sending one every ' . SURVEY_QUEUE_INTERVAL . ' seconds. Next in ' . $q['wait'] . ' seconds. About ' . $left . ' to go.';
    }

    $last = '';
    $row  = null;

    if ($q['last'] !== null) {
        $last = ($q['last']['sent'] ? 'Last sent to ' : 'Last refused: ') . $q['last']['email']
              . ' at ' . survey_time((string) $q['last']['at'], 'g:i a') . '.';

        $invite = survey_invite((string) $q['last']['id']);
        if ($invite !== null) {
            $status = survey_invite_status($invite);
            $sent   = survey_sent_at($invite);
            $row    = [
                'id'     => $invite['id'],
                'status' => $status,
                'sent'   => $sent === '' ? '' : survey_time($sent, 'M j g:ia'),
            ];
        }
    }

    return ['running' => $q['running'], 'summary' => $summary, 'last' => $last, 'row' => $row];
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
                $values['name']    = survey_line(survey_input($_POST, 'name'));
                $values['email']   = trim(survey_input($_POST, 'email'));
                $values['subject'] = survey_line(survey_input($_POST, 'subject'));
                $values['body']    = survey_block(survey_input($_POST, 'body'));

                // A misspelled placeholder would reach the candidate as typed.
                $fills = 'This page fills in ' . SURVEY_FILL_NAME . ' and ' . SURVEY_FILL_LINK . '.';
                foreach (['subject' => [SURVEY_FILL_NAME], 'body' => [SURVEY_FILL_NAME, SURVEY_FILL_LINK]] as $field => $allowed) {
                    $unknown = survey_unknown_fills($values[$field], $allowed);
                    if ($unknown !== []) {
                        $errors[$field] = 'Nothing fills in ' . implode(' or ', $unknown) . '. '
                            . ($field === 'subject' ? 'The subject can use ' . SURVEY_FILL_NAME . ' only.' : $fills);
                    }
                }

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

                if (survey_length($values['name']) > 200) {
                    $errors['name'] = 'That is longer than 200 characters.';
                } elseif ($values['name'] === '' && survey_wants_name($values['subject'], $values['body'])) {
                    $errors['name'] = 'The email uses ' . SURVEY_FILL_NAME . ', so it needs the candidate\'s name.';
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

                    $invite  = survey_invite_create($values['email'], $values['name'], $admin);
                    $token   = survey_deliver($invite, $values['subject'], $values['body'], $admin);
                    $created = [
                        'email'  => $invite['email'],
                        'link'   => survey_link($token),
                        'mailed' => $invite['mail'] === 'sent',
                    ];
                    $values['name']  = '';
                    $values['email'] = '';
                }
                break;

            case 'reset':
                survey_draft_reset();
                $back('reset=1');
                break;

            case 'new-link':
                $draft = survey_draft();

                if ($invite !== null && ($invite['name'] ?? '') === '' && survey_wants_name($draft['subject'], $draft['body'])) {
                    $errors['form'] = 'This invite has no name on record, and the saved email uses '
                                    . SURVEY_FILL_NAME . '. Invite the candidate again from the form above, with their name.';
                    break;
                }

                if ($invite !== null && survey_invite_status($invite) !== 'answered') {
                    // Sent by hand, it leaves the queue whether or not the host
                    // takes it. A refusal marks it unsent rather than queued, so
                    // the queue cannot replace the link shown here.
                    $invite['withdrawn_at'] = null;
                    unset($invite['mail'], $invite['mail_attempts']);
                    $token   = survey_deliver($invite, $draft['subject'], $draft['body'], $admin);
                    $created = [
                        'email'  => $invite['email'],
                        'link'   => survey_link($token),
                        'mailed' => $invite['mail'] === 'sent',
                    ];
                }
                break;

            case 'withdraw':
                if ($invite !== null && in_array(survey_invite_status($invite), ['waiting', 'queued', 'unsent'], true)) {
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

            // Names, offices, parties and incumbents for many invites at once,
            // from a CSV file, and new addresses into the queue. Shown straight
            // back rather than redirected, so the list of rows it could not use
            // survives. Running it twice changes nothing the second time.
            case 'import':
                $file  = $_FILES['csv'] ?? null;
                $error = is_array($file) && is_int($file['error'] ?? null) ? $file['error'] : UPLOAD_ERR_NO_FILE;

                if ($error === UPLOAD_ERR_NO_FILE) {
                    $errors['form'] = 'Choose a CSV file first.';
                } elseif ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || ($file['size'] ?? 0) > 1000000) {
                    $errors['form'] = 'That file is over 1 MB. An export of every invite is a small fraction of that.';
                } elseif ($error !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
                    $errors['form'] = 'The file did not arrive. Try again.';
                } else {
                    try {
                        $imported = survey_import((string) file_get_contents($file['tmp_name']), $admin);
                    } catch (InvalidArgumentException $e) {
                        $errors['form'] = $e->getMessage();
                    }
                }
                break;

            // The name the email greets, and what the public pages list the
            // candidate under. The candidate's own answers are not touched.
            case 'listing':
                if ($invite === null) {
                    $back();
                }

                $name   = survey_line(survey_input($_POST, 'name'));
                $office = survey_line(survey_input($_POST, 'office'));
                $party  = survey_line(survey_input($_POST, 'party'));

                if (survey_length($name) > 200 || survey_length($office) > 200 || survey_length($party) > 100) {
                    $errors['form'] = 'The name and office can be up to 200 characters, and the party up to 100.';
                    break;
                }

                $invite['name']      = $name;
                $invite['office']    = $office;
                $invite['party']     = $party;
                $invite['incumbent'] = survey_input($_POST, 'incumbent') === '1';
                survey_invite_save($invite);
                $back('listed=' . $invite['id']);
                break;

            case 'queue-start':
            case 'queue-pause':
                survey_queue_run($action === 'queue-start');
                $back($action === 'queue-start' ? 'started=1' : 'paused=1');
                break;

            // The open page asks this every ten seconds while the queue runs.
            // It sends at most one invite, and only once the interval has passed.
            case 'queue-tick':
                header('Content-Type: application/json');
                echo json_encode($queueView(survey_queue_tick()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
        }
    } catch (RuntimeException $e) {
        $errors['form'] = $writeFailed;
    }
}

/* -------------------------------------------------------------- export -- */

// Every invite as a CSV file a spreadsheet opens. A plain GET link, since it
// only reads. It still needs the sign-in cookie.
if ($admin !== null && !$post && survey_input($_GET, 'export') === 'csv') {
    try {
        $csv = survey_invites_csv();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="candidate-invites-' . survey_time(gmdate('c'), 'Y-m-d') . '.csv"');
        echo $csv;
        exit;
    } catch (RuntimeException $e) {
        $errors['form'] = 'The server could not read the survey\'s data folder, so there is nothing to export.';
        $requested = 'export';
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
    'started'    => 'Sending has started. Keep this page open: queued invites go out only while it is.',
    'paused'     => 'Sending is paused. Nothing queued has been lost.',
];
$notice = null;
foreach ($notices as $flag => $text) {
    if (($_GET[$flag] ?? '') === '1') {
        $notice = $text;
    }
}

$rows    = [];
$offices = [];
$parties = [];
$counts  = ['answered' => 0, 'waiting' => 0, 'queued' => 0, 'unsent' => 0, 'withdrawn' => 0];
$draft   = survey_invite_defaults() + ['saved' => false];
$queue   = null;
$editing = null;

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

    // Offices and parties typed so far, offered back as suggestions so the
    // same seat is spelled the same way on every tile.
    $suggest = static function (string $key) use ($rows): array {
        $seen = array_unique(array_filter(array_map(
            static fn (array $row): string => (string) ($row['invite'][$key] ?? ''),
            $rows
        )));
        natcasesort($seen);

        return $seen;
    };
    $offices = $suggest('office');
    $parties = $suggest('party');

    $queue = survey_queue_report();

    // The invite opened for editing, from its email address in the table.
    $edit = survey_input($_GET, 'edit');
    foreach ($rows as $row) {
        if ($row['invite']['id'] === $edit) {
            $editing = $row;
        }
    }
}

if (survey_input($_GET, 'listed') !== '') {
    $notice = 'Saved.';
}

$date = static function (?string $iso, string $format = 'M j g:ia'): string {
    return $iso === null || $iso === '' ? '' : strtolower(survey_time($iso, $format));
};

// What the last action did. The list's own buttons return the reader to the
// list, so what they did prints there rather than above the invite form.
$feedbackAt = in_array($requested, ['new-link', 'withdraw', 'hide', 'show', 'listing', 'export', 'import', 'queue-start', 'queue-pause'], true) ? 'invites' : 'invite';
foreach (['withdrawn', 'hidden', 'shown', 'listed', 'started', 'paused'] as $flag) {
    if (survey_input($_GET, $flag) !== '') {
        $feedbackAt = 'invites';
    }
}

$feedback = static function () use ($notice, $errors, $created, $imported): void { ?>
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

<?php if ($imported !== null): ?>
    <div class="notice <?= $imported['problems'] === [] ? 'notice--good' : '' ?>" role="status">
      <h2>Imported</h2>
      <p>
        Read <?= $imported['rows'] ?> <?= $imported['rows'] === 1 ? 'row' : 'rows' ?> with an email address.
        Updated <?= $imported['updated'] ?> <?= $imported['updated'] === 1 ? 'invite' : 'invites' ?>.
        Queued <?= $imported['queued'] ?> new <?= $imported['queued'] === 1 ? 'invite' : 'invites' ?>.
<?php if ($imported['queued'] > 0): ?>
        Nothing has been emailed yet. Check the queued rows below, then press Start sending.
<?php endif; ?>
      </p>
<?php if ($imported['problems'] !== []): ?>
      <p class="mt-s">These rows were skipped:</p>
      <ul class="import-list">
<?php foreach ($imported['problems'] as $problem): ?>
        <li><?= e($problem) ?></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
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

$page    = '';
$title   = 'Survey admin';
$hidden  = true;
$scripts = $admin !== null ? ['/assets/js/survey-admin.js'] : [];

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

      <div class="field-pair">
      <div class="field<?= isset($errors['name']) ? ' field--bad' : '' ?>">
        <label for="a-name">Candidate's name
          <span class="hint">Fills in <?= e(SURVEY_FILL_NAME) ?>.</span>
        </label>
        <input id="a-name" name="name" type="text" maxlength="200"
               autocomplete="off" value="<?= e($values['name']) ?>"
               <?= isset($errors['name']) ? 'aria-describedby="e-name" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['name'])): ?>
        <span class="field__error" id="e-name"><?= e($errors['name']) ?></span>
<?php endif; ?>
      </div>

      <div class="field<?= isset($errors['email']) ? ' field--bad' : '' ?>">
        <label for="a-email">Candidate's email
          <span class="hint">The invite goes here.</span>
        </label>
        <input id="a-email" name="email" type="email" maxlength="254"
               autocomplete="off" value="<?= e($values['email']) ?>"
               <?= isset($errors['email']) ? 'aria-describedby="e-email" aria-invalid="true"' : '' ?>>
<?php if (isset($errors['email'])): ?>
        <span class="field__error" id="e-email"><?= e($errors['email']) ?></span>
<?php endif; ?>
      </div>
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
          <span class="hint">
            <?= e(SURVEY_FILL_LINK) ?> becomes the candidate's survey link, and
            <?= e(SURVEY_FILL_NAME) ?> their name. Without <?= e(SURVEY_FILL_LINK) ?>,
            the link goes on its own line at the end.
          </span>
        </label>
        <textarea id="a-body" name="body" rows="24" required maxlength="10000"
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
  <div class="shell shell--wide">
<?php if ($feedbackAt === 'invites') { $feedback(); } ?>

    <div class="invites-head">
      <h2 class="h-section" data-reveal>Invites.</h2>
      <div class="invites-head__tools">
<?php if ($rows !== []): ?>
        <a class="pill pill--ghost pill--small" href="/survey-admin?export=csv">Export CSV</a>
<?php endif; ?>
        <form class="import" method="post" action="/survey-admin#invites" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">
          <input type="hidden" name="action" value="import">
          <input id="a-csv" class="visually-hidden" name="csv" type="file" accept=".csv,text/csv" data-import>
          <label class="pill pill--ghost pill--small" for="a-csv"
                 title="A CSV file with an Email column. Known addresses are updated; new ones join the send queue.">Import CSV</label>
          <button class="pill pill--ghost pill--small" type="submit" data-import-submit>Import</button>
        </form>
      </div>
    </div>
    <p class="label-mono">
<?php foreach ($counts as $status => $count): ?>
      <?= (int) $count ?> <?= e($status) ?><?= $status === 'withdrawn' ? '' : ' &middot;' ?>
<?php endforeach; ?>
    </p>

<?php if ($editing !== null):
        ['invite' => $invite, 'status' => $status, 'response' => $response] = $editing;
        $i = $invite['id']; ?>
    <div class="panel panel--form mt-m" id="edit">
      <h3 class="h-block edit-title"><?= e($invite['email']) ?></h3>
      <p class="label-mono mt-s">
        <?= e($status) ?><?= $response !== null && !empty($response['hidden']) ? ', hidden' : '' ?>
        &middot; invited <?= e($date($invite['invited_at'] ?? '', 'M j, Y')) ?> by <?= e((string) ($invite['invited_by'] ?? '')) ?>
<?php if ((int) ($invite['links_issued'] ?? 1) > 1): ?>
        &middot; link sent <?= (int) $invite['links_issued'] ?> times
<?php endif; ?>
      </p>
<?php if (($invite['mail_error'] ?? '') === 'no name'): ?>
      <p class="note mt-s">
        The queue held this one back: it has no name, and the saved message uses
        <?= e(SURVEY_FILL_NAME) ?>. Add the name, save, then send a new link.
      </p>
<?php elseif ($status === 'unsent'): ?>
      <p class="note mt-s">The server refused this invite's email. Send a new link to try again.</p>
<?php endif; ?>
<?php if ($response !== null): ?>
      <p class="note mt-s">Typed by the candidate: <?= e($response['name']) ?>, <?= e($response['office']) ?></p>
<?php endif; ?>

      <form class="edit-form mt-m" method="post" action="/survey-admin#i-<?= e($i) ?>">
        <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">
        <input type="hidden" name="id" value="<?= e($i) ?>">

        <div class="field">
          <label for="l-name">Name
            <span class="hint">Fills in <?= e(SURVEY_FILL_NAME) ?>.</span>
          </label>
          <input id="l-name" name="name" type="text" maxlength="200" autocomplete="off"
                 value="<?= e((string) ($invite['name'] ?? '')) ?>">
        </div>

        <div class="field">
          <label for="l-office">Office
            <span class="hint">Shown on the tile. Blank shows what the candidate typed.</span>
          </label>
          <input id="l-office" name="office" type="text" maxlength="200" list="l-offices"
                 autocomplete="off" value="<?= e((string) ($invite['office'] ?? '')) ?>">
        </div>

        <div class="field">
          <label for="l-party">Party
            <span class="hint">Blank shows no party.</span>
          </label>
          <input id="l-party" name="party" type="text" maxlength="100" list="l-parties"
                 autocomplete="off" value="<?= e((string) ($invite['party'] ?? '')) ?>">
        </div>

        <label class="choice">
          <input type="checkbox" name="incumbent" value="1"<?= empty($invite['incumbent']) ? '' : ' checked' ?>>
          <span>Incumbent</span>
        </label>

        <button class="pill pill--ghost" type="submit" name="action" value="listing">Save</button>
      </form>

      <datalist id="l-offices">
<?php foreach ($offices as $office): ?>
        <option value="<?= e($office) ?>"></option>
<?php endforeach; ?>
      </datalist>
      <datalist id="l-parties">
<?php foreach ($parties as $party): ?>
        <option value="<?= e($party) ?>"></option>
<?php endforeach; ?>
      </datalist>

      <div class="edit-actions mt-m">
<?php if ($status === 'answered' && $response !== null): ?>
<?php if (empty($response['hidden'])): ?>
        <a class="accent-link" href="/candidate-surveys/<?= e($response['slug']) ?>">Read the answers</a>
<?php endif; ?>
        <form method="post" action="/survey-admin#i-<?= e($i) ?>">
          <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">
          <input type="hidden" name="id" value="<?= e($i) ?>">
<?php if (empty($response['hidden'])): ?>
          <button class="accent-link textbutton" type="submit" name="action" value="hide">Hide from the site</button>
<?php else: ?>
          <button class="accent-link textbutton" type="submit" name="action" value="show">Put back on the site</button>
<?php endif; ?>
        </form>
<?php else: ?>
        <form method="post" action="/survey-admin#invites">
          <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">
          <input type="hidden" name="id" value="<?= e($i) ?>">
          <button class="accent-link textbutton" type="submit" name="action" value="new-link"><?= $status === 'queued' ? 'Send it now' : 'Send a new link' ?></button>
        </form>
<?php if ($status !== 'withdrawn'): ?>
        <form method="post" action="/survey-admin#i-<?= e($i) ?>">
          <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">
          <input type="hidden" name="id" value="<?= e($i) ?>">
          <button class="accent-link textbutton" type="submit" name="action" value="withdraw"><?= $status === 'queued' ? 'Take out of the queue' : 'Withdraw' ?></button>
        </form>
<?php endif; ?>
<?php endif; ?>
        <a class="accent-link" href="/survey-admin#i-<?= e($i) ?>">Close</a>
      </div>
<?php if ($status !== 'answered'): ?>
      <p class="note mt-s">
        A new link replaces the old one, so the old one stops working. It goes
        out with the saved message, not with anything unsaved in the form above.
      </p>
<?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($queue !== null && ($queue['queued'] > 0 || $queue['running'])):
        $view = $queueView($queue); ?>
    <div class="queue mt-s" id="queue" data-queue data-csrf="<?= e(survey_csrf()) ?>" data-running="<?= $queue['running'] ? '1' : '0' ?>">
      <div class="queue__line">
        <p class="queue__summary" data-queue-summary><?= e($view['summary']) ?></p>
        <form method="post" action="/survey-admin#queue">
          <input type="hidden" name="csrf" value="<?= e(survey_csrf()) ?>">
<?php if ($queue['running']): ?>
          <button class="pill pill--ghost pill--small" type="submit" name="action" value="queue-pause">Pause sending</button>
<?php else: ?>
          <button class="pill pill--small" type="submit" name="action" value="queue-start">Start sending</button>
<?php endif; ?>
        </form>
      </div>
      <p class="label-mono queue__last" data-queue-last><?= e($view['last']) ?></p>
      <p class="queue__note">Sends the saved message. Keep this page open while it runs.</p>
    </div>
<?php endif; ?>

<?php if ($rows === []): ?>
    <p class="note mt-s">Nobody has been invited yet.</p>
<?php endif; ?>

<?php if ($rows !== []): ?>
    <div class="table-wrap mt-s">
      <table class="invites">
        <caption class="visually-hidden">Every invite, newest first. Select an email address to edit that invite.</caption>
        <thead>
          <tr>
            <th scope="col">Email</th>
            <th scope="col">Name</th>
            <th scope="col">Office</th>
            <th scope="col">Party</th>
            <th scope="col"><abbr title="Incumbent">Inc.</abbr></th>
            <th scope="col">Sent</th>
            <th scope="col">Responded</th>
            <th scope="col">Status</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($rows as ['invite' => $invite, 'status' => $status, 'response' => $response]):
        $i        = $invite['id'];
        $offSite  = $response !== null && !empty($response['hidden']); ?>
          <tr id="i-<?= e($i) ?>"<?= $editing !== null && $editing['invite']['id'] === $i ? ' class="is-editing"' : '' ?>>
            <td class="invites__email"><a class="invites__clip" href="/survey-admin?edit=<?= e($i) ?>#edit" title="<?= e($invite['email']) ?>"><?= e($invite['email']) ?></a></td>
<?php   $name  = (string) ($invite['name'] ?? '') ?: (string) ($response['name'] ?? '');
        $party = (string) ($invite['party'] ?? ''); ?>
            <td><span class="invites__clip" title="<?= e($name) ?>"><?= e($name) ?></span></td>
<?php   $office = (string) ($invite['office'] ?? ''); ?>
            <td><span class="invites__clip" title="<?= e($office) ?>"><?= e($office) ?></span></td>
            <td><span class="invites__clip invites__clip--short" title="<?= e($party) ?>"><?= e($party) ?></span></td>
            <td class="invites__inc"><?php if (!empty($invite['incumbent'])): ?><span aria-hidden="true">&check;</span><span class="visually-hidden">Incumbent</span><?php endif; ?></td>
            <td class="invites__date" data-cell="sent"><?= e($date(survey_sent_at($invite))) ?></td>
            <td class="invites__date"><?= e($date($response['submitted_at'] ?? '')) ?></td>
            <td data-cell="status">
<?php if ($status === 'answered' && !$offSite): ?>
              <a class="status status--answered" href="/candidate-surveys/<?= e($response['slug']) ?>">answered</a>
<?php else: ?>
<?php if ($offSite): ?>
              <span class="status status--withdrawn" title="Answered, and hidden from the site">hidden</span>
<?php else: ?>
              <span class="status status--<?= e($status) ?>"><?= e($status) ?></span>
<?php endif; ?>
<?php endif; ?>
            </td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
