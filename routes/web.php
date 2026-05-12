<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');

Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
Route::get('/cards/{card:slug}', [CardController::class, 'show'])->name('cards.show');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{shopItem:slug}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/auctions', [AuctionController::class, 'index'])->name('auctions.index');
Route::get('/auctions/{auction}', [AuctionController::class, 'show'])->name('auctions.show');

Route::get('/packs', [PackController::class, 'index'])->name('packs.index');

/*
|--------------------------------------------------------------------------
| Customer (authenticated) routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/items', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:code}', [OrderController::class, 'show'])->name('orders.show');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{card:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Auctions — bidding requires auth
    Route::post('/auctions/{auction}/bid', [AuctionController::class, 'bid'])->name('auctions.bid');

    // Trades
    Route::get('/trades', [TradeController::class, 'index'])->name('trades.index');
    Route::get('/trades/create', [TradeController::class, 'create'])->name('trades.create');
    Route::post('/trades', [TradeController::class, 'store'])->name('trades.store');
    Route::get('/trades/{trade}', [TradeController::class, 'show'])->name('trades.show');
    Route::post('/trades/{trade}/respond', [TradeController::class, 'respond'])->name('trades.respond');

    // Pack opening
    Route::post('/packs/open', [PackController::class, 'open'])->name('packs.open');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('cards', Admin\CardController::class)->except('show');
    Route::resource('shop', Admin\ShopItemController::class)
        ->parameters(['shop' => 'shopItem'])
        ->except('show');

    Route::get('orders',           [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order:code}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order:code}/status', [Admin\OrderController::class, 'updateStatus'])
        ->name('orders.updateStatus');

    Route::get('users',          [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}',   [Admin\UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/role', [Admin\UserController::class, 'updateRole'])->name('users.updateRole');
});

require __DIR__.'/auth.php';
