<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseRequestController extends Controller
{
    protected $prService;

    public function __construct(PurchaseRequestService $prService)
    {
        $this->prService = $prService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = PurchaseRequest::with([
            'location',
            'items.product',
            'requestedBy',
            'approvedBy',
        ]);

        // Rule 1 & 2: Filter PR based on user role and location
        if ($user->role === 'owner') {
            // Owner can see all PRs
        } elseif ($user->role === 'kasir') {
            // Kasir users see only PRs created by kasir role users
            $query->whereHas('requestedBy', function($q) {
                $q->where('role', 'kasir');
            });
        } elseif ($this->isProcurementUser($user)) {
            // Procurement users can see all PRs
        } else {
            // Other staff can only see PRs from their own department
            if ($user->location_id) {
                $query->where('location_id', $user->location_id);
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->has('requested_by')) {
            $query->where('requested_by', $request->requested_by);
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('request_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('request_date', '<=', $request->to_date);
        }

        // Department filter (via location)
        if ($request->has('department_id')) {
            $query->where('location_id', $request->department_id);
        }

        $prs = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($prs);
    }
    
    private function isProcurementUser($user)
    {
        if (!$user->location) return false;
        
        return $user->location->type === 'DEPARTMENT' && 
               stripos($user->location->name, 'procurement') !== false;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_date' => 'nullable|date',
            'required_date' => 'nullable|date',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $pr = $this->prService->createPR($request->all(), auth()->id());
            return response()->json($pr, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        return response()->json(
            $purchaseRequest->load([
                'location',
                'items.product.category',
                'requestedBy',
                'approvedBy',
            ])
        );
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validator = Validator::make($request->all(), [
            'request_date' => 'nullable|date',
            'required_date' => 'nullable|date',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
            'items' => 'nullable|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $pr = $this->prService->updatePR($purchaseRequest->id, $request->all());
            return response()->json($pr);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if (!in_array($purchaseRequest->status, ['DRAFT', 'CANCELLED', 'REJECTED'])) {
            return response()->json([
                'message' => 'Only draft, cancelled, or rejected PRs can be deleted'
            ], 422);
        }

        $purchaseRequest->delete();

        return response()->json(['message' => 'Purchase Request deleted successfully']);
    }

    public function submit(PurchaseRequest $purchaseRequest)
    {
        try {
            $pr = $this->prService->submitPR($purchaseRequest->id);
            return response()->json($pr);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();
        $pr = $purchaseRequest->load('requestedBy');
        
        // Rule 3: If PR was created by kasir, owner or any kasir can approve (including self)
        if ($pr->requestedBy && $pr->requestedBy->role === 'kasir') {
            if ($user->role !== 'owner' && $user->role !== 'kasir') {
                return response()->json([
                    'message' => 'Only owner or kasir can approve kasir Purchase Requests'
                ], 403);
            }
            // Kasir can approve their own PR or other kasir's PR
        }
        // For non-kasir PRs: only supervisor from same department can approve
        else {
            if ($user->role !== 'supervisor') {
                return response()->json([
                    'message' => 'Only supervisors can approve Purchase Requests'
                ], 403);
            }
            
            if ($user->location_id !== $purchaseRequest->location_id) {
                return response()->json([
                    'message' => 'You can only approve PRs from your own department'
                ], 403);
            }
        }
        
        try {
            $pr = $this->prService->approvePR($purchaseRequest->id, auth()->id());
            return response()->json($pr);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $pr = $this->prService->rejectPR(
                $purchaseRequest->id,
                $request->reason,
                auth()->id()
            );
            return response()->json($pr);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(PurchaseRequest $purchaseRequest)
    {
        try {
            $pr = $this->prService->cancelPR($purchaseRequest->id);
            return response()->json($pr);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function pendingList(Request $request)
    {
        $locationId = $request->get('location_id');
        $prs = $this->prService->getPendingPRs($locationId);

        return response()->json($prs);
    }
}
