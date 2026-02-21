<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssetController extends Controller
{
    protected $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Display a listing of assets
     */
    public function index(Request $request)
    {
        $query = Asset::with(['product', 'location']);

        // Authorization: owner sees all, non-owner only sees data at their assigned location
        $user = auth()->user();
        if ($user->role !== 'owner' && $user->location_id) {
            $query->where('location_id', $user->location_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by location
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Filter by PIC
        if ($request->has('pic')) {
            $query->where('pic', 'ilike', "%{$request->pic}%");
        }

        // Filter by product type (asset only)
        $query->whereHas('product', function ($q) {
            $q->where('type', 'ASSET');
        });

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'ilike', "%{$search}%")
                    ->orWhere('serial_number', 'ilike', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'ilike', "%{$search}%")
                            ->orWhere('sku', 'ilike', "%{$search}%");
                    });
            });
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        return response()->json($assets);
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        // Authorization: only owner and supervisor can add asset
        $user = auth()->user();
        if (!in_array($user->role, ['owner', 'supervisor'])) {
            return response()->json([
                'message' => 'Access denied. Only Owner and Supervisor can add assets.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number',
            'location_id' => 'nullable|exists:locations,id',
            'condition' => 'nullable|in:NEW,GOOD,FAIR,POOR,BROKEN',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'useful_life_months' => 'nullable|integer|min:1',
            'depreciation_method' => 'nullable|in:STRAIGHT_LINE,DECLINING_BALANCE',
            'warranty_until' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $asset = $this->assetService->createAsset($request->all(), auth()->id());
            return response()->json($asset->load(['product', 'location', 'movements']), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified asset
     */
    public function show(Asset $asset)
    {
        return response()->json(
            $asset->load([
                'product.category',
                'location',
                'purchaseOrder.vendor',
                'goodsReceipt',
                'movements.fromUser',
                'movements.toUser',
                'movements.fromLocation',
                'movements.toLocation',
                'movements.movedBy',
            ])
        );
    }

    /**
     * Update the specified asset
     */
    public function update(Request $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number,' . $asset->id,
            'location_id' => 'nullable|exists:locations,id',
            'condition' => 'nullable|in:NEW,GOOD,FAIR,POOR,BROKEN',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'useful_life_months' => 'nullable|integer|min:1',
            'depreciation_method' => 'nullable|in:STRAIGHT_LINE,DECLINING_BALANCE',
            'warranty_until' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $asset = $this->assetService->updateAsset($asset->id, $request->all(), auth()->id());
            return response()->json($asset->load(['product', 'location']));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Assign asset to user
     */
    public function assign(Request $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'pic' => 'required|string|max:100',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $asset = $this->assetService->assignAsset(
                $asset->id,
                $request->pic,
                $request->all(),
                auth()->id()
            );
            return response()->json([
                'message' => 'Asset assigned successfully',
                'asset' => $asset->load(['product', 'location'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Return asset from user
     */
    public function return(Request $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'condition' => 'required|in:NEW,GOOD,FAIR,POOR,BROKEN',
            'location_id' => 'nullable|exists:locations,id',
            'needs_maintenance' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $asset = $this->assetService->returnAsset($asset->id, $request->all(), auth()->id());
            return response()->json([
                'message' => 'Asset returned successfully',
                'asset' => $asset->load(['product', 'location'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Transfer asset to another location
     */
    public function transfer(Request $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'to_location_id' => 'required|exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $asset = $this->assetService->transferAsset(
                $asset->id,
                $request->to_location_id,
                $request->all(),
                auth()->id()
            );
            return response()->json([
                'message' => 'Asset transferred successfully',
                'asset' => $asset->load(['product', 'location'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Dispose asset
     */
    public function dispose(Request $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->assetService->disposeAsset($asset->id, $request->all(), auth()->id());
            return response()->json(['message' => 'Asset disposed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get asset history
     */
    public function history(Asset $asset)
    {
        $history = $this->assetService->getAssetHistory($asset->id);
        return response()->json($history);
    }

    /**
     * Add manual movement history entry
     */
    public function addHistory(Request $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'movement_type' => 'required|in:PURCHASED,ASSIGNED,RETURNED,TRANSFERRED,MAINTENANCE,REPAIRED,DAMAGED,DISPOSED',
            'moved_at' => 'nullable|date_format:Y-m-d H:i:s',
            'condition_after' => 'nullable|in:NEW,GOOD,FAIR,POOR,BROKEN',
            'notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $movement = $this->assetService->addManualMovement(
                $asset->id,
                $request->all(),
                auth()->id()
            );
            return response()->json([
                'message' => 'History entry added successfully',
                'movement' => $movement
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get assets by user
     */
    public function byUser(Request $request, $userId)
    {
        $assets = $this->assetService->getUserAssets($userId);
        return response()->json($assets);
    }

    /**
     * Get available assets
     */
    public function available(Request $request)
    {
        $assets = $this->assetService->getAssetsByStatus('AVAILABLE');
        return response()->json($assets);
    }
}
