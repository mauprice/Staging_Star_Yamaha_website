<?php

namespace Honda\Catalog;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Honda\Catalog\Assets\AssetManager;
use Honda\Catalog\Console\InstallCommand;
use Honda\Catalog\Console\MirrorAssetsCommand;
use Honda\Catalog\Console\SyncCommand;
use Honda\Catalog\Crawling\SitemapCrawler;
use Honda\Catalog\Http\RobotsTxtChecker;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Http\ThrottleGate;
use Honda\Catalog\Parsing\ModelPageParser;
use Honda\Catalog\Parsing\SpecsPageParser;
use Honda\Catalog\Pricing\MpePcmPricingClient;
use Honda\Catalog\Services\IngestService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HondaCatalogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('honda-catalog')
            ->hasConfigFile('honda-catalog')
            ->hasMigrations([
                '2026_01_01_000001_create_honda_assets_table',
                '2026_01_01_000002_create_honda_models_table',
                '2026_01_01_000003_create_honda_model_features_table',
                '2026_01_01_000004_create_honda_variants_table',
                '2026_01_01_000005_create_honda_specifications_table',
                '2026_01_01_000006_create_honda_colours_table',
                '2026_01_01_000007_create_honda_model_asset_table',
            ])
            ->hasCommands([
                InstallCommand::class,
                SyncCommand::class,
                MirrorAssetsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->bind(ClientInterface::class, function () {
            return new Client;
        });

        $this->app->singleton(ThrottleGate::class, function ($app) {
            return new ThrottleGate((float) config('honda-catalog.http.requests_per_second', 1.5));
        });

        $this->app->singleton(RobotsTxtChecker::class, function ($app) {
            return new RobotsTxtChecker(
                $app->make(ClientInterface::class),
                config('honda-catalog.http.user_agent'),
                (bool) config('honda-catalog.http.respect_robots_txt', true),
            );
        });

        $this->app->singleton(ThrottledHttpClient::class, function ($app) {
            return new ThrottledHttpClient(
                $app->make(ClientInterface::class),
                $app->make(RobotsTxtChecker::class),
                $app->make(ThrottleGate::class),
                config('honda-catalog.http', []),
            );
        });

        $this->app->singleton(SitemapCrawler::class, function ($app) {
            return new SitemapCrawler(
                $app->make(ThrottledHttpClient::class),
                config('honda-catalog.discovery', []),
            );
        });

        $this->app->singleton(ModelPageParser::class, function ($app) {
            return new ModelPageParser(config('honda-catalog.selectors.model_page', []));
        });

        $this->app->singleton(SpecsPageParser::class, function ($app) {
            return new SpecsPageParser(config('honda-catalog.selectors.specs_page', []));
        });

        $this->app->singleton(AssetManager::class, function ($app) {
            return new AssetManager(
                $app->make(ThrottledHttpClient::class),
                config('honda-catalog.assets', []),
            );
        });

        $this->app->singleton(MpePcmPricingClient::class, function ($app) {
            return new MpePcmPricingClient(
                $app->make(ThrottledHttpClient::class),
                config('honda-catalog', []),
            );
        });

        $this->app->singleton(IngestService::class, function ($app) {
            return new IngestService(
                $app->make(ThrottledHttpClient::class),
                $app->make(ModelPageParser::class),
                $app->make(SpecsPageParser::class),
                $app->make(AssetManager::class),
                $app->make(MpePcmPricingClient::class),
                config('honda-catalog', []),
            );
        });
    }
}
