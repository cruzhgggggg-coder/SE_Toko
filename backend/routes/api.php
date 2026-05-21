<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard & Notifications
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);

    // Products
    Route::apiResource('products', ProductController::class);
    Route::post('/products/{id}/stock', [ProductController::class, 'addStock']);
    Route::get('/products/{id}/history', [ProductController::class, 'stockHistory']);

    // Customers
    Route::apiResource('customers', CustomerController::class);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::post('/reports/profit', [TransactionController::class, 'profitReport']);
    Route::post('/reports/detailed', [TransactionController::class, 'detailedReport']);
    
    // Financial Reports (Persistence)
    Route::get('/financial-reports', [\App\Http\Controllers\Api\FinancialReportController::class, 'index']);
    Route::get('/financial-reports/generate', [\App\Http\Controllers\Api\FinancialReportController::class, 'generate']);
    Route::post('/financial-reports', [\App\Http\Controllers\Api\FinancialReportController::class, 'store']);
    Route::get('/financial-reports/{id}', [\App\Http\Controllers\Api\FinancialReportController::class, 'show']);

    // Debts
    Route::get('/debts', [\App\Http\Controllers\Api\DebtController::class, 'index']);
    Route::get('/debts/{id}', [\App\Http\Controllers\Api\DebtController::class, 'show']);
    Route::post('/debts/{id}/pay', [\App\Http\Controllers\Api\DebtController::class, 'pay']);

    // Backup & Recovery
    Route::get('/backup', [\App\Http\Controllers\Api\BackupController::class, 'backup']);
    Route::post('/restore', [\App\Http\Controllers\Api\BackupController::class, 'restore']);

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Api\SettingController::class, 'index']);
    Route::post('/settings', [\App\Http\Controllers\Api\SettingController::class, 'update']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);
});
