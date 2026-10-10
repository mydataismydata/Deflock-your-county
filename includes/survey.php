<?php
/**
 * The candidate survey: where invites and answers are kept, the one-use links,
 * the sign-in for /survey-admin, and the mail each step sends.
 *
 * This is the one part of the site that writes to disk, and it writes only
 * under SURVEY_DATA_DIR. Each record is one small file:
 *
 *   invites/<id>.php     who was invited, by whom, and the hash of their link
 *   responses/<id>.php   what they answered, under the same id as the invite
 *   throttle/<hash>.php  recent failed sign-ins, keyed by a hash, never an IP
 *   draft.php            the admin's current wording of the invite email
 *   key.php              the random key that signs an admin's sign-in cookie
 *
 * Every file starts with a line of PHP that answers 404 and exits, and JSON
 * follows it. A host that ignores the directory's .htaccess still serves
 * nothing from it.
 *
 * Nothing here comes from FORM_SECRET. The deploy script mints a new form
 * secret for every bundle, and anything signed with it would stop working
 * after each upload: every link already emailed, and every admin's sign-in.
 */

declare(strict_types=1);

require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/survey-text.php';

/* ------------------------------------------------------------- storage -- */

// The first line of every stored file.
const SURVEY_FILE_GUARD = "<?php http_response_code(404); exit; ?>\n";

/** A path inside the data directory. Creates nothing. */
function survey_path(string $name = ''): string
{
    return rtrim(SURVEY_DATA_DIR, '/') . ($name === '' ? '' : '/' . $name);
}

/**
 * Create the data directory, its three subdirectories and the .htaccess that
 * refuses web access to all of it. Throws when the host will not let PHP write
 * there.
 *
 * Only writes call this. A page that merely reads finds nothing and says so,
 * rather than creating folders as a side effect.
 */
function survey_prepare(): void
{
    static $ready = false;

    if ($ready) {
        return;
    }

    foreach (['', 'invites', 'responses', 'throttle'] as $sub) {
        $dir = survey_path($sub);

        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create ' . $dir);
        }
    }

    // The same rules as includes/.htaccess. Written by PHP rather than shipped,
    // because a deploy that does not carry this directory would not carry its
    // .htaccess either.
    $deny = survey_path('.htaccess');

    if (!is_file($deny)) {
        @file_put_contents($deny, "# Written by includes/survey.php. Nothing here is for the web.\n"
            . "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n"
            . "<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n");
    }

    $ready = true;
}

/** One stored record, or null if the file is missing or unreadable. */
function survey_read(string $file): ?array
{
    $raw = @file_get_contents($file);

    if ($raw === false || !str_starts_with($raw, SURVEY_FILE_GUARD)) {
        return null;
    }

    $data = json_decode(substr($raw, strlen(SURVEY_FILE_GUARD)), true);

    return is_array($data) ? $data : null;
}

function survey_encode(array $data): string
{
    try {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        // The pages catch RuntimeException as "could not save". This is that.
        throw new RuntimeException('Cannot encode a record: ' . $e->getMessage(), 0, $e);
    }

    return SURVEY_FILE_GUARD . $json . "\n";
}

/**
 * Replace a record in one step. The new copy is written beside the old one
 * and renamed over it, so a reader sees one or the other and never half of
 * each. The temporary name ends in .php too, so it gets the same guard.
 */
function survey_write(string $file, array $data): void
{
    survey_prepare();

    $tmp = dirname($file) . '/tmp-' . bin2hex(random_bytes(6)) . '.php';

    if (@file_put_contents($tmp, survey_encode($data), LOCK_EX) === false || !@rename($tmp, $file)) {
        @unlink($tmp);
        throw new RuntimeException('Cannot write ' . $file);
    }
}

/**
 * Write a record that must not exist yet. Returns false if it already does.
 *
 * The 'x' mode asks the filesystem to create the file only if it is absent,
 * in one operation. Two submissions racing on the same link cannot both get
 * through it, which is what makes a link good for exactly one set of answers.
 */
