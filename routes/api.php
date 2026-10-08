<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ===== MASTER DATA =====
    Route::get('/company', [\App\Http\Controllers\Master\CompanyController::class, 'show']);
    Route::put('/company', [\App\Http\Controllers\Master\CompanyController::class, 'update']);

    // Hierarki 4 level: divisions|departments|classes|subclasses
    foreach (['divisions', 'departments', 'classes', 'subclasses'] as $key) {
        Route::get("/$key", [\App\Http\Controllers\Master\MasterDataController::class, 'index'])->defaults('key', $key);
        Route::post("/$key", [\App\Http\Controllers\Master\MasterDataController::class, 'store'])->defaults('key', $key);
        Route::put("/$key/{id}", [\App\Http\Controllers\Master\MasterDataController::class, 'update'])->defaults('key', $key);
        Route::delete("/$key/{id}", [\App\Http\Controllers\Master\MasterDataController::class, 'destroy'])->defaults('key', $key);
    }

    Route::get('/loyalty-rules', [\App\Http\Controllers\Master\LoyaltyRuleController::class, 'show']);
    Route::put('/loyalty-rules', [\App\Http\Controllers\Master\LoyaltyRuleController::class, 'update']);

    Route::apiResource('events', \App\Http\Controllers\Master\EventController::class)->parameters(['events' => 'id']);
    Route::post('/events/{id}/close', [\App\Http\Controllers\Master\EventController::class, 'close']);

    Route::apiResource('vendors', \App\Http\Controllers\Master\VendorController::class)->parameters(['vendors' => 'id']);
    Route::apiResource('products', \App\Http\Controllers\Master\ProductController::class)->parameters(['products' => 'id']);
    Route::post('/products/bulk-inject', [\App\Http\Controllers\Master\ProductController::class, 'bulkInject']);

    Route::get('/roles', [\App\Http\Controllers\Master\RoleController::class, 'index']);
    Route::apiResource('users', \App\Http\Controllers\Master\UserController::class)->parameters(['users' => 'id']);

    // ===== OPERASIONAL =====
    Route::get('/shifts/current', [\App\Http\Controllers\Operational\ShiftController::class, 'current']);
    Route::post('/shifts/open', [\App\Http\Controllers\Operational\ShiftController::class, 'open']);
    Route::post('/shifts/close', [\App\Http\Controllers\Operational\ShiftController::class, 'close']);

    Route::post('/pos/sales', [\App\Http\Controllers\Operational\PosSaleController::class, 'store']);
    Route::post('/refunds', [\App\Http\Controllers\Operational\RefundController::class, 'store']);
    Route::get('/receiving', [\App\Http\Controllers\Operational\ReceivingController::class, 'index']);
    Route::post('/receiving', [\App\Http\Controllers\Operational\ReceivingController::class, 'store']);
    Route::get('/vendor-returns', [\App\Http\Controllers\Operational\VendorReturnController::class, 'index']);
    Route::post('/vendor-returns', [\App\Http\Controllers\Operational\VendorReturnController::class, 'store']);
    Route::get('/product-loads', [\App\Http\Controllers\Operational\ProductLoadController::class, 'index']);
    Route::post('/product-loads', [\App\Http\Controllers\Operational\ProductLoadController::class, 'store']);
    Route::get('/stock-mutations', [\App\Http\Controllers\Operational\StockMutationController::class, 'index']);

    // ===== CRM MEMBER (spec: tabel Member + Transaksi_member) =====
    Route::get('/members', [\App\Http\Controllers\Master\MemberController::class, 'index']);
    Route::post('/members', [\App\Http\Controllers\Master\MemberController::class, 'store']);
    Route::get('/members/{id}', [\App\Http\Controllers\Master\MemberController::class, 'show']);   // id_anggota atau no HP
    Route::put('/members/{id}', [\App\Http\Controllers\Master\MemberController::class, 'update']);
    Route::delete('/members/{id}', [\App\Http\Controllers\Master\MemberController::class, 'destroy']);
    Route::post('/members/{id}/points', [\App\Http\Controllers\Master\MemberController::class, 'points']);

    // ===== LAPORAN (12 spec + export csv stream) =====
    $rep = \App\Http\Controllers\Operational\ReportController::class;
    Route::middleware('permission:view_reports')->group(function () use ($rep) {
        Route::get('/reports', [$rep, 'index']);
        Route::get('/reports/vendor-receiving', [$rep, 'vendorReceiving']);
        Route::get('/reports/internal-receiving', [$rep, 'internalReceiving']);
        Route::get('/reports/vendor-return', [$rep, 'vendorReturn']);
        Route::get('/reports/receipts-recap', [$rep, 'receiptsRecap']);
        Route::get('/reports/receipts-detail', [$rep, 'receiptsDetail']);
        Route::get('/reports/omzet-produk', [$rep, 'omzetProduk']);
        Route::get('/reports/omzet-departemen', [$rep, 'omzetDepartemen']);
        Route::get('/reports/omzet-kasir', [$rep, 'omzetKasir']);
        Route::get('/reports/member-baru', [$rep, 'memberBaru']);
        Route::get('/reports/member-riwayat', [$rep, 'memberRiwayat']);
        Route::get('/reports/till-closing', [$rep, 'tillClosing']);
        Route::get('/reports/event-closing', [$rep, 'eventClosing']);
    });

    // ===== CUSTOMER (base CRM; member VIP pakai /members) =====
    Route::apiResource('customers', \App\Http\Controllers\CustomerController::class)->parameters(['customers' => 'id']);

    // ===== RIWAYAT TRANSAKSI + STRUK =====
    Route::get('/sales', [\App\Http\Controllers\SaleHistoryController::class, 'index']);
    Route::get('/sales/{sale}', [\App\Http\Controllers\SaleHistoryController::class, 'show']);
    Route::get('/sales/{sale}/receipt', [\App\Http\Controllers\ReceiptController::class, 'show']);

    // ===== PEMBELIAN / PO (spec: Pembelian ke vendor) =====
    Route::get('/purchase-orders', [\App\Http\Controllers\POController::class, 'index']);
    Route::post('/purchase-orders', [\App\Http\Controllers\POController::class, 'store']);
    Route::get('/purchase-orders/{id}', [\App\Http\Controllers\POController::class, 'show']);
    Route::patch('/purchase-orders/{id}/approve', [\App\Http\Controllers\POController::class, 'approve']);
    Route::patch('/purchase-orders/{id}/receive', [\App\Http\Controllers\POController::class, 'receive']);
    Route::patch('/purchase-orders/{id}/cancel', [\App\Http\Controllers\POController::class, 'cancel']);

    // ===== DASHBOARD =====
    Route::get('/dashboard/kpi', [\App\Http\Controllers\DashboardController::class, 'kpi']);
    Route::get('/dashboard/sales-trend', [\App\Http\Controllers\DashboardController::class, 'salesTrend']);
    Route::get('/dashboard/demographics', [\App\Http\Controllers\DashboardController::class, 'demographics']);
    Route::get('/dashboard/top-products', [\App\Http\Controllers\DashboardController::class, 'topProducts']);
    Route::get('/dashboard/top-books', [\App\Http\Controllers\DashboardController::class, 'topProducts']); // alias lama
    Route::get('/dashboard/low-stock', [\App\Http\Controllers\DashboardController::class, 'lowStock']);
    Route::get('/dashboard/event-revenue', [\App\Http\Controllers\DashboardController::class, 'eventRevenue']);

    // ===== EXPORT =====
    Route::get('/export/sales', [\App\Http\Controllers\ExportController::class, 'sales']);
    Route::get('/export/purchase-orders', [\App\Http\Controllers\ExportController::class, 'purchaseOrders']);
});
