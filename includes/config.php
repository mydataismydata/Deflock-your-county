<?php
/**
 * Settings for one installation: addresses, the form secret, meeting dates and
 * social links. Edit it once after uploading to the server.
 *
 * Anything that names a county, a state, a statute or an elected body lives in
 * place.php instead. Nothing here or there is stored in a database.
 */

declare(strict_types=1);

// Everything that names a county, a state or an elected body lives in
// place.php. Fork the site to another county by rewriting that file alone.
require_once __DIR__ . '/place.php';

// ---------------------------------------------------------------- identity --

// Absolute URL of the live site, no trailing slash. Used for canonical tags.
const SITE_URL = 'https://example.org';

// ----------------------------------------------------------------- contact --

// Where the contact form delivers. One address.
const CONTACT_TO = 'hello@example.org';

// The envelope sender. This MUST be a mailbox on the domain the site runs on.
// Ionos rejects or spam-folders mail claiming to come from an outside domain.
const CONTACT_FROM      = 'website@example.org';
const CONTACT_FROM_NAME = SITE_NAME . ' website';

// Prefix on the subject line of every delivered message.
const CONTACT_SUBJECT_PREFIX = '[deflock-sjc]';

// ------------------------------------------------------------ form defense --

// Random string, at least 32 characters. Generate a fresh one per install:
//   php -r "echo bin2hex(random_bytes(32));"
// The form refuses to send while this is left at the shipped value.
//
// A DEFLOCK_FORM_SECRET environment variable wins over the value below, which
// keeps the secret out of the repository on any host that can set one. Ionos
// shared hosting cannot, so edit the string here for that deployment.
define('FORM_SECRET', getenv('DEFLOCK_FORM_SECRET') ?: 'CHANGE-ME-BEFORE-GOING-LIVE');

// A submission faster than this many seconds is treated as a bot.
const FORM_MIN_SECONDS = 4;

// A form token older than this many seconds is expired.
const FORM_MAX_SECONDS = 7200;

// --------------------------------------------------------------- channels --

// Social and chat links. Delete any line you do not use; the templates skip
// empty values rather than printing a dead link.
const CHANNELS = [
    'Mastodon' => '',
    'Bluesky'  => '',
    'Reddit'   => '',
    'Signal'   => '',
    'Discord'  => '',
];

// --------------------------------------------------------------- meetings --

// Upcoming meetings, soonest first. Clear the array to show a standing
// "nothing scheduled" message instead.
// The county board dates as the county calendar listed them on 18 September
// 2026. The county publishes a rolling window of a few months, so top these up
// when the list runs short. Add our own meetings here when we hold any.
const MEETINGS = [
    ['when' => 'Oct 6, 2026',  'what' => 'Board of County Commissioners', 'where' => COUNTY_MEETING_PLACE, 'note' => '9:00 am'],
    ['when' => 'Oct 20, 2026', 'what' => 'Board of County Commissioners', 'where' => COUNTY_MEETING_PLACE, 'note' => '9:00 am'],
    ['when' => 'Nov 3, 2026',  'what' => 'Board of County Commissioners', 'where' => COUNTY_MEETING_PLACE, 'note' => '9:00 am'],
    ['when' => 'Nov 17, 2026', 'what' => 'Board of County Commissioners', 'where' => COUNTY_MEETING_PLACE, 'note' => '9:00 am'],
    ['when' => 'Dec 1, 2026',  'what' => 'Board of County Commissioners', 'where' => COUNTY_MEETING_PLACE, 'note' => '9:00 am'],
    ['when' => 'Dec 15, 2026', 'what' => 'Board of County Commissioners', 'where' => COUNTY_MEETING_PLACE, 'note' => '9:00 am'],
];

// ---------------------------------------------------------------- helpers --

if (!function_exists('asset')) {
    /**
     * A site-root path with the file's modification time on the end.
     *
     * The stylesheet and the script are cached for a week by .htaccess, so
     * without this a returning visitor keeps the old CSS for seven days after
     * a deploy and sees new markup in an old skin. The stamp changes when the
     * file does, and not otherwise.
     */
    function asset(string $path): string
    {
        $file  = dirname(__DIR__) . $path;
        $stamp = is_file($file) ? filemtime($file) : false;

        return $stamp === false ? $path : $path . '?v=' . $stamp;
    }
}

if (!function_exists('e')) {
    /** Escape a string for safe output inside HTML. */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