function survey_create(string $file, array $data): bool
{
    survey_prepare();

    $handle = @fopen($file, 'x');

    if ($handle === false) {
        if (file_exists($file)) {
            return false;
        }
        throw new RuntimeException('Cannot create ' . $file);
    }

    fwrite($handle, survey_encode($data));
    fclose($handle);

    return true;
}

/** Every record in one subdirectory, skipping temporary files. */
function survey_read_all(string $sub): array
{
    $records = [];

    foreach (glob(survey_path($sub) . '/*.php') ?: [] as $file) {
        if (preg_match('~/[0-9a-f]{16}\.php$~', $file) !== 1) {
            continue;
        }

        $record = survey_read($file);

        if ($record !== null) {
            $records[] = $record;
        }
    }

    return $records;
}

/**
 * The random key that signs sign-in cookies and names throttle files. Made
 * once, on first use, and kept in the data directory so it survives deploys.
 */
function survey_key(): string
{
    static $key = null;

    if ($key !== null) {
        return $key;
    }

    $file = survey_path('key.php');
    $data = survey_read($file);

    if ($data === null) {
        survey_create($file, ['key' => bin2hex(random_bytes(32))]);
        @chmod($file, 0600);
        $data = survey_read($file);
    }

    if (!is_string($data['key'] ?? null) || strlen($data['key']) !== 64) {
        throw new RuntimeException('The survey key in ' . $file . ' is unreadable.');
    }

    return $key = $data['key'];
}

/* -------------------------------------------------------------- helpers -- */

/**
 * One request field as a string. A field sent as name[]=x arrives as an
 * array, and casting that would print a warning and store "Array".
 */
function survey_input(array $source, string $key): string
{
    $value = $source[$key] ?? '';

    return is_string($value) ? $value : '';
}

function survey_new_id(): string
{
    return bin2hex(random_bytes(8));
}

function survey_valid_id(string $id): bool
{
    return preg_match('/^[0-9a-f]{16}$/', $id) === 1;
}

function survey_base64url(string $bytes): string
{
    return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
}

/** A stored UTC timestamp, shown on the county's clock. */
function survey_time(string $iso, string $format): string
{
    try {
        return (new DateTimeImmutable($iso))->setTimezone(new DateTimeZone(TIMEZONE))->format($format);
    } catch (Exception $e) {
        return $iso;
    }
}

