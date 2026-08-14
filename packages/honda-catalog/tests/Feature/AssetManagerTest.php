<?php

namespace Honda\Catalog\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Assets\AssetManager;
use Honda\Catalog\DataTransferObjects\AssetRef;
use Honda\Catalog\Enums\AssetHost;
use Honda\Catalog\Enums\AssetStatus;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class AssetManagerTest extends TestCase
{
    private function ref(string $guid = 'abc123', ?string $versionHash = 'v1'): AssetRef
    {
        return new AssetRef($guid, 'https://delivery.contenthub.honda.com.au/api/public/content/abc123.jpg', $versionHash, AssetHost::ContentHub);
    }

    public function test_cdn_strategy_stores_a_remote_reference_without_downloading(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->expects($this->never())->method('get');

        $manager = new AssetManager($http, ['disk' => 'public', 'path_prefix' => 'honda-catalog']);
        $asset = $manager->record($this->ref(), 'cdn');

        $this->assertSame(AssetStatus::Remote, $asset->status);
        $this->assertNull($asset->local_path);
        $this->assertSame('abc123', $asset->guid);
    }

    public function test_mirror_strategy_downloads_and_stores_the_file(): void
    {
        Storage::fake('public');

        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willReturn(new Response(200, ['Content-Type' => 'image/jpeg'], 'fake-image-bytes'));

        $manager = new AssetManager($http, ['disk' => 'public', 'path_prefix' => 'honda-catalog']);
        $asset = $manager->record($this->ref(), 'mirror');

        $this->assertSame(AssetStatus::Mirrored, $asset->status);
        $this->assertNotNull($asset->local_path);
        $this->assertSame(hash('sha256', 'fake-image-bytes'), $asset->checksum);
        Storage::disk('public')->assertExists($asset->local_path);
    }

    public function test_mirror_strategy_skips_redownload_when_version_hash_is_unchanged(): void
    {
        Storage::fake('public');

        $http = $this->createMock(ThrottledHttpClient::class);
        $http->expects($this->once())->method('get')
            ->willReturn(new Response(200, [], 'fake-image-bytes'));

        $manager = new AssetManager($http, ['disk' => 'public', 'path_prefix' => 'honda-catalog']);
        $manager->record($this->ref('abc123', 'v1'), 'mirror');
        $manager->record($this->ref('abc123', 'v1'), 'mirror');
    }

    public function test_mirror_strategy_redownloads_when_version_hash_changes(): void
    {
        Storage::fake('public');

        $http = $this->createMock(ThrottledHttpClient::class);
        $http->expects($this->exactly(2))->method('get')
            ->willReturn(new Response(200, [], 'fake-image-bytes'));

        $manager = new AssetManager($http, ['disk' => 'public', 'path_prefix' => 'honda-catalog']);
        $manager->record($this->ref('abc123', 'v1'), 'mirror');
        $manager->record($this->ref('abc123', 'v2'), 'mirror');
    }

    public function test_mirror_failure_degrades_to_failed_status_instead_of_throwing(): void
    {
        Storage::fake('public');

        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willThrowException(new \RuntimeException('network error'));

        $manager = new AssetManager($http, ['disk' => 'public', 'path_prefix' => 'honda-catalog']);
        $asset = $manager->record($this->ref(), 'mirror');

        $this->assertSame(AssetStatus::Failed, $asset->status);
    }

    public function test_url_falls_back_to_remote_source_when_status_is_failed(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willThrowException(new \RuntimeException('network error'));

        $manager = new AssetManager($http, ['disk' => 'public', 'path_prefix' => 'honda-catalog']);
        $asset = $manager->record($this->ref('abc123', 'v1'), 'mirror');

        $this->assertSame(
            'https://delivery.contenthub.honda.com.au/api/public/content/abc123.jpg?v=v1',
            $asset->url(),
        );
    }

    public function test_url_returns_the_storage_url_when_mirrored(): void
    {
        Storage::fake('public');

        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willReturn(new Response(200, [], 'fake-image-bytes'));

        $manager = new AssetManager($http, ['disk' => 'public', 'path_prefix' => 'honda-catalog']);
        $asset = $manager->record($this->ref(), 'mirror');

        $this->assertStringContainsString($asset->local_path, $asset->url());
    }
}
