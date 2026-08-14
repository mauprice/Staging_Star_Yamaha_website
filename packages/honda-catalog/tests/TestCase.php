<?php

namespace Honda\Catalog\Tests;

use Honda\Catalog\HondaCatalogServiceProvider;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [HondaCatalogServiceProvider::class];
    }

    /**
     * Drops and re-migrates on every test, rather than relying on
     * RefreshDatabase's once-per-process caching: with the sqlite :memory:
     * default that caching is harmless (each process gets a fresh DB
     * anyway), but under the MySQL fallback (see defineEnvironment - used in
     * environments without pdo_sqlite) a persistent database combined with
     * many separate TestCase classes made that caching unreliable. Brute
     * force is slower but correct either way. InstallCommandTest overrides
     * $loadPackageMigrations to false, since it tests the publish+migrate
     * flow itself and must start from a genuinely empty database.
     */
    protected bool $loadPackageMigrations = true;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        if ($this->loadPackageMigrations) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            $this->artisan('migrate')->run();
        }
    }

    protected function defineEnvironment($app): void
    {
        // Defaults to sqlite in-memory, the portable choice for CI. Override
        // via DB_TEST_CONNECTION=mysql (+ DB_TEST_*) for environments without
        // the pdo_sqlite extension available.
        $driver = env('DB_TEST_CONNECTION', 'sqlite');

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', $driver === 'mysql' ? [
            'driver' => 'mysql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '3306'),
            'database' => env('DB_TEST_DATABASE', 'honda_catalog_test'),
            'username' => env('DB_TEST_USERNAME', 'root'),
            'password' => env('DB_TEST_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ] : [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => '/storage',
            'visibility' => 'public',
        ]);
    }

    protected function fixture(string $name): string
    {
        return file_get_contents(__DIR__."/fixtures/{$name}");
    }
}
