<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\UserController;

// Dashboard routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// API routes
Route::prefix('api')->group(function () {
    // Dashboard metrics
    Route::get('/metrics', [DashboardController::class, 'metrics']);
    Route::get('/revenue-overview', [DashboardController::class, 'revenueOverview']);
    Route::get('/daily-revenue', [DashboardController::class, 'dailyRevenue']);
    Route::get('/weekly-revenue', [DashboardController::class, 'weeklyRevenue']);
    Route::get('/monthly-revenue', [DashboardController::class, 'monthlyRevenue']);
    Route::get('/recent-orders', [DashboardController::class, 'recentOrders']);
    Route::get('/system-stats', [DashboardController::class, 'systemStats']);
    Route::get('/revenue-chart', [DashboardController::class, 'revenueChart']);
    Route::get('/top-selling-products', [DashboardController::class, 'topSellingProducts']);
    Route::get('/user-growth', [DashboardController::class, 'userGrowth']);
    Route::get('/order-status-distribution', [DashboardController::class, 'orderStatusDistribution']);
    Route::get('/monthly-sales-comparison', [DashboardController::class, 'monthlySalesComparison']);
    Route::get('/average-order-value', [DashboardController::class, 'averageOrderValue']);
    Route::get('/conversion-rate', [DashboardController::class, 'conversionRate']);
    
    // Products API
    Route::apiResource('products', ProductController::class);
    Route::get('/products-statistics', [ProductController::class, 'stats']);
    Route::get('/products/top-purchased', [ProductController::class, 'topPurchased']);
    
    // Categories API
    Route::apiResource('categories', CategoryController::class);
    
    // Users API
    Route::apiResource('users', UserController::class);
    Route::post('/users/update-activity', [UserController::class, 'updateActivity']);
    
    // Orders API
    Route::apiResource('orders', OrderController::class);
    Route::get('/orders/{order}/approve', [OrderController::class, 'approve']);
    Route::get('/orders/{order}/reject', [OrderController::class, 'reject']);
    Route::get('/orders/{order}/shipped', [OrderController::class, 'markAsShipped']);
    Route::get('/orders/{order}/delivered', [OrderController::class, 'markAsDelivered']);
    Route::post('/orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->withoutMiddleware(['web']);
    
    // Reviews API
    Route::apiResource('reviews', ReviewController::class);
    Route::get('/reviews/{review}/approve', [ReviewController::class, 'approve']);
    Route::get('/reviews/{review}/reject', [ReviewController::class, 'reject']);
    
    // Vouchers API
    Route::apiResource('vouchers', VoucherController::class);
    Route::post('/vouchers/use', [VoucherController::class, 'useVoucher'])->withoutMiddleware(['web']);
    Route::post('/vouchers/check', [VoucherController::class, 'checkVoucher'])->withoutMiddleware(['web']);
    
    // News API
    Route::get('/news', [NewsController::class, 'apiIndex']);
    Route::post('/news', [NewsController::class, 'store']);
    Route::get('/news/{news}', [NewsController::class, 'show']);
    Route::put('/news/{news}', [NewsController::class, 'update']);
    Route::delete('/news/{news}', [NewsController::class, 'destroy']);
});

// Admin panel routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('admin.products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('admin.categories.show');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');
    
    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('admin.reviews.show');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('admin.reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('admin.reviews.approve');
    Route::post('/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('admin.reviews.reject');
    
    // Vouchers
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('admin.vouchers.index');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('admin.vouchers.create');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('admin.vouchers.store');
    Route::get('/vouchers/{voucher}', [VoucherController::class, 'show'])->name('admin.vouchers.show');
    Route::get('/vouchers/{voucher}/edit', [VoucherController::class, 'edit'])->name('admin.vouchers.edit');
    Route::put('/vouchers/{voucher}', [VoucherController::class, 'update'])->name('admin.vouchers.update');
    Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('admin.vouchers.destroy');
    
    // News
    Route::get('/news', [NewsController::class, 'index'])->name('admin.news.index');
    Route::get('/news/create', [NewsController::class, 'create'])->name('admin.news.create');
    Route::post('/news', [NewsController::class, 'store'])->name('admin.news.store');
    Route::get('/news/{news}', [NewsController::class, 'show'])->name('admin.news.show');
    Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->name('admin.news.edit');
    Route::put('/news/{news}', [NewsController::class, 'update'])->name('admin.news.update');
    Route::delete('/news/{news}', [NewsController::class, 'destroy'])->name('admin.news.destroy');
    Route::post('/news/{news}/approve', [NewsController::class, 'approve'])->name('admin.news.approve');
    Route::post('/news/{news}/reject', [NewsController::class, 'reject'])->name('admin.news.reject');
    Route::post('/news/{news}/publish', [NewsController::class, 'publish'])->name('admin.news.publish');
    Route::post('/news/{news}/unpublish', [NewsController::class, 'unpublish'])->name('admin.news.unpublish');
    
    // Users
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});

Auth::routes();
