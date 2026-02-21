<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    protected $poService;

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = PurchaseOrder::with([
            'vendor',
            'location',
            'items.product',
            'createdBy',
            'approvedBy',
        ]);

        // Filter PO based on user role
        if (in_array($user->role, ['owner', 'inventory'])) {
            // Owner and inventory can see all POs
        } elseif ($this->isProcurementUser($user)) {
            // Procurement users can see all POs
        } else {
            // Other staff can only see POs from their own department
            if ($user->location_id) {
                $query->where('location_id', $user->location_id);
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('order_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('order_date', '<=', $request->to_date);
        }

        $pos = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($pos);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Rule 4: Only owner, inventory and procurement users can create PO
        if (!in_array($user->role, ['owner', 'inventory']) && !$this->isProcurementUser($user)) {
            return response()->json([
                'message' => 'Only Owner, Inventory and Procurement Department users can create Purchase Orders'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'order_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'location_id' => 'required|exists:locations,id',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.pr_item_id' => 'nullable|exists:purchase_request_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $po = $this->poService->createPOFromPR($request->all(), auth()->id());
            return response()->json($po, 201);
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

    public function show(PurchaseOrder $purchaseOrder)
    {
        return response()->json(
            $purchaseOrder->load([
                'vendor',
                'location',
                'items.product.category',
                'items.purchaseRequestItem.purchaseRequest',
                'createdBy',
                'approvedBy',
            ])
        );
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'order_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'location_id' => 'required|exists:locations,id',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'items' => 'nullable|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.pr_item_id' => 'nullable|exists:purchase_request_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $po = $this->poService->updatePO($purchaseOrder->id, $request->all());
            return response()->json($po);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Only draft POs can be deleted'
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json(['message' => 'Purchase Order deleted successfully']);
    }

    public function submit(PurchaseOrder $purchaseOrder)
    {
        try {
            $po = $this->poService->submitPO($purchaseOrder->id);
            return response()->json($po);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $user = auth()->user();
        
        // Rule 5: Only owner can approve PO
        if (!in_array($user->role, ['owner', 'inventory'])) {
            return response()->json([
                'message' => 'Only Owner can approve Purchase Orders'
            ], 403);
        }
        
        try {
            $po = $this->poService->approvePO($purchaseOrder->id, auth()->id());
            return response()->json($po);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        $user = auth()->user();
        
        // Rule 6: Only owner, inventory and procurement users can send PO
        if (!in_array($user->role, ['owner', 'inventory']) && !$this->isProcurementUser($user)) {
            return response()->json([
                'message' => 'Only Owner, Inventory and Procurement Department users can send Purchase Orders'
            ], 403);
        }
        
        try {
            $po = $this->poService->sendPO($purchaseOrder->id);
            return response()->json($po);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        try {
            $po = $this->poService->cancelPO($purchaseOrder->id);
            return response()->json($po);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        try {
            // Load PO with all relationships
            $po = PurchaseOrder::with([
                'vendor',
                'location',
                'items.product',
                'createdBy',
                'approvedBy'
            ])->findOrFail($purchaseOrder->id);

            // Generate PDF
            $pdf = Pdf::loadView('pdf.purchase-order', compact('po'));
            
            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Return PDF download response
            return $pdf->download('PO-' . $po->po_number . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
