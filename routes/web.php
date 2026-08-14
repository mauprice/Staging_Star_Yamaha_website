<?php

use App\Http\Controllers\HondaController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PreOwnedController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecialController;
use App\Http\Controllers\YamahaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('yamaha.index');
});

// Registered before the Yamaha wildcard {group} routes below - otherwise
// /bikes/honda would be captured by Yamaha's {group} parameter and 404
// inside YamahaController::group() before ever reaching these.
Route::prefix('bikes/honda')->name('honda.')->group(function () {
    Route::get('/', [HondaController::class, 'index'])->name('index');
    Route::get('/{category}', [HondaController::class, 'category'])->name('category');
    Route::get('/{category}/{subcategory}', [HondaController::class, 'subcategory'])->name('subcategory');
    Route::get('/{category}/{subcategory}/{slug}', [HondaController::class, 'product'])->name('product');
});

Route::prefix('bikes')->name('yamaha.')->group(function () {
    Route::get('/', [YamahaController::class, 'index'])->name('index');
    Route::get('/{group}', [YamahaController::class, 'group'])->name('group');
    Route::get('/{group}/{category}', [YamahaController::class, 'category'])->name('category');
    Route::get('/{group}/{category}/{slug}', [YamahaController::class, 'product'])->name('product');
});

Route::get('/insurance', function () {
    return view('yamaha.insurance');
})->name('yamaha.insurance');

Route::get('/finance', function () {
    return view('yamaha.finance');
})->name('yamaha.finance');

Route::get('/tyres-service', [ServiceController::class, 'index'])->name('yamaha.service');
Route::post('/tyres-service', [ServiceController::class, 'store'])->name('yamaha.service.store');

Route::get('/about-us', function () {
    return view('yamaha.about');
})->name('yamaha.about');

Route::get('/privacy-policy', function () {
    return view('yamaha.privacy-policy');
})->name('yamaha.privacy');

Route::get('/returns-exchanges', function () {
    return view('yamaha.returns-exchanges');
})->name('yamaha.returns');

Route::get('/delivery-information', function () {
    return view('yamaha.delivery-information');
})->name('yamaha.delivery');

Route::get('/pre-owned', [PreOwnedController::class, 'index'])->name('yamaha.preowned');
Route::get('/pre-owned/{id}-{slug}', [PreOwnedController::class, 'show'])->name('yamaha.preowned.show');

Route::get('/sell-my-bike', [PreOwnedController::class, 'sellForm'])->name('yamaha.sell');
Route::post('/sell-my-bike', [PreOwnedController::class, 'sellStore'])->name('yamaha.sell.store');

Route::get('/specials', [SpecialController::class, 'index'])->name('yamaha.specials');
Route::get('/specials/{id}-{slug}', [SpecialController::class, 'show'])->name('yamaha.specials.show');

Route::get('/parts-finder', function () {
    return view('yamaha.parts-finder');
})->name('yamaha.parts-finder');

Route::get('/shop-parts', function () {
    return view('yamaha.shop-parts');
})->name('yamaha.shop-parts');

Route::get('/news', [NewsController::class, 'index'])->name('yamaha.news');
Route::get('/news/{id}-{slug}', [NewsController::class, 'show'])->name('yamaha.news.show');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/index.xml', [SitemapController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
