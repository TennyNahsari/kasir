<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\InventoryLedger;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryStockController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $query = InventoryStock::with(['product.category', 'location']);

        // Authorization: owner sees all, non-owner only sees data at their assigned location
        $user = auth()->user();
        if ($user->role !== 'owner' && $user->location_id) {
            $query->where('location_id', $user->location_id);
        }

        // Only filter by quantity > 0 if explicitly requested
        if ($request->has('hide_zero_stock') && $request->hide_zero_stock == true) {
            $query->where('quantity', '>', 0);
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $stocks = $query->orderBy('product_id')->paginate($request->per_page ?? 25);

        // Transform paginated data
        $stocks->getCollection()->transform(function ($stock) {
            return [
                'id' => $stock->id,
                'product_id' => $stock->product_id,
                'product_name' => $stock->product->name,
                'sku' => $stock->product->sku,
                'category' => $stock->product->category ? $stock->product->category->name : null,
                'location_id' => $stock->location_id,
                'location_name' => $stock->location->name,
                'quantity' => $stock->quantity,
                'reserved_quantity' => $stock->reserved_quantity,
                'available_quantity' => $stock->available_quantity,
                'reorder_level' => $stock->reorder_level,
                'uom' => $stock->product->uom,
                'last_stock_in' => $stock->last_stock_in,
                'last_stock_out' => $stock->last_stock_out,
            ];
        });

        return response()->json($stocks);
    }

    public function lowStock(Request $request)
    {
        $locationId = $request->get('location_id');
        $products = $this->inventoryService->getLowStockProducts($locationId);

        return response()->json($products);
    }

    public function adjust(Request $request)
    {
        // Authorization: only owner and supervisor can adjust stock
        $user = auth()->user();
        if (!in_array($user->role, ['owner', 'supervisor'])) {
            return response()->json([
                'message' => 'Access denied. Only Owner and Supervisor can adjust stock.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'new_quantity' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $ledger = $this->inventoryService->adjustStock(
                $request->product_id,
                $request->location_id,
                $request->new_quantity,
                $request->reorder_level,
                $request->notes,
                auth()->id()
            );

            return response()->json([
                'message' => 'Stock adjusted successfully',
                'ledger' => $ledger->load('product', 'location'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request)
    {
        // Authorization: only owner and supervisor can add stock
        $user = auth()->user();
        if (!in_array($user->role, ['owner', 'supervisor'])) {
            return response()->json([
                'message' => 'Access denied. Only Owner and Supervisor can add stock.'
            ], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'reserved_quantity' => 'nullable|numeric|min:0',
        ]);

        // Check if stock already exists for this product-location combination
        $existingStock = InventoryStock::where('product_id', $validated['product_id'])
            ->where('location_id', $validated['location_id'])
            ->first();

        if ($existingStock) {
            return response()->json([
                'message' => 'Stock entry already exists for this product at this location. Use adjust or update instead.'
            ], 422);
        }

        $stock = InventoryStock::create([
            'product_id' => $validated['product_id'],
            'location_id' => $validated['location_id'],
            'quantity' => $validated['quantity'],
            'reorder_level' => $validated['reorder_level'] ?? 0,
            'reserved_quantity' => $validated['reserved_quantity'] ?? 0,
        ]);

        // Create ledger entry for initial stock
        if ($validated['quantity'] > 0) {
            InventoryLedger::create([
                'product_id' => $validated['product_id'],
                'location_id' => $validated['location_id'],
                'movement_type' => 'STOCK_IN',
                'quantity' => $validated['quantity'],
                'balance_before' => 0,
                'balance_after' => $validated['quantity'],
                'reference_type' => 'INITIAL',
                'reference_id' => $stock->id,
                'notes' => $validated['notes'] ?? 'Initial stock entry',
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json($stock->load(['product', 'location']), 201);
    }

    public function update(Request $request, InventoryStock $inventoryStock)
    {
        $validated = $request->validate([
            'reorder_level' => 'nullable|numeric|min:0',
            'reserved_quantity' => 'nullable|numeric|min:0',
        ]);

        $inventoryStock->update($validated);

        return response()->json($inventoryStock->load(['product', 'location']));
    }

    public function destroy(InventoryStock $inventoryStock)
    {
        // Check if there's any quantity
        if ($inventoryStock->quantity > 0) {
            return response()->json([
                'message' => 'Cannot delete stock entry with quantity greater than 0. Adjust quantity to 0 first.'
            ], 422);
        }

        $inventoryStock->delete();

        return response()->json(['message' => 'Stock entry deleted successfully']);
    }

    public function ledger(Request $request)
    {
        $query = InventoryLedger::with(['product', 'location', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Authorization: owner sees all, non-owner only sees ledger at their assigned location
        $user = auth()->user();
        if ($user->role !== 'owner' && $user->location_id) {
            $query->where('location_id', $user->location_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $ledgers = $query->paginate($request->get('per_page', 50));

        return response()->json($ledgers);
    }
}
