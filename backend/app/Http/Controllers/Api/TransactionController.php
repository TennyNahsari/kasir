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

        // Filter by order type (dine_in / take_away)
        if ($request->has('order_type') && $request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
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
                'table_id' => 'nullable',
                'order_type' => 'nullable|in:dine_in,take_away',
                'customer_name' => 'nullable|string|max:255',
                'status' => 'nullable|in:pending,processed,delivered,completed,void,refund',
            ]);
            
            // If location_id is provided, get outlet_id from location
            if (isset($validated['location_id']) && !isset($validated['outlet_id'])) {
                $location = \App\Models\Location::find($validated['location_id']);
                
                if (!$location) {
                    return response()->json([
                        'message' => 'Location not found',
                        'location_id' => $validated['location_id']
                    ], 422);
                }
                
                // Check if location type is valid for POS (OUTLET or FNB)
                if (!in_array($location->type, ['OUTLET', 'FNB'])) {
                    return response()->json([
                        'message' => 'Invalid location type for POS. Only OUTLET and FNB locations are allowed.',
                        'location_type' => $location->type
                    ], 422);
                }
                
                // If location has outlet_id, use it
                if ($location->outlet_id) {
                    $validated['outlet_id'] = $location->outlet_id;
                    \Log::info('Got outlet_id from location', [
                        'location_id' => $validated['location_id'],
                        'outlet_id' => $validated['outlet_id']
                    ]);
                } else {
                    // Location doesn't have outlet_id, but it's OK for OUTLET/FNB type
                    // We'll use location_id directly
                    \Log::info('Location has no outlet_id, using location_id directly', [
                        'location_id' => $validated['location_id'],
                        'location_type' => $location->type
                    ]);
                }
            }
            
            // Ensure at least location_id or outlet_id is set
            if (!isset($validated['outlet_id']) && !isset($validated['location_id'])) {
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
            // If outlet_id is available, use it; otherwise infer from location
            $outlet = null;
            $businessType = 'retail'; // default
            $outletIdForTransaction = $validated['outlet_id'] ?? null;
            
            if ($outletIdForTransaction) {
                $outlet = \App\Models\Outlet::find($outletIdForTransaction);
                $businessType = $outlet ? $outlet->business_type : 'retail';
            } elseif (isset($validated['location_id'])) {
                // No outlet_id, use location to determine business type
                $location = \App\Models\Location::find($validated['location_id']);
                $businessType = ($location && $location->type === 'FNB') ? 'fnb' : 'retail';
            }
            
            \Log::info('Creating transaction', [
                'outlet_id' => $outletIdForTransaction,
                'location_id' => $validated['location_id'] ?? null,
                'outlet_name' => $outlet ? $outlet->name : 'N/A',
                'business_type' => $businessType,
                'table_id' => $validated['table_id'] ?? null
            ]);

            // Generate transaction number
            $transactionNo = $outletIdForTransaction 
                ? Transaction::generateTransactionNo($outletIdForTransaction)
                : 'TRX-' . now()->format('YmdHis') . '-' . rand(100, 999);

            // Resolve table_id or text table number
            $tableIdToStore = null;
            $tableVal = $validated['table_id'] ?? null;
            $notesToStore = $validated['notes'] ?? null;

            if (!empty($tableVal)) {
                if (is_numeric($tableVal) && \App\Models\Table::where('id', $tableVal)->exists()) {
                    $tableIdToStore = $tableVal;
                } else {
                    $foundTable = \App\Models\Table::where('table_number', (string)$tableVal)->first();
                    if ($foundTable) {
                        $tableIdToStore = $foundTable->id;
                    } else {
                        $tableNote = "Meja: {$tableVal}";
                        $notesToStore = $notesToStore ? ($notesToStore . " | " . $tableNote) : $tableNote;
                    }
                }
            }

            // Check if this is an F&B / QR Table order that should append to an active transaction
            $isFnbOrTableOrder = ($businessType === 'fnb')
                || (isset($location) && strtoupper($location->type) === 'FNB')
                || !empty($tableVal);

            $activeTransaction = null;

            if ($isFnbOrTableOrder && (!empty($tableIdToStore) || !empty($tableVal))) {
                $activeQuery = Transaction::whereIn('status', ['pending', 'processed', 'delivered']);

                if (isset($validated['location_id'])) {
                    $activeQuery->where('location_id', $validated['location_id']);
                } elseif ($outletIdForTransaction) {
                    $activeQuery->where('outlet_id', $outletIdForTransaction);
                }

                $activeQuery->where(function($q) use ($tableIdToStore, $tableVal) {
                    if ($tableIdToStore) {
                        $q->where('table_id', $tableIdToStore);
                    }
                    if ($tableVal) {
                        $q->orWhere('notes', 'like', "%Meja: {$tableVal}%");
                    }
                });

                $activeTransaction = $activeQuery->latest()->first();
            }

            if ($activeTransaction) {
                \Log::info('Appending order items to active FNB transaction', [
                    'transaction_id' => $activeTransaction->id,
                    'transaction_no' => $activeTransaction->transaction_no
                ]);

                // Append items to active transaction
                foreach ($validated['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    $addQty = (int) $item['quantity'];
                    $addDiscount = (float) ($item['discount'] ?? 0);

                    $existingItem = TransactionItem::where('transaction_id', $activeTransaction->id)
                        ->where('product_id', $product->id)
                        ->first();

                    if ($existingItem) {
                        $existingItem->quantity += $addQty;
                        $existingItem->discount += $addDiscount;
                        $existingItem->subtotal = ($existingItem->price * $existingItem->quantity) - $existingItem->discount;
                        $existingItem->save();
                    } else {
                        $itemSubtotal = ($item['price'] * $addQty) - $addDiscount;
                        TransactionItem::create([
                            'transaction_id' => $activeTransaction->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'price' => $item['price'],
                            'quantity' => $addQty,
                            'discount' => $addDiscount,
                            'subtotal' => $itemSubtotal,
                            'notes' => $item['notes'] ?? null,
                        ]);
                    }

                    // Decrement inventory stock for the location
                    $locationIdForStock = $validated['location_id'] ?? $activeTransaction->location_id;
                    if ($locationIdForStock) {
                        $inventoryStock = InventoryStock::where('product_id', $product->id)
                            ->where('location_id', $locationIdForStock)
                            ->first();
                        if ($inventoryStock) {
                            $inventoryStock->decrement('quantity', $addQty);
                            $inventoryStock->update(['last_stock_out' => now()]);
                        }
                    }
                }

                // Update customer_name & order_type if provided
                if (!empty($validated['customer_name'])) {
                    $activeTransaction->customer_name = $validated['customer_name'];
                }
                if (!empty($validated['order_type'])) {
                    $activeTransaction->order_type = $validated['order_type'];
                }

                // Add Order Tambahan flag to notes & set unconfirmed addon flag
                if (empty($activeTransaction->notes)) {
                    $activeTransaction->notes = '[Order Tambahan]';
                } elseif (!str_contains($activeTransaction->notes, '[Order Tambahan]')) {
                    $activeTransaction->notes = $activeTransaction->notes . ' | [Order Tambahan]';
                }

                $activeTransaction->has_unconfirmed_addon = true;

                // Recalculate subtotal & total
                $allSubtotal = TransactionItem::where('transaction_id', $activeTransaction->id)->get()->sum('subtotal');
                $activeTransaction->subtotal = $allSubtotal;
                $activeTransaction->discount = ($activeTransaction->discount ?? 0) + ($discount ?? 0);
                $activeTransaction->tax = $tax ?? $activeTransaction->tax;
                $activeTransaction->total = max(0, $activeTransaction->subtotal - $activeTransaction->discount + $activeTransaction->tax);

                if ($paidAmount > 0) {
                    $activeTransaction->paid_amount = ($activeTransaction->paid_amount ?? 0) + $paidAmount;
                }

                $activeTransaction->save();

                // Log activity
                ActivityLog::log('update_transaction', $activeTransaction, [
                    'transaction_no' => $activeTransaction->transaction_no,
                    'action' => 'append_items',
                    'total' => $activeTransaction->total,
                ]);

                return response()->json($activeTransaction->load(['items.product', 'outlet', 'user', 'table']), 200);
            }

            // Create transaction
            $transaction = Transaction::create([
                'transaction_no' => $transactionNo,
                'outlet_id' => $outletIdForTransaction,
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
                'customer_name' => $validated['customer_name'] ?? null,
                'notes' => $notesToStore,
                'table_id' => $tableIdToStore,
                'order_type' => $validated['order_type'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'completed_at' => (isset($validated['status']) && $validated['status'] === 'completed') ? now() : null,
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
                
                if (!$locationId && isset($validated['outlet_id'])) {
                    // Find location for this outlet (only if outlet_id exists)
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
                    \Log::error('No location_id found', [
                        'outlet_id' => $validated['outlet_id'] ?? null,
                        'location_id' => $validated['location_id'] ?? null
                    ]);
                }
            }

            // Record cash flow (only if we have outlet_id)
            if ($outletIdForTransaction) {
                CashFlow::create([
                    'outlet_id' => $outletIdForTransaction,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'amount' => $total,
                    'category' => 'penjualan',
                    'description' => "Transaksi #{$transaction->transaction_no}",
                    'transaction_id' => $transaction->id,
                ]);
            }

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
            // Determine location_id for stock restoration
            $locationId = $transaction->location_id;
            if (!$locationId && $transaction->outlet_id) {
                $location = \App\Models\Location::where('outlet_id', $transaction->outlet_id)
                    ->where('type', 'OUTLET')
                    ->first();
                $locationId = $location?->id;
            }

            // Restore inventory stock
            foreach ($transaction->items as $item) {
                if ($locationId) {
                    $inventoryStock = InventoryStock::where('product_id', $item->product_id)
                        ->where('location_id', $locationId)
                        ->first();
                    
                    if ($inventoryStock) {
                        $inventoryStock->increment('quantity', $item->quantity);
                        $inventoryStock->update(['last_stock_in' => now()]);
                    }
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

    public function publicOrders(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required',
            'table_id' => 'nullable',
        ]);

        $query = Transaction::with(['items.product', 'items'])
            ->where('location_id', $validated['location_id']);

        if (!empty($validated['table_id'])) {
            $tableId = $validated['table_id'];
            $query->where(function ($q) use ($tableId) {
                $q->where('table_id', $tableId)
                  ->orWhere('table_id', (string)$tableId);
            });
        }

        // Fetch active orders only (status not completed / void / refund)
        $transactions = $query->whereIn('status', ['pending', 'processed', 'delivered'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function confirmAddon(Transaction $transaction)
    {
        $transaction->update(['has_unconfirmed_addon' => false]);
        return response()->json([
            'message' => 'Order tambahan berhasil dikonfirmasi',
            'transaction' => $transaction->load(['items.product', 'outlet', 'user', 'table'])
        ]);
    }
}
