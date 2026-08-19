<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;

/**
 * Lets the admin panel edit a handful of known .env keys (payment gateway
 * credentials) without touching anything else in the file. Used instead of
 * storing secrets in the database, per a deliberate choice to keep API
 * keys out of the DB (backups, admin-panel access, etc. are a bigger
 * exposure surface than a properly permissioned .env file).
 */
class EnvFileWriter
{
    private string $path;

    public function __construct()
    {
        $this->path = base_path('.env');
    }

    public function isWritable(): bool
    {
        return is_writable($this->path);
    }

    /**
     * @param  array<string, string|null>  $values  key => new value. A null
     *         value leaves that key untouched (used for "blank means don't
     *         change" secret fields); pass an empty string to actually clear it.
     */
    public function set(array $values): void
    {
        $values = array_filter($values, fn ($v) => $v !== null);

        if (! $values) {
            return;
        }

        $this->backup();

        $content = file_get_contents($this->path);

        foreach ($values as $key => $value) {
            $line = $key . '=' . $this->formatValue($value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            $content = preg_match($pattern, $content)
                ? preg_replace($pattern, $line, $content)
                : rtrim($content) . "\n" . $line . "\n";
        }

        file_put_contents($this->path, $content);

        // .env changes are invisible to a running app once config is
        // cached (as it is after every deploy here) - clear it so the
        // new values take effect on the very next request.
        Artisan::call('config:clear');
    }

    private function backup(): void
    {
        $dir = storage_path('app/env-backups');

        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        copy($this->path, $dir . '/.env.' . now()->format('Ymd-His') . '.bak');
    }

    private function formatValue(string $value): string
    {
        if ($value === '' || ! preg_match('/[\s#"\'\\\\]/', $value)) {
            return $value;
        }

        return '"' . str_replace('"', '\\"', $value) . '"';
    }
}
