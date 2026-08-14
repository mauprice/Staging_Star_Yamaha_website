<?php

namespace Honda\Catalog\Tests\Feature;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Enums\AssetHost;
use Honda\Catalog\Enums\AssetStatus;
use Honda\Catalog\Models\HondaAsset;
use Honda\Catalog\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class MirrorAssetsCommandTest extends TestCase
{
    public function test_it_mirrors_pending_assets_and_leaves_mirrored_ones_alone(): void
    {
        Storage::fake('public');

        HondaAsset::create([
            'guid' => 'pending-1',
            'source_url' => 'https://delivery.contenthub.honda.com.au/api/public/content/pending-1',
            'host' => AssetHost::ContentHub,
            'status' => AssetStatus::Remote,
        ]);

        HondaAsset::create([
            'guid' => 'already-mirrored',
            'source_url' => 'https://delivery.contenthub.honda.com.au/api/public/content/already-mirrored',
            'host' => AssetHost::ContentHub,
            'status' => AssetStatus::Mirrored,
            'local_path' => 'honda-catalog/already-mirrored.jpg',
            'storage_disk' => 'public',
        ]);

        $mock = $this->createMock(ClientInterface::class);
        $mock->method('request')->willReturn(new Response(200, [], 'fake-bytes'));
        $this->app->bind(ClientInterface::class, fn () => $mock);

        $this->artisan('honda-catalog:assets:mirror')->assertExitCode(0);

        $this->assertSame(AssetStatus::Mirrored, HondaAsset::where('guid', 'pending-1')->first()->status);
    }

    public function test_it_reports_success_when_nothing_is_pending(): void
    {
        HondaAsset::create([
            'guid' => 'already-mirrored',
            'source_url' => 'https://delivery.contenthub.honda.com.au/api/public/content/already-mirrored',
            'host' => AssetHost::ContentHub,
            'status' => AssetStatus::Mirrored,
        ]);

        $this->artisan('honda-catalog:assets:mirror')->assertExitCode(0);
    }
}
