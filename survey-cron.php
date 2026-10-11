<?php
/**
 * Sends queued survey invites from the host's cron, so /survey-admin can be
 * closed while they go out.
 *
 * On Ionos, add a WebCron job that fetches this address every minute. The
 * signed-in admin page shows the full address, key included, under
 * "Background sending":
 *
 *   https://example.org/survey-cron?key=...
 *
 * A host with a command-line cron can run the same file instead, with a PHP 8
 * binary:
 *
 *   /usr/bin/php8.3-cli ~/site-folder/survey-cron.php
 *
 * It sends only while the queue is started on /survey-admin, and Pause there
 * stops it. Each run lasts at most 35 seconds and sends when the next invite
 * falls due inside that time, so a once-a-minute cron keeps the
 * SURVEY_QUEUE_INTERVAL spacing. The queue's lock means this and an open admin
 * page never send the same invite twice. Without the key, it is a 404.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/survey.php';

$web = PHP_SAPI !== 'cli';

if ($web) {
    if (!hash_equals(survey_cron_key(), (string) ($_GET['key'] ?? ''))) {
        require __DIR__ . '/404.php';
        exit;
    }

    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');

    // Keep sending if the cron service hangs up before the run ends.
    ignore_user_abort(true);
    set_time_limit(55);

    echo "ok\n";
    flush();
}

// A web run writes a byte every few seconds while it waits, so the web server
// does not take the quiet connection for a stuck one.
$pause = static function (int $seconds) use ($web): void {
    while ($seconds > 0) {
        $step = min(5, $seconds);
        sleep($step);
        $seconds -= $step;

        if ($web) {
            echo ' ';
            flush();
        }
    }
};

try {
    survey_cron_run(35, $pause);
} catch (Throwable $e) {
    if ($web) {
        error_log('survey-cron: ' . $e->getMessage());
        echo "\nerror\n";
    } else {
        fwrite(STDERR, 'survey-cron: ' . $e->getMessage() . "\n");
    }
    exit(1);
}
