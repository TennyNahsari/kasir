<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
{
    /**
     * Create new Purchase Request
     */
    public function createPR(array $data, int $userId): PurchaseRequest
    {
        return DB::transaction(function () use ($data, $userId) {
            $pr = PurchaseRequest::create([
                'pr_no' => $this->generatePRNo(),
                'request_date' => $data['request_date'] ?? now(),
                'required_date' => $data['required_date'] ?? null,
                'location_id' => $data['location_id'] ?? null,
                'status' => 'DRAFT',
                'requested_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create items
            foreach ($data['items'] as $item) {
                PurchaseRequestItem::create([
                    'pr_id' => $pr->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'estimated_price' => $item['estimated_price'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $pr->load('items.product', 'location', 'requestedBy');
        });
    }

    /**
     * Update PR (only if DRAFT)
     */
    public function updatePR(int $prId, array $data): PurchaseRequest
    {
        return DB::transaction(function () use ($prId, $data) {
            $pr = PurchaseRequest::findOrFail($prId);

            if ($pr->status !== 'DRAFT') {
                throw new \Exception('Only draft PRs can be updated');
            }

            // Update header
            $pr->update([
                'request_date' => $data['request_date'] ?? $pr->request_date,
                'required_date' => $data['required_date'] ?? $pr->required_date,
                'location_id' => $data['location_id'] ?? $pr->location_id,
                'notes' => $data['notes'] ?? $pr->notes,
            ]);

            // Update items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $pr->items()->delete();

                // Create new items
                foreach ($data['items'] as $item) {
                    PurchaseRequestItem::create([
                        'pr_id' => $pr->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'estimated_price' => $item['estimated_price'] ?? null,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            return $pr->fresh('items.product');
        });
    }

    /**
     * Submit PR for approval
     */
    public function submitPR(int $prId): PurchaseRequest
    {
        $pr = PurchaseRequest::with('items')->findOrFail($prId);

        if ($pr->status !== 'DRAFT') {
            throw new \Exception('Only draft PRs can be submitted');
        }

        if ($pr->items->isEmpty()) {
            throw new \Exception('PR must have at least one item');
        }

        $pr->update(['status' => 'PENDING_APPROVAL']);

        return $pr;
    }

    /**
     * Approve PR
     */
    public function approvePR(int $prId, int $userId): PurchaseRequest
    {
        $pr = PurchaseRequest::findOrFail($prId);

        if ($pr->status !== 'PENDING_APPROVAL') {
            throw new \Exception('Only pending PRs can be approved');
        }

        $pr->update([
            'status' => 'APPROVED',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $pr;
    }

    /**
     * Reject PR
     */
    public function rejectPR(int $prId, string $reason, int $userId): PurchaseRequest
    {
        $pr = PurchaseRequest::findOrFail($prId);

        if ($pr->status !== 'PENDING_APPROVAL') {
            throw new \Exception('Only pending PRs can be rejected');
        }

        $pr->update([
            'status' => 'REJECTED',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $pr;
    }

    /**
     * Cancel PR
     */
    public function cancelPR(int $prId): PurchaseRequest
    {
        $pr = PurchaseRequest::with('items')->findOrFail($prId);

        if (!in_array($pr->status, ['DRAFT', 'PENDING_APPROVAL', 'APPROVED'])) {
            throw new \Exception('Cannot cancel this PR');
        }

        // Check if any items are already ordered
        $hasOrders = $pr->items->some(function ($item) {
            return $item->quantity_ordered > 0;
        });

        if ($hasOrders) {
            throw new \Exception('Cannot cancel PR with existing orders');
        }

        $pr->update(['status' => 'CANCELLED']);

        return $pr;
    }

    /**
     * Mark PR item as ordered
     */
    public function markItemOrdered(int $prItemId, float $quantity): void
    {
        DB::transaction(function () use ($prItemId, $quantity) {
            $item = PurchaseRequestItem::with('purchaseRequest')->findOrFail($prItemId);

            $newQuantityOrdered = $item->quantity_ordered + $quantity;

            if ($newQuantityOrdered > $item->quantity) {
                throw new \Exception('Ordered quantity exceeds requested quantity');
            }

            $item->update(['quantity_ordered' => $newQuantityOrdered]);

            // Update PR status
            $pr = $item->purchaseRequest;
            $allItemsOrdered = $pr->items->every(function ($item) {
                return $item->quantity_ordered >= $item->quantity;
            });

            if ($allItemsOrdered) {
                $pr->update(['status' => 'FULLY_ORDERED']);
            } else {
                $anyItemOrdered = $pr->items->some(function ($item) {
                    return $item->quantity_ordered > 0;
                });

                if ($anyItemOrdered && $pr->status === 'APPROVED') {
                    $pr->update(['status' => 'PARTIALLY_ORDERED']);
                }
            }
        });
    }

    /**
     * Get pending PRs for PO creation
     */
    public function getPendingPRs(int $locationId = null)
    {
        $query = PurchaseRequest::with(['items.product', 'location'])
            ->whereIn('status', ['APPROVED', 'PARTIALLY_ORDERED'])
            ->whereHas('items', function ($q) {
                $q->whereColumn('quantity_ordered', '<', 'quantity');
            });

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->orderBy('required_date')->get();
    }

    /**
     * Generate unique PR number
     */
    protected function generatePRNo(): string
    {
        $date = now()->format('Ymd');
        $latest = PurchaseRequest::whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        $sequence = $latest ? intval(substr($latest->pr_no, -4)) + 1 : 1;

        return 'PR-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
