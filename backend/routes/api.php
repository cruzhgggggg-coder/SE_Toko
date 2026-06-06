<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes with Role-Based Authorization
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard & Notifications — accessible by all authenticated roles
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);

    // Products — owner + admin (inventory management)
    Route::middleware('role:owner,admin')->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::post('/products/{id}/stock', [ProductController::class, 'addStock']);
        Route::post('/products/{id}/discard-batch', [ProductController::class, 'discardBatch']);
        Route::get('/products/{id}/history', [ProductController::class, 'stockHistory']);
    });

    // Customers — owner + kasir
    Route::middleware('role:owner,kasir')->group(function () {
        Route::apiResource('customers', CustomerController::class);
    });

    // Transactions — owner + kasir
    Route::middleware('role:owner,kasir')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    });

    // Reports — owner only
    Route::middleware('role:owner')->group(function () {
        Route::post('/reports/profit', [TransactionController::class, 'profitReport']);
        Route::post('/reports/detailed', [TransactionController::class, 'detailedReport']);

        // Financial Reports (Persistence)
        Route::get('/financial-reports', [\App\Http\Controllers\Api\FinancialReportController::class, 'index']);
        Route::get('/financial-reports/generate', [\App\Http\Controllers\Api\FinancialReportController::class, 'generate']);
        Route::post('/financial-reports', [\App\Http\Controllers\Api\FinancialReportController::class, 'store']);
        Route::get('/financial-reports/{id}', [\App\Http\Controllers\Api\FinancialReportController::class, 'show']);
    });

    // Debts — owner + kasir
    Route::middleware('role:owner,kasir')->group(function () {
        Route::get('/debts', [\App\Http\Controllers\Api\DebtController::class, 'index']);
        Route::get('/debts/{id}', [\App\Http\Controllers\Api\DebtController::class, 'show']);
        Route::post('/debts/{id}/pay', [\App\Http\Controllers\Api\DebtController::class, 'pay']);
    });

    // Backup & Recovery — owner only (CRITICAL: restrict database operations)
    Route::middleware('role:owner')->group(function () {
        Route::get('/backup', [\App\Http\Controllers\Api\BackupController::class, 'backup']);
        Route::post('/restore', [\App\Http\Controllers\Api\BackupController::class, 'restore']);
    });

    // Settings — owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\Api\SettingController::class, 'index']);
        Route::post('/settings', [\App\Http\Controllers\Api\SettingController::class, 'update']);
    });

    // User Management — owner only (CRITICAL: prevent privilege escalation)
    Route::middleware('role:owner')->group(function () {
        Route::apiResource('users', \App\Http\Controllers\Api\UserController::class)->except(['show']);
    });

    // Categories & Suppliers — owner + admin
    Route::middleware('role:owner,admin')->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('suppliers', SupplierController::class);
    });
});
