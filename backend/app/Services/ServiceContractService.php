<?php

namespace App\Services;

use App\Models\ServiceContract;
use App\Models\GoodsReceiptItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceContractService
{
    /**
     * Create service contract from GRN item
     */
    public function createFromGRN(GoodsReceiptItem $grnItem, array $data = [])
    {
        try {
            DB::beginTransaction();

            // Generate contract number
            $contractNumber = $this->generateContractNumber();

            // Get GRN and PO information
            $grn = $grnItem->goodsReceipt;
            $po = $grn->purchaseOrder;

            // Calculate contract value (unit_price * quantity_received)
            $contractValue = $grnItem->unit_price * $grnItem->quantity_received;

            // Set default dates
            $startDate = $grnItem->service_start_date ?? now()->toDateString();
            // If end_date is not provided, default to 1 year from start_date
            $endDate = $grnItem->service_end_date ?? now()->parse($startDate)->addYear()->toDateString();

            // Create contract
            $contract = ServiceContract::create([
                'contract_number' => $contractNumber,
                'grn_id' => $grn->id,
                'po_id' => $po->id,
                'product_id' => $grnItem->product_id,
                'vendor_id' => $po->vendor_id,
                'location_id' => $grn->location_id,
                'contract_type' => $grnItem->contract_type ?? 'OTHER',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'contract_value' => $contractValue,
                'billing_cycle' => $data['billing_cycle'] ?? 'MONTHLY',
                'status' => $this->determineStatus($startDate),
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            Log::info('Service contract created', [
                'contract_id' => $contract->id,
                'contract_number' => $contractNumber,
                'grn_id' => $grn->id,
            ]);

            return $contract;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create service contract', [
                'error' => $e->getMessage(),
                'grn_item_id' => $grnItem->id,
            ]);
            throw $e;
        }
    }

    /**
     * Renew existing contract
     */
    public function renew(ServiceContract $contract, array $data)
    {
        try {
            DB::beginTransaction();

            // Generate new contract number for renewal
            $newContractNumber = $this->generateContractNumber();

            // Create new contract based on existing
            $newContract = ServiceContract::create([
                'contract_number' => $newContractNumber,
                'grn_id' => $contract->grn_id,
                'po_id' => $contract->po_id,
                'product_id' => $contract->product_id,
                'vendor_id' => $contract->vendor_id,
                'location_id' => $contract->location_id,
                'contract_type' => $data['contract_type'] ?? $contract->contract_type,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'contract_value' => $data['contract_value'] ?? $contract->contract_value,
                'billing_cycle' => $data['billing_cycle'] ?? $contract->billing_cycle,
                'status' => $this->determineStatus($data['start_date']),
                'notes' => $data['notes'] ?? "Renewed from contract {$contract->contract_number}",
                'renewal_date' => now()->toDateString(),
            ]);

            // Mark old contract as expired
            $contract->update([
                'status' => 'EXPIRED',
                'notes' => ($contract->notes ? $contract->notes . "\n\n" : '') . 
                           "Renewed with contract {$newContractNumber} on " . now()->toDateString()
            ]);

            DB::commit();

            Log::info('Service contract renewed', [
                'old_contract_id' => $contract->id,
                'new_contract_id' => $newContract->id,
                'new_contract_number' => $newContractNumber,
            ]);

            return $newContract;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to renew service contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
            ]);
            throw $e;
        }
    }

    /**
     * Terminate contract early
     */
    public function terminate(ServiceContract $contract, string $reason)
    {
        try {
            DB::beginTransaction();

            $contract->update([
                'status' => 'TERMINATED',
                'notes' => ($contract->notes ? $contract->notes . "\n\n" : '') . 
                           "Terminated on " . now()->toDateString() . ": " . $reason
            ]);

            DB::commit();

            Log::info('Service contract terminated', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'reason' => $reason,
            ]);

            return $contract;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to terminate service contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
            ]);
            throw $e;
        }
    }

    /**
     * Get active contracts
     */
    public function getActive()
    {
        return ServiceContract::active()
            ->with(['product', 'vendor', 'location'])
            ->orderBy('end_date', 'asc')
            ->get();
    }

    /**
     * Get expiring contracts (within specified days)
     */
    public function getExpiring(int $days = 30)
    {
        return ServiceContract::expiring($days)
            ->with(['product', 'vendor', 'location'])
            ->orderBy('end_date', 'asc')
            ->get();
    }

    /**
     * Update expired contracts status
     */
    public function updateExpiredContracts()
    {
        $expired = ServiceContract::expired()->get();
        
        foreach ($expired as $contract) {
            $contract->update(['status' => 'EXPIRED']);
            
            Log::info('Contract auto-expired', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
            ]);
        }

        return $expired->count();
    }

    /**
     * Generate unique contract number
     */
    private function generateContractNumber(): string
    {
        $year = now()->year;
        $prefix = "SVC-{$year}-";
        
        // Get last contract number for this year
        $lastContract = ServiceContract::where('contract_number', 'like', $prefix . '%')
            ->orderBy('contract_number', 'desc')
            ->first();

        if ($lastContract) {
            // Extract number and increment
            $lastNumber = (int) substr($lastContract->contract_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Determine initial contract status based on start date
     */
    private function determineStatus(?string $startDate): string
    {
        if (!$startDate) {
            return 'ACTIVE';
        }

        $start = \Carbon\Carbon::parse($startDate);
        
        if ($start->isFuture()) {
            return 'PENDING';
        }

        return 'ACTIVE';
    }
}
