<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\GachaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');

// Card price tracker — browse the catalogue + market value, no buying.
Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
Route::get('/cards/{card:slug}', [CardController::class, 'show'])->name('cards.show');

// Official merch store.
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{shopItem:slug}', [ShopController::class, 'show'])->name('shop.show');

// Auctions for real, physical cards.
Route::get('/auctions', [AuctionController::class, 'index'])->name('auctions.index');
Route::get('/auctions/{auction}', [AuctionController::class, 'show'])->name('auctions.show');

// Digital gacha — pull packs, collect digital cards.
Route::get('/gacha', [GachaController::class, 'index'])->name('gacha.index');

// Community forums.
Route::get('/forums', [ForumController::class, 'index'])->name('forums.index');
Route::get('/forums/c/{category:slug}', [ForumController::class, 'category'])->name('forums.category');
Route::get('/forums/t/{thread}', [ForumController::class, 'thread'])->name('forums.thread');

// Public trainer profiles.
Route::get('/u/{user}', [PublicProfileController::class, 'show'])->name('profiles.show');

// Trainer leaderboard.
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

// Google
Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| Customer (authenticated) routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');

    // Account profile (name / email / password / delete)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Trainer settings — public profile: bio, socials, visibility toggles.
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Cart (merch only)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/items', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout & Payment
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/payment/{order:code}', [CheckoutController::class, 'paymentStatus'])->name('payment_status');
    Route::get('/orders/{order:code}/payment', [CheckoutController::class, 'showPayment'])->name('payment_show');
    Route::patch('/orders/{order:code}/cancel', [OrderController::class, 'cancel'])->name('orders_cancel');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:code}', [OrderController::class, 'show'])->name('orders.show');

    // Chase cards (wishlist) — cards a trainer is hunting for.
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{card:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Auctions — bidding + winner payment + refund flow require auth.
    Route::post('/auctions/{auction}/bid', [AuctionController::class, 'bid'])->name('auctions.bid');
    Route::post('/auctions/{auction}/pay', [AuctionController::class, 'pay'])->name('auctions.pay');
    Route::post('/auctions/{auction}/refund', [AuctionController::class, 'requestRefund'])->name('auctions.refund');

    // Gacha — pull a pack + view your digital collection.
    Route::post('/gacha/pull', [GachaController::class, 'pull'])->name('gacha.pull');
    Route::get('/collection', [GachaController::class, 'collection'])->name('collection.index');

    // Forums — creating threads + replying.
    Route::get('/forums/new', [ForumController::class, 'create'])->name('forums.create');
    Route::post('/forums', [ForumController::class, 'store'])->name('forums.store');
    Route::post('/forums/t/{thread}/reply', [ForumController::class, 'reply'])->name('forums.reply');

    // Profile comment wall.
    Route::post('/u/{user}/comments', [PublicProfileController::class, 'comment'])->name('profiles.comment');

    // Notifications inbox.
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::post('cards/refresh', [Admin\CardController::class, 'refresh'])->name('cards.refresh');
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

    // Auctions — admin bidding console.
    // Custom/literal routes registered BEFORE the {auction} routes so the
    // literal `create` and `cards/search` segments are not shadowed.
    Route::get('auctions', [Admin\AuctionController::class, 'index'])->name('auctions.index');
    Route::get('auctions/create', [Admin\AuctionController::class, 'create'])->name('auctions.create');
    Route::post('auctions', [Admin\AuctionController::class, 'store'])->name('auctions.store');
    Route::get('auctions/cards/search', [Admin\AuctionController::class, 'cardSearch'])->name('auctions.cards.search');
    Route::get('auctions/{auction}/edit', [Admin\AuctionController::class, 'edit'])->name('auctions.edit');
    Route::patch('auctions/{auction}', [Admin\AuctionController::class, 'update'])->name('auctions.update');
    Route::delete('auctions/{auction}', [Admin\AuctionController::class, 'destroy'])->name('auctions.destroy');
    Route::patch('auctions/{auction}/refund', [Admin\AuctionController::class, 'resolveRefund'])->name('auctions.resolveRefund');
});

require __DIR__.'/auth.php';
