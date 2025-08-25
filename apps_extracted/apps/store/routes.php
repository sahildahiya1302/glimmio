<?php
declare(strict_types=1);

/**
 * Resolve the current request path to a template name or PHP file.
 * Returns a string like 'index' for layouts or 'php:/path/to/file.php'
 * when serving a direct PHP page, or '404' if no match.
 */
function resolveCurrentRoute(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = rtrim($path, '/');
    if ($path === '') {
        return 'index';
    }

    // Static PHP template routes
    $phpRoutes = [
        '/password' => 'php:' . passwordLayoutFile(),
    ];


    // JSON layout templates
    $jsonRoutes = [
        '/cart' => 'cart',
        '/checkout/cart' => 'checkout.cart',
        '/checkout/address' => 'checkout.address',
        '/checkout/payment' => 'checkout.payment',
        '/checkout/success' => 'checkout.success',
        '/compare' => 'compare',
        '/account/login' => 'customers.login',
        '/wishlist' => 'wishlist',
        '/search' => 'search',
        '/page/about-us' => 'page.about-us',
        '/page/contact' => 'page.contact',
        '/help-center' => 'help-center',
        '/language' => 'language',
        '/reviews' => 'reviews',
        '/policies' => 'policies',
        '/faq' => 'faq',
        '/settings' => 'settings',
    ];

    if (isset($jsonRoutes[$path])) {
        return $jsonRoutes[$path];
    }

    if (isset($phpRoutes[$path])) {
        return $phpRoutes[$path];
    }

    if (preg_match('#^/page/([^/]+)$#', $path, $m)) {
        return 'dbpage:' . $m[1];
    }

    // Dynamic product or collection page
    if (preg_match('#^/products/([^/]+)$#', $path, $m)) {
        return 'product:' . $m[1];
    }
    if (preg_match('#^/collections/([^/]+)$#', $path, $m)) {
        return 'collection:' . $m[1];
    }

    // Catch: check for a real JSON file in templates
    $slug = ltrim($path, '/');
    $jsonFile = __DIR__ . '/themes/default/templates/' . $slug . '.json';
    if (is_file($jsonFile)) {
        return $slug;
    }

    return '404';
}