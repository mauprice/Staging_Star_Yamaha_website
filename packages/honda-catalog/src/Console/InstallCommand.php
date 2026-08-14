<?php

namespace Honda\Catalog\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'honda-catalog:install {--strategy= : Asset strategy to use, cdn or mirror}';

    protected $description = 'Publish honda-catalog config and migrations, run migrations, and print next steps.';

    public function handle(): int
    {
        $this->info('Installing honda-catalog...');

        $this->call('vendor:publish', ['--tag' => 'honda-catalog-config', '--force' => false]);
        $this->call('vendor:publish', ['--tag' => 'honda-catalog-migrations', '--force' => false]);

        if ($this->confirm('Run migrations now?', true)) {
            $this->call('migrate');
        }

        $strategy = $this->option('strategy');

        if (! in_array($strategy, ['cdn', 'mirror'], true)) {
            $strategy = $this->choice(
                'Which asset strategy do you want to use?',
                ['cdn', 'mirror'],
                'cdn',
            );
        }

        $this->newLine();
        $this->comment("Selected asset strategy: {$strategy}");
        $this->line('honda-catalog does not edit your .env file. Set this yourself if it differs from the default:');
        $this->line("  HONDA_CATALOG_ASSET_STRATEGY={$strategy}");

        $this->newLine();
        $this->info('honda-catalog is installed. Next steps:');
        $this->line('  1. Review config/honda-catalog.php (selectors, throttle, category allow-list).');
        $this->line('  2. Run a first sync: php artisan honda-catalog:sync --dry-run');
        $this->line('  3. If it looks right: php artisan honda-catalog:sync --with-assets');
        $this->line('  4. To schedule it yourself, add this to your routes/console.php:');
        $this->line("       Schedule::command('honda-catalog:sync --with-assets')->daily();");
        $this->line('     (honda-catalog never registers a schedule automatically.)');

        return self::SUCCESS;
    }
}
