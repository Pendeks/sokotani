<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminFarmerController;
use App\Http\Controllers\FarmerProfileController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [MarketplaceController::class, 'index'])->name('home');
Route::get('/products/{product}', [MarketplaceController::class, 'show'])->name('products.show');
Route::get('/farmers/{farmer}', [FarmerProfileController::class, 'show'])->name('farmers.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/dashboard', [MarketplaceController::class, 'dashboard'])->name('dashboard');

    Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
    Route::middleware('role:buyer')->group(function () {
        Route::post('/products/{product}/offers', [OfferController::class, 'store'])->name('offers.store');
        Route::post('/offers/{offer}/respond', [OfferController::class, 'buyerRespond'])->name('offers.buyer.respond');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/farmers', [AdminFarmerController::class, 'index'])->name('farmers.index');
        Route::patch('/farmers/{farmer}/verification', [AdminFarmerController::class, 'setVerification'])->name('farmers.verification');
    });

    Route::prefix('farmer')->name('farmer.')->middleware('role:farmer')->group(function () {
        Route::get('/profile', [FarmerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [FarmerProfileController::class, 'update'])->name('profile.update');
        Route::get('/products', [MarketplaceController::class, 'myListings'])->name('listings.index');
        Route::get('/products/create', [MarketplaceController::class, 'create'])->name('listings.create');
        Route::post('/products', [MarketplaceController::class, 'store'])->name('listings.store');
        Route::get('/products/{product}/edit', [MarketplaceController::class, 'edit'])->name('listings.edit');
        Route::put('/products/{product}', [MarketplaceController::class, 'update'])->name('listings.update');
        Route::patch('/products/{product}/deactivate', [MarketplaceController::class, 'deactivate'])->name('listings.deactivate');
        Route::post('/offers/{offer}/respond', [OfferController::class, 'farmerRespond'])->name('offers.respond');
        Route::post('/offers/{offer}/complete', [OfferController::class, 'complete'])->name('offers.complete');
    });
});
