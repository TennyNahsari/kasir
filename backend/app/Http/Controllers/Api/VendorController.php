<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhere('contact_person', 'ilike', "%{$search}%");
            });
        }

        $vendors = $query->orderBy('name')->get();

        return response()->json($vendors);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:vendors,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'business_license' => 'nullable|string|max:100',
            'payment_term_days' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendor = Vendor::create($request->all());

        return response()->json($vendor, 201);
    }

    public function show(Vendor $vendor)
    {
        return response()->json($vendor->load('purchaseOrders'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:vendors,code,' . $vendor->id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'business_license' => 'nullable|string|max:100',
            'payment_term_days' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendor->update($request->all());

        return response()->json($vendor);
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->purchaseOrders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete vendor with existing purchase orders'
            ], 422);
        }

        $vendor->delete();

        return response()->json(['message' => 'Vendor deleted successfully']);
    }
}
