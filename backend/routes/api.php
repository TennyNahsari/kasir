<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\InventoryStockController;
use App\Http\Controllers\Api\InventoryTransferController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\GoodsReceiptController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Public routes for QR Order (customer)
Route::get('/public/categories', [CategoryController::class, 'index']);
Route::get('/public/products', [ProductController::class, 'index']);
Route::post('/public/orders', [TransactionController::class, 'store']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/reports/sales', [DashboardController::class, 'salesReport']);

    // Outlets (Owner only)
    Route::apiResource('outlets', OutletController::class);
    Route::post('/outlets/{outlet}/generate-qr', [OutletController::class, 'generateQrCodes']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('/products-by-location', [ProductController::class, 'getByLocation']);
    Route::post('/products/find-barcode', [ProductController::class, 'findByBarcode']);
    Route::post('/products/{product}/generate-barcode', [ProductController::class, 'generateBarcode']);

    // Transactions
    Route::apiResource('transactions', TransactionController::class);
    Route::post('/transactions/{transaction}/void', [TransactionController::class, 'void']);

    // ==================== INVENTORY & PROCUREMENT ROUTES ====================
    
    // Locations (Warehouses & Outlets)
    Route::apiResource('locations', LocationController::class);
    Route::get('/locations/{location}/stock-summary', [LocationController::class, 'stockSummary']);
    
    // Vendors
    Route::apiResource('vendors', VendorController::class);
    
    // Departments
    Route::apiResource('departments', DepartmentController::class);
    
    // Inventory Stocks
    Route::get('/inventory-stocks', [InventoryStockController::class, 'index']);
    Route::post('/inventory-stocks', [InventoryStockController::class, 'store']);
    Route::put('/inventory-stocks/{inventoryStock}', [InventoryStockController::class, 'update']);
    Route::delete('/inventory-stocks/{inventoryStock}', [InventoryStockController::class, 'destroy']);
    Route::get('/inventory-stocks/low-stock', [InventoryStockController::class, 'lowStock']);
    Route::post('/inventory-stocks/adjust', [InventoryStockController::class, 'adjust']);
    Route::get('/inventory-stocks/ledger', [InventoryStockController::class, 'ledger']);
    
    // Inventory Transfers
    Route::apiResource('inventory-transfers', InventoryTransferController::class);
    Route::post('/inventory-transfers/{transfer}/submit', [InventoryTransferController::class, 'submit']);
    Route::post('/inventory-transfers/{transfer}/approve', [InventoryTransferController::class, 'approve']);
    Route::post('/inventory-transfers/{transfer}/receive', [InventoryTransferController::class, 'receive']);
    Route::post('/inventory-transfers/{transfer}/cancel', [InventoryTransferController::class, 'cancel']);
    
    // Purchase Requests (PR)
    Route::apiResource('purchase-requests', PurchaseRequestController::class);
    Route::post('/purchase-requests/{purchaseRequest}/submit', [PurchaseRequestController::class, 'submit']);
    Route::post('/purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve']);
    Route::post('/purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject']);
    Route::post('/purchase-requests/{purchaseRequest}/cancel', [PurchaseRequestController::class, 'cancel']);
    Route::get('/purchase-requests/pending/list', [PurchaseRequestController::class, 'pendingList']);
    
    // Purchase Orders (PO)
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit']);
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve']);
    Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send']);
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
    Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPdf']);
    
    // Goods Receipts (GRN)
    Route::apiResource('goods-receipts', GoodsReceiptController::class);
    Route::post('/goods-receipts/{goodsReceipt}/quality-check', [GoodsReceiptController::class, 'qualityCheck']);
    Route::post('/goods-receipts/{goodsReceipt}/approve', [GoodsReceiptController::class, 'approve']);
    Route::post('/goods-receipts/{goodsReceipt}/post', [GoodsReceiptController::class, 'post']);
    Route::post('/goods-receipts/{goodsReceipt}/reject', [GoodsReceiptController::class, 'reject']);
    Route::get('/goods-receipts/by-po/{poId}', [GoodsReceiptController::class, 'byPurchaseOrder']);
});