function survey_length(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

/** One line of text: trimmed, with line breaks and control characters as spaces. */
function survey_line(string $s): string
{
    return trim((string) preg_replace('/[\p{Cc}\x{2028}\x{2029}]+/u', ' ', $s));
}

/** A block of text: Windows line endings made plain, ends trimmed. */
function survey_block(string $s): string
{
    $s = str_replace(["\r\n", "\r"], "\n", $s);

    return trim((string) preg_replace('/[^\P{Cc}\n\t]/u', '', $s));
}

/** True for a well-formed email address, by PHP's own test. */
function survey_is_email(string $s): bool
{
    return strlen($s) <= 254 && filter_var($s, FILTER_VALIDATE_EMAIL) !== false;
}

/* --------------------------------------------------------------- invites -- */

function survey_invite_file(string $id): string
{
    return survey_path('invites/' . $id . '.php');
}

function survey_invite(string $id): ?array
{
    return survey_valid_id($id) ? survey_read(survey_invite_file($id)) : null;
}

/**
 * Give an invite a new link and return it. The invite keeps only a hash of
 * the secret half, so the working link exists in the email and on the admin's
 * screen and nowhere on the server. Any earlier link stops working.
 */
function survey_issue_link(array &$invite): string
{
    $secret = survey_base64url(random_bytes(24));

    $invite['token_hash']     = hash('sha256', $secret);
    $invite['links_issued']   = (int) ($invite['links_issued'] ?? 0) + 1;
    $invite['link_issued_at'] = gmdate('c');

    return $invite['id'] . '.' . $secret;
}

/**
 * Record a new invite. Returns the invite and its link.
 *
 * @return array{0: array, 1: string}
 */
function survey_invite_create(string $email, array $admin): array
{
    $invite = [
        'id'           => survey_new_id(),
        'email'        => $email,
        'invited_at'   => gmdate('c'),
        'invited_by'   => $admin['login'],
        'withdrawn_at' => null,
    ];

    $token = survey_issue_link($invite);
    survey_write(survey_invite_file($invite['id']), $invite);

    return [$invite, $token];
}

function survey_invite_save(array $invite): void
{
    survey_write(survey_invite_file($invite['id']), $invite);
}

/** The invite a link belongs to. Null for a link we never issued or have since replaced. */
function survey_invite_for_token(string $token): ?array
{
    $parts = explode('.', $token, 2);

    if (count($parts) !== 2) {
        return null;
    }

    $invite = survey_invite($parts[0]);

    if ($invite === null || !is_string($invite['token_hash'] ?? null)) {
        return null;
    }

    return hash_equals($invite['token_hash'], hash('sha256', $parts[1])) ? $invite : null;
}

/**
 * 'answered', 'withdrawn' or 'waiting'. An invite is answered when a response
 * file exists under its id. Nothing else records it, so the two cannot
 * disagree.
 */
function survey_invite_status(array $invite): string
{
    if (is_file(survey_response_file($invite['id']))) {
        return 'answered';
    }

    return empty($invite['withdrawn_at']) ? 'waiting' : 'withdrawn';
}

/** Every invite, newest first. */
function survey_invites(): array
{
    $invites = survey_read_all('invites');

    usort($invites, static function (array $a, array $b): int {
        return strcmp((string) ($b['invited_at'] ?? ''), (string) ($a['invited_at'] ?? ''));
    });

    return $invites;
}

/* ------------------------------------------------------------- responses -- */

function survey_response_file(string $id): string
{
    return survey_path('responses/' . $id . '.php');
}

function survey_response(string $id): ?array
{
    return survey_valid_id($id) ? survey_read(survey_response_file($id)) : null;
}

/**
 * Every response, grouped by office and then by name, so candidates for the
 * same seat sit side by side. Hidden ones only when asked for.
 */
function survey_responses(bool $withHidden = false): array
{
    $responses = array_values(array_filter(survey_read_all('responses'), static function (array $r) use ($withHidden): bool {
        return $withHidden || empty($r['hidden']);
    }));

    usort($responses, static function (array $a, array $b): int {
        return strnatcasecmp((string) $a['office'], (string) $b['office'])
            ?: strnatcasecmp((string) $a['name'], (string) $b['name']);
    });

    return $responses;
}

function survey_response_by_slug(string $slug): ?array
{
    foreach (survey_responses() as $response) {
        if (($response['slug'] ?? '') === $slug) {
            return $response;
        }
    }

    return null;
}

/** The address of a response's public page, from a name: "Jane Doe" becomes jane-doe. */
function survey_slug(string $name, array $taken): string
{
    if (function_exists('transliterator_transliterate')) {
        $name = (string) transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
    }

    $base = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
    $base = substr($base, 0, 60) ?: 'candidate';
    $base = rtrim($base, '-');

    $slug = $base;
    for ($n = 2; in_array($slug, $taken, true); $n++) {
        $slug = $base . '-' . $n;
    }

    return $slug;
}

/**
 * Store a candidate's answers against their invite. Returns the stored
 * response, or null if that invite already has one.
 *
 * The questions are stored beside the answers, word for word, so a later edit
 * to survey-text.php cannot change what this candidate was asked.
 */
function survey_response_create(array $invite, array $answers): ?array
{
    $positions = [];
    foreach (SURVEY_POSITIONS as $key => $question) {
        $positions[] = ['key' => $key, 'question' => $question, 'answer' => $answers[$key]];
    }

    $response = [
        'id'           => $invite['id'],
        'slug'         => survey_slug($answers['name'], array_column(survey_responses(true), 'slug')),
        'submitted_at' => gmdate('c'),
        'email'        => $invite['email'],
        'name'         => $answers['name'],
        'office'       => $answers['office'],
        'positions'    => $positions,
        'stance'       => ['question' => SURVEY_STANCE, 'answer' => $answers['stance']],
        'signature'    => $answers['signature'],
        'hidden'       => false,
    ];

    return survey_create(survey_response_file($invite['id']), $response) ? $response : null;
}

function survey_response_set_hidden(string $id, bool $hidden): void
{
    $response = survey_response($id);

    if ($response !== null) {
        $response['hidden'] = $hidden;
        survey_write(survey_response_file($id), $response);
    }
}

/* ----------------------------------------------------------------- draft -- */

/** The invite email's subject and message as the admin last saved them, or the defaults. */
function survey_draft(): array
{
    $saved = survey_read(survey_path('draft.php'));

    return [
        'subject' => is_string($saved['subject'] ?? null) ? $saved['subject'] : SURVEY_INVITE_SUBJECT,
        'body'    => is_string($saved['body'] ?? null) ? $saved['body'] : SURVEY_INVITE_BODY,
        'saved'   => $saved !== null,
    ];
}

function survey_draft_save(string $subject, string $body, array $admin): void
{
    survey_write(survey_path('draft.php'), [
        'subject'  => $subject,
        'body'     => $body,
        'saved_at' => gmdate('c'),
        'saved_by' => $admin['login'],
    ]);
}

function survey_draft_reset(): void
{
    @unlink(survey_path('draft.php'));
}

/* -------------------------------------------------------------- accounts -- */

/**
 * The form of a username that accounts are filed and matched under: spaces
 * trimmed from the ends, and capitals folded, so "Jo@Example.org " signs in as
 * "jo@example.org".
 */
function survey_login_key(string $login): string
{
    $login = trim($login);

    return function_exists('mb_convert_case') ? mb_convert_case($login, MB_CASE_FOLD, 'UTF-8') : strtolower($login);
}

/**
 * Why a username cannot be used, or null if it can. Any character is allowed
 * except the ones that cannot be stored or typed back in.
 */
function survey_login_problem(string $login): ?string
{
    $login = trim($login);

    if ($login === '') {
        return 'The username is empty.';
    }
    if (preg_match('//u', $login) !== 1) {
        return 'The username is not valid UTF-8.';
    }
    if (preg_match('/\p{Cc}/u', $login) === 1) {
        return 'The username contains a control character.';
    }

    return null;
}

/** Why a password cannot be used, or null if it can. Length is the only rule. */
function survey_password_problem(string $password): ?string
{
    return survey_length($password) < 8 ? 'The password needs 8 characters or more.' : null;
}

/**
 * bcrypt reads only the first 72 bytes of a password and stops at a zero byte.
 * Hashing the password first means every character of a long one counts.
 */
function survey_password_prehash(string $password): string
{
    return base64_encode(hash('sha256', $password, true));
}

function survey_password_hash(string $password): string
{
    return password_hash(survey_password_prehash($password), PASSWORD_DEFAULT);
}

/**
 * The accounts, keyed by survey_login_key(). Each holds the username as it was
 * typed, which is also where the admin copy of an answer is sent, and the hash.
 *
 * @return array<string, array{login: string, hash: string}>
 */
function survey_accounts(): array
{
    if (!is_file(SURVEY_ADMINS_FILE)) {
        return [];
    }

    $accounts = include SURVEY_ADMINS_FILE;

    return is_array($accounts) ? $accounts : [];
}

/** Write the accounts file. Only tools/survey-admin.php calls this. */
function survey_accounts_save(array $accounts): void
{
    ksort($accounts);

    $php = "<?php\n"
         . "// Accounts for /survey-admin. Written by tools/survey-admin.php: change\n"
         . "// them with that rather than by hand. Git ignores this file because the\n"
         . "// repository is public. Upload it with the rest of includes/.\n\n"
         . 'return ' . var_export($accounts, true) . ";\n";

    $tmp = SURVEY_ADMINS_FILE . '.' . bin2hex(random_bytes(4)) . '.tmp';

    if (@file_put_contents($tmp, $php, LOCK_EX) === false || !@rename($tmp, SURVEY_ADMINS_FILE)) {
        @unlink($tmp);
        throw new RuntimeException('Cannot write ' . SURVEY_ADMINS_FILE);
    }
}

// A hash of a random string nobody kept. A username with no account is
// checked against it, so it takes as long to refuse as a wrong password.
const SURVEY_NOBODY_HASH = '$2y$10$cQCZEwkDswmDFRErw9NrOel8p/K/nJG7dRSQdIakUDhnuAK.xOPwO';

/** The account a username and password open, or null. */
function survey_check_login(string $login, string $password): ?array
{
    $key     = survey_login_key($login);
    $account = survey_accounts()[$key] ?? null;
    $hash    = is_array($account) ? (string) ($account['hash'] ?? '') : SURVEY_NOBODY_HASH;

    $ok = password_verify(survey_password_prehash($password), $hash);

    return $ok && is_array($account) ? ['key' => $key] + $account : null;
}

/* -------------------------------------------------------------- sign-in -- */

function survey_https(): bool
{
    return ($_SERVER['HTTPS'] ?? '') === 'on'
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

/**
 * The browser refuses a __Host- cookie unless it is Secure, set for the whole
 * site and tied to this exact host. Plain http only happens on a development
 * machine, where the browser would refuse the prefix, so it goes without.
 */
function survey_cookie_name(): string
{
    return survey_https() ? '__Host-survey-admin' : 'survey-admin';
}

/**
 * The cookie value for one signed-in admin: who, until when, and a signature.
 * The account's password hash is part of what gets signed, so changing a
 * password signs that account out everywhere.
 */
function survey_session_value(string $key, array $account, int $expires): string
{
    $mac = hash_hmac('sha256', $key . '|' . $expires . '|' . $account['hash'], survey_key());

    return survey_base64url($key) . '.' . $expires . '.' . $mac;
}

function survey_set_cookie(string $value, int $expires): void
{
    setcookie(survey_cookie_name(), $value, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => survey_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Sign an admin in. This cookie is the only one the site ever sets, and only
 * someone who signs in to /survey-admin receives it.
 */
function survey_sign_in(array $account): void
{
    $expires = time() + SURVEY_SESSION_HOURS * 3600;
    survey_set_cookie(survey_session_value($account['key'], $account, $expires), $expires);
}

function survey_sign_out(): void
{
    survey_set_cookie('', 1);
}

/** The signed-in admin, or null. */
function survey_current_admin(): ?array
{
    $value = (string) ($_COOKIE[survey_cookie_name()] ?? '');
    $parts = explode('.', $value);

    if (count($parts) !== 3 || !ctype_digit($parts[1]) || (int) $parts[1] < time()) {
        return null;
    }

    $key     = base64_decode(strtr($parts[0], '-_', '+/'), true);
    $account = $key === false ? null : (survey_accounts()[$key] ?? null);

    if (!is_array($account)) {
        return null;
    }

    try {
        $expected = survey_session_value($key, $account, (int) $parts[1]);
    } catch (RuntimeException $e) {
        return null;
    }

    return hash_equals($expected, $value) ? ['key' => $key] + $account : null;
}

/**
 * The hidden value every admin form carries. It is derived from the sign-in
 * cookie, which another site's page cannot read, so a form that page builds
 * cannot carry it.
 */
function survey_csrf(): string
{
    return hash_hmac('sha256', 'csrf|' . ($_COOKIE[survey_cookie_name()] ?? ''), survey_key());
}

/* ------------------------------------------------------------- throttle -- */

// Failed sign-ins allowed in the window, per address and per address and
// username together. Past either, that address waits out the window.
const SURVEY_LOGIN_WINDOW = 900;
const SURVEY_LOGIN_LIMITS = ['address' => 20, 'account' => 5];

/** The two throttle files a sign-in attempt counts against. Named by keyed hash, so no IP is stored. */
function survey_throttle_files(string $login): array
{
    $ip  = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $key = survey_key();

    return [
        'address' => survey_path('throttle/' . substr(hash_hmac('sha256', 'address|' . $ip, $key), 0, 16) . '.php'),
        'account' => survey_path('throttle/' . substr(hash_hmac('sha256', 'account|' . $ip . '|' . survey_login_key($login), $key), 0, 16) . '.php'),
    ];
}

function survey_recent_failures(string $file): array
{
    $times  = survey_read($file)['failures'] ?? [];
    $cutoff = time() - SURVEY_LOGIN_WINDOW;

    return array_values(array_filter(is_array($times) ? $times : [], static function ($t) use ($cutoff): bool {
        return is_int($t) && $t > $cutoff;
    }));
}

function survey_login_blocked(string $login): bool
{
    foreach (survey_throttle_files($login) as $kind => $file) {
        if (count(survey_recent_failures($file)) >= SURVEY_LOGIN_LIMITS[$kind]) {
            return true;
        }
    }

    return false;
}

function survey_login_failed(string $login): void
{
    foreach (survey_throttle_files($login) as $file) {
        $times   = survey_recent_failures($file);
        $times[] = time();
        survey_write($file, ['failures' => $times]);
    }

    // Sweep anything a day old, so the folder holds today's attempts and no more.
    foreach (glob(survey_path('throttle') . '/*.php') ?: [] as $file) {
        if (@filemtime($file) < time() - 86400) {
            @unlink($file);
        }
    }
}

function survey_login_cleared(string $login): void
{
    @unlink(survey_throttle_files($login)['account']);
}

/* ------------------------------------------------------------------ mail -- */

function survey_link(string $token): string
{
    return rtrim(SITE_URL, '/') . '/survey?t=' . rawurlencode($token);
}

function survey_public_url(array $response): string
{
    return rtrim(SITE_URL, '/') . '/candidate-surveys/' . rawurlencode((string) $response['slug']);
}

/**
 * Where an admin's mail goes. The username is an email address by convention,
 * not by rule, so a username that is not one sends to the group inbox.
 */
function survey_admin_address(string $login): string
{
    return survey_is_email($login) ? $login : CONTACT_TO;
}

/** Email a candidate their link: the admin's message, then the link on its own line. */
function survey_send_invite(array $invite, string $token, string $subject, string $body, array $admin): bool
{
    $text = rtrim($body) . "\n\n" . survey_link($token) . "\n";

    return send_site_mail($invite['email'], $subject, $text, '', survey_admin_address($admin['login']));
}

/** A response as plain text, in the order the form asked it. */
function survey_response_text(array $response): string
{
    $lines = [
        SURVEY_TITLE,
        '',
        SURVEY_NAME_LABEL . ': ' . $response['name'],
        SURVEY_OFFICE_LABEL . ': ' . $response['office'],
        '',
        SURVEY_PART1,
        '',
    ];

    foreach ($response['positions'] as $row) {
        $lines[] = $row['question'];
        $lines[] = 'Answer: ' . $row['answer'];
        $lines[] = '';
    }

    array_push(
        $lines,
        SURVEY_PART2,
        '',
        $response['stance']['question'],
        '',
        $response['stance']['answer'],
        '',
        SURVEY_SIGNATURE_HEADING,
        '',
        SURVEY_SIGNATURE_TEXT,
        '',
        'Signed: ' . $response['signature'],
        'Submitted: ' . survey_time($response['submitted_at'], 'F j, Y \a\t g:i A T')
    );

    return implode("\n", $lines) . "\n";
}

/**
 * Send the two copies of a new response: one to the candidate, one to the
 * admin who invited them. Each reply-to points at the other person.
 *
 * @return array{candidate: bool, admin: bool}
 */
function survey_send_copies(array $invite, array $response): array
{
    $admin = survey_accounts()[survey_login_key((string) $invite['invited_by'])]['login'] ?? (string) $invite['invited_by'];
    $admin = survey_admin_address($admin);
    $text  = survey_response_text($response);
    $rule  = str_repeat('-', 59);

    $forCandidate = "This is your copy of the answers you submitted to " . SITE_NAME . ".\n"
                  . "They are published at:\n" . survey_public_url($response) . "\n\n"
                  . $rule . "\n\n" . $text;

    $forAdmin = $response['name'] . " answered the survey sent to " . $invite['email'] . ".\n"
              . "Published at:\n" . survey_public_url($response) . "\n"
              . "Hide it from the site at " . rtrim(SITE_URL, '/') . "/survey-admin\n\n"
              . $rule . "\n\n" . $text;

    return [
        'candidate' => send_site_mail($invite['email'], 'Your answers: ' . SURVEY_TITLE, $forCandidate, '', $admin),
        'admin'     => send_site_mail(
            $admin,
            CONTACT_SUBJECT_PREFIX . ' Survey answered: ' . $response['name'] . ', ' . $response['office'],
            $forAdmin,
            $response['name'],
            $invite['email']
        ),
    ];
}
