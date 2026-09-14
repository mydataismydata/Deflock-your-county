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
const FORM_SECRET = 'CHANGE-ME-BEFORE-GOING-LIVE';

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
const MEETINGS = [
    [
        'when'  => 'TBD',
        'what'  => 'Monthly organizing meeting',
        'where' => 'Location to be announced',
        'note'  => 'Open to anyone. No experience needed, no commitment asked.',
    ],
    [
        'when'  => 'TBD',
        'what'  => 'Board of County Commissioners public comment',
        'where' => 'County Administration Building, 500 San Sebastian View, ' . COUNTY_SEAT,
        'note'  => 'Check the published agenda before you go. Times move.',
    ],
];

// ---------------------------------------------------------------- helpers --

if (!function_exists('e')) {
    /** Escape a string for safe output inside HTML. */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
