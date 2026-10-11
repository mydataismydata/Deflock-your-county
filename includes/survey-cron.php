<?php
/**
 * Sends queued survey invites from the host's cron, so /survey-admin can be
 * closed while they go out.
 *
 * Add one cron job in the Ionos control panel that runs this every minute.
 * Plain `php` on Ionos is PHP 4, so name a PHP 8 binary:
 *
 *   /usr/bin/php8.3-cli ~/site-folder/includes/survey-cron.php 2>> ~/survey-cron-errors.log
 *
 * It sends only while the queue is started on /survey-admin, and Pause there
 * stops it. Each run stays up for less than a minute and sends when the next
 * invite falls due inside that minute, so a once-a-minute cron keeps the
 * SURVEY_QUEUE_INTERVAL spacing. The queue's lock means this and an open
 * admin page never send the same invite twice.
 *
 * It does nothing over the web: includes/ is closed to browsers, and this
 * file answers 404 to any request that is not from the command line.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/survey.php';

// Leave the next run its own minute.
$until = time() + 55;

try {
    survey_cron_beat();

    while (true) {
        $queue = survey_queue_tick();

        if (!$queue['running'] || $queue['queued'] === 0) {
            break;
        }

        // At least a second, so a send in progress elsewhere is not polled in a tight loop.
        $wait = max(1, $queue['wait']);

        if (time() + $wait > $until) {
            break;
        }

        sleep($wait);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'survey-cron: ' . $e->getMessage() . "\n");
    exit(1);
}
