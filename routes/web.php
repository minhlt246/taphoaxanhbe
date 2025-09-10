<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserController;

// Dashboard routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// API routes for dashboard data
Route::prefix('api')->group(function () {
    // Dashboard metrics
    Route::get('/metrics', [DashboardController::class, 'metrics']);
    Route::get('/revenue-overview', [DashboardController::class, 'revenueOverview']);
    Route::get('/daily-revenue', [DashboardController::class, 'dailyRevenue']);
    Route::get('/weekly-revenue', [DashboardController::class, 'weeklyRevenue']);
    Route::get('/monthly-revenue', [DashboardController::class, 'monthlyRevenue']);
    Route::get('/recent-orders', [DashboardController::class, 'recentOrders']);
    Route::get('/system-stats', [DashboardController::class, 'systemStats']);
    
    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('/products/stats', [ProductController::class, 'stats']);
    Route::get('/products/top-purchased', [ProductController::class, 'topPurchased']);
    
    // Categories
    Route::apiResource('categories', CategoryController::class);
    
    // Users
    Route::apiResource('users', UserController::class);
    Route::post('/users/update-activity', [UserController::class, 'updateActivity'])->name('admin.users.update-activity');
    
    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::get('/orders/{order}/approve', [OrderController::class, 'approve']);
    Route::get('/orders/{order}/reject', [OrderController::class, 'reject']);
    Route::get('/orders/{order}/shipped', [OrderController::class, 'markAsShipped']);
    Route::get('/orders/{order}/delivered', [OrderController::class, 'markAsDelivered']);
    Route::post('/orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->withoutMiddleware(['web']);
    Route::get('/orders/{order}/delete', [OrderController::class, 'destroy']);
    
    // Reviews
    Route::apiResource('reviews', ReviewController::class);
    Route::get('/reviews/{review}/approve', [ReviewController::class, 'approve']);
    Route::get('/reviews/{review}/reject', [ReviewController::class, 'reject']);
    Route::get('/reviews/{review}/delete', [ReviewController::class, 'destroy']);
    
    // Vouchers
    Route::apiResource('vouchers', VoucherController::class);
    
    // Articles
    Route::apiResource('articles', ArticleController::class);
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
    
    // Articles
    Route::get('/articles', [ArticleController::class, 'index'])->name('admin.articles.index');
    Route::get('/articles/create', [ArticleController::class, 'create'])->name('admin.articles.create');
    Route::post('/articles', [ArticleController::class, 'store'])->name('admin.articles.store');
    Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('admin.articles.show');
    Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->name('admin.articles.edit');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('admin.articles.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('admin.articles.destroy');
    
    // Users
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});

// API Routes for Dashboard Statistics
Route::prefix('api')->group(function () {
    Route::get('/metrics', [DashboardController::class, 'metrics']);
    Route::get('/revenue-overview', [DashboardController::class, 'revenueOverview']);
    Route::get('/revenue-chart', [DashboardController::class, 'revenueChart']);
    Route::get('/daily-revenue', [DashboardController::class, 'dailyRevenue']);
    Route::get('/weekly-revenue', [DashboardController::class, 'weeklyRevenue']);
    Route::get('/monthly-revenue', [DashboardController::class, 'monthlyRevenue']);
    Route::get('/recent-orders', [DashboardController::class, 'recentOrders']);
    Route::get('/system-stats', [DashboardController::class, 'systemStats']);
    Route::get('/top-selling-products', [DashboardController::class, 'topSellingProducts']);
    Route::get('/user-growth', [DashboardController::class, 'userGrowth']);
    Route::get('/order-status-distribution', [DashboardController::class, 'orderStatusDistribution']);
    Route::get('/monthly-sales-comparison', [DashboardController::class, 'monthlySalesComparison']);
    Route::get('/average-order-value', [DashboardController::class, 'averageOrderValue']);
    Route::get('/conversion-rate', [DashboardController::class, 'conversionRate']);
    
    // Voucher API routes
    Route::get('/vouchers', [VoucherController::class, 'apiIndex']);
    Route::post('/vouchers/use', [VoucherController::class, 'useVoucher'])->withoutMiddleware(['web']);
    Route::post('/vouchers/check', [VoucherController::class, 'checkVoucher'])->withoutMiddleware(['web']);
    Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->withoutMiddleware(['web']);
});

Auth::routes();
