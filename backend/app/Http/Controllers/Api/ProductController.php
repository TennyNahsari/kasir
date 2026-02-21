<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Debug logging
        \Log::info('ProductController::index called', [
            'type' => $request->type,
            'search' => $request->search,
            'per_page' => $request->per_page,
            'all_params' => $request->all(),
            'query_params' => $request->query(),
            'input_all' => $request->input(),
            'url' => $request->fullUrl(),
            'query_string' => $request->getQueryString()
        ]);

        $query = Product::with('category');

        // Filter by product type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // If location_id is provided, load stock for that location
        if ($request->has('location_id')) {
            $query->with(['inventoryStocks' => function($q) use ($request) {
                $q->where('location_id', $request->location_id);
            }]);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id !== '') {
            $query->where('category_id', $request->category_id);
        }

        // Filter active
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // Filter low stock (only when location_id is provided)
        if ($request->has('low_stock') && $request->low_stock) {
            if ($request->has('location_id')) {
                // For inventory system: check against inventory_stocks
                $query->whereHas('inventoryStocks', function($q) use ($request) {
                    $q->where('location_id', $request->location_id)
                      ->whereColumn('quantity', '<=', 'products.min_stock');
                });
            } else {
                // For legacy: check products.stock
                $query->whereColumn('stock', '<=', 'min_stock')
                      ->where('track_stock', true);
            }
        }

        // Default ordering: by created_at descending (newest first)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $query->orderBy($sortBy, $sortOrder);

        // Debug: Log SQL query
        \Log::info('ProductController SQL query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $products = $query->paginate($request->per_page ?? 25);
        
        // Debug: Log result count and types
        \Log::info('ProductController::index result', [
            'total' => $products->total(),
            'types' => $products->pluck('type')->unique()->values()
        ]);
        
        // Map inventory stock to product's stock field for easier access
        if ($request->has('location_id')) {
            $products->getCollection()->transform(function ($product) {
                if ($product->inventoryStocks->isNotEmpty()) {
                    $product->stock = $product->inventoryStocks->first()->quantity;
                } else {
                    $product->stock = 0;
                }
                return $product;
            });
        }

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|unique:products,sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'track_stock' => 'nullable|boolean',
            'type' => 'nullable|string|in:INVENTORY,ASSET,SERVICE',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('storage/products'), $imageName);
            $validated['image'] = 'products/' . $imageName;
        }

        $product = Product::create($validated);

        return response()->json($product->load('category'), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'sku' => 'sometimes|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string',
            'cost_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'track_stock' => 'nullable|boolean',
            'type' => 'nullable|string|in:INVENTORY,ASSET,SERVICE',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && file_exists(public_path('storage/' . $product->image))) {
                unlink(public_path('storage/' . $product->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('storage/products'), $imageName);
            $validated['image'] = 'products/' . $imageName;
        }

        $product->update($validated);

        return response()->json($product->load('category'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function findByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::with('category')
            ->where('barcode', $request->barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function generateBarcode(Product $product)
    {
        if ($product->barcode) {
            return response()->json(['message' => 'Product already has a barcode'], 400);
        }

        // Generate EAN-13 barcode (13 digits)
        $barcode = '200' . str_pad($product->id, 9, '0', STR_PAD_LEFT);
        
        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$barcode[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        $barcode .= $checkDigit;

        $product->update(['barcode' => $barcode]);

        return response()->json([
            'barcode' => $barcode,
            'product' => $product,
        ]);
    }

    public function getByLocation(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'is_active' => 'sometimes|in:true,false,1,0' // Accept string "true"/"false" or 1/0
        ]);
        
        $locationId = $validated['location_id'];
        
        \Log::info('getByLocation called', [
            'location_id' => $locationId,
            'all_query_params' => $request->query(),
            'validated' => $validated
        ]);

        // Get location info for debugging
        $location = \App\Models\Location::find($locationId);
        
        \Log::info('Location lookup', [
            'location_id' => $locationId,
            'location_found' => $location ? true : false,
            'location_name' => $location?->name,
            'location_type' => $location?->type
        ]);
        
        if (!$location) {
            return response()->json([
                'message' => 'Location not found.',
                'location_id' => $locationId
            ], 404);
        }

        // Authorization: Check if user can access this location
        $user = auth()->user();
        \Log::info('User authorization check', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_location_id' => $user->location_id,
            'user_outlet_id' => $user->outlet_id,
            'requested_location_id' => $locationId
        ]);

        // Owner and inventory can access all locations
        if (!in_array($user->role, ['owner', 'inventory'])) {
            $canAccess = false;
            
            if ($user->location_id) {
                // User assigned to specific location - can only access their location
                $canAccess = ($user->location_id == $locationId);
            } elseif ($user->outlet_id) {
                // User assigned to outlet - can access all locations in that outlet
                $canAccess = ($location->outlet_id == $user->outlet_id);
            }
            
            if (!$canAccess) {
                \Log::warning('Access denied to location', [
                    'user_id' => $user->id,
                    'requested_location_id' => $locationId,
                    'user_location_id' => $user->location_id,
                    'user_outlet_id' => $user->outlet_id,
                    'location_outlet_id' => $location->outlet_id
                ]);
                
                return response()->json([
                    'message' => 'Access denied. You do not have permission to access this location.',
                    'debug' => [
                        'your_role' => $user->role,
                        'your_location_id' => $user->location_id,
                        'your_outlet_id' => $user->outlet_id,
                        'requested_location_id' => $locationId,
                        'location_outlet_id' => $location->outlet_id
                    ]
                ], 403);
            }
        }

        $query = Product::with(['category'])
            ->join('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
            ->where('inventory_stocks.location_id', $locationId)
            ->where('products.is_active', true)
            ->select('products.*', 'inventory_stocks.quantity as stock', 'inventory_stocks.reserved_quantity');

        \Log::info('Query inventory_stocks', ['location_id' => $locationId]);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%")
                  ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        $query->orderBy('products.name', 'asc');

        $products = $query->get()->map(function($product) {
            $product->available_stock = $product->stock - $product->reserved_quantity;
            return $product;
        });

        \Log::info('Products retrieved', [
            'count' => $products->count(),
            'location_id' => $locationId,
            'location_name' => $location->name
        ]);

        // Add debug info if no products found
        if ($products->isEmpty()) {
            $stockCount = \App\Models\InventoryStock::where('location_id', $locationId)->count();
            $activeProductCount = Product::where('is_active', true)->count();
            
            return response()->json([
                'message' => 'No products found',
                'data' => [],
                'debug' => [
                    'location_id' => $locationId,
                    'location_name' => $location->name,
                    'location_type' => $location->type,
                    'inventory_stocks_count' => $stockCount,
                    'active_products_count' => $activeProductCount,
                    'hint' => $stockCount === 0 
                        ? 'No inventory stocks found for this location. Please add stock in inventory app.'
                        : 'Location has inventory stocks but no active products matched.'
                ]
            ]);
        }

        return response()->json($products);
    }
}
