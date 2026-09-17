<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductHeroImageTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        return $user;
    }

    public function test_hero_image_and_gallery_photos_save_correctly(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();

        Livewire::test(CreateProduct::class)
            ->set('data.name', 'Test Brake Pads')
            ->set('data.category', Product::CATEGORIES[0])
            ->set('data.price', 49.95)
            ->set('data.hero_image', UploadedFile::fake()->image('hero.jpg'))
            ->set('data.images', [
                UploadedFile::fake()->image('gallery1.jpg'),
                UploadedFile::fake()->image('gallery2.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Test Brake Pads')->firstOrFail();

        $this->assertCount(3, $product->images, 'Expected 3 product_images rows (1 hero + 2 gallery), got ' . $product->images->count());
        $this->assertNotNull($product->heroImage, 'Expected a hero image to be set on the product.');
        $this->assertTrue(Storage::disk('public')->exists($product->heroImage->path));
    }

    public function test_first_gallery_photo_becomes_hero_when_none_chosen(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();

        Livewire::test(CreateProduct::class)
            ->set('data.name', 'Test Oil Filter')
            ->set('data.category', Product::CATEGORIES[0])
            ->set('data.price', 19.95)
            ->set('data.images', [
                UploadedFile::fake()->image('filter1.jpg'),
                UploadedFile::fake()->image('filter2.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Test Oil Filter')->firstOrFail();

        $this->assertCount(2, $product->images);
        $this->assertNotNull($product->heroImage, 'Expected the first gallery photo to be auto-promoted to hero.');
    }
}
