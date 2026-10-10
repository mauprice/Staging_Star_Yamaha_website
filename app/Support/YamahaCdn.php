<?php

namespace App\Support;

/**
 * Yamaha's old www.yamaha-motor.com.au CDN was decommissioned — every path on
 * that host now 404s/403s — but plenty of synced product rows still carry
 * image URLs pointing at it from before the Dealers-API migration. Anywhere
 * an image URL from the database is rendered, it needs to be checked against
 * this host first or it silently renders as a broken image/blank slide.
 */
class YamahaCdn
{
    public static function isLive(?string $url): bool
    {
        return $url !== null && ! str_contains($url, 'yamaha-motor.com.au');
    }

    public static function liveOrNull(?string $url): ?string
    {
        return self::isLive($url) ? $url : null;
    }
}
