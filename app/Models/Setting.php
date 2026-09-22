<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType    = 'string';
    public    $incrementing = false;
    public    $timestamps   = false;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, string $default = ''): string
    {
        return (string) (static::find($key)?->value ?? $default);
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    /**
     * Read a comma-separated list setting (e.g. "a@x.com, b@x.com") as an
     * array of valid email addresses, falling back to $default (itself
     * comma-separated) when the setting is unset or empty.
     *
     * @return string[]
     */
    public static function getEmailList(string $key, string $default = ''): array
    {
        $value = static::get($key, $default);

        return collect(explode(',', $value))
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();
    }
}
