<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoodsReceiptController extends Controller
{
    protected $grnService;

    public function __construct(GoodsReceiptService $grnService)
    {
        $this->grnService = $grnService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = GoodsReceipt::with([
            'purchaseOrder.vendor',
            'location',
            'items.product',
            'receivedBy',
            'approvedBy',
        ]);

        // Filter GRN based on user role and location
        if ($user->role === 'owner') {
            // Owner can see all GRNs
        } elseif ($this->isProcurementUser($user)) {
            // Procurement users can see all GRNs
        } else {
            // Staff and supervisor can only see GRNs from their own department
            if ($user->location_id) {
                $query->where('location_id', $user->location_id);
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('po_id')) {
            $query->where('po_id', $request->po_id);
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->has('is_posted')) {
            $query->where('is_posted', $request->is_posted);
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('receipt_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('receipt_date', '<=', $request->to_date);
        }

        // PO Number search
        if ($request->has('po_number')) {
            $query->whereHas('purchaseOrder', function($q) use ($request) {
                $q->where('po_no', 'like', '%' . $request->po_number . '%');
            });
        }

        $grns = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($grns);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Rule 6: Only owner and procurement users can create GRN
        if ($user->role !== 'owner' && !$this->isProcurementUser($user)) {
            return response()->json([
                'message' => 'Only Owner and Procurement Department users can create Goods Receipts'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'po_id' => 'required|exists:purchase_orders,id',
            'location_id' => 'nullable|exists:locations,id',
            'receipt_date' => 'nullable|date',
            'supplier_invoice_no' => 'nullable|string|max:100',
            'supplier_invoice_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.quantity_rejected' => 'nullable|numeric|min:0',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.serial_numbers.*' => 'nullable|string|max:255',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $grn = $this->grnService->createGRN($request->all(), auth()->id());
            return response()->json($grn, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    private function isProcurementUser($user)
    {
        if (!$user->location) return false;
        
        return $user->location->type === 'DEPARTMENT' && 
               stripos($user->location->name, 'procurement') !== false;
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        return response()->json(
            $goodsReceipt->load([
                'purchaseOrder.vendor',
                'purchaseOrder.items.product',
                'location',
                'items.product.category',
                'items.purchaseOrderItem',
                'receivedBy',
                'approvedBy',
                'postedBy',
                'qualityCheckedBy',
            ])
        );
    }

    public function update(Request $request, GoodsReceipt $goodsReceipt)
    {
        if (!in_array($goodsReceipt->status, ['DRAFT', 'QUALITY_CHECK'])) {
            return response()->json([
                'message' => 'Only draft or quality-check GRNs can be updated'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'receipt_date' => 'nullable|date',
            'supplier_invoice_no' => 'nullable|string|max:100',
            'supplier_invoice_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goodsReceipt->update($request->only([
            'receipt_date',
            'supplier_invoice_no',
            'supplier_invoice_date',
            'notes',
        ]));

        return response()->json($goodsReceipt);
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->is_posted) {
            return response()->json([
                'message' => 'Cannot delete posted GRN'
            ], 422);
        }

        if ($goodsReceipt->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Only draft GRNs can be deleted'
            ], 422);
        }

        $goodsReceipt->delete();

        return response()->json(['message' => 'Goods Receipt deleted successfully']);
    }

    public function qualityCheck(Request $request, GoodsReceipt $goodsReceipt)
    {
        $user = auth()->user();
        
        // Only owner and procurement users can submit for quality check
        if ($user->role !== 'owner' && !$this->isProcurementUser($user)) {
            return response()->json([
                'message' => 'Only Owner and Procurement Department users can submit for quality check'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:goods_receipt_items,id',
            'items.*.quality_status' => 'required|in:PENDING,PASSED,FAILED',
            'items.*.quality_notes' => 'nullable|string',
            'items.*.quantity_rejected' => 'nullable|numeric|min:0',
            'grn_quality_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $grn = $this->grnService->qualityCheck(
                $goodsReceipt->id,
                $request->all(),
                auth()->id()
            );
            return response()->json($grn);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(GoodsReceipt $goodsReceipt)
    {
        $user = auth()->user();
        
        // Only owner and procurement users can approve quality check
        if ($user->role !== 'owner' && !$this->isProcurementUser($user)) {
            return response()->json([
                'message' => 'Only Owner and Procurement Department users can approve quality check'
            ], 403);
        }
        
        try {
            $grn = $this->grnService->approveGRN($goodsReceipt->id, auth()->id());
            return response()->json($grn);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function post(GoodsReceipt $goodsReceipt)
    {
        $user = auth()->user();
        
        // Rule 6: Only owner and procurement users can post GRN to inventory
        if ($user->role !== 'owner' && !$this->isProcurementUser($user)) {
            return response()->json([
                'message' => 'Only Owner and Procurement Department users can post GRN to inventory'
            ], 403);
        }
        
        try {
            $grn = $this->grnService->postGRN($goodsReceipt->id, auth()->id());
            return response()->json([
                'message' => 'GRN posted to inventory successfully',
                'grn' => $grn,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, GoodsReceipt $goodsReceipt)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $grn = $this->grnService->rejectGRN($goodsReceipt->id, $request->reason);
            return response()->json($grn);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function byPurchaseOrder($poId)
    {
        try {
            $grns = $this->grnService->getGRNsByPO($poId);
            return response()->json($grns);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
