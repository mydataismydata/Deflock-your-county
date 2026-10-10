<?php
/**
 * Manage the accounts that can sign in to /survey-admin.
 *
 *   php tools/survey-admin.php add <username>       create an account
 *   php tools/survey-admin.php password <username>  set a new password
 *   php tools/survey-admin.php remove <username>    delete an account
 *   php tools/survey-admin.php list                 show every username
 *
 * The username is the admin's email address: the copy of each answer goes
 * there. Any characters are allowed apart from control characters, spaces at
 * the ends are dropped, and capitals do not matter when signing in. A password
 * needs 8 characters or more and nothing else.
 *
 * The accounts are written to includes/survey-admins.php, which git ignores.
 * Upload that file to includes/ on the host after any change. Set
 * DEFLOCK_SURVEY_ADMINS_FILE to write somewhere else, such as a test file:
 *
 *   DEFLOCK_SURVEY_ADMINS_FILE=data/dev-admins.php php tools/survey-admin.php add you@example.org
 *
 * A password is read without echo when this runs in a terminal. Piped in, it
 * is read from the first line of input, and from the second as the repeat.
 */

declare(strict_types=1);

// A web request never runs this. If the file reaches a web root by accident,
// it answers 404 rather than acting.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/survey.php';

function out(string $line): void
{
    fwrite(STDOUT, $line . "\n");
}

/** Print the reason and stop. */
function fail(string $line): void
{
    fwrite(STDERR, $line . "\n");
    exit(1);
}

/** Read one line, without echoing it when the input is a terminal. */
function ask_secret(string $prompt): string
{
    $tty = function_exists('stream_isatty') && stream_isatty(STDIN);

    if ($tty) {
        fwrite(STDOUT, $prompt);
        shell_exec('stty -echo');
    }

    $line = fgets(STDIN);

    if ($tty) {
        shell_exec('stty echo');
        fwrite(STDOUT, "\n");
    }

    return $line === false ? '' : rtrim($line, "\r\n");
}

function ask_new_password(): string
{
    $password = ask_secret('Password (8 characters or more): ');

    if (($problem = survey_password_problem($password)) !== null) {
        fail($problem);
    }

    if (ask_secret('The same password again: ') !== $password) {
        fail('The two passwords differ. Nothing was changed.');
    }

    return $password;
}

$command = $argv[1] ?? '';
$login   = $argv[2] ?? '';

$accounts = survey_accounts();
$key      = survey_login_key($login);

switch ($command) {
    case 'add':
        if (($problem = survey_login_problem($login)) !== null) {
            fail($problem);
        }
        if (isset($accounts[$key])) {
            fail('"' . $accounts[$key]['login'] . '" already has an account. Use "password" to change it.');
        }
        if (!survey_is_email(trim($login))) {
            out('Note: "' . trim($login) . '" is not an email address. Copies of answers will go to ' . CONTACT_TO . ' instead.');
        }
        $accounts[$key] = ['login' => trim($login), 'hash' => survey_password_hash(ask_new_password())];
        survey_accounts_save($accounts);
        out('Added "' . trim($login) . '" to ' . SURVEY_ADMINS_FILE);
        out('Upload that file to includes/ on the host.');
        break;

    case 'password':
        if (!isset($accounts[$key])) {
            fail('No account matches "' . trim($login) . '".');
        }
        $accounts[$key]['hash'] = survey_password_hash(ask_new_password());
        survey_accounts_save($accounts);
        out('Changed the password for "' . $accounts[$key]['login'] . '". Every sign-in under the old one has ended.');
        out('Upload ' . SURVEY_ADMINS_FILE . ' to includes/ on the host.');
        break;

    case 'remove':
        if (!isset($accounts[$key])) {
            fail('No account matches "' . trim($login) . '".');
        }
        $gone = $accounts[$key]['login'];
        unset($accounts[$key]);
        survey_accounts_save($accounts);
        out('Removed "' . $gone . '". Upload ' . SURVEY_ADMINS_FILE . ' to includes/ on the host.');
        break;

    case 'list':
        if ($accounts === []) {
            out('No accounts in ' . SURVEY_ADMINS_FILE);
        }
        foreach ($accounts as $account) {
            out($account['login']);
        }
        break;

    default:
        fail("Usage:\n"
            . "  php tools/survey-admin.php add <username>\n"
            . "  php tools/survey-admin.php password <username>\n"
            . "  php tools/survey-admin.php remove <username>\n"
            . "  php tools/survey-admin.php list");
}
