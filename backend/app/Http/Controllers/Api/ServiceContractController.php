<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceContract;
use App\Services\ServiceContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceContractController extends Controller
{
    protected $serviceContractService;

    public function __construct(ServiceContractService $serviceContractService)
    {
        $this->serviceContractService = $serviceContractService;
    }

    /**
     * Display a listing of service contracts.
     */
    public function index(Request $request)
    {
        try {
            $query = ServiceContract::with(['product', 'vendor', 'location', 'goodsReceipt', 'purchaseOrder']);

            // Authorization: owner sees all, non-owner only sees data at their assigned location
            $user = auth()->user();
            if ($user->role !== 'owner' && $user->location_id) {
                $query->where('location_id', $user->location_id);
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Filter by contract type
            if ($request->has('contract_type') && $request->contract_type !== '') {
                $query->where('contract_type', $request->contract_type);
            }

            // Filter by vendor
            if ($request->has('vendor_id') && $request->vendor_id !== '') {
                $query->where('vendor_id', $request->vendor_id);
            }

            // Filter by location
            if ($request->has('location_id') && $request->location_id !== '') {
                $query->where('location_id', $request->location_id);
            }

            // Filter by product
            if ($request->has('product_id') && $request->product_id !== '') {
                $query->where('product_id', $request->product_id);
            }

            // Search by contract number
            if ($request->has('search') && $request->search !== '') {
                $query->where('contract_number', 'ilike', '%' . $request->search . '%');
            }

            // Filter expiring contracts
            if ($request->has('expiring_days') && $request->expiring_days !== '') {
                $days = (int) $request->expiring_days;
                $query->expiring($days);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $contracts = $query->paginate($request->get('per_page', 15));

            return response()->json($contracts);
        } catch (\Exception $e) {
            Log::error('Error fetching service contracts', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch service contracts'], 500);
        }
    }

    /**
     * Display the specified service contract.
     */
    public function show($id)
    {
        try {
            Log::info('Fetching service contract', ['id' => $id]);
            
            // Check if contract exists at all (including soft-deleted)
            $exists = ServiceContract::withTrashed()->where('id', $id)->exists();
            Log::info('Contract exists check', ['id' => $id, 'exists' => $exists]);
            
            if (!$exists) {
                Log::warning('Service contract not found in database', ['id' => $id]);
                return response()->json(['error' => 'Service contract not found', 'id' => $id], 404);
            }
            
            // Load contract without eager loading first to avoid relation errors
            $contract = ServiceContract::withTrashed()->find($id);
            
            // Try to load relations separately with error handling
            try {
                $contract->load(['product', 'vendor', 'location']);
            } catch (\Exception $e) {
                Log::warning('Failed to load some relations', ['id' => $id, 'error' => $e->getMessage()]);
                // Continue without relations if they fail
            }
            
            try {
                $contract->load(['goodsReceipt.receivedBy', 'purchaseOrder.requestedBy']);
            } catch (\Exception $e) {
                Log::warning('Failed to load GRN/PO relations', ['id' => $id, 'error' => $e->getMessage()]);
                // Continue without these relations if they fail
            }

            Log::info('Service contract loaded successfully', ['id' => $id, 'contract_number' => $contract->contract_number]);
            
            return response()->json($contract);
        } catch (\Exception $e) {
            Log::error('Error fetching service contract', [
                'id' => $id, 
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Service contract not found', 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * Store a newly created service contract.
     */
    public function store(Request $request)
    {
        // Authorization: only owner and supervisor can create service contract
        $user = auth()->user();
        if (!in_array($user->role, ['owner', 'supervisor'])) {
            return response()->json([
                'message' => 'Access denied. Only Owner and Supervisor can create service contracts.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'vendor_id' => 'required|exists:vendors,id',
                'location_id' => 'nullable|exists:locations,id',
                'pic' => 'nullable|string|max:100',
                'contract_type' => 'required|in:RENTAL,SUBSCRIPTION,MAINTENANCE,CONSULTING,UTILITY,OTHER',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'contract_value' => 'required|numeric|min:0',
                'billing_cycle' => 'required|in:MONTHLY,QUARTERLY,YEARLY,ONE_TIME',
                'notes' => 'nullable|string',
            ]);

            // Generate contract number
            $year = now()->year;
            $prefix = "SVC-{$year}-";
            $lastContract = ServiceContract::where('contract_number', 'like', $prefix . '%')
                ->orderBy('contract_number', 'desc')
                ->first();

            if ($lastContract) {
                $lastNumber = (int) substr($lastContract->contract_number, -4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $contractNumber = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            // Determine status based on start date
            $status = 'ACTIVE';
            if ($validated['start_date'] && \Carbon\Carbon::parse($validated['start_date'])->isFuture()) {
                $status = 'PENDING';
            }

            $contract = ServiceContract::create([
                'contract_number' => $contractNumber,
                'product_id' => $validated['product_id'],
                'vendor_id' => $validated['vendor_id'],
                'location_id' => $validated['location_id'] ?? null,
                'pic' => $validated['pic'] ?? null,
                'contract_type' => $validated['contract_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'contract_value' => $validated['contract_value'],
                'billing_cycle' => $validated['billing_cycle'],
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Service contract created manually', [
                'contract_id' => $contract->id,
                'contract_number' => $contractNumber,
            ]);

            return response()->json($contract->load(['product', 'vendor', 'location']), 201);
        } catch (\Exception $e) {
            Log::error('Error creating service contract', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create service contract'], 500);
        }
    }

    /**
     * Update the specified service contract.
     */
    public function update(Request $request, $id)
    {
        try {
            $contract = ServiceContract::withTrashed()->findOrFail($id);

            $validated = $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'location_id' => 'nullable|exists:locations,id',
                'pic' => 'nullable|string|max:100',
                'contract_type' => 'nullable|in:RENTAL,SUBSCRIPTION,MAINTENANCE,CONSULTING,UTILITY,OTHER',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'contract_value' => 'nullable|numeric|min:0',
                'billing_cycle' => 'nullable|in:MONTHLY,QUARTERLY,YEARLY,ONE_TIME',
                'notes' => 'nullable|string',
            ]);

            $contract->update($validated);

            Log::info('Service contract updated', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
            ]);

            return response()->json([
                'message' => 'Service contract updated successfully',
                'contract' => $contract->fresh(['product', 'vendor', 'location']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating service contract', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update service contract'], 500);
        }
    }

    /**
     * Renew a service contract.
     */
    public function renew(Request $request, $id)
    {
        try {
            $contract = ServiceContract::withTrashed()->findOrFail($id);

            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'contract_value' => 'nullable|numeric|min:0',
                'billing_cycle' => 'nullable|in:MONTHLY,QUARTERLY,YEARLY,ONE_TIME',
                'contract_type' => 'nullable|in:RENTAL,SUBSCRIPTION,MAINTENANCE,CONSULTING,UTILITY,OTHER',
                'notes' => 'nullable|string',
            ]);

            $newContract = $this->serviceContractService->renew($contract, $validated);

            return response()->json([
                'message' => 'Service contract renewed successfully',
                'old_contract' => $contract->fresh(),
                'new_contract' => $newContract->load(['product', 'vendor', 'location']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error renewing service contract', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to renew service contract'], 500);
        }
    }

    /**
     * Terminate a service contract.
     */
    public function terminate(Request $request, $id)
    {
        try {
            $contract = ServiceContract::withTrashed()->findOrFail($id);

            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $contract = $this->serviceContractService->terminate($contract, $validated['reason']);

            return response()->json([
                'message' => 'Service contract terminated successfully',
                'contract' => $contract->fresh(['product', 'vendor', 'location']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error terminating service contract', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to terminate service contract'], 500);
        }
    }

    /**
     * Get dashboard stats for service contracts.
     */
    public function stats()
    {
        try {
            $stats = [
                'total_active' => ServiceContract::where('status', 'ACTIVE')->count(),
                'total_pending' => ServiceContract::where('status', 'PENDING')->count(),
                'expiring_soon' => ServiceContract::expiring(30)->count(),
                'expired' => ServiceContract::where('status', 'EXPIRED')->count(),
                'terminated' => ServiceContract::where('status', 'TERMINATED')->count(),
                'total_monthly_value' => ServiceContract::active()
                    ->where('billing_cycle', 'MONTHLY')
                    ->sum('contract_value'),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error fetching service contract stats', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch stats'], 500);
        }
    }
}
