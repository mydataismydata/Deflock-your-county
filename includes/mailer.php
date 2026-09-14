<?php
/**
 * Contact form plumbing: stateless anti-spam tokens and one mail() call.
 *
 * Nothing is written to disk and no session is started, so the site runs on
 * shared hosting with no database and no writable directory.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** Strip the characters that would let a value open a new mail header. */
function header_safe(string $value): string
{
    return trim(str_replace(["\r", "\n", "\0"], ' ', $value));
}

/** RFC 2047 encode a header value, but only when it is not plain ASCII. */
function header_encode(string $value): string
{
    $value = header_safe($value);

    if (preg_match('/[^\x20-\x7E]/', $value) !== 1) {
        return $value;
    }

    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

/**
 * Build a "Display Name <address>" header value.
 *
 * The display name is quoted, because a name like "Deflock St. Johns" carries
 * a period and strict mail servers reject that in an unquoted atom.
 */
function header_address(string $name, string $email): string
{
    $name  = header_safe($name);
    $email = header_safe($email);

    if ($name === '') {
        return '<' . $email . '>';
    }

    if (preg_match('/[^\x20-\x7E]/', $name) === 1) {
        $display = '=?UTF-8?B?' . base64_encode($name) . '?=';
    } else {
        $display = '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $name) . '"';
    }

    return $display . ' <' . $email . '>';
}

/** True while config.php still carries the shipped placeholder secret. */
function form_secret_unset(): bool
{
    return FORM_SECRET === 'CHANGE-ME-BEFORE-GOING-LIVE' || strlen(FORM_SECRET) < 32;
}

/**
 * Issue a signed timestamp. The form carries it in a hidden field, which lets
 * the handler tell how long the visitor spent on the page without storing
 * anything server side.
 */
function form_token(): string
{
    $issued = time();

    return $issued . '.' . hash_hmac('sha256', (string) $issued, FORM_SECRET);
}

/**
 * Seconds since the token was issued, or null if the signature does not verify.
 */
function form_token_age(string $token): ?int
{
    $parts = explode('.', $token, 2);

    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        return null;
    }

    [$issued, $signature] = $parts;

    if (!hash_equals(hash_hmac('sha256', $issued, FORM_SECRET), $signature)) {
        return null;
    }

    return time() - (int) $issued;
}

/**
 * Deliver one contact message. Returns false if the host refused to queue it.
 *
 * The submitter's address goes in Reply-To, never in From. Ionos checks that
 * the sending domain matches the account and drops mail that does not.
 */
function send_contact_message(string $name, string $email, string $topic, string $message): bool
{
    if (!function_exists('mail')) {
        return false;
    }

    $subject = header_encode(CONTACT_SUBJECT_PREFIX . ' ' . $topic . ' from ' . $name);

    $body = "Sent from the " . SITE_NAME . " contact form.\n"
          . "Time: " . gmdate('Y-m-d H:i:s') . " UTC\n\n"
          . "Name:  " . $name . "\n"
          . "Email: " . $email . "\n"
          . "Topic: " . $topic . "\n\n"
          . "-----------------------------------------------------------\n\n"
          . $message . "\n\n"
          . "-----------------------------------------------------------\n"
          . "Reply directly to this message to answer the sender.\n"
          . "No IP address or browser details were recorded.\n";

    $headers = implode("\r\n", [
        'From: ' . header_address(CONTACT_FROM_NAME, CONTACT_FROM),
        'Reply-To: ' . header_address($name, $email),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'Auto-Submitted: auto-generated',
    ]);

    $body = wordwrap(str_replace("\r\n", "\n", $body), 78, "\n", false);

    // The envelope sender helps deliverability where the host allows it. Some
    // shared configurations block the fifth argument, so fall back without it.
    $sent = @mail(CONTACT_TO, $subject, $body, $headers, '-f' . CONTACT_FROM);

    if (!$sent) {
        $sent = @mail(CONTACT_TO, $subject, $body, $headers);
    }

    return $sent;
}
