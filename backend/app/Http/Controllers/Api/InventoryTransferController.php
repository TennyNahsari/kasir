<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransfer;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryTransferController extends Controller
{
    protected $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index(Request $request)
    {
        $query = InventoryTransfer::with([
            'fromLocation',
            'toLocation',
            'items.product',
            'requestedBy',
            'approvedBy',
        ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_location_id')) {
            $query->where('from_location_id', $request->from_location_id);
        }

        if ($request->has('to_location_id')) {
            $query->where('to_location_id', $request->to_location_id);
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($transfers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $transfer = $this->transferService->createTransfer(
                $request->all(),
                auth()->id()
            );

            return response()->json($transfer, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(InventoryTransfer $inventoryTransfer)
    {
        return response()->json(
            $inventoryTransfer->load([
                'fromLocation',
                'toLocation',
                'items.product',
                'requestedBy',
                'approvedBy',
                'receivedBy',
            ])
        );
    }

    public function update(Request $request, InventoryTransfer $inventoryTransfer)
    {
        if ($inventoryTransfer->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Only draft transfers can be updated'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $inventoryTransfer->update($request->only([
                'from_location_id',
                'to_location_id',
                'transfer_date',
                'notes',
            ]));

            // Update items
            $inventoryTransfer->items()->delete();
            foreach ($request->items as $item) {
                $inventoryTransfer->items()->create($item);
            }

            return response()->json($inventoryTransfer->fresh('items.product'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(InventoryTransfer $inventoryTransfer)
    {
        if ($inventoryTransfer->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Only draft transfers can be deleted'
            ], 422);
        }

        $inventoryTransfer->delete();

        return response()->json(['message' => 'Transfer deleted successfully']);
    }

    public function submit(InventoryTransfer $transfer)
    {
        try {
            $transfer = $this->transferService->submitTransfer($transfer->id);
            return response()->json($transfer);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(InventoryTransfer $transfer)
    {
        $user = auth()->user();
        
        // Authorization: owner can approve all, supervisor can only approve from their outlet
        if ($user->role !== 'owner') {
            $transfer->load('fromLocation');
            
            if ($user->role !== 'supervisor' || $user->outlet_id !== $transfer->fromLocation->outlet_id) {
                return response()->json([
                    'message' => 'You are not authorized to approve this transfer. Only supervisors from the source outlet can approve.'
                ], 403);
            }
        }

        try {
            $transfer = $this->transferService->approveTransfer(
                $transfer->id,
                auth()->id()
            );
            return response()->json($transfer);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function receive(Request $request, InventoryTransfer $transfer)
    {
        $user = auth()->user();
        
        // Authorization: owner can receive all, supervisor can only receive to their outlet
        if ($user->role !== 'owner') {
            $transfer->load('toLocation');
            
            if ($user->role !== 'supervisor' || $user->outlet_id !== $transfer->toLocation->outlet_id) {
                return response()->json([
                    'message' => 'You are not authorized to receive this transfer. Only supervisors from the destination outlet can receive.'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:inventory_transfer_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.quantity_rejected' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $transfer = $this->transferService->receiveTransfer(
                $transfer->id,
                $request->items,
                auth()->id()
            );
            return response()->json($transfer);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request, InventoryTransfer $transfer)
    {
        try {
            $transfer = $this->transferService->cancelTransfer(
                $transfer->id,
                $request->get('reason')
            );
            return response()->json($transfer);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
