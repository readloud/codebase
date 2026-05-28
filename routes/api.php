<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvestorController;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/social-login', [AuthController::class, 'socialLogin']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOTP']);
Route::post('/webhooks/midtrans', [PaymentController::class, 'webhook']);

// Product public routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/brands', [ProductController::class, 'brands']);

// Public content routes
Route::get('/sliders', [ContentController::class, 'sliders']);
Route::get('/banners', [ContentController::class, 'banners']);
Route::get('/faqs', [ContentController::class, 'faqs']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // User profile
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::get('/user/balance', [UserController::class, 'balance']);
    Route::post('/user/topup', [UserController::class, 'topupBalance']);
    
    // Orders
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'history']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'track']);
    
    // Investor routes
    Route::get('/investor/dashboard', [InvestorController::class, 'dashboard']);
    Route::post('/investor/invest', [InvestorController::class, 'invest']);
    Route::get('/investor/profits', [InvestorController::class, 'profits']);
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Product management
        Route::get('/products', [AdminController::class, 'products']);
        Route::post('/products', [AdminController::class, 'createProduct']);
        Route::put('/products/{id}', [AdminController::class, 'updateProduct']);
        Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);
        Route::post('/products/sync', [ProductController::class, 'syncFromDigiflazz']);
        
        // User management
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        
        // Topup management
        Route::post('/manual-topup', [AdminController::class, 'manualTopup']);
        Route::post('/deposit-vendor', [AdminController::class, 'depositToVendor']);
        
        // Content management
        Route::post('/banners', [AdminController::class, 'manageBanner']);
        Route::post('/sliders', [AdminController::class, 'manageSlider']);
        Route::post('/faqs', [AdminController::class, 'manageFaq']);
        
        // SEO settings
        Route::post('/seo', [AdminController::class, 'updateSEO']);
        
        // Settings
        Route::get('/settings', [AdminController::class, 'getSettings']);
        Route::post('/settings', [AdminController::class, 'manageSettings']);
        
        // Reports
        Route::get('/reports', [AdminController::class, 'getReports']);
        
        // Social media auto-share settings
        Route::post('/social-settings', [AdminController::class, 'updateSocialSettings']);
    });
});