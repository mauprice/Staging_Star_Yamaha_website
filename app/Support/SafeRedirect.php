<?php

namespace App\Support;

class SafeRedirect
{
    /**
     * The post-login/register ?redirect= param is attacker-controlled query
     * input, so only ever honour it when it points at a local path - never
     * an absolute/external URL (open-redirect guard).
     */
    public static function resolve(?string $redirect): ?string
    {
        if (! $redirect) {
            return null;
        }

        if (! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            return null;
        }

        return $redirect;
    }
}
