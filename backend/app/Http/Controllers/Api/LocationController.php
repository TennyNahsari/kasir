<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::with('outlet');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        $locations = $query->orderBy('name')->get();

        return response()->json($locations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:locations,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:WAREHOUSE,OUTLET',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'person_in_charge' => 'nullable|string|max:255',
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = Location::create($request->all());

        return response()->json($location->load('outlet'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        return response()->json($location->load('outlet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:locations,code,' . $location->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:WAREHOUSE,OUTLET',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'person_in_charge' => 'nullable|string|max:255',
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location->update($request->all());

        return response()->json($location->load('outlet'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        if ($location->inventoryStocks()->exists()) {
            return response()->json([
                'message' => 'Cannot delete location with existing inventory stocks'
            ], 422);
        }

        $location->delete();

        return response()->json(['message' => 'Location deleted successfully']);
    }

    public function stockSummary(Location $location)
    {
        $stocks = $location->inventoryStocks()
            ->with('product.category')
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($stock) {
                return [
                    'product_id' => $stock->product_id,
                    'product_name' => $stock->product->name,
                    'category' => $stock->product->category->name,
                    'quantity' => $stock->quantity,
                    'reserved_quantity' => $stock->reserved_quantity,
                    'available_quantity' => $stock->available_quantity,
                    'uom' => $stock->product->uom,
                ];
            });

        return response()->json($stocks);
    }
}
