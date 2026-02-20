<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashFlow;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'outlet', 'items.product', 'table']);

        // Filter by location or outlet
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        } elseif ($request->has('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        } elseif (auth()->user()->location_id) {
            // User with location_id: filter by location
            $query->where('location_id', auth()->user()->location_id);
        } elseif (auth()->user()->outlet_id) {
            // User with outlet_id: filter by outlet
            $query->where('outlet_id', auth()->user()->outlet_id);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by business type
        if ($request->has('business_type')) {
            $query->where('business_type', $request->business_type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($transactions);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processed,delivered,completed,void,refund',
        ]);

        $transaction->update([
            'status' => $validated['status'],
            'completed_at' => in_array($validated['status'], ['completed', 'void', 'refund']) ? now() : null,
        ]);

        return response()->json($transaction->load(['user', 'outlet', 'items.product', 'table']));
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'outlet_id' => 'sometimes|exists:outlets,id',
                'location_id' => 'sometimes|exists:locations,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
                'items.*.notes' => 'nullable|string',
                'items.*.product_name' => 'nullable|string',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|in:cash,qris,transfer,ewallet,multiple',
                'payment_details' => 'nullable|array',
                'paid_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'table_id' => 'nullable|exists:tables,id',
            ]);
            
            // If location_id is provided, get outlet_id from location
            if (isset($validated['location_id']) && !isset($validated['outlet_id'])) {
                $location = \App\Models\Location::find($validated['location_id']);
                if (!$location || !$location->outlet_id) {
                    return response()->json([
                        'message' => 'Location does not have an associated outlet',
                        'location_id' => $validated['location_id']
                    ], 422);
                }
                $validated['outlet_id'] = $location->outlet_id;
                // Keep location_id for filtering
                \Log::info('Got outlet_id from location', [
                    'location_id' => $validated['location_id'],
                    'outlet_id' => $validated['outlet_id']
                ]);
            }
            
            // Ensure outlet_id is set
            if (!isset($validated['outlet_id'])) {
                return response()->json([
                    'message' => 'Either outlet_id or location_id must be provided'
                ], 422);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        return DB::transaction(function () use ($validated, $request) {
            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemSubtotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $itemSubtotal;
            }

            $discount = $validated['discount'] ?? 0;
            $tax = $validated['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;
            $paidAmount = $validated['paid_amount'] ?? null;
            $changeAmount = $paidAmount ? ($paidAmount - $total) : null;

            // Get outlet to determine business_type
            $outlet = \App\Models\Outlet::find($validated['outlet_id']);
            $businessType = $outlet ? $outlet->business_type : 'retail';
            
            \Log::info('Creating transaction', [
                'outlet_id' => $validated['outlet_id'],
                'outlet_name' => $outlet ? $outlet->name : 'Unknown',
                'business_type' => $businessType,
                'table_id' => $validated['table_id'] ?? null
            ]);

            // Create transaction
            $transaction = Transaction::create([
                'transaction_no' => Transaction::generateTransactionNo($validated['outlet_id']),
                'outlet_id' => $validated['outlet_id'],
                'location_id' => $validated['location_id'] ?? null, // Save location_id if provided
                'business_type' => $businessType,
                'user_id' => auth()->id() ?? null, // Allow null for public orders
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_details' => $validated['payment_details'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'table_id' => $validated['table_id'] ?? null,
                'status' => $paidAmount && $paidAmount > 0 ? 'completed' : 'pending',
                'completed_at' => $paidAmount && $paidAmount > 0 ? now() : null,
            ]);

            // Create transaction items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $itemSubtotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Decrease inventory stock for the location
                // Use location_id from request if available, otherwise lookup location for outlet
                $locationId = $validated['location_id'] ?? null;
                
                if (!$locationId) {
                    // Find location for this outlet
                    $location = \App\Models\Location::where('outlet_id', $validated['outlet_id'])
                        ->where('type', 'OUTLET')
                        ->first();
                    $locationId = $location?->id;
                }
                
                if ($locationId) {
                    $inventoryStock = InventoryStock::where('product_id', $product->id)
                        ->where('location_id', $locationId)
                        ->first();
                    
                    if ($inventoryStock) {
                        $inventoryStock->decrement('quantity', $item['quantity']);
                        $inventoryStock->update(['last_stock_out' => now()]);
                        
                        \Log::info('Inventory stock updated', [
                            'product_id' => $product->id,
                            'location_id' => $locationId,
                            'quantity_reduced' => $item['quantity']
                        ]);
                    } else {
                        \Log::warning('Inventory stock not found for product', [
                            'product_id' => $product->id,
                            'location_id' => $locationId
                        ]);
                    }
                } else {
                    \Log::error('No location_id found for outlet', [
                        'outlet_id' => $validated['outlet_id']
                    ]);
                }
            }

            // Record cash flow
            CashFlow::create([
                'outlet_id' => $validated['outlet_id'],
                'user_id' => auth()->id(),
                'type' => 'in',
                'amount' => $total,
                'category' => 'penjualan',
                'description' => "Transaksi #{$transaction->transaction_no}",
                'transaction_id' => $transaction->id,
            ]);

            // Log activity
            ActivityLog::log('create_transaction', $transaction, [
                'transaction_no' => $transaction->transaction_no,
                'total' => $total,
            ]);

            return response()->json($transaction->load(['items.product', 'outlet', 'user']), 201);
        });
    }

    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load(['items.product', 'outlet', 'user', 'table']));
    }

    public function void(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return response()->json(['message' => 'Only completed transactions can be voided'], 400);
        }

        return DB::transaction(function () use ($transaction) {
            // Restore inventory stock
            foreach ($transaction->items as $item) {
                $inventoryStock = InventoryStock::where('product_id', $item->product_id)
                    ->where('location_id', $transaction->outlet_id)
                    ->first();
                
                if ($inventoryStock) {
                    $inventoryStock->increment('quantity', $item->quantity);
                    $inventoryStock->update(['last_stock_in' => now()]);
                }
            }

            // Update transaction status
            $transaction->update(['status' => 'void']);

            // Reverse cash flow
            CashFlow::create([
                'outlet_id' => $transaction->outlet_id,
                'user_id' => auth()->id(),
                'type' => 'out',
                'amount' => $transaction->total,
                'category' => 'void_transaksi',
                'description' => "Void transaksi #{$transaction->transaction_no}",
                'transaction_id' => $transaction->id,
            ]);

            // Log activity
            ActivityLog::log('void_transaction', $transaction, [
                'transaction_no' => $transaction->transaction_no,
            ]);

            return response()->json(['message' => 'Transaction voided successfully']);
        });
    }
}
