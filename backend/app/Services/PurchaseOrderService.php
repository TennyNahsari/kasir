<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequestItem;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    protected $prService;

    public function __construct(PurchaseRequestService $prService)
    {
        $this->prService = $prService;
    }

    /**
     * Create PO from PR items
     */
    public function createPOFromPR(array $data, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $po = PurchaseOrder::create([
                'po_no' => $this->generatePONo(),
                'vendor_id' => $data['vendor_id'],
                'order_date' => $data['order_date'] ?? now(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'location_id' => $data['location_id'],
                'status' => 'DRAFT',
                'created_by' => $userId,
                'notes' => $data['notes'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
            ]);

            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discountPercent = $item['discount_percent'] ?? 0;
                $taxPercent = $item['tax_percent'] ?? 0;

                // Calculate line amounts
                $discountAmount = ($unitPrice * $quantity * $discountPercent) / 100;
                $amountAfterDiscount = ($unitPrice * $quantity) - $discountAmount;
                $taxAmount = ($amountAfterDiscount * $taxPercent) / 100;
                $lineTotal = $amountAfterDiscount + $taxAmount;

                $poItem = PurchaseOrderItem::create([
                    'po_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'pr_item_id' => $item['pr_item_id'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                    'notes' => $item['notes'] ?? null,
                ]);

                $subtotal += $lineTotal;

                // Mark PR item as ordered
                if (isset($item['pr_item_id'])) {
                    $this->prService->markItemOrdered($item['pr_item_id'], $quantity);
                }
            }

            // Update PO totals
            $shippingCost = $data['shipping_cost'] ?? 0;
            $po->update([
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $shippingCost,
            ]);

            return $po->load('items.product', 'vendor', 'location');
        });
    }

    /**
     * Create PO directly (without PR)
     */
    public function createPO(array $data, int $userId): PurchaseOrder
    {
        return $this->createPOFromPR($data, $userId);
    }

    /**
     * Update PO (only if DRAFT)
     */
    public function updatePO(int $poId, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $data) {
            $po = PurchaseOrder::with('items')->findOrFail($poId);

            if ($po->status !== 'DRAFT') {
                throw new \Exception('Only draft POs can be updated');
            }

            // Update header
            $po->update([
                'vendor_id' => $data['vendor_id'] ?? $po->vendor_id,
                'order_date' => $data['order_date'] ?? $po->order_date,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $po->expected_delivery_date,
                'location_id' => $data['location_id'] ?? $po->location_id,
                'notes' => $data['notes'] ?? $po->notes,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? $po->terms_and_conditions,
            ]);

            // Update items if provided
            if (isset($data['items'])) {
                // Reverse PR item quantities
                foreach ($po->items as $oldItem) {
                    if ($oldItem->pr_item_id) {
                        $prItem = PurchaseRequestItem::find($oldItem->pr_item_id);
                        if ($prItem) {
                            $prItem->decrement('quantity_ordered', $oldItem->quantity);
                        }
                    }
                }

                // Delete existing items
                $po->items()->delete();

                $subtotal = 0;

                // Create new items
                foreach ($data['items'] as $item) {
                    $quantity = $item['quantity'];
                    $unitPrice = $item['unit_price'];
                    $discountPercent = $item['discount_percent'] ?? 0;
                    $taxPercent = $item['tax_percent'] ?? 0;

                    $discountAmount = ($unitPrice * $quantity * $discountPercent) / 100;
                    $amountAfterDiscount = ($unitPrice * $quantity) - $discountAmount;
                    $taxAmount = ($amountAfterDiscount * $taxPercent) / 100;
                    $lineTotal = $amountAfterDiscount + $taxAmount;

                    PurchaseOrderItem::create([
                        'po_id' => $po->id,
                        'product_id' => $item['product_id'],
                        'pr_item_id' => $item['pr_item_id'] ?? null,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_percent' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'tax_percent' => $taxPercent,
                        'tax_amount' => $taxAmount,
                        'line_total' => $lineTotal,
                        'notes' => $item['notes'] ?? null,
                    ]);

                    $subtotal += $lineTotal;

                    // Mark PR item as ordered
                    if (isset($item['pr_item_id'])) {
                        $this->prService->markItemOrdered($item['pr_item_id'], $quantity);
                    }
                }

                // Update PO totals
                $shippingCost = $data['shipping_cost'] ?? 0;
                $po->update([
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'total' => $subtotal + $shippingCost,
                ]);
            }

            return $po->fresh('items.product');
        });
    }

    /**
     * Submit PO for approval
     */
    public function submitPO(int $poId): PurchaseOrder
    {
        $po = PurchaseOrder::with('items')->findOrFail($poId);

        if ($po->status !== 'DRAFT') {
            throw new \Exception('Only draft POs can be submitted');
        }

        if ($po->items->isEmpty()) {
            throw new \Exception('PO must have at least one item');
        }

        $po->update(['status' => 'PENDING_APPROVAL']);

        return $po;
    }

    /**
     * Approve PO
     */
    public function approvePO(int $poId, int $userId): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($poId);

        if ($po->status !== 'PENDING_APPROVAL') {
            throw new \Exception('Only pending POs can be approved');
        }

        $po->update([
            'status' => 'APPROVED',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $po;
    }

    /**
     * Send PO to vendor
     */
    public function sendPO(int $poId): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($poId);

        if ($po->status !== 'APPROVED') {
            throw new \Exception('Only approved POs can be sent');
        }

        $po->update(['status' => 'SENT']);

        // TODO: Send email to vendor

        return $po;
    }

    /**
     * Cancel PO
     */
    public function cancelPO(int $poId): PurchaseOrder
    {
        return DB::transaction(function () use ($poId) {
            $po = PurchaseOrder::with('items')->findOrFail($poId);

            if (!in_array($po->status, ['DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'SENT'])) {
                throw new \Exception('Cannot cancel this PO');
            }

            // Check if any items are already received
            $hasReceipts = $po->items->some(function ($item) {
                return $item->quantity_received > 0;
            });

            if ($hasReceipts) {
                throw new \Exception('Cannot cancel PO with received items');
            }

            // Reverse PR item quantities
            foreach ($po->items as $item) {
                if ($item->pr_item_id) {
                    $prItem = PurchaseRequestItem::find($item->pr_item_id);
                    if ($prItem) {
                        $prItem->decrement('quantity_ordered', $item->quantity);
                    }
                }
            }

            $po->update(['status' => 'CANCELLED']);

            return $po;
        });
    }

    /**
     * Mark PO item as received
     */
    public function markItemReceived(int $poItemId, float $quantity): void
    {
        DB::transaction(function () use ($poItemId, $quantity) {
            $item = PurchaseOrderItem::with('purchaseOrder')->findOrFail($poItemId);

            $newQuantityReceived = $item->quantity_received + $quantity;

            if ($newQuantityReceived > $item->quantity) {
                throw new \Exception('Received quantity exceeds ordered quantity');
            }

            $item->update(['quantity_received' => $newQuantityReceived]);

            // Update PO status
            $po = $item->purchaseOrder;
            $allItemsReceived = $po->items->every(function ($item) {
                return $item->quantity_received >= $item->quantity;
            });

            if ($allItemsReceived) {
                $po->update(['status' => 'FULLY_RECEIVED']);
            } else {
                $anyItemReceived = $po->items->some(function ($item) {
                    return $item->quantity_received > 0;
                });

                if ($anyItemReceived && in_array($po->status, ['SENT', 'APPROVED'])) {
                    $po->update(['status' => 'PARTIALLY_RECEIVED']);
                }
            }
        });
    }

    /**
     * Generate unique PO number
     */
    protected function generatePONo(): string
    {
        $date = now()->format('Ymd');
        $latest = PurchaseOrder::whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        $sequence = $latest ? intval(substr($latest->po_no, -4)) + 1 : 1;

        return 'PO-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
