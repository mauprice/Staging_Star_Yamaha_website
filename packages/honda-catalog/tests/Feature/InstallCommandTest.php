<?php

namespace Honda\Catalog\Tests\Feature;

use Honda\Catalog\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    /**
     * This test exercises the cold install flow (vendor:publish + migrate)
     * itself, so - unlike every other test class - it must not pre-load the
     * package's raw migrations; setUp() should just reset to an empty
     * database and let the command under test create the schema.
     */
    protected bool $loadPackageMigrations = false;

    protected function tearDown(): void
    {
        // vendor:publish physically copies migration files into the
        // Testbench skeleton app on disk; clean them up so they don't leak
        // into other test classes on the next run.
        foreach (glob(__DIR__.'/../../vendor/orchestra/testbench-core/laravel/database/migrations/*_create_honda_*_table.php') as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    public function test_install_runs_migrations_and_prints_next_steps(): void
    {
        $this->artisan('honda-catalog:install')
            ->expectsConfirmation('Run migrations now?', 'yes')
            ->expectsChoice('Which asset strategy do you want to use?', 'cdn', ['cdn', 'mirror'])
            ->expectsOutputToContain('honda-catalog is installed')
            ->assertExitCode(0);
    }

    public function test_install_accepts_strategy_via_option_without_prompting(): void
    {
        $this->artisan('honda-catalog:install', ['--strategy' => 'mirror'])
            ->expectsConfirmation('Run migrations now?', 'no')
            ->expectsOutputToContain('Selected asset strategy: mirror')
            ->assertExitCode(0);
    }
}
