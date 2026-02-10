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
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\ServiceContractController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController as TicketDashboardController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Public routes for QR Order (customer)
Route::get('/public/categories', [CategoryController::class, 'index']);
Route::get('/public/products', [ProductController::class, 'index']);
Route::post('/public/orders', [TransactionController::class, 'store']);

// Public routes for Asset QR Code (anyone can scan)
Route::get('/public/assets/{asset}', [AssetController::class, 'show']);
Route::get('/public/assets/{asset}/history', [AssetController::class, 'history']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/procurement-stats', [DashboardController::class, 'procurementStats']);
    Route::get('/dashboard/expected-deliveries', [DashboardController::class, 'expectedDeliveries']);
    Route::get('/dashboard/recent-purchase-requests', [DashboardController::class, 'recentPurchaseRequests']);
    Route::get('/reports/sales', [DashboardController::class, 'salesReport']);

    // Outlets (Owner only)
    Route::apiResource('outlets', OutletController::class);
    Route::post('/outlets/{outlet}/generate-qr', [OutletController::class, 'generateQrCodes']);

    // Users (Owner only)
    Route::apiResource('users', UserController::class);

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
    Route::post('/locations/{location}/generate-qr-codes', [LocationController::class, 'generateQrCodes']);
    
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
    
    // Assets
    Route::get('/assets', [AssetController::class, 'index']);
    Route::post('/assets', [AssetController::class, 'store']);
    Route::get('/assets/available', [AssetController::class, 'available']);
    Route::get('/assets/by-user/{userId}', [AssetController::class, 'byUser']);
    Route::get('/assets/{asset}', [AssetController::class, 'show']);
    Route::put('/assets/{asset}', [AssetController::class, 'update']);
    Route::post('/assets/{asset}/assign', [AssetController::class, 'assign']);
    Route::post('/assets/{asset}/return', [AssetController::class, 'return']);
    Route::post('/assets/{asset}/transfer', [AssetController::class, 'transfer']);
    Route::post('/assets/{asset}/dispose', [AssetController::class, 'dispose']);
    Route::get('/assets/{asset}/history', [AssetController::class, 'history']);
    Route::post('/assets/{asset}/history', [AssetController::class, 'addHistory']);
    
    // Service Contracts
    Route::get('/service-contracts', [ServiceContractController::class, 'index']);
    Route::post('/service-contracts', [ServiceContractController::class, 'store']);
    Route::get('/service-contracts/stats', [ServiceContractController::class, 'stats']);
    Route::get('/service-contracts/{id}', [ServiceContractController::class, 'show']);
    Route::put('/service-contracts/{id}', [ServiceContractController::class, 'update']);
    Route::post('/service-contracts/{id}/renew', [ServiceContractController::class, 'renew']);
    Route::post('/service-contracts/{id}/terminate', [ServiceContractController::class, 'terminate']);

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/statistics', [TicketController::class, 'statistics']);
    Route::get('/tickets/my-assets', [TicketController::class, 'myAssets']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::post('/tickets/{id}/worklogs', [TicketController::class, 'addWorklog']);
    Route::post('/tickets/{id}/attachments', [TicketController::class, 'uploadAttachment']);
    Route::delete('/tickets/{ticketId}/attachments/{attachmentId}', [TicketController::class, 'deleteAttachment']);
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);

    // Ticket Dashboard
    Route::get('/ticket-dashboard', [TicketDashboardController::class, 'index']);
});
