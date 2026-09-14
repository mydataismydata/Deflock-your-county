<?php
/**
 * Development router for PHP's built-in server. Never used in production.
 *
 *   php -S localhost:8000 router.php
 *
 * Apache does this job with .htaccess on the live host. The built-in server
 * ignores .htaccess, so without a router the clean URLs, the 404 page and the
 * block on includes/ all behave differently in development than they do live.
 */

declare(strict_types=1);

// Only the built-in server ever runs this. If the file reaches a production
// host by accident, it answers 404 rather than sitting there as a live script.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Matches includes/.htaccess. This test comes first, because the files in
// there are real and would otherwise be served.
if (str_starts_with($path, '/includes/')) {
    http_response_code(403);
    exit('Forbidden');
}

// Hand real files (stylesheet, script, favicon) to the server unchanged.
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}

$target = $path === '/' ? '/index.php' : '/' . trim($path, '/') . '.php';

if (is_file(__DIR__ . $target)) {
    require __DIR__ . $target;
    return true;
}

require __DIR__ . '/404.php';
return true;
