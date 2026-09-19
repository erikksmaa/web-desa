<?php

namespace App\Support;

final class SafeUrl
{
    public static function web(?string $url): bool
    {
        return is_string($url) && ! preg_match('/[\x00-\x20\x7f]/', $url)
            && filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['https', 'http'], true)
            && ! parse_url($url, PHP_URL_USER) && ! parse_url($url, PHP_URL_PASS);
    }

    public static function map(?string $url): bool
    {
        if (! self::web($url) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return false;
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        return (in_array($host, ['www.google.com', 'maps.google.com'], true)
                && ($path === '/maps/embed' || str_starts_with($path, '/maps/embed/')))
            || ($host === 'www.openstreetmap.org' && $path === '/export/embed.html');
    }
}
